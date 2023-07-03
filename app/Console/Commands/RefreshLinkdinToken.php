<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class RefreshLinkdinToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refreshlinkdintoken:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            \Log::info("Refresh Linkdin Token Cron Started :: ". Carbon::now()->toDateTimeString());
            $users = User::query()->select(["id", "linkedin_auth_token", "linkedin_auth_token_expire_at", "linkedin_auth_refresh_token", "linkedin_auth_refresh_token_expire_at"])->get();
            foreach ($users as $key=>$user) {
                if(!empty($user['linkedin_auth_token'])) {
                    $tokenDetails = $this->getTokenDetails($user['linkedin_auth_token']);
                    if($tokenDetails['status'] == 200) {
                        if($tokenDetails['data']['status'] == "expired") {
                            $newTokenDetails = $this->getNewToken($user['linkedin_auth_refresh_token']);
                            if($newTokenDetails['status'] == 200) {
                                \Log::info("User updated :: ". $user->id);
                                \Log::info("New token details :: ".$newTokenDetails);
                                $user->linkedin_auth_token = $newTokenDetails['data']['access_token'];
                                $user->linkedin_auth_token_expire_at = $newTokenDetails['data']['expires_in'];
                                $user->linkedin_auth_refresh_token = $newTokenDetails['data']['refresh_token'];
                                $user->linkedin_auth_refresh_token_expire_at = $newTokenDetails['data']['refresh_token_expires_in'];
                                $user->save();
                            }
                        }
                    }
                }
            }
            \Log::info("Refresh Linkdin Token Cron End :: ". Carbon::now()->toDateTimeString());
        } catch (Exception $e) {
            \Log::info("Something went wrong, Please try again.");
        }
        return 0;
    }

    private function getTokenDetails($accessToken) {
        $apiURL = 'https://www.linkedin.com/oauth/v2/introspectToken';
        $postData = [
            "client_id" => "86nosqkud9v945",
            "client_secret" => "iIdRVgfx4Z6kqaad",
            "token" => $accessToken
        ];
        $response = Http::asForm()->post($apiURL, $postData);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }

    private function getNewToken($refreshToken) {
        $apiURL = 'https://www.linkedin.com/oauth/v2/accessToken';
        $postData = [
            "grant_type" => "refresh_token",
            "client_id" => "86nosqkud9v945",
            "client_secret" => "iIdRVgfx4Z6kqaad",
            "refresh_token" => $refreshToken
        ];
        $response = Http::asForm()->post($apiURL, $postData);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }

}
