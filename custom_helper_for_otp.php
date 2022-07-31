<?php
defined('BASEPATH') OR ('No direct script access allowed');
function send_notification($data=array(),$fcm_id){  
        if(!isset($fcm_id))
        {
            $data['fcm_id']='xxsx';
        }
       
        $fields=array(
            "to"  => $fcm_id,
            "data"=> $data
        );
       
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
         'Content-Type:application/json',
         'Authorization: key='.'AAAAbGMULYo:APA91bFY1tQSlxhXZjiA7NDklRO9KJNUwAqtPAdeB88B3-FUS_9XKtjvEO5NoTTWNfpBTMN2MdA9i4t4cjLUSXFk7w7AINsELASy01c6ZoesvXw0JP7-paGDz9zzN4coHRzmKE8awhDu',

        'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // Execute post
        $result = curl_exec($ch);
        //print_r($result);die;
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        return $result;
    }
    
    
    
    
    
    // $for_sms_mobile = $mobile; //phone number must included with countriy code like: +91xxxxxxxxxxx
    //     send_opt($for_sms_mobile, $random_no); 
    
      
    function send_opt($mobile,$otp,$user_name){
        
      
      
        
      $token='383674656d736475636172653130301638857674';
      $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
      
    $senderid = 'MYUSDL';
    $templeteId = '1207162531530467137';
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "http://text.adservs.in/http-tokenkeyapi.php?authentic-key=383674656d736475636172653130301638857674&senderid=MYUSDL&route=1&number=$mobile&message=Dear%20$user_name%20$otp%20is%20your%20one%20time%20password%20(OTP).%20Please%20enter%20to%20proceed.%20Thank%20You,%20TEAM%20EDUCARE&templateid=1207162531530467137",
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => array(
            'field1' => 'some date',
            'field2' => 'some other data',
        )
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
      // var_dump($result);die;
    }
    
    
    
    
    
    
    
    
    
    
    function send_opt_order($mobile, $order_id,$user_name){
      $token='343576656769656265653130301636528027';
      $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
      //$url = "https://2factor.in/API/V1/$YourAPIKey/SMS/$SentTo/$OTPValue";
    // $username = 'anyservice';
    // $password = 'any@service951';
    // $EntityId = 1201162487088105711;
    $senderid = 'VEGCNF';
    $templeteId = '1207163636950076759';
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "http://text.adservs.in/http-tokenkeyapi.php?authentic-key=343576656769656265653130301636528027&senderid=VEGCNF&route=1&number=$mobile&message=Hi%20$user_name,%20Thanks%20for%20Choosing%20Us,%20We've%20got%20your%20order%20$order_id!%20Your%20cart%20is%20about%20to%20look%20a%20whole%20lot%20Farm-fresh.%20Hold%20your%20hands,%20We'll%20drop%20you%20another%20fresh%20update%20once%20your%20order%20is%20cleanly%20shipped.%20Thanks%20Team%20VegieBee&templateid=1207163636950076759",
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => array(
            'field1' => 'some date',
            'field2' => 'some other data',
        )
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
      // var_dump($result);die;
    }
    
    
    
    
    
    
    
    
    
    
    function send_opt_order_delivered($mobile, $order_id,$user_name){
      $token='343576656769656265653130301636528027';
      $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
      //$url = "https://2factor.in/API/V1/$YourAPIKey/SMS/$SentTo/$OTPValue";
    // $username = 'anyservice';
    // $password = 'any@service951';
    // $EntityId = 1201162487088105711;
    $senderid = 'VEGCNF';
    $templeteId = '1207163636965371593';
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "http://text.adservs.in/http-tokenkeyapi.php?authentic-key=343576656769656265653130301636528027&senderid=VEGCNF&route=1&number=$mobile&message=Hi%20$user_name,%20We%20know,%20you've%20got%20your%20cart%20delivered%20with%20order%20$order_id.%20Why%20don't%20you%20unpack%20and%20start%20eating?%20Trust%20us,%20it's%20fresh%20and%20clean.%20Thanks%20Team%20VegieBee&templateid=1207163636965371593",
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => array(
            'field1' => 'some date',
            'field2' => 'some other data',
        )
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
      // var_dump($result);die;
    }
    
    
    
    
    
    
    
    
    
    
    
    
     function send_opt_dispatched($mobile, $order_id,$user_name){
      $token='343576656769656265653130301636528027';
      $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
      //$url = "https://2factor.in/API/V1/$YourAPIKey/SMS/$SentTo/$OTPValue";
    // $username = 'anyservice';
    // $password = 'any@service951';
    // $EntityId = 1201162487088105711;
    $senderid = 'VEGCNF';
    $templeteId = '1207163636958258538';
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "http://text.adservs.in/http-tokenkeyapi.php?authentic-key=343576656769656265653130301636528027&senderid=VEGCNF&route=1&number=$mobile&message=Hi%20$user_name,%20Yay!%20Great%20Choices!%20Your%20order%20$order_id%20is%20on%20it's%20way.%20Are%20you%20smiling?%20You're%20smiling.%20Thanks%20Team%20VegieBee&templateid=1207163636958258538",
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => array(
            'field1' => 'some date',
            'field2' => 'some other data',
        )
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
      // var_dump($result);die;
    }
    