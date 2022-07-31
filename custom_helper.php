<?php
defined('BASEPATH') OR ('No direct script access allowed');
// define('FIREBASEKEY','AIzaSyCKEfuVM38j-mj7ZPijX5RAw1oFJj-b9LA'); //AIzaSyD9txqR2an5VyaW8_okh-IIbt9bfmpI0v0





// $notification = array(
// 		           'msg' =>$msg,
// 		           'title' =>$title,
// 		           'body' => $msg,
// 		           'data' => $book_id,
// 		           );
                      
                      
//                     $res = send_notification($notification,$device_token,$device_type); 





	function send_notification($data=array(),$fcm_id,$device_type){ 
   	    
   	     
        if(!isset($fcm_id))
        {
            $data['fcm_id']='xxsx';
        }
       
     
        
        
        
          if($device_type == "Android"){
              $fields = array(
                'to' => $fcm_id,
                 'data' => $data
                //'notification' => $data
              );
            }
            else
            {
               $fields = array(
                'to' => $fcm_id,
                'notification' => $data
               );
           }
        
        
        
        
      
        
        
        // print_r($fields);exit();
          
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
         'Content-Type:application/json',
        
        'Authorization: key='.'AAAARuvsSOw:APA91bFqrweNPrEuHYtJNmHTrqFm1rbMRLeAtly7bZk40r4z5cCEGeQrYxCnjge-hqvFQ6dcGBLxWf-wbxwBxVCTx8vSJq6PXDc7Pl-T2ZuWikRZPFL3TUvykpEiVbIl3b3smgzpANwG',
        
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
    

              











 function user_noti($msg,$title,$data,$device_token){

  
    $new_data=$data;
    $message = $msg;
    $title = $title;
    $path_to_fcm = 'https://fcm.googleapis.com/fcm/send';   
    $server_key='AAAARuvsSOw:APA91bFqrweNPrEuHYtJNmHTrqFm1rbMRLeAtly7bZk40r4z5cCEGeQrYxCnjge-hqvFQ6dcGBLxWf-wbxwBxVCTx8vSJq6PXDc7Pl-T2ZuWikRZPFL3TUvykpEiVbIl3b3smgzpANwG';

    $headers = array(
      'Authorization:key=' .$server_key,
      'Content-Type: application/json'
    );

    $fields = array
    (
      'to'        => $device_token,
      'notification'  => array('title' => $title,'body' => $message),
      'data'=>$new_data,
    );
    
    $payload = json_encode($fields);

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, "$path_to_fcm" );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt( $ch,CURLOPT_POSTFIELDS, $payload);
    $result = curl_exec($ch);
    $result;
    //curl_close($ch);
  }















    //send notification by user side  to nurse,doctor,ambulence
   	// function send_notification($data=array(),$fcm_id){  
    //     if(!isset($fcm_id))
    //     {
    //         $data['fcm_id']='xxsx';
    //     }
       
    //     $fields=array(
    //          "to"  => $fcm_id,
    //         // "to" =>"dr9PlLkLlRE:APA91bFDtI6mUdXcoJYFXo1dnbDpzYQb9fQ9i2wAzuZGtu-DxK5veVMbi03hfgvCTIlhRPAevMH7VEq52qjpOE_Oxm04Xe8u7t63YaXP3BSjbwUmbFpd5kAWdHTnH4p2_hsaFJf4Ie7R",
    //         "data"=> $data
        
    //     );
    //     print_r($fields);exit();
          
    //     $url = 'https://fcm.googleapis.com/fcm/send';
    //     $headers = array(
    //      'Content-Type:application/json',
    //      // 'Authorization: key='.'AIzaSyCKEfuVM38j-mj7ZPijX5RAw1oFJj-b9LA',
    //     'Authorization: key='.'AIzaSyB0-bzj5FCUN5G05UUgL_SOx-1dYUyaark',

    //     'Content-Type: application/json'
    //     );
    //     // Open connection
    //     $ch = curl_init();
    //     // Set the url, number of POST vars, POST data
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     // Disabling SSL Certificate support temporarly
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    //     // Execute post
    //     $result = curl_exec($ch);
    //     //print_r($result);die;
    //     if ($result === FALSE) {
    //         die('Curl failed: ' . curl_error($ch));
    //     }
    //     // Close connection
    //     curl_close($ch);
    //     return $result;
    // }

    function send_notification_on_tripD($data=array()){
        $body='This body of notification';
        $type='This body of notification';
        $title='Title of notification';
        $icon='myIcon';/*Default Icon*/
        $sound='mySound';/*Default sound*/
        $fcm_id='';
        $extra=array();
        extract($data);
            
        $notification = array(
            'message'  => $message,
            'type'=>$type
        );
        
        $fields = array(
            'to'        => $fcm_id,
            'notification'  => $notification,
            'data'  => $extra
        );

        $headers = array(
            'Authorization: key='.FIREBASEKEY,
            'Content-Type: application/json'
        );
            
            #Send Reponse To FireBase Server    
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        return $result;
    }
    
    // Save Notification..
    
    function save_notification($data=''){
        $obj = & get_instance();
	    return $obj->User_Model->insertAllData('notifications', $data);	    
    }
    
    // Save Url Picture
    function save_pic($url){
    	$imgName = randomstr(15);
    	
    	$content = file_get_contents($url);
    	if(file_put_contents('images/profile/'.$imgName.'.jpg', $content)){
    		return $imgName.'.jpg';
    	}else{
    		return false;
    	}
    }
    
    function randomstr($length='')
{
	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
