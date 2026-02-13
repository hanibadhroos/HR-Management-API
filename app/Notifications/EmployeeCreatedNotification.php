<?php
namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Employee $employee
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Employee Assigned')
            ->line("A new employee has been assigned under you.")
            ->line("Name: {$this->employee->name}")
            ->line("Salary: {$this->employee->salary}")
            ->line('Thank you.');
    }
}
