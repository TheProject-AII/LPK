<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\Models\User;
use App\Models\Role;
use App\JsonApi\BackOffice\V1\Users\UserRequest;
use App\JsonApi\BackOffice\V1\Users\UserRequestUpdateStatus;
use App\JsonApi\BackOffice\V1\Users\UserSchema;
use Carbon\Carbon;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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
     *
     *
    * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function profile(Request $request)
    {

        $data = auth()->user();

        return new DataResponse($data);
    }


     /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function UpdateProfile(Request $request)
    {
        $user = $request->user();
        $login = $request->validate([
            'data.attributes.current_password' => ['required_unless:data.attributes.password,!=,null', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password) === 1) {
                    return $fail(__('The current password is incorrect.'));
                }
            }],
            'data.attributes.password'  => 'required_unless:data.attributes.current_password,!=,null|same:data.attributes.password_confirmation',
            'data.attributes.password_confirmation'  => 'required_unless:data.attributes.password,!=,null',

        ],
        [

            'data.attributes.current_password.required_unless'=> 'The current password field is required',
            'data.attributes.password.required_unless'=> 'The password field is required',
            'data.attributes.password_confirmation.required_unless'=> 'The password confirmation field is required',
            'data.attributes.password.same'=> 'The password must match password confirmation.',
        ]);

        $user->name= $request->data['attributes']['name'];
        if(isset($request->data['attributes']['current_password']) && isset($request->data['attributes']['password'])){
           $user->password= Hash::make($request->data['attributes']['password']);
        }
        $user->save();

        // return DataResponse::make($user)->withServer('v1');
        return new DataResponse($user);
    }




     /**
     * Create a new resource.
     *
     * @param UserSchema $schema
     * @param UserRequest $request
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function store(UserSchema $schema, UserRequest $request)
    {

        $user = User::create([
            'name'=> $request->data['attributes']['name'],
            'lpk_id'=> $request->data['attributes']['userLpk'],
            'email'=> $request->data['attributes']['email'],
            'password'=> Hash::make($request->data['attributes']['password']),
            'timezone'=> 'Asia/Jakarta',
            'active'=> 0
        ]);

        foreach ($request->data['attributes']['roles'] as $value) {
            $role = Role::find($value);
            $user->assignRole($role->name);
        }

        return new DataResponse($user);
    }


    /**
     * Create a new resource.
     *
     * @param UserSchema $schema
     * @param UserRequest $request
     * @param UserQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function Update(UserSchema $schema, UserRequest $request, User $user)
    {

        if(isset($request->data['attributes']['name'])){
          $user->name= $request->data['attributes']['name'];
        }
        if(isset($request->data['attributes']['email'])){
            $user->email= $request->data['attributes']['email'];
          }

          if(isset($request->data['attributes']['userLpk'])){
            $user->lpk_id= $request->data['attributes']['userLpk'];
          }

        if(isset($request->data['attributes']['active'])){
            $user->active= $request->data['attributes']['active'];
         }

        if(isset($request->data['attributes']['password'])){
           $user->password= Hash::make($request->data['attributes']['password']);
        }
        $user->save();
        if(isset($request->data['attributes']['roles'])){
            $user->syncRoles();
            foreach ($request->data['attributes']['roles'] as $value) {
                $role = Role::find($value);
                $user->assignRole($role->name);
            }
        }

        return new DataResponse($user);
    }


     /**
     * update status user for a given id.
     */
    public function updateStatus(UserSchema $schema, UserRequestUpdateStatus $request, User $user)
    {
        $this->authorize('update users',$user);
        $oldActiver = $user->active;
        $user->active = $request->data['attributes']['desireStatus'];
        $user->save();
        //delete all token user if set to inactive
        if($oldActiver == 1 && $request->data['attributes']['desireStatus'] == 0){
            $user->tokens()->delete();
        }
        return new DataResponse($user);
    }

    
     /**
     * reset-2fa status user for a given id.
     */
    public function reset2fa(UserSchema $schema, User $user)
    {
        $this->authorize('update users');
        $user->google_two_fa_secret = null;
        $user->save();
        return new DataResponse($user);
    }

}
