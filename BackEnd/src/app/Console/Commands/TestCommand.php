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
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Notifications\StatusStudentUpdateWaitingApprovalNotification;
use Illuminate\Support\Facades\Auth;


class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test-command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      

     
          // $roles = Role::all();
      
          // foreach ($roles as $role) {
          //   Log::info($role->name);
          // }


          $users = User::role('admin', 'backoffice')->get();
          $student = Student::find('9cd54ad4-7fa0-4778-aae3-129ca084e1ce');
          // Log::info($student->student_name);
          if ($users) {
              foreach ($users as $userEach) {
                  // $userEach->notify(new StatusStudentUpdateWaitingApprovalNotification('Your application is now waiting for approval.'));
                  $userEach->notify(new StatusStudentUpdateWaitingApprovalNotification($student->student_name,$student->studentLpk->lpk_name));
                }
          }

      
      
    }


     


     

}

