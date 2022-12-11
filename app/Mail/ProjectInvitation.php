<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invitation;
    public $project;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $invitation, $project)
    {
//        $this->to($user);
        $this->subject('Приглашение в проект');
        $this->user = $user;
        $this->invitation = $invitation;
        $this->project = $project;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $actionUrl = env('APP_URL') . '/api/project/invite';
        $actionUrlParams = '?id=' . $this->invitation->id . '&token=' . $this->invitation->invitation_code;
        $types = [
            'author' => 'автора',
            'team' => 'участника',
            'cast' => 'актера',
        ];
        $bag = [
            'user' => $this->user,
            'invitation' => $this->invitation,
            'type' => $types[$this->invitation->type],
            'project' => $this->project,
            'url' => [
                'accept' => $actionUrl . '/accept' . $actionUrlParams,
                'reject' => $actionUrl . '/reject' . $actionUrlParams
            ],
        ];
        return $this->markdown('mail.project-invite', $bag);
    }
}
