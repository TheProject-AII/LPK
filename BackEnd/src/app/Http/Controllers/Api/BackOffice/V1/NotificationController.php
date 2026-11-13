<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use Attribute;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Notifications\NotificationQuery;
use App\JsonApi\BackOffice\V1\Notifications\NotificationRequest;
use App\JsonApi\BackOffice\V1\Notifications\NotificationSchema;
// use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Media;
use App\Models\Notification;
use App\Models\NotificationStudent;
use App\Models\NotificationContent;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Log; // Hanya perlu satu kali di sini
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store;

class NotificationController extends Controller
{
  use Actions\FetchMany;
  use Actions\FetchOne;
  use Actions\Store;
  use Actions\Update;
  use Actions\Destroy;
  use Actions\FetchRelated;
  use Actions\FetchRelationship;
  use Actions\UpdateRelationship;
  use Actions\AttachRelationship;
  use Actions\DetachRelationship;


  /**
   * Create a new resource.
   *
   * @param NotificationSchema $schema
   * @param NotificationRequest $request
   * @param NotificationQuery $query
   * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
   */

  public function store(NotificationSchema $schema, NotificationRequest $request, NotificationQuery $query)
  {

    $notification = new Notification;
    $notification->notification_type = $request->data['attributes']['notificationType'];
    $notification->notification_at = $request->data['attributes']['notificationAt'];
    $notification->notification_subject = $request->data['attributes']['notificationSubject'];
    $notification->notification_information = $request->data['attributes']['notificationInformation'];
    $notification->lpk_id = $request->data['attributes']['notificationLpk'];
    $notification->created_by = auth()->user()->id;
    $notification->save();

    
    if(isset($request->data['attributes']['notificationImage'])) {
            try {
                $extension = self::getExtension($request->data['attributes']['notificationImage']);
            } catch (\Exception $exception) {
                $extension = '';
            }
      $filename = Str::random() . $extension;
      $notification->addMediaFromBase64($request->data['attributes']['notificationImage'], ['image/webp', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'])
      ->usingFileName($filename)->toMediaCollection('notificationImage');
    }

    $notificationContent = new NotificationContent;
    $notificationContent->notification_id = $notification->id;
    $notificationContent->content = $request->data['attributes']['notificationContent'];
    $notificationContent->save();

    foreach ($request->data['attributes']['notificationStudent'] as $value) {
      $notificationStudent = new NotificationStudent;
      $notificationStudent->notification_id = $notification->id;
      $notificationStudent->student_id = $value;
      $notificationStudent->save();
    }

    foreach ($request->data['attributes']['notificationAttachment'] as $value) {
      
      $notification->addMediaFromBase64($value['base64'], ['image/webp', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf','application/x-rar','application/zip'])
      ->usingFileName($value['name'])->toMediaCollection('notificationAttachment');
      
    }

    return new DataResponse($notification);

  }

  public function update(NotificationSchema $schema, NotificationRequest $request, NotificationQuery $query)
  {

    // Log::info($schema);
    //   $oldLpk = Lpk::where('id', $request->data['id'])->first();
    //   $oldLpk->lpk_no = $request->data['attributes']['lpkNo'];
    //   $oldLpk->lpk_name = $request->data['attributes']['lpkName'];
    //   $oldLpk->lpk_business_type = $request->data['attributes']['lpkBusinessType'];
    //   $oldLpk->lpk_phone_no = $request->data['attributes']['lpkPhoneNo'];
    //   $oldLpk->lpk_email = $request->data['attributes']['lpkEmail'];
    //   $oldLpk->lpk_address = $request->data['attributes']['lpkAddress'];
    //   $oldLpk->lpk_contact_person = $request->data['attributes']['lpkContactPerson'];
    //   $oldLpk->lpk_title = $request->data['attributes']['lpkTitle'];
    //   $oldLpk->save();
    //   return new DataResponse($oldLpk);

    
    $notification = Notification::find($request->data['id']);
    $notification->notification_type = $request->data['attributes']['notificationType'];
    $notification->notification_at = $request->data['attributes']['notificationAt'];
    $notification->notification_subject = $request->data['attributes']['notificationSubject'];
    $notification->notification_information = $request->data['attributes']['notificationInformation'];
    $notification->lpk_id = $request->data['attributes']['notificationLpk'];
    $notification->save();
    
    if(isset($request->data['attributes']['notificationImage'])) {
      try {
          $extension = self::getExtension($request->data['attributes']['notificationImage']);
      } catch (\Exception $exception) {
          $extension = '';
      }
      $filename = Str::random() . $extension;
      $notification->addMediaFromBase64($request->data['attributes']['notificationImage'], ['image/webp', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'])
      ->usingFileName($filename)->toMediaCollection('notificationImage');
      }

    
    $notificationContent = NotificationContent::find($notification->notificationContent->id);
    if(!$notificationContent){
      $notificationContent = new NotificationContent;
    }
    $notificationContent->notification_id = $notification->id;
    $notificationContent->content = $request->data['attributes']['notificationContent'];
    $notificationContent->save();

    

    $listIdStudent = array();
    foreach ($request->data['attributes']['notificationStudent'] as $value) {
      
      if(!isset($value["id"])) {
        
        $notificationStudent = new NotificationStudent;
        $notificationStudent->notification_id = $notification->id;
        $notificationStudent->student_id = $value;
        $notificationStudent->save();
        array_push($listIdStudent, $notificationStudent->id);
      }
      else{
        
        array_push($listIdStudent, $value["id"]);
      }
    }
    if (empty($listIdStudent)) {
        NotificationStudent::where('notification_id', $notification->id)->delete();
    } else {
        $listIdStudentNotfound = NotificationStudent::where('notification_id',$notification->id)->whereNotIn('id', $listIdStudent)->get('id');
        NotificationStudent::destroy($listIdStudentNotfound);
    }

    $listIdAttachment = array();
    foreach ($request->data['attributes']['notificationAttachment'] as $value) {
      
      if(!isset($value["id"])) {

       $media =  $notification->addMediaFromBase64($value['base64'], ['image/webp', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf','application/x-rar','application/zip'])
        ->usingFileName($value['name'])->toMediaCollection('notificationAttachment');
    
        array_push($listIdAttachment, $media->id);
      }
      else{
        array_push($listIdAttachment, $value["id"]);
      }
      
    }
    if (empty($listIdAttachment)) {

      $listIdAttachment = Media::where('model_id', $notification->id)->where('collection_name','notificationAttachment')->select('id')->get();
      
      foreach ($listIdAttachment as $value) {
          // Log::info($value->id);
        $notification->deleteMedia($value->id);
        
       
        
      }
      
    
    } else {
        $listIdAttachmentNotfound = Media::where('model_id',$notification->id)->whereNotIn('id', $listIdAttachment)->where('collection_name','notificationAttachment')->select('id')->get();
        // Media::destroy($listIdAttachmentNotfound);
        foreach ($listIdAttachmentNotfound as $value) {
          
          $notification->deleteMedia($value->id);
          
         
          
        }
        
    }
    $notification = Notification::find($notification->id);
    return new DataResponse($notification);

  }

    /**
     * content for a given id.
     */
    public function content(NotificationSchema $schema, Notification $notification)

    {
        return new DataResponse($notification->notificationContent);
    }

    /**
     * student for a given id.
     */
    public function student(NotificationSchema $schema, Notification $notification)

    {
        return new DataResponse($notification->notificationStudent);
    }

    
    /**
     * @param string $base64EncodedString
     * @return string
     */
    public static function getExtension(string $base64EncodedString): string
    {
        if (str_contains($base64EncodedString, 'base64')) {
            $extension = explode(";", explode("/", $base64EncodedString)[1])[0];
            switch ($extension) {
                case 'image/jpeg':
                case 'jpeg':
                    return ".jpeg";
                case 'image/jpg':
                case 'jpg':
                    return ".jpg";
                case 'image/png':
                case 'png':
                    return '.png';
                case 'image/gif':
                case 'gif':
                    return '.gif';
                case 'image/webp':
                case 'webp':
                    return '.webp';
                default:
                    return "";
            }
        } else {
            return self::detectMimeType($base64EncodedString);
        }
    }

    /**
     * @param string $base64
     * @return string
     */
    static function detectMimeType(string $base64)
    {
        $signaturesForBase64 = [
            // 'JVBERi0'     => "application/pdf",
            // 'R0lGODdh'    => "image/gif",
            // 'R0lGODlh'    => "image/gif",
            // 'iVBORw0KGgo' => "image/png",
            // '/9j/'        => "image/jpeg"
            'JVBERi0'     => ".pdf",
            'R0lGODdh'    => ".gif",
            'R0lGODlh'    => ".gif",
            'iVBORw0KGgo' => ".png",
            '/9j/'        => ".jpeg",
            'UklGR'       => ".webp",

        ];

        foreach ($signaturesForBase64 as $sign => $mimeType) {
            if (strpos($base64, $sign) === 0) {
                return $mimeType;
            }
        }
        return false;
    }


}
