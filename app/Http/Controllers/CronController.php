<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\MilitaryBranch;
use App\Models\MilitaryCode;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class CronController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    public $onesignal_appid="270fbfba-af13-45ff-bbe8-72e03b2c22e4";
    public $onesignal_apikey="OTk1OWMzZTUtZDJlZS00NjIyLTk0NGYtY2YwN2FmNmVkYTYy";


  
    public function __construct()  {
     }

    public function cron_send_noti(){    
        // run every  ; 
        echo "--------send notification job----------"."<br/>\n";
        set_time_limit(0);        
        $current_time = date('Y-m-d H:i:s');
        echo $current_time."\n";

       

        //------------------
        $new_user_lists = User::query()->where('new_userflag', '1')
        ->with(['schools', 'associations','industries','hobbies'])->get()->toArray();
         
        $total_user_list= User::query()->where('new_userflag', '0')
        ->with(['schools', 'associations','industries','hobbies'])->get()->toArray();
      

        foreach($new_user_lists  as $key=> $new_user){    

            if($new_user['military_code_id'] != null) {
                $new_user_lists[$key]['military_code']= MilitaryCode::find($new_user['military_code_id'])->toArray();
                $new_user_lists[$key]['military_branch']= MilitaryBranch::find($new_user_lists[$key]['military_code']['military_branch_id'])->toArray();
            }

            if (count($new_user_lists[$key]['schools'])>0)
               $new_user_lists[$key]['schools']=  array_filter($new_user_lists[$key]['schools'],function ($value) { return $value['high_school'] == 1;  });


            $new_user_id=$new_user['id'];

            foreach($total_user_list as $key1=> $user_item){      

                if($user_item['military_code_id'] != null) {
                    $total_user_list[$key1]['military_code']= MilitaryCode::find($user_item['military_code_id'])->toArray();
                    $total_user_list[$key1]['military_branch']= MilitaryBranch::find($total_user_list[$key1]['military_code']['military_branch_id'])->toArray();
                }

                if (count($total_user_list[$key1]['schools'])>0)
                   $total_user_list[$key1]['schools']=  array_filter($total_user_list[$key1]['schools'],function ($value) { return $value['high_school'] == 1;  });
 
                

                if($user_item['device_token'] && $user_item['device_token']!=''){

                    $school_arry=$this->check_array_data($new_user['schools'],$user_item['schools']);
                    $associations_arry=$this->check_array_data($new_user['associations'],$user_item['associations']);
                    $industries_arry=$this->check_array_data($new_user['industries'],$user_item['industries']);
                    $hobbies_arry=$this->check_array_data($new_user['hobbies'],$user_item['hobbies']);


                    if (count($school_arry)>0 ||
                        count($associations_arry)>0 ||
                        count($industries_arry)>0 ||
                        count($hobbies_arry)>0 ||
                        ($new_user['military_code_id']!= null && $user_item['military_code_id']!= null  && $new_user['military_code_id']==$user_item['military_code_id'])
                        ){

                            $user_deviceid=$user_item['device_token'];
                            $msg=$new_user['first_name'].' '.$new_user['last_name'].' joined. ';

                            if (count($school_arry)>0){
                                foreach($school_arry as $item){
                                    $msg=$msg.$item['name'].', ';
                                }
                            }else if ( $new_user['military_code_id']!= null && $user_item['military_code_id']!= null  && $new_user['military_code_id']==$user_item['military_code_id']){
                                $msg=$msg.$new_user['military_branch']['name']." ".$new_user['military_code']['name'].', ';
                            }else if (count($associations_arry)>0){
                                foreach($associations_arry as $item){
                                    $msg=$msg.$item['name'].', ';
                                }
                            }else if (count($industries_arry)>0){
                                foreach($industries_arry as $item){
                                    $msg=$msg.$item['name'].', ';
                                }
                            }else if (count($hobbies_arry)>0){
                                foreach($hobbies_arry as $item){
                                    $msg=$msg.$item['name'].', ';
                                }
                            }

                            $msg=substr($msg, 0, -2);

                           $this->send_notification($new_user_id,$user_deviceid ,$msg,0);                                    
                    }            
                }   
            }
           
   
           User::where('id',$new_user['id'])->update(['new_userflag'=>0]);
        } 
           
       // echo response()->json(['status'=>'1','data'=>$new_user_lists  ]);
    
        echo "--end cron job--";                 
        return;
    }

    public function check_array_data($array1,$array2){
        $arr = array_uintersect($array1 , $array2, function ($e1, $e2) { 
            if($e1['id'] == $e2['id']) {
                return 0;
            } else {
                return 1;
            }
        });

        return $arr;

        // if (count($arr)==0){
        //     return false;
        // }else{
        //     return true;
        // }
    }

    public function send_notification($user_id,$user_deviceid,$msg,$multiple_flag){

        if ($multiple_flag==0){  // single device
            $send_ids = [];
            $send_ids[]=$user_deviceid;
        }else{   // multiple device
            $send_ids=$user_deviceid;
        }
   
        
        $app_id=$this->onesignal_appid ;// from onesignal

        $content = array(
        "en" => $msg
        );
    

        $data=  array(
            "user_id" => $user_id,  //general, alarm
        );

        
        $fields = array(
            'app_id' => $app_id,
            'contents' => $content,
            'include_player_ids' => $send_ids ,   
            'data' =>$data,
        );
                
 
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic '.$this->onesignal_apikey));// from onesignal rest api key
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);       

        return $response;
    }
}


