<?php

namespace App\Jobs;

use App\Mail\SharedTraitsUser;
use App\Models\User;
use App\Traits\UserTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewUserRegistered implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UserTrait;

    public User $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereNotNull('connections_size')->where('id', '<>', $this->user->id)->get();
        foreach ($users as $u) {
            $con = $this->getSharedCount($u, $this->user);
            Log::alert($u->email . ' <> ' . $u->connections_size . " <> " . $con);
            if ($u->connections_size <= $con) {
                try {
                    Log::alert('Email try to send ' . $u->email);
                    Mail::to($u)->send(new SharedTraitsUser($u, $con));
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            }
        }
    }
}
