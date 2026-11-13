<?php

namespace App\Http\Controllers\Api\BackOffice\V1;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportSummaryController extends Controller
{

    //status
    // 1 : registered
    // 2 : waiting approval
    // 3 : Rejected
    // 4 : waiting contract
    // 5 : Active
    // 6 : Complete

    function GetStudentStatus(Request $request ) {
        // Validate the filters
        $validated = $request->validate([
            'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
        ]);

         // Retrieve validated filter parameters
         $date = $validated['date'] ?? null;
         if ($date) {
             // If a date is provided, parse it to get the start and end of the month
             $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
             $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();
         } else {
             // If no date is provided, use the current month (default)
             $startOfMonth = Carbon::now()->startOfMonth(); // First day of the current month
             $endOfMonth = Carbon::now()->endOfMonth();     // Last day of the current month
         }

         $statusCounts = Student::select('status', \DB::raw('count(*) as total'))
                        ->groupBy('status')
                        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->pluck('total', 'status');

        // Mapping status berdasarkan ID yang diberikan
        $statusLabels = [
            1 => 'registered',
            2 => 'waiting_approval',
            3 => 'Rejected',
            4 => 'waiting_contract',
            5 => 'Active',
            6 => 'Complete'
        ];

        // Format hasil output
        $result = [];
        foreach ($statusLabels as $key => $label) {
            $result[$label] = $statusCounts[$key] ?? 0; // Jika status tidak ada, default 0
        }
        return response()->json($result);
    }

    function getRegisterYearly(Request $request) {
        // Validate the filters
        $validated = $request->validate([
            'year' => 'nullable|date_format:Y', // Validates the date format (YYYY)
        ]);

        // Retrieve validated filter parameters
        $date = $validated['year'] ?? null;
        if ($date) {
            $currentYear = Carbon::createFromFormat('Y', $date)->year;
        } else {
            $currentYear = Carbon::now()->year;
        }

        // Ambil data yang sesuai dengan tahun tertentu
        $students = StudentStatus::whereYear('created_at', $currentYear)
            ->select(
                DB::raw("MONTH(created_at) as month"), // Ambil nomor bulan
                DB::raw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as register"), // Hitung jumlah siswa dengan status 1
                DB::raw("SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as contract"), // Hitung jumlah siswa dengan status 5
               // DB::raw("COUNT(*) as total")
               // Hitung total semua siswa dalam bulan tersebut
            )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->orderBy(DB::raw("MONTH(created_at)"), 'ASC')
            ->get();

        // Daftar nama bulan dalam bahasa Indonesia
        $months = [
            1 => "January", 2 => "February", 3 => "Maret", 4 => "April",
            5 => "May", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
        ];

        // Inisialisasi hasil dengan semua bulan bernilai 0
        $monthlyData = [];
        foreach ($months as $num => $name) {
            $monthlyData["$name $currentYear"] = [
                "register" => 0,
                "contract" => 0
            ];
        }

        // Mengisi hasil berdasarkan data yang diambil
        foreach ($students as $data) {
            $monthName = $months[$data->month] . " $currentYear";
            $monthlyData[$monthName] = [
                "register" => $data->register,
                "contract" => $data->contract
            ];
        }

        return response()->json($monthlyData);
    }

    function getRegisterWeekly(Request $request) {
        // Validate the filters
        $validated = $request->validate([
            'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
        ]);
        // Retrieve validated filter parameters
        $date = $validated['date'] ?? Carbon::now()->format('Y-m');
        // Parse given date to get start and end of the month
        $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();
        // Inisialisasi array untuk hasil mingguan
        $weeklyData = [];
        // Loop untuk membagi bulan menjadi 4 minggu tetap
        for ($week = 0; $week < 4; $week++) {
            $startWeek = $startOfMonth->copy()->addDays($week * 7);
            $endWeek = $startWeek->copy()->addDays(6);
            // Pastikan tidak melebihi akhir bulan
            if ($endWeek->greaterThan($endOfMonth)) {
                $endWeek = $endOfMonth;
            }
            $students = Student::whereBetween('created_at', [$startWeek, $endWeek])
                ->select(
                    DB::raw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as register"), // Hitung siswa dengan status 1
                    DB::raw("SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as contract")  // Hitung siswa dengan status 5
                )
                ->first();
            $weekRange = $startWeek->format('d-m-Y') . " - " . $endWeek->format('d-m-Y');
            $weeklyData[$weekRange] = [
                "register" => $students->register ?? 0,
                "contract" => $students->contract ?? 0
            ];
        }
        return response()->json($weeklyData);
    }

    function getPaymentYearly(Request $request) {

        // Validate the filters
        $validated = $request->validate([
            'year' => 'nullable|date_format:Y', // Validates the date format (YYYY)
        ]);

        // Retrieve validated filter parameters
        $date = $validated['year'] ?? null;
        if ($date) {
            $currentYear = Carbon::createFromFormat('Y', $date)->year;
        } else {
            $currentYear = Carbon::now()->year;
        }

        $count_sql = DB::table(function ($query) use ($currentYear) {
            $query->select(
                    DB::raw("MONTH(created_at) as month"),
                    DB::raw("SUM(payment_receive_amount) as paid"),
                    DB::raw("0 as estimate_paid") // Default 0 untuk data dari Payment
                )
                ->from('payments')
                ->whereYear('created_at', $currentYear)
                ->groupBy(DB::raw("MONTH(created_at)"))

                ->unionAll(
                    DB::table('installments')
                        ->select(
                            DB::raw("MONTH(installment_due_date) as month"),
                            DB::raw("0 as paid"), // Default 0 untuk data dari Installment
                            DB::raw("SUM(installment_monthly_installment) as estimate_paid")
                        )
                        ->whereYear('installment_due_date', $currentYear)
                        ->groupBy(DB::raw("MONTH(installment_due_date)"))
                );
        })
        ->select(
            'month',
            DB::raw("SUM(paid) as paid"),
            DB::raw("SUM(estimate_paid) as estimate_paid")
        )
        ->groupBy('month')
        ->orderBy('month', 'ASC')
        ->get();

        // Daftar nama bulan dalam bahasa Indonesia
        $months = [
            1 => "January", 2 => "February", 3 => "Maret", 4 => "April",
            5 => "May", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
        ];

        // Inisialisasi hasil dengan semua bulan bernilai 0
        $monthlyData = [];
        foreach ($months as $num => $name) {
            $monthlyData["$name $currentYear"] = [
                "paid" => "0",
                "estimate_paid" => "0"
            ];
        }

        // Mengisi hasil berdasarkan data yang diambil
        foreach ($count_sql as $data) {
            $monthName = $months[$data->month] . " $currentYear";
            $monthlyData[$monthName] = [
                "paid" => $data->paid,
                "estimate_paid" => $data->estimate_paid
                // "estimate_paid" => ($data->paid == 0) ? 0 : $data->estimate_paid
            ];
        }
        return response()->json($monthlyData);
    }

    function getPrincipal(Request $request) {
        switch ($request->input('type')) {
            case 'weekly':
                    // Validate the filters
                    $validated = $request->validate([
                        'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
                    ]);

                    // Retrieve validated filter parameters
                    $date = $validated['date'] ?? Carbon::now()->format('Y-m');

                    // Parse given date to get start and end of the month
                    $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
                    $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();

                    // Inisialisasi array untuk hasil mingguan
                    $responses = [];

                    // Loop untuk membagi bulan menjadi 4 minggu tetap
                    for ($week = 0; $week < 4; $week++) {
                        $startWeek = $startOfMonth->copy()->addDays($week * 7);
                        $endWeek = $startWeek->copy()->addDays(6);

                        // Pastikan tidak melebihi akhir bulan
                        if ($endWeek->greaterThan($endOfMonth)) {
                            $endWeek = $endOfMonth;
                        }
                        $students = Contract::whereBetween('created_at', [$startWeek, $endWeek])
                            ->select(
                                DB::raw("SUM(CASE WHEN status IN (4, 5) THEN contract_principle ELSE 0 END) as principle")
                                )
                                ->first();
                        $weekRange = $startWeek->format('d-m-Y') . " - " . $endWeek->format('d-m-Y');
                        $responses[$weekRange] = $students->principle ?? "0";
                    }
                break;

            default:
                    // Validate the filters
                    $validated = $request->validate([
                        'year' => 'nullable|date_format:Y', // Validates the date format (YYYY)
                    ]);

                    // Retrieve validated filter parameters
                    $date = $validated['year'] ?? null;
                    if ($date) {
                        $currentYear = Carbon::createFromFormat('Y', $date)->year;
                    } else {
                        $currentYear = Carbon::now()->year;
                    }

                    $students = Contract::whereYear('created_at', $currentYear)
                                ->select(
                                    DB::raw("MONTH(created_at) as month"), // Ambil nomor bulan
                                    DB::raw("SUM(CASE WHEN status IN (4, 5) THEN contract_principle ELSE 0 END) as principle"), // Jumlahkan contract_provision jika status 4 atau 5
                                    DB::raw("COUNT(*) as total") // Hitung total semua siswa dalam bulan tersebut
                                )
                                ->groupBy(DB::raw("MONTH(created_at)"))
                                ->orderBy(DB::raw("MONTH(created_at)"), 'ASC')
                                ->get();

                    // Daftar nama bulan dalam bahasa Indonesia
                    $months = [
                        1 => "January", 2 => "February", 3 => "Maret", 4 => "April",
                        5 => "May", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
                        9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                    ];

                    // Inisialisasi hasil dengan semua bulan bernilai 0
                    $responses = [];
                    foreach ($months as $num => $name) {
                        $responses["$name $currentYear"] = "0";
                    }
                    // Mengisi hasil berdasarkan data yang diambil
                    foreach ($students as $data) {
                        $monthName = $months[$data->month] . " $currentYear";
                        $responses[$monthName] = $data->principle;
                    }
                break;
        }

        return response()->json($responses);
    }

    function getProvision(Request $request) {
        switch ($request->input('type')) {
            case 'weekly':
                    // Validate the filters
                    $validated = $request->validate([
                        'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
                    ]);

                    // Retrieve validated filter parameters
                    $date = $validated['date'] ?? Carbon::now()->format('Y-m');

                    // Parse given date to get start and end of the month
                    $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
                    $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();

                    // Inisialisasi array untuk hasil mingguan
                    $responses = [];

                    // Loop untuk membagi bulan menjadi 4 minggu tetap
                    for ($week = 0; $week < 4; $week++) {
                        $startWeek = $startOfMonth->copy()->addDays($week * 7);
                        $endWeek = $startWeek->copy()->addDays(6);

                        // Pastikan tidak melebihi akhir bulan
                        if ($endWeek->greaterThan($endOfMonth)) {
                            $endWeek = $endOfMonth;
                        }
                        $students = Contract::whereBetween('created_at', [$startWeek, $endWeek])
                            ->select(
                                DB::raw("SUM(CASE WHEN status IN (4, 5) THEN contract_process_fee ELSE 0 END) as provision") // Jumlahkan contract_process_fee jika status 4 atau 5
                            )
                            ->first();
                        $weekRange = $startWeek->format('d-m-Y') . " - " . $endWeek->format('d-m-Y');
                        $responses[$weekRange] = $students->provision ?? "0";
                    }
                break;

            default:
                    // Validate the filters
                    $validated = $request->validate([
                        'year' => 'nullable|date_format:Y', // Validates the date format (YYYY)
                    ]);

                    // Retrieve validated filter parameters
                    $date = $validated['year'] ?? null;
                    if ($date) {
                        $currentYear = Carbon::createFromFormat('Y', $date)->year;
                    } else {
                        $currentYear = Carbon::now()->year;
                    }

                    $students = Contract::whereYear('created_at', $currentYear)
                                ->select(
                                    DB::raw("MONTH(created_at) as month"), // Ambil nomor bulan
                                    DB::raw("SUM(CASE WHEN status IN (4, 5) THEN contract_process_fee ELSE 0 END) as provision"), // Jumlahkan contract_process_fee jika status 4 atau 5
                                    DB::raw("COUNT(*) as total") // Hitung total semua siswa dalam bulan tersebut
                                )
                                ->groupBy(DB::raw("MONTH(created_at)"))
                                ->orderBy(DB::raw("MONTH(created_at)"), 'ASC')
                                ->get();

                    // Daftar nama bulan dalam bahasa Indonesia
                    $months = [
                        1 => "January", 2 => "February", 3 => "Maret", 4 => "April",
                        5 => "May", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
                        9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                    ];

                    // Inisialisasi hasil dengan semua bulan bernilai 0
                    $responses = [];
                    foreach ($months as $num => $name) {
                        $responses["$name $currentYear"] = "0";
                    }
                    // Mengisi hasil berdasarkan data yang diambil
                    foreach ($students as $data) {
                        $monthName = $months[$data->month] . " $currentYear";
                        $responses[$monthName] = $data->provision;
                    }
                break;
        }

        return response()->json($responses);
    }

    function getRegisteredSteps(Request $request) {
        switch ($request->input('type'))  {
            case 'weekly':
                    // Validate the filters
                    $validated = $request->validate([
                        'date' => 'nullable|date_format:Y-m', // Validates the date format (YYYY-MM)
                    ]);
                    // Retrieve validated filter parameters
                    $date = $validated['date'] ?? Carbon::now()->format('Y-m');
                    // Parse given date to get start and end of the month
                    $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
                    $endOfMonth = Carbon::createFromFormat('Y-m', $date)->endOfMonth();
                    // Inisialisasi array untuk hasil mingguan
                    $responses = [];

                    $responses = [];

                    // Loop untuk membagi bulan menjadi 4 minggu tetap
                    for ($week = 0; $week < 4; $week++) {
                        $startWeek = $startOfMonth->copy()->addDays($week * 7);
                        $endWeek = $startWeek->copy()->addDays(6);

                        // Pastikan tidak melebihi akhir bulan
                        if ($endWeek->greaterThan($endOfMonth)) {
                            $endWeek = $endOfMonth;
                        }
                        $students = Contract::whereBetween('created_at', [$startWeek, $endWeek])
                            ->select(
                                DB::raw("SUM(CASE WHEN status IN (4, 5) THEN contract_process_fee ELSE 0 END) as provision") // Jumlahkan contract_process_fee jika status 4 atau 5
                            )
                            ->first();
                        $weekRange = $startWeek->format('d-m-Y') . " - " . $endWeek->format('d-m-Y');
                        $responses[$weekRange] = $students->provision ?? "0";
                    }

                break;

            default:
                // Validate the filters
                $validated = $request->validate([
                    'year' => 'nullable|date_format:Y', // Validates the date format (YYYY)
                ]);
                // Retrieve validated filter parameters
                $date = $validated['year'] ?? null;
                if ($date) {
                    $currentYear = Carbon::createFromFormat('Y', $date)->year;
                } else {
                    $currentYear = Carbon::now()->year;
                }

                $students = StudentStatus::whereYear('created_at', $currentYear)
                            ->select(
                                DB::raw("MONTH(created_at) as month"), // Ambil nomor bulan
                                DB::raw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as register"), // Hitung jumlah siswa dengan status 1
                                DB::raw("SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as complate"), // Hitung jumlah siswa dengan status 5
                            // DB::raw("COUNT(*) as total")
                            // Hitung total semua siswa dalam bulan tersebut
                            )
                            ->groupBy(DB::raw("MONTH(created_at)"))
                            ->orderBy(DB::raw("MONTH(created_at)"), 'ASC')
                            ->get();

                        // Daftar nama bulan dalam bahasa Indonesia
                        $months = [
                            1 => "January", 2 => "February", 3 => "Maret", 4 => "April",
                            5 => "May", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
                            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                        ];

                        // Inisialisasi hasil dengan semua bulan bernilai 0
                        $responses = [];
                        foreach ($months as $num => $name) {
                            $responses["$name $currentYear"] = [
                                "register" => 0,
                                "complate" => 0
                            ];
                        }

                        // Mengisi hasil berdasarkan data yang diambil
                        foreach ($students as $data) {
                            $monthName = $months[$data->month] . " $currentYear";
                            $responses[$monthName] = [
                                "register" => $data->register,
                                "contract" => $data->complate
                            ];
                        }
                break;
        }

        return response()->json($responses);
    }

    function getSummary(Request $request) {

        $response['student'] = Student::where('deleted_at','=',null)->count();
        $response['default'] = Student::where('deleted_at','=','3')->count();
        $response['active']  = Student::where('deleted_at','=','5')->count();
        $response['graduate']= Student::where('deleted_at','=','6')->count();

        return response()->json($response);

    }
}
