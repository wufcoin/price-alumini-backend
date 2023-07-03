<?php

namespace App\Console\Commands;

use App\Mail\SharedTraitsUser;
use App\Models\City;
use App\Models\Degree;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserCity;
use Illuminate\Support\Facades\Mail;

class TestDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::find(5);

        Mail::to($user)->send(new SharedTraitsUser($user, 5));
    }
}
