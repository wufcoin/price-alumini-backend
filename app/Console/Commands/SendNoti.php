<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendNoti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_noti:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pushnotification when new user signup';

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
     * === php artisan send_noti:process   
     * @return int
     */
    public function handle()
    {
        \Log::info ("--------Start handle----------\n");
        app('App\Http\Controllers\CronController')->cron_send_noti();    
    }
}
