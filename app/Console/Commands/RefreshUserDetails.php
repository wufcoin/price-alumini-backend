<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Organization;

use Illuminate\Support\Facades\Http;

class RefreshUserDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refreshuserdetails:cron';

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
            \Log::info("Update User Details Cron Started :: ". Carbon::now()->toDateTimeString());
            $users = User::query()->select(["id", "linkedin_auth_token"])->get();
            foreach ($users as $key=>$user) {
                if(!empty($user['linkedin_auth_token'])) {
                    $basicFields = $this->getBasicField($user['linkedin_auth_token']);
                    
                    if(!empty($basicFields['data']) && $basicFields['status'] == 200) {
                        
                        $user->linkedin_id = $basicFields['data']['id'];
                        $user->first_name = $basicFields['data']['firstName']['localized']['en_US'];
                        $user->last_name = $basicFields['data']['lastName']['localized']['en_US'];
                        $user->headline = $basicFields['data']['headline']['localized']['en_US'];
                        if(array_key_exists("profilePicture", $basicFields['data'])) {
                            $profilePicArray = end($basicFields['data']['profilePicture']['displayImage~']['elements']);
                            $user->profile_pic = $profilePicArray['identifiers'][0]['identifier'];
                        } else {
                            $user->profile_pic = "https://ousooners.net/linkedin_leaders_laravel/public/images/default_profile_pic.jpeg";
                        }
                        $user->profile_url = "https://www.linkedin.com/in/".$basicFields['data']['vanityName'];
                        
                        $emailAddressField = $this->getEmailAddress($user['linkedin_auth_token']);
                        if(!empty($emailAddressField['data']) && $emailAddressField['status'] == 200) {
                            $user->email = $emailAddressField['data']['elements'][0]["handle~"]['emailAddress'];
                        }
                        
                        $connectionSize = $this->getConnectionSize($user['linkedin_auth_token'], $basicFields['data']['id']);
                        if(!empty($connectionSize['data']) && $connectionSize['status'] == 200) {
                            $user->connections_size = $connectionSize['data']['firstDegreeSize'];
                        }
                        
                        $organizations = $this->getOrganizations($user['linkedin_auth_token']);
                        if($organizations['status'] == 200) {
                            $organizations_array = $organizations['data']['elements'];
    
                            $organization_ids = [];
                            if($organizations_array && !empty($organizations_array)) {
                                foreach ($organizations_array as $organization) {
                                    $organization = Organization::updateOrCreate([
                                        'name' => $organization['organization~']['localizedName'],
                                        'role' => $organization['role'],
                                        'role_assignee_urn' => $organization['roleAssignee'],
                                        'state' => $organization['state'],
                                        'organization_urn' => $organization['organization']
                                    ],[
                                        'name' => $organization['organization~']['localizedName'],
                                        'role' => $organization['role'],
                                        'role_assignee_urn' => $organization['roleAssignee'],
                                        'state' => $organization['state'],
                                        'organization_urn' => $organization['organization']
                                    ]);
                                    array_push($organization_ids, $organization->id);
                                }
                                $user->organizations()->sync($organization_ids);
                            } else {
                                $user->organizations()->detach();
                            }
                        }
                        $user->save();
                    }
                }
            }
            \Log::info("Update User Details Cron End :: ". Carbon::now()->toDateTimeString());
        } catch (Exception $e) {
            \Log::info("Something went wrong, Please try again.");
        }
        return 0;
    }

    private function getBasicField($accessToken) {
        $apiURL = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,headline,vanityName,profilePicture(displayImage~:playableStreams))';
        $headers = [
            "Authorization" => "Bearer ".$accessToken
        ];
  
        $response = Http::withHeaders($headers)->get($apiURL);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }

    private function getEmailAddress($accessToken) {
        $apiURL = 'https://api.linkedin.com/v2/clientAwareMemberHandles?q=members&projection=(elements*(primary,type,handle~))';
        $headers = [
            "Authorization" => "Bearer ".$accessToken
        ];
  
        $response = Http::withHeaders($headers)->get($apiURL);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }

    private function getConnectionSize($accessToken, $personID) {
        $apiURL = 'https://api.linkedin.com/v2/connections/urn:li:person:'.$personID;
        $headers = [
            "Authorization" => "Bearer ".$accessToken
        ];
  
        $response = Http::withHeaders($headers)->get($apiURL);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }

    private function getOrganizations($accessToken) {
        $apiURL = 'https://api.linkedin.com/v2/organizationAcls?q=roleAssignee&projection=(elements*(*,roleAssignee~(localizedFirstName, localizedLastName), organization~(localizedName)))&role=ADMINISTRATOR&state=APPROVED';
        $headers = [
            "Authorization" => "Bearer ".$accessToken,
            "X-Restli-Protocol-Version" => "2.0.0"
        ];
  
        $response = Http::withHeaders($headers)->get($apiURL);
  
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);
        return ["data" => $responseBody, 'status' => $statusCode];
    }
}
