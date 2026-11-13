<?php

namespace App\Http\Controllers\Api\Public\V1;

use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Illuminate\Support\Facades\Log;
use App\Models\Lpk;

use App\Http\Controllers\Controller;
use App\JsonApi\Public\v1\Lpks\LpkQuery;
use App\JsonApi\Public\v1\Lpks\LpkRequest;
use App\JsonApi\Public\v1\Lpks\LpkSchema;
use Illuminate\Http\Request;


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
  public function store(Request $request)
  {
    // Log::info($request->data);
    $lpk = new Lpk;
    $lpk->lpk_name = $request['lpkName'];
    $lpk->lpk_business_type = $request['lpkBusinessType'];
    $lpk->lpk_phone_no = $request['lpkPhoneNo'];
    $lpk->lpk_email = $request['lpkEmail'];
    $lpk->lpk_address = $request['lpkAddress'];
    $lpk->lpk_contact_person = $request['lpkContactPerson'];
    $lpk->lpk_title = $request['lpkTitle'];

    $dataResponse = $lpk->save();
    $return['status'] = 'Success';
    $return['message'] = 'data success submite';
    $return['result'] = $dataResponse;
    return response()->json($return,201);
  }

  public function storeBak(LpkSchema $schema, LpkRequest $request, LpkQuery $query)
  {

    // Log::info($request->data);

    $lpk = new Lpk;
    $lpk->lpk_name = $request->data['attributes']['lpkName'];
    $lpk->lpk_business_type = $request->data['attributes']['lpkBusinessType'];
    $lpk->lpk_phone_no = $request->data['attributes']['lpkPhoneNo'];
    $lpk->lpk_email = $request->data['attributes']['lpkEmail'];
    $lpk->lpk_address = $request->data['attributes']['lpkAddress'];
    $lpk->lpk_contact_person = $request->data['attributes']['lpkContactPerson'];
    $lpk->lpk_title = $request->data['attributes']['lpkTitle'];
    $lpk->save();

    return new DataResponse($lpk);
  }

  public function update(LpkSchema $schema, LpkRequest $request, LpkQuery $query)
  {
    // Log::info($request->data);
    $oldLpk = Lpk::where('id', $request->data['id'])->first();
    $oldLpk->lpk_no = $request->data['attributes']['lpkNo'];
    $oldLpk->lpk_name = $request->data['attributes']['lpkName'];
    $oldLpk->lpk_business_type = $request->data['attributes']['lpkBusinessType'];
    $oldLpk->lpk_phone_no = $request->data['attributes']['lpkPhoneNo'];
    $oldLpk->lpk_email = $request->data['attributes']['lpkEmail'];
    $oldLpk->lpk_address = $request->data['attributes']['lpkAddress'];
    $oldLpk->lpk_contact_person = $request->data['attributes']['lpkContactPerson'];
    $oldLpk->lpk_title = $request->data['attributes']['lpkTitle'];
    $oldLpk->save();
    return new DataResponse($oldLpk);
  }

}
