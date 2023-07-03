<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SharedTraitsUser extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $shared_count;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $shared_count)
    {
        $this->user = $user;
        $this->shared_count = $shared_count;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(sprintf("%s, %s joined Price Alumni - %s shared traits", $this->user->first_name, $this->user->last_name, $this->shared_count))->markdown('emails.shared-traits-user');
    }
}
