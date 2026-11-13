<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Roles\RoleQuery;
use App\JsonApi\BackOffice\V1\Roles\RoleRequest;
use App\JsonApi\BackOffice\V1\Roles\RoleSchema;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Responses\DataResponse;
use App\JsonApi\BackOffice\V1\Roles\RoleCollectionQuery;

class RoleController extends Controller
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




    // /**
    //  * Fetch zero to many JSON API resources.
    //  *
    //  * @param RoleSchema $schema
    //  * @param RoleQuery $request
    //  * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
    //  */
    // public function index(RoleSchema $schema, RoleQuery $request)
    // {
    //         $models = $schema
    //         ->repository()
    //         ->queryAll()
    //         ->withRequest($request)
    //         ->firstOrPaginate($request->page());




    // return new DataResponse($models);
    // }



    /**
     * Create a new resource.
     *
     * @param RoleSchema $schema
     * @param RoleRequest $request
     * @param RoleQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function store(RoleSchema $schema, RoleRequest $request, RoleQuery $query)
    {

        if(auth()->user()->lpk_id){
            $lpk_id = auth()->user()->lpk_id;
        }
        else{
            $lpk_id = $request->data['attributes']['roleLpk']?? null;
        }

        $role = Role::create(['name' => $request->data['attributes']['name'],'lpk_id' => $lpk_id]);

        if($request->data['attributes']['permissions']){
            foreach ($request->data['attributes']['permissions'] as $key => $value) {
                foreach ($value as $permission) {
                    if(Permission::where('name',$permission)->exists()){
                        $role->givePermissionTo($permission);
                    }
                }

            }
        }

        return new DataResponse($role);
    }


    /**
     * Update a resource.
     *
     * @param RoleSchema $schema
     * @param RoleRequest $request
     * @param RoleQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function Update(RoleSchema $schema, RoleRequest $request, Role $role)
    {

        if(auth()->user()->lpk_id){
            $lpk_id = auth()->user()->lpk_id;
        }
        else{
            $lpk_id = $request->data['attributes']['roleLpk']?? null;
        }

        $role->lpk_id = $lpk_id;
        $role->name = $request->data['attributes']['name'];
        $role->save();

        $role->syncPermissions();
        if($request->data['attributes']['permissions']){
            foreach ($request->data['attributes']['permissions'] as $key => $value) {
                foreach ($value as $permission) {
                    if(Permission::where('name',$permission)->exists()){
                        $role->givePermissionTo($permission);
                    }
                }

            }
        }



        return new DataResponse($role);
    }

}
