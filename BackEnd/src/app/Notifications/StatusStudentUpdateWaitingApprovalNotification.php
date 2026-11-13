<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusStudentUpdateWaitingApprovalNotification extends Notification
{ 
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $studentName;
    public $lpkName;

    public function __construct($studentName, $lpkName)
    {
        $this->studentName = $studentName;
        $this->lpkName = $lpkName;
    }

    

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                        ->line('Student with this data')
                        ->line('Name:  ' . str_pad($this->studentName, 30))  // Adjust the padding as needed
                        ->line('LPK:   ' . str_pad($this->lpkName, 30))      // Adjust the padding as needed
                        ->line('Requires review');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
