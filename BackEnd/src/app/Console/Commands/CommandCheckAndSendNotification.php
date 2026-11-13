<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Notification as dataNotification;
use App\Models\NotificationStudent;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\FcmOptions;
use App\Helpers\NotificationContentRenderHelper;

class CommandCheckAndSendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-and-send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check And Send Notification To Mobile Using FCM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      // Log::info('run app:check-and-send-notification');
     
      // Create a Carbon instance for the current time
      $now = Carbon::now();

      // Set the seconds to 00
      $now->second(0);

      // Log::info('Now : '.$now->format('Y-m-d H:i:s'));
      // $now = $now->format('Y-m-d H:i:s');

        // $dataNotification = dataNotification::where('notification_at','2023-02-01 17:10:00')
        // ->where('status',0)->get();
        
        $dataNotification = dataNotification::where('status',0)->get();
        foreach ($dataNotification as $valueDataNotification) {

            $notificationAt = $valueDataNotification->notification_at;

            if($valueDataNotification->notificationCreatedBy->timezone){
                // Create a Carbon instance for the specified date and time in user timezone
                $dateIn = Carbon::createFromFormat('Y-m-d H:i:s', $notificationAt, $valueDataNotification->notificationCreatedBy->timezone);

                // Change the timezone to UTC
                $dateInUTC = $dateIn->setTimezone('UTC');

                // set the modified time in UTC
                // $notificationAt = $dateInUTC->format('Y-m-d H:i:s')
                
              }

              // Log::info('Schedule : '.$dateInUTC->format('Y-m-d H:i:s'));
            
            // if($now->eq($dateInUTC)){
            if($dateInUTC->format('Y-m-d H:i:s') == $now->format('Y-m-d H:i:s')){
              // Log::info('true');
              // if($valueDataNotification){

              // Extract the fcm_token values as an array
              $fcmTokens = $valueDataNotification->notificationStudent->map(function ($item) {
                return $item['student']['fcm_token'];
              })->toArray();

              // Log::info($fcmTokens);

              $listNotificationStudent = [];
              foreach ($valueDataNotification->notificationStudent as $item) {
                  $listNotificationStudent[] = [
                      'id' => $item['id'],
                      'fcm_token' => $item['student']['fcm_token']
                  ];
              }

              // Log::info($listNotificationStudent);
              $messaging = app('firebase.messaging');
            
              // $title = 'My Notification Title';
              // $body = 'My Notification Body';
              // $imageUrl = 'https://picsum.photos/400/200';

              $notification = Notification::fromArray([
                  'title' => $valueDataNotification->notification_subject,
                  'body' => $valueDataNotification->notification_information,
                  'image' => $valueDataNotification->notification_image_file,
              ]);

              Log::info("notification_image");
              Log::info($valueDataNotification->notification_image_file);
              
              $data = [
                  'id'  => $valueDataNotification->id,
                  'category'  => $valueDataNotification->notification_type,
                  'content' => $valueDataNotification->notificationContent?NotificationContentRenderHelper::renderContent($valueDataNotification->notificationContent->content):null
              ];
              // Log::info($valueDataNotification->notificationContent->content);
              

          
              //   $deviceToken = 'cIzQgRRxQa-2BiXjoVBtE2:APA91bE_MeWx4CXW5HvJwUAWReNe4q6kwxcC68YuKqQK6LeRs3nssKuXcdq8_zsiRcVCPxvqZCGP33X43QwuxRZLpxZFKEWeInVy11FNCSozvdx6hHddJQ9JEXkHeNJuhxsx7VW5peq4';
              //   $deviceToken2 = ['cIzQgRRxQa-2BiXjoVBtE2:APA91bE_MeWx4CXW5HvJwUAWReNe4q6kwxcC68YuKqQK6LeRs3nssKuXcdq8_zsiRcVCPxvqZCGP33X43QwuxRZLpxZFKEWeInVy11FNCSozvdx6hHddJQ9JEXkHeNJuhxsx7VW5peq4','cIzQgRRxQa-2BiXjoVBtE2:APA91bE_MeWx4CXW5HvJwUAWReNe4q6kwxcC68YuKqQK6LeRs3nssKuXcdq8_zsiRcVCPxvqZCGP33X43QwuxRZLpxZFKEWeInVy11FNCSozvdx6hHddJQ9JEXkHeNJuhxsx7VW5peq4'];
              

              // $message = CloudMessage::withTarget('token', $fcmTokens)
              //     ->withNotification($notification)
              //     ->withData( $data);
              
              // $respon = $messaging->send($message);

              // $fcmTokens = ['1','2'];
              $message = CloudMessage::new()->withNotification($notification)
              ->withData( $data);

              $report = $messaging->sendMulticast($message, $fcmTokens);
                  
              // The following methods return arrays with registration token strings
              $successfulTargets = $report->validTokens(); // string[]

              // Unknown tokens are tokens that are valid but not know to the currently
              // used Firebase project. This can, for example, happen when you are
              // sending from a project on a staging environment to tokens in a
              // production environment
              $unknownTargets = $report->unknownTokens(); // string[]

              // Invalid (=malformed) tokens
              $invalidTargets = $report->invalidTokens(); // string[]

              Log::info('$successfulTargets : '.implode( ", ", $successfulTargets));
              // Log::info('$unknownTargets : '.implode( ", ", $unknownTargets));
              // Log::info('$invalidTargets : '.implode( ", ", $invalidTargets));

              foreach ($listNotificationStudent as $item) {
                if (in_array($item['fcm_token'], $successfulTargets)) {
                    // Log::info('invalid : '. $item['id']);
                    $notificationStudent = NotificationStudent::find($item['id']);
                    $notificationStudent->status_send = 1;
                    $notificationStudent->save();
                }
              }

              // Log::info($valueDataNotification->id);
              $notifSendCount = NotificationStudent::where('notification_id',$valueDataNotification->id)
              ->where('status_send',1)->count();
              $notifTotalCount= NotificationStudent::where('notification_id',$valueDataNotification->id)->count();
              // Log::info($notifSendCount);
              // Log::info($notifTotalCount);
              if($notifSendCount == $notifTotalCount){
                $dataNotificationUpdate = dataNotification::find($valueDataNotification->id);
                $dataNotificationUpdate->status = 1;
                $dataNotificationUpdate->save();
              }
            }
            else{
              // Log::info('false');

            }
        }
        
    }


     


     

}

