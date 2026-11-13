<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use Attribute;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Lpks\LpkQuery;
use App\JsonApi\BackOffice\V1\Lpks\LpkRequest;
use App\JsonApi\BackOffice\V1\Lpks\LpkRequestUpdateStatus;
use App\JsonApi\BackOffice\V1\Lpks\LpkSchema;
use App\Models\Lpk;
use App\Models\Student;
use App\Models\LpkCategory;
use App\Models\User;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Log; // Hanya perlu satu kali di sini
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store;

class LpkController extends Controller
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
   * @param LpkSchema $schema
   * @param LpkRequest $request
   * @param LpkQuery $query
   * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
   */

  public function store(LpkSchema $schema, LpkRequest $request, LpkQuery $query)
  {

    // Log::info($request->data);

    $lpk = new Lpk;
    // $lpk->lpk_no = $request->data['attributes']['lpkNo'];
    $lpk->lpk_name = $request->data['attributes']['lpkName'];
    // $lpk->lpk_business_type = $request->data['attributes']['lpkBusinessType'];
    $lpk->lpk_phone_no = $request->data['attributes']['lpkPhoneNo'];
    $lpk->lpk_email = $request->data['attributes']['lpkEmail'];
    $lpk->lpk_address = $request->data['attributes']['lpkAddress'];
    $lpk->lpk_bank_account_name = $request->data['attributes']['lpkBankAccountName'];
    $lpk->lpk_bank_account_number = $request->data['attributes']['lpkBankAccountNumber'];
    $lpk->lpk_bank_branch = $request->data['attributes']['lpkBankBranch'];
    $lpk->lpk_contact_person_phone_no = $request->data['attributes']['lpkContactPersonPhoneNo'];
    $lpk->lpk_contact_person_position = $request->data['attributes']['lpkContactPersonOccupation'];
    $lpk->lpk_contact_person_identity_no = $request->data['attributes']['lpkContactPersonIdentityNo'];
    $lpk->lpk_contact_person_passport_no = $request->data['attributes']['lpkContactPersonPassportNo'];
    $lpk->lpk_contact_person_nationality = $request->data['attributes']['lpkContactPersonNationality'];
    $lpk->lpk_contact_person_occupation = $request->data['attributes']['lpkContactPersonOccupation'];
    $lpk->lpk_contact_person_address = $request->data['attributes']['lpkContactPersonAddress'];
    $lpk->lpk_contact_person_date_of_birth = $request->data['attributes']['lpkContactPersonDateOfBirth'];
    $lpk->lpk_contact_person_place_of_birth = $request->data['attributes']['lpkContactPersonPlaceOfBirth'];
    $lpk->lpk_contact_person_name = $request->data['attributes']['lpkContactPersonName'];
    $lpk->save();

    $user = new User;
    $user->lpk_id = $lpk->id;
    $user->name = 'admin ' . $request->data['attributes']['lpkName'];
    $user->email = $request->data['attributes']['lpkEmail'];
    $user->password = $request->data['attributes']['password'];
    $user->timezone = 'Asia/Jakarta';
    $user->active= 0;
    $user->save();
    $user->assignRole('admin lpk');

    foreach ($request->data['attributes']['lpkCategory'] as $value) {
        $lpkCategory = new LpkCategory;
        $lpkCategory->lpk_category_lpk_id = $lpk->id;
        $lpkCategory->lpk_category_id = $value;
        $lpkCategory->save();
      }

    return new DataResponse($lpk);

    // return $request;
    // return new DataResponse($student);
  }

  public function update(LpkSchema $schema, LpkRequest $request, LpkQuery $query)
  {
    // Log::info($request->data);
      $oldLpk = Lpk::where('id', $request->data['id'])->first();
      $oldLpk->lpk_no = $request->data['attributes']['lpkNo'];
      $oldLpk->lpk_name = $request->data['attributes']['lpkName'];
    //   $oldLpk->lpk_business_type = $request->data['attributes']['lpkBusinessType'];
      $oldLpk->lpk_phone_no = $request->data['attributes']['lpkPhoneNo'];
      $oldLpk->lpk_email = $request->data['attributes']['lpkEmail'];
      $oldLpk->lpk_address = $request->data['attributes']['lpkAddress'];
      $oldLpk->lpk_bank_account_name = $request->data['attributes']['lpkBankAccountName'];
      $oldLpk->lpk_bank_account_number = $request->data['attributes']['lpkBankAccountNumber'];
      $oldLpk->lpk_bank_branch = $request->data['attributes']['lpkBankBranch'];
      $oldLpk->lpk_contact_person_phone_no = $request->data['attributes']['lpkContactPersonPhoneNo'];
        $oldLpk->lpk_contact_person_position = $request->data['attributes']['lpkContactPersonOccupation'];
        $oldLpk->lpk_contact_person_identity_no = $request->data['attributes']['lpkContactPersonIdentityNo'];
        $oldLpk->lpk_contact_person_passport_no = $request->data['attributes']['lpkContactPersonPassportNo'];
        $oldLpk->lpk_contact_person_nationality = $request->data['attributes']['lpkContactPersonNationality'];
        $oldLpk->lpk_contact_person_occupation = $request->data['attributes']['lpkContactPersonOccupation'];
        $oldLpk->lpk_contact_person_address = $request->data['attributes']['lpkContactPersonAddress'];
        $oldLpk->lpk_contact_person_date_of_birth = $request->data['attributes']['lpkContactPersonDateOfBirth'];
        $oldLpk->lpk_contact_person_place_of_birth = $request->data['attributes']['lpkContactPersonPlaceOfBirth'];
        $oldLpk->lpk_contact_person_name = $request->data['attributes']['lpkContactPersonName'];
      $oldLpk->save();


    $listIdLpkCategory = array();
    foreach ($request->data['attributes']['lpkCategory'] as $value) {

      if(!isset($value["id"])) {

        $lpkCategory = new lpkCategory;
        $lpkCategory->lpk_category_lpk_id = $oldLpk->id;
        $lpkCategory->lpk_category_id = $value;
        $lpkCategory->save();
        array_push($listIdLpkCategory, $lpkCategory->id);
      }
      else{

        array_push($listIdLpkCategory, $value["id"]);
      }
    }
    if (empty($listIdLpkCategory)) {
        lpkCategory::where('lpk_category_lpk_id', $oldLpk->id)->delete();
    } else {
        $listIdLpkCategoryNotfound = lpkCategory::where('lpk_category_lpk_id',$oldLpk->id)->whereNotIn('id', $listIdLpkCategory)->get('id');
        lpkCategory::destroy($listIdLpkCategoryNotfound);
    }

      return new DataResponse($oldLpk);
  }


   /**
     * update status lpk for a given id.
     */
    public function updateStatus(LpkSchema $schema, LpkRequestUpdateStatus $request, Lpk $lpk)
    {
        $this->authorize('update lpks',$lpk);
        $oldActiver = $lpk->active;
        $lpk->active = $request->data['attributes']['desireStatus'];
        $lpk->save();
        User::where('lpk_id', $lpk->id)->update(['active' => $request->data['attributes']['desireStatus']]);
        //delete all token user if set to inactive
        if($oldActiver == 1 && $request->data['attributes']['desireStatus'] == 0){
            $user = User::where('lpk_id',$lpk->id)->get();
            $student = Student::where('lpk_id',$lpk->id)->get();
            foreach($user as $value){
              $value->tokens()->delete();
            }
            foreach($student as $value){
              $value->tokens()->delete();
            }
        }
        return new DataResponse($lpk);
    }
}
