<?php

namespace App\Notifications;

use App\Models\ContestApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;


class NewContestApplicationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private ContestApplication $contestApplication)
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $contest = $this->contestApplication->contest;
        $applicant = $this->contestApplication->applicant;

        return [
            'title' => 'Новый отклык на конкурс',
            'body' => $applicant->fullname . 'откликнулся на конкурс ' . $contest->title
        ];
    }
}
