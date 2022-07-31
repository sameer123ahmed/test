<?php

class MY_Controller extends CI_Controller

{

	   public function __construct() {
        parent::__construct();
    }
    
    
    
    public function imageUpload($image_name, $image_path)
    {
        $f_name = $_FILES[$image_name]['name'];
        $f_tmp = $_FILES[$image_name]['tmp_name'];
        $f_extension = explode('.',$f_name); //To breaks the string into array
        $f_extension = strtolower(end($f_extension)); //end() is used to retrun a last element to the array
        $f_newfile="";
        if($f_name){
        $f_newfile = uniqid().'.'.$f_extension; // It`s use to stop overriding if the image will be same then uniqid() will generate the unique name 
        $store = "$image_path" . $f_newfile;
        $file1 =  move_uploaded_file($f_tmp,$store);
        }
        return $f_newfile;
    }

}



class MY_VenderController extends CI_Controller
{
   public function __construct()
   {
   	parent::__construct();
   	if (!$this->session->userdata('login_id'))
      {
       	redirect('VendorLogin');	
      }
   }
}