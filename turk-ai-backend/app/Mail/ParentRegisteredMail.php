<?php

namespace App\Mail;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParentRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User    $user,
        public Student $student
    )
    {
    }

    public function build(): self
    {
        return $this
            ->subject(__('general.mail_welcome_subject'))
            ->view('emails.parent_registered', [
                'parent' => [
                    'fullName' => $this->user->name . ' ' . $this->user->surname
                ],
                'student' => [
                    'fullName' => $this->student->name . ' ' . $this->student->surname,
                    'number' => $this->student->number,
                    'grade' => $this->student->grade,
                ]
            ]);
    }
}
