<?php

namespace App\Http\Controllers\Api\BackOffice\V1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

// use App\Models\Student;
// use App\Models\User;
// use Carbon\Carbon;
// use Illuminate\Http\Request;

class ReportSummaryController extends Controller
{
    // use Actions\FetchMany;
    // use Actions\FetchOne;
    // use Actions\Store;
    // use Actions\Update;
    // use Actions\Destroy;
    // use Actions\FetchRelated;
    // use Actions\FetchRelationship;
    // use Actions\UpdateRelationship;
    // use Actions\AttachRelationship;
    // use Actions\DetachRelationship;


    /**
     * Create a new resource.
     *
     * @param StudentSchema $schema
     * @param StudentRequest $request
     * @param StudentQuery $query
     * @param  string  $mediaCollectionName
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */


    public function GetStudentStatus(Request $request ) {

        return response()->json(['message' => 'Report summary']);
        // Validate the filters
        // $validated = $request->validate([
        //     'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
        // ]);

        //  // Retrieve validated filter parameters
        //  $date = $validated['date'] ?? null;

        //  if ($date) {
        //      // If a date is provided, parse it to get the start and end of the month
        //      $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
        //      $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();
        //  } else {
        //      // If no date is provided, use the current month (default)
        //      $startOfMonth = Carbon::now()->startOfMonth(); // First day of the current month
        //      $endOfMonth = Carbon::now()->endOfMonth();     // Last day of the current month
        //  }





    }


}




// class ReportSummaryController extends Controller{

    //status
    // 1 : registered
    // 2 : waiting approval
    // 3 : Rejected
    // 4 : waiting contract
    // 5 : Active
    // 6 : Complete

    // public function GetStudentStatus(Request $request ) {

    //     dd($request);
    //     // Validate the filters
    //     $validated = $request->validate([
    //         'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
    //     ]);

    //      // Retrieve validated filter parameters
    //      $date = $validated['date'] ?? null;

    //      if ($date) {
    //          // If a date is provided, parse it to get the start and end of the month
    //          $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
    //          $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();
    //      } else {
    //          // If no date is provided, use the current month (default)
    //          $startOfMonth = Carbon::now()->startOfMonth(); // First day of the current month
    //          $endOfMonth = Carbon::now()->endOfMonth();     // Last day of the current month
    //      }





    // }


// }
