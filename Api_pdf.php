<?php

if(!defined('BASEPATH')) exit ('No direct script access allowed');
class Api extends CI_Controller
{

  public function __construct(){
    parent::__construct();
    date_default_timezone_set('Asia/Calcutta');
    $this->load->model('Admin_model');
    $this->load->model('User_Model');
    $this->load->model('Nf_Model');

    $this->load->model('Api_Model');
    $this->load->helper('custom_helper');
    $this->load->library('form_validation');
    date_default_timezone_set('Asia/Kolkata');
    $this->db->query("SET SESSION time_zone = '+5:30'");


  }

public function test_msg(){

  $mobile = '7509356357';
  $otp = '12346';
  send_opt($mobile,$otp);
}
public function return_product(){

    extract($_POST);
        if(!empty($userid) AND !empty($orderid) AND !empty($reason))
        {


          $pre_data = $this->db->query("SELECT * FROM return_order_requests
                            WHERE return_order_requests.userid='$userid'
                            AND return_order_requests.orderid='$orderid'
                            AND return_order_requests.status=0");
          if($pre_data->num_rows()>0){
            $data['result'] = 'false';
            $data['msg']    = 'Request is Pending Now';
            echo json_encode($data);
            exit;
          }


          if(!empty($_FILES['image']['name']))
          {
            $uploadPath = 'assets/images/';
            $config['upload_path'] = $uploadPath;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = 'return_photo_req_photo'.time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            // Upload file to server
            if($this->upload->do_upload('image')){
              $fileData = $this->upload->data();
                  //$this->delete_id_proof_image($doctor_id);
                $DATA['image'] = $fileData['file_name'];
            }
          }

          if(!empty($_FILES['video']['name']))
          {
            $uploadPath = 'assets/images/';
            $config['upload_path'] = $uploadPath;
            $config['allowed_types'] = 'avi|mp4|mpeg|mpg|flv|wmv';
            $config['max_size'] = '20000';

            $config['file_name'] = 'return_photo_req_photo'.time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            // Upload file to server
            if($this->upload->do_upload('video')){
              $fileData = $this->upload->data();
                  //$this->delete_id_proof_image($doctor_id);
                $DATA['video'] = $fileData['file_name'];
            }
          }


          $DATA['userid'] = $userid;
          $DATA['orderid'] = $orderid;
          $DATA['reason'] = $reason;
          $DATA['unique_id'] = 'R'.time();

          $this->db->insert('return_order_requests',$DATA);

          if($this->db->affected_rows()>0){

            //Sending Notifications start
            $this->Admin_model->on_return_req($orderid);
            //Sending Notification Ends


            $data['result'] = 'true';
            $data['msg']    = 'Submited Successfully';
            echo json_encode($data);
            exit;

          }

          $data['result'] = 'false';
          $data['msg']    = 'Could Not Submitted';
          echo json_encode($data);
          exit;
        }
        else{
          $json['result'] = 'false';
          $json['msg']    = 'REQ: userid, orderid,reason';
          echo json_encode($json);
          exit;
        }
}


  public function shipped_status(){

    // $orders = $this->db->query("SELECT * FROM orders WHERE  orders.status=2");
    $orders = $this->db->query("SELECT * FROM orders WHERE  orders.status=2");

    //var_dump($orders->num_rows());

    if($orders->num_rows() > 0)
    {

      foreach ($orders->result() as $row) {

        $timestamp = strtotime($row->picked_date);

        var_dump($timestamp);

        $date = date('Y-m-d', $timestamp);
        $time = date('H:i:s', $timestamp);

        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');


        //var_dump($date);
        //var_dump($current_date);


        if($date == $current_date OR $date < $current_date){

          $minutes = ROUND(ABS(($timestamp - time()) / 60));

          //var_dump($minutes);

          if($minutes>1){ //minute
            // var_dump($row->id);
            // var_dump($row->booking_uniqe_id);

            $this->db->where('orders.id',$row->id);
            $this->db->update('orders', array('status'=>3, 'shipped_date'=>date('Y-m-d')));

            $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$row->userid'");
            $fcm_id = $user_data->row()->fcm_id;

            // var_dump($fcm_id);
            $this->Nf_Model->OnShipped($user_data,$fcm_id,$row,$row->id,$row->booking_uniqe_id);
            echo "SENT <br>";


          }


        }

      }


    }

  }


  public function support(){
    $dataa = $this->db->query("SELECT * FROM customer_service WHERE  customer_service.status=1");
    if($dataa->num_rows() > 0)
    {
      $data['result'] = "true";
      $data['data'] = $dataa->row();
      echo json_encode($data);
      exit;

    }
    else
    {
      $data['result'] = "false";
      $data['msg'] = 'data not found';
      echo json_encode($data);
      exit;
    }
  }



public function test_opt(){
  $mobile = '7509356357';
  $otp = '12345';
send_opt($mobile, $otp);


}


public function get_units(){

  extract($_POST);
      if(!empty($userid))
      {
        $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");

        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $user_data = $user_data->row();




        $data['result'] = 'true';
        $data['msg']    = 'Get Successfully';
        $data['data']    = $this->db->query("SELECT * FROM units ")->result();
        echo json_encode($data);
        exit;
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }

}

public function get_weight_units(){

  extract($_POST);
      if(!empty($userid))
      {
        $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");

        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $user_data = $user_data->row();




        $data['result'] = 'true';
        $data['msg']    = 'Get Successfully';
        $data['data']    = $this->db->query("SELECT * FROM weight_units ")->result();
        echo json_encode($data);
        exit;
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }

}

  public function policies(){
    $data['result'] = "true";
    $data['privacy_policy'] = $this->db->query("SELECT * FROM rules WHERE rules.id=1")->row();
    $data['terms_conditions'] = $this->db->query("SELECT * FROM rules WHERE rules.id=2")->row();
    $data['about_us'] = $this->db->query("SELECT * FROM rules WHERE rules.id=3")->row();


    echo json_encode($data);
    exit;
  }

  public function get_single_slider(){
    $banner = $this->db->query("SELECT * FROM banners ORDER BY banners.id DESC");
    if($banner->num_rows()>0){
      $data['result'] = 'true';
      $data['msg']    = 'get successfully';
      $data['data']    = $banner->row();

      echo json_encode($data);
      exit;

    }
    else{
      $data['result'] = 'false';
      $data['msg']    = 'No slider Found';
      echo json_encode($data);
      exit;
    }
  }



public function remove_from_wishlist(){

  extract($_POST);
      if(!empty($userid) AND !empty($product_id))
      {
        $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");

        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $user_data = $user_data->row();

        $this->db->where('wish_list.product_id',$product_id);
        $this->db->delete('wish_list');

        $data['result'] = 'true';
        $data['msg']    = 'Removed Successfully';
        $data['data']    = $this->db->query("SELECT * FROM wish_list WHERE wish_list.userid='$userid'")->result();
        echo json_encode($data);
        exit;
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid, product_id';
        echo json_encode($json);
        exit;
      }

}
  public function add_to_wish_list(){
    extract($_POST);
        if(!empty($userid) AND !empty($product_id))
        {
          $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");

          if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
          $user_data = $user_data->row();

          $pre_wish_list = $this->db->query("SELECT * FROM wish_list WHERE wish_list.userid='$userid' AND wish_list.product_id='$product_id'");

          if($pre_wish_list->num_rows()>0){
            $data['result'] = 'false';
            $data['msg']    = 'Already Exists In Whish List';
            $data['data']    = $this->db->query("SELECT * FROM wish_list WHERE wish_list.userid='$userid'")->result();
            echo json_encode($data);
            exit;
          }
          else{
            $DATA['product_id'] = $product_id;
            $DATA['userid'] = $userid;
            $this->db->insert('wish_list',$DATA);

            $data['result'] = 'true';
            $data['msg']    = 'Added Successfully';
            echo json_encode($data);
            exit;
          }
        }
        else{
          $json['result'] = 'false';
          $json['msg']    = 'REQ: userid, product_id';
          echo json_encode($json);
          exit;
        }
  }

  public function get_user_brand_list(){
    extract($_POST);
        if(!empty($userid))
        {
          $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");
          if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
          $user_data = $user_data->row();

          $brands = $this->db->query("SELECT user_brands.*, brands.brand_name as brand_name, brands.image as brand_image  FROM user_brands JOIN brands ON brands.id=user_brands.brand_id WHERE user_brands.userid ORDER BY user_brands.id DESC");

          if($brands->num_rows()>0){
            $data['result'] = 'true';
            $data['msg']    = 'get successfully';
            // $data['cat_id'] = $user_data->cat_id;
            // $data['cat_name'] = $user_data->cat_name;
            // $data['cat_image'] = $user_data->category_image;

            $data['data']    = $brands->result();

            echo json_encode($data);
            exit;

          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'No Users Brands Found';
            echo json_encode($data);
            exit;
          }
        }
        else{
          $json['result'] = 'false';
          $json['msg']    = 'REQ: userid';
          echo json_encode($json);
          exit;
        }
  }
public function get_brand_list(){
  extract($_POST);
      if(!empty($userid))
      {
        $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name, (SELECT category.category_image FROM category WHERE category.id=users.cat_id) as category_image FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $user_data = $user_data->row();

        $user_brands = $this->db->query("SELECT user_brands.* FROM user_brands WHERE user_brands.userid='$userid' AND user_brands.status=1");

        if($user_brands->num_rows()>0){
          $BRAND_IDS = array();
          foreach ($user_brands->result() as $row) {
            array_push($BRAND_IDS, $row->brand_id);
          }
          $BRAND_IDS  = implode(',',$BRAND_IDS);
          //var_dump($BRAND_IDS);

          $brands = $this->db->query("SELECT * FROM brands WHERE brands.id NOT IN ($BRAND_IDS) ORDER BY brands.id DESC");

        }
        else{
          $brands = $this->db->query("SELECT * FROM brands ORDER BY brands.id DESC");

        }
        if($brands->num_rows()>0){
          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          $data['cat_id'] = $user_data->cat_id;
          $data['cat_name'] = $user_data->cat_name;
          $data['cat_image'] = $user_data->category_image;

          $data['data']    = $brands->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Brands Found';
          echo json_encode($data);
          exit;
        }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}


public function add_brand(){
  extract($_POST);
      if(!empty($userid) && !empty($brand_id) &&  !empty($relationship))
      {

        $user_data = $this->db->query("SELECT users.*, users.cat_id, (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $pre_data = $this->db->query("SELECT * FROM user_brands WHERE user_brands.userid='$userid' AND user_brands.brand_id='$brand_id'");
        if($pre_data->num_rows()>0){$json['result']=false;$json['msg']='Already Saved';echo json_encode($json);exit;}

        $user_data = $user_data->row();

        $DATA['userid'] =  $userid;
        $DATA['brand_id'] = $brand_id;
        $DATA['relationship'] =  $relationship;

        $this->db->insert("user_brands", $DATA);

        if($this->db->affected_rows()>0){
          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'Could Not Saved, Please Try Again';
          echo json_encode($data);
          exit;
        }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid,brand_id,relationship';
        echo json_encode($json);
        exit;
      }
}


public function add_new_product(){
  extract($_POST);
      if(!empty($userid) && !empty($brand_id) && !empty($cat_id) && !empty($is_new))
      {

        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        // if(empty($user_data->row()->cat_id)){
        //   $json['result']=false;$json['msg']='Vendor Not Selected Business Category';echo json_encode($json);exit;
        // }

        $product_id = $this->input->post('product_id');
        if($is_new == 'pre'){
          $DATA['category_id'] = $cat_id;//$user_data->row()->cat_id;
          $DATA['brand_id'] = $brand_id;
          $DATA['vendor_id'] = $userid;

          $this->db->where('products.id',$product_id);
          $this->db->update('products',$DATA);

          if($this->db->affected_rows()>0){
            $data['result'] = 'true';
            $data['msg']    = 'Updated successfully';
            $data['product_id'] = $product_id;
            echo json_encode($data);
            exit;

          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'Could Not Updated, Please Try Again';
            echo json_encode($data);
            exit;
          }
        }


        if($is_new == 'new'){

          $DATA['category_id'] = $cat_id;//$user_data->row()->cat_id;
          $DATA['brand_id'] = $brand_id;
          $DATA['vendor_id'] = $userid;
          $DATA['model_no'] = 'PR'.time();

          $this->db->insert('products',$DATA);
          $last_id = $this->db->insert_id();

          if($this->db->affected_rows()>0){
            $data['result'] = 'true';
            $data['msg']    = 'Created successfully';
            $data['product_id'] = $last_id;
            echo json_encode($data);
            exit;

          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'Could Not Created, Please Try Again';
            echo json_encode($data);
            exit;
          }
        }

      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid,brand_id, cat_id, is_new,(product_id)';
        echo json_encode($json);
        exit;
      }
}

public function delete_my_product(){

  extract($_POST);
    if(!empty($product_id))
    {
      $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$product_id'");
      if($product_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong product_id';echo json_encode($json);exit;}

      $this->db->where('products.id',$product_id);
      $this->db->update('products', array('is_deleted'=>1));

      $json['result'] = 'true';
      $json['msg']    = 'Deleted successfully';
      echo json_encode($json);
      exit;

    }
    else{
      $json['result'] = 'false';
      $json['msg']    = 'REQ: product_id';
      echo json_encode($json);
      exit;
    }

}

public function delete_incomplete_product(){

  extract($_POST);
    if(!empty($product_id) && !empty($vendor_id))
    {
      $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$product_id'");
      if($product_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong product_id';echo json_encode($json);exit;}

      $this->db->where('products.id',$product_id);
      $this->db->delete('products');

      $json['result'] = 'true';
      $json['msg']    = 'Deleted successfully';
      echo json_encode($json);
      exit;

    }
    else{
      $json['result'] = 'false';
      $json['msg']    = 'REQ: product_id, vendor_id';
      echo json_encode($json);
      exit;
    }
}

public function finish_creating_prodouct(){
  extract($_POST);
    if(!empty($product_id))
    {
      $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$product_id'");
      if($product_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong product_id';echo json_encode($json);exit;}

      $this->db->where('products.id',$product_id);
      $this->db->update("products", array('is_submited'=>1));

      $json['result']='true';
      $json['msg']='Final Submited Successfully';
      echo json_encode($json);exit;


    }
    else{
      $json['result'] = 'false';
      $json['msg']    = 'REQ: product_id';
      echo json_encode($json);
      exit;
    }


}

public function saved_product_detail(){
  extract($_POST);
    if(!empty($userid))
    {


      if(!empty($product_id)){

        $product_data = $this->db->query("SELECT products.*, (SELECT brands.brand_name FROM brands WHERE brands.id=products.brand_id) as brand_name
                                          FROM products
                                          WHERE products.vendor_id='$userid' AND products.id='$product_id'");
        if($product_data->num_rows()>0){

          $product_data = $product_data->row();
          $DATA['id'] = (string) $product_data->id;
          $DATA['brand_id'] = (string) $product_data->brand_id;
          $DATA['brand_name'] = (string) $product_data->brand_name;
          $DATA['product_name'] = (string) $product_data->product_name;
          $DATA['about'] = (string) $product_data->about;
          $DATA['packed_of'] = (string) $product_data->packed_of;
          $DATA['material_type'] = (string) $product_data->material_type;
          $DATA['model_no'] = (string) $product_data->model_no;
          $DATA['weight'] = (string) $product_data->weight;
          $DATA['color'] = (string) $product_data->color;
          $DATA['sell_type'] = (string) $product_data->sell_type;
          $DATA['no_of_piece'] = (string) $product_data->no_of_piece;
          $DATA['min_qty'] = (string) $product_data->min_qty;
          $DATA['price_per_set'] = (string) $product_data->price_per_set;
          $DATA['gst'] = (string) $product_data->gst;
          $DATA['mrp'] = (string) $product_data->mrp;

          $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$product_data->id'");
          if($images->num_rows()>0){
            $DATA['images'] = $images->result();
          }
          else{
            $DATA['images'] = array();
          }

          $json['result'] = 'true';
          $json['data']    = $DATA;
          echo json_encode($json);
          exit;

        }
        else{

          $json['result']='false';
          $json['msg']='No Un Complete Product in Previous';
          echo json_encode($json);exit;

        }

      }


      $product_data = $this->db->query("SELECT products.*, (SELECT brands.brand_name FROM brands WHERE brands.id=products.brand_id) as brand_name
                                        FROM products
                                        WHERE products.vendor_id='$userid' AND products.is_submited=0");
      if($product_data->num_rows()>0){

        $product_data = $product_data->row();
        $DATA['id'] = (string) $product_data->id;
        $DATA['brand_id'] = (string) $product_data->brand_id;
        $DATA['brand_name'] = (string) $product_data->brand_name;
        $DATA['product_name'] = (string) $product_data->product_name;
        $DATA['about'] = (string) $product_data->about;
        $DATA['packed_of'] = (string) $product_data->packed_of;
        $DATA['material_type'] = (string) $product_data->material_type;
        $DATA['model_no'] = (string) $product_data->model_no;
        $DATA['weight'] = (string) $product_data->weight;
        $DATA['color'] = (string) $product_data->color;
        $DATA['sell_type'] = (string) $product_data->sell_type;
        $DATA['no_of_piece'] = (string) $product_data->no_of_piece;
        $DATA['min_qty'] = (string) $product_data->min_qty;
        $DATA['price_per_set'] = (string) $product_data->price_per_set;
        $DATA['gst'] = (string) $product_data->gst;
        $DATA['mrp'] = (string) $product_data->mrp;

        $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$product_data->id'");
        if($images->num_rows()>0){
          $DATA['images'] = $images->result();
        }
        else{
          $DATA['images'] = array();
        }

        $json['result'] = 'true';
        $json['data']    = $DATA;
        echo json_encode($json);
        exit;

      }
      else{

        $json['result']='false';
        $json['msg']='No Un Complete Product in Previous';
        echo json_encode($json);exit;

      }




    }
    else{
      $json['result'] = 'false';
      $json['msg']    = 'REQ: userid';
      echo json_encode($json);
      exit;
    }
}

public function add_product_image(){

    extract($_POST);
      if(!empty($userid) && !empty($product_id) && !empty($_FILES['image']['name']))
      {

        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $pre_data = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$product_id'");

        if($pre_data->num_rows()>6){$json['result']='false';$json['msg']='Can not be added, limit is 6';echo json_encode($json);exit;}




        if(!empty($_FILES['image']['name']))
        {
          $_FILES['file']['name']     = $_FILES['image']['name'];
          $_FILES['file']['type']     = $_FILES['image']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['image']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['image']['error'];
          $_FILES['file']['size']     =  $_FILES['image']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $config['file_name'] = 'product_image'.time();

          $this->load->library('upload', $config);
          $this->upload->initialize($config);
          // Upload file to server
          if($this->upload->do_upload('image')){
            $fileData = $this->upload->data();
              $image = $fileData['file_name'];
              //var_dump($userid);
              $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
              if($this->db->affected_rows()>0){
                $data['result'] = 'true';
                $data['msg']    = 'Image Saved successfully';
                echo json_encode($data);
                exit;

              }
              else{
                $data['result'] = 'false';
                $data['msg']    = 'Could Not Save, Please Try Again';
                echo json_encode($data);
                exit;
              }
          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
            echo json_encode($data);
            exit;
          }
        }
        else{
          $json['result'] = 'false';
          $json['msg']    = 'Must Select Image File';
          echo json_encode($json);
          exit;
        }


        // if(!empty($_FILES['image2']['name']))
        // {
        //   $_FILES['file']['name']     = $_FILES['image2']['name'];
        //   $_FILES['file']['type']     = $_FILES['image2']['type'];
        //   $_FILES['file']['tmp_name'] = $_FILES['image2']['tmp_name'];
        //   $_FILES['file']['error']    = $_FILES['image2']['error'];
        //   $_FILES['file']['size']     =  $_FILES['image2']['size'];
        //   // File upload configuration
        //   $uploadPath = 'assets/images/';
        //   $config['upload_path'] = $uploadPath;
        //   $config['allowed_types'] = 'jpg|jpeg|png|gif';
        //   $config['file_name'] = 'product_image'.time();
        //
        //   $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //   // Upload file to server
        //   if($this->upload->do_upload('image2')){
        //     $fileData = $this->upload->data();
        //       $image = $fileData['file_name'];
        //       //var_dump($userid);
        //       $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
        //       if($this->db->affected_rows()>0){
        //         $data['result'] = 'true';
        //         $data['msg']    = 'Image Saved successfully';
        //         echo json_encode($data);
        //         exit;
        //
        //       }
        //       else{
        //         $data['result'] = 'false';
        //         $data['msg']    = 'Could Not Save, Please Try Again';
        //         echo json_encode($data);
        //         exit;
        //       }
        //
        //   }
        //   else{
        //     $data['result'] = 'false';
        //     $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
        //     echo json_encode($data);
        //     exit;
        //   }
        // }
        //
        // if(!empty($_FILES['image3']['name']))
        // {
        //   $_FILES['file']['name']     = $_FILES['image3']['name'];
        //   $_FILES['file']['type']     = $_FILES['image3']['type'];
        //   $_FILES['file']['tmp_name'] = $_FILES['image3']['tmp_name'];
        //   $_FILES['file']['error']    = $_FILES['image3']['error'];
        //   $_FILES['file']['size']     =  $_FILES['image3']['size'];
        //   // File upload configuration
        //   $uploadPath = 'assets/images/';
        //   $config['upload_path'] = $uploadPath;
        //   $config['allowed_types'] = 'jpg|jpeg|png|gif';
        //   $config['file_name'] = 'product_image'.time();
        //
        //   $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //   // Upload file to server
        //   if($this->upload->do_upload('image3')){
        //     $fileData = $this->upload->data();
        //       $image = $fileData['file_name'];
        //       //var_dump($userid);
        //       $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
        //       if($this->db->affected_rows()>0){
        //         $data['result'] = 'true';
        //         $data['msg']    = 'Image Saved successfully';
        //         echo json_encode($data);
        //         exit;
        //
        //       }
        //       else{
        //         $data['result'] = 'false';
        //         $data['msg']    = 'Could Not Save, Please Try Again';
        //         echo json_encode($data);
        //         exit;
        //       }
        //
        //   }
        //   else{
        //     $data['result'] = 'false';
        //     $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
        //     echo json_encode($data);
        //     exit;
        //   }
        // }
        //
        // if(!empty($_FILES['image4']['name']))
        // {
        //   $_FILES['file']['name']     = $_FILES['image4']['name'];
        //   $_FILES['file']['type']     = $_FILES['image4']['type'];
        //   $_FILES['file']['tmp_name'] = $_FILES['image4']['tmp_name'];
        //   $_FILES['file']['error']    = $_FILES['image4']['error'];
        //   $_FILES['file']['size']     =  $_FILES['image4']['size'];
        //   // File upload configuration
        //   $uploadPath = 'assets/images/';
        //   $config['upload_path'] = $uploadPath;
        //   $config['allowed_types'] = 'jpg|jpeg|png|gif';
        //   $config['file_name'] = 'product_image'.time();
        //
        //   $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //   // Upload file to server
        //   if($this->upload->do_upload('image4')){
        //     $fileData = $this->upload->data();
        //       $image = $fileData['file_name'];
        //       //var_dump($userid);
        //       $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
        //       if($this->db->affected_rows()>0){
        //         $data['result'] = 'true';
        //         $data['msg']    = 'Image Saved successfully';
        //         echo json_encode($data);
        //         exit;
        //
        //       }
        //       else{
        //         $data['result'] = 'false';
        //         $data['msg']    = 'Could Not Save, Please Try Again';
        //         echo json_encode($data);
        //         exit;
        //       }
        //
        //   }
        //   else{
        //     $data['result'] = 'false';
        //     $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
        //     echo json_encode($data);
        //     exit;
        //   }
        // }
        //
        // if(!empty($_FILES['image5']['name']))
        // {
        //   $_FILES['file']['name']     = $_FILES['image5']['name'];
        //   $_FILES['file']['type']     = $_FILES['image5']['type'];
        //   $_FILES['file']['tmp_name'] = $_FILES['image5']['tmp_name'];
        //   $_FILES['file']['error']    = $_FILES['image5']['error'];
        //   $_FILES['file']['size']     =  $_FILES['image5']['size'];
        //   // File upload configuration
        //   $uploadPath = 'assets/images/';
        //   $config['upload_path'] = $uploadPath;
        //   $config['allowed_types'] = 'jpg|jpeg|png|gif';
        //   $config['file_name'] = 'product_image'.time();
        //
        //   $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //   // Upload file to server
        //   if($this->upload->do_upload('image5')){
        //     $fileData = $this->upload->data();
        //       $image = $fileData['file_name'];
        //       //var_dump($userid);
        //       $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
        //       if($this->db->affected_rows()>0){
        //         $data['result'] = 'true';
        //         $data['msg']    = 'Image Saved successfully';
        //         echo json_encode($data);
        //         exit;
        //
        //       }
        //       else{
        //         $data['result'] = 'false';
        //         $data['msg']    = 'Could Not Save, Please Try Again';
        //         echo json_encode($data);
        //         exit;
        //       }
        //
        //   }
        //   else{
        //     $data['result'] = 'false';
        //     $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
        //     echo json_encode($data);
        //     exit;
        //   }
        // }
        //
        // if(!empty($_FILES['image6']['name']))
        // {
        //   $_FILES['file']['name']     = $_FILES['image6']['name'];
        //   $_FILES['file']['type']     = $_FILES['image6']['type'];
        //   $_FILES['file']['tmp_name'] = $_FILES['image6']['tmp_name'];
        //   $_FILES['file']['error']    = $_FILES['image6']['error'];
        //   $_FILES['file']['size']     =  $_FILES['image6']['size'];
        //   // File upload configuration
        //   $uploadPath = 'assets/images/';
        //   $config['upload_path'] = $uploadPath;
        //   $config['allowed_types'] = 'jpg|jpeg|png|gif';
        //   $config['file_name'] = 'product_image'.time();
        //
        //   $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //   // Upload file to server
        //   if($this->upload->do_upload('image6')){
        //     $fileData = $this->upload->data();
        //       $image = $fileData['file_name'];
        //       //var_dump($userid);
        //       $this->db->insert('product_images', array('product_id'=>$product_id, 'image'=>$image));
        //       if($this->db->affected_rows()>0){
        //         $data['result'] = 'true';
        //         $data['msg']    = 'Image Saved successfully';
        //         echo json_encode($data);
        //         exit;
        //
        //       }
        //       else{
        //         $data['result'] = 'false';
        //         $data['msg']    = 'Could Not Save, Please Try Again';
        //         echo json_encode($data);
        //         exit;
        //       }
        //
        //   }
        //   else{
        //     $data['result'] = 'false';
        //     $data['msg']    = 'Only Allow Files: jpg|jpeg|png|gif';
        //     echo json_encode($data);
        //     exit;
        //   }
        // }



      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid,product_id, image';
        echo json_encode($json);
        exit;
      }
}



public function update_business_detail(){

    extract($_POST);
      if(!empty($userid))
      {

        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $image_saved = false;
        $detail_update = false;
        $msg = array();

        if(!empty($_FILES['image']['name']))
        {
          $_FILES['file']['name']     = $_FILES['image']['name'];
          $_FILES['file']['type']     = $_FILES['image']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['image']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['image']['error'];
          $_FILES['file']['size']     =  $_FILES['image']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $config['file_name'] = 'business_image'.time();

          $this->load->library('upload', $config);
          $this->upload->initialize($config);
          // Upload file to server
          $pre_images = $this->db->query("SELECT * FROM business_images WHERE business_images.vendor_id='$userid'");
          if($pre_images->num_rows() < 3){
            if($this->upload->do_upload('image')){
              $fileData = $this->upload->data();
                $image = $fileData['file_name'];
                //var_dump($userid);
                $this->db->insert('business_images', array('vendor_id'=>$userid, 'image'=>$image));
                $image_saved = true;
                array_push($msg, 'Image Saved');

            }
          }
          else{
            $image_saved = true;
            array_push($msg, 'Can Not Upload More thann 3 Image');
          }

        }
        $DATA = array();
        $establish_year = $this->input->post('establish_year');
        $business_type = $this->input->post('business_type');
        $desc = $this->input->post('desc');

        if(!empty($establish_year)){
          $DATA['establish_year'] = $this->input->post('establish_year');
        }
        if(!empty($business_type)){
          $DATA['business_type'] = $this->input->post('business_type');

        }
        if(!empty($desc)){
          $DATA['business_desc'] = $this->input->post('desc');
        }

        if(sizeof($DATA)>0){
          $this->db->where('users.id', $userid);
          $this->db->update('users', $DATA);
          $detail_update = true;
        }

        if($detail_update == true){
          array_push($msg, 'Detail Updated');
        }

        if($image_saved == true OR $detail_update == true){

          $json['result'] = 'true';
          $json['msg']    = implode($msg, ' & ');
          echo json_encode($json);
          exit;
        }
        else{
          $json['result'] = 'true';
          $json['msg']    = 'No Any Update Detail';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid, establish_year, business_type, desc, OPTIONAL: image';
        echo json_encode($json);
        exit;
      }
}


public function update_pan_gst_images(){

    extract($_POST);
      if(!empty($userid))
      {

        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $pan_image_saved = false;
        $gst_image_saved = false;
        $msg = array();

        if(!empty($_FILES['pan']['name']))
        {
          $_FILES['file']['name']     = $_FILES['pan']['name'];
          $_FILES['file']['type']     = $_FILES['pan']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['pan']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['pan']['error'];
          $_FILES['file']['size']     =  $_FILES['pan']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $config['file_name'] = 'pan_image'.time();

          $this->load->library('upload', $config);
          $this->upload->initialize($config);

          if($this->upload->do_upload('pan')){
            $fileData = $this->upload->data();
              $image = $fileData['file_name'];
              $this->db->where('users.id', $userid);
              $this->db->update('users', array('pan_image'=>$image));
              $pan_image_saved = true;
              array_push($msg, 'Pan Image Updated');
          }
        }
        if(!empty($_FILES['gst']['name']))
        {
          $_FILES['file']['name']     = $_FILES['gst']['name'];
          $_FILES['file']['type']     = $_FILES['gst']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['gst']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['gst']['error'];
          $_FILES['file']['size']     =  $_FILES['gst']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $config['file_name'] = 'gst_image'.time();

          $this->load->library('upload', $config);
          $this->upload->initialize($config);

          if($this->upload->do_upload('gst')){
            $fileData = $this->upload->data();
              $image = $fileData['file_name'];
              $this->db->where('users.id', $userid);
              $this->db->update('users', array('gst_image'=>$image));
              $gst_image_saved = true;
              array_push($msg, 'gst Image Updated');
          }
        }


        if($pan_image_saved == true OR $gst_image_saved == true){

          $json['result'] = 'true';
          $json['msg']    = implode($msg, ' & ');
          echo json_encode($json);
          exit;
        }
        else{
          $json['result'] = 'true';
          $json['msg']    = 'No Any Update Detail';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ: userid, pan, gst';
        echo json_encode($json);
        exit;
      }
}



public function add_product_specs(){
    extract($_POST);
      if(!empty($userid) && !empty($product_id) )
      {
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $hsn = $this->input->post('hsn');
        $product_name = $this->input->post('product_name');
        $about = $this->input->post('about');
        $packed_of = $this->input->post('packed_of');
        $material_type = $this->input->post('material_type');
        //$model_no = $this->input->post('model_no');
        $weight = $this->input->post('weight');
        $weight_unit = $this->input->post('weight_unit');

        $dimension = $this->input->post('dimension');
        $dimension_unit = $this->input->post('dimension_unit');



        $color = $this->input->post('color');
        $DATA = array();
        
        
        
        if(!empty($hsn)){
              $DATA['hsn'] = $hsn;
          }
       
        
        if(!empty($product_name)){
              $DATA['product_name'] = $product_name;
          }
          
          
          
        if(!empty($about)){
              $DATA['about'] = $about;
          }
        if(!empty($packed_of)){
              $DATA['packed_of'] = $packed_of;
          }
        if(!empty($material_type)){
              $DATA['material_type'] = $material_type;
          }
        // if(!empty($model_no)){
        //       $DATA['model_no'] = $model_no;
        //   }
        if(!empty($weight)){
              $DATA['weight'] = $weight;
        }

        if(!empty($weight_unit)){
              $DATA['weight_unit'] = $weight_unit;
        }



        if(!empty($color)){
              $DATA['color'] = $color;
        }

        if(!empty($dimension)){
              $DATA['dimension'] = $dimension;
        }

        if(!empty($dimension_unit)){
              $DATA['dimension_unit'] = $dimension_unit;
        }


        if(!empty($product_feature)){
              $DATA['product_feature'] = $product_feature;
        }



          if(sizeof($DATA)>0){
              $this->db->where('products.id',$product_id);
              $this->db->update('products', $DATA);
              if($this->db->affected_rows()>0){
                $json['result'] = 'true';
                $json['msg']    = 'Updated Successfully';
                echo json_encode($json);
                exit;
              }
              else{
                $json['result'] = 'false';
                $json['msg']    = 'Updated Failed';
                echo json_encode($json);
                exit;
              }
          }
          else{

            $json['result'] = 'false';
            $json['msg']    = 'Please Atleast One from the tags: product_name,about,packed_of,material_type,model_no,weight,color,weight_unit,dimension_unit';
            echo json_encode($json);
            exit;
          }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ:userid,product_id OPTIONAL: product_name,about,packed_of,material_type,model_no,weight,color,weight_unit,dimension_unit';
        echo json_encode($json);
        exit;
      }
}



public function add_product_specs_two(){
    extract($_POST);
      if(!empty($userid) && !empty($product_id) )
      {
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}


        $sell_type = $this->input->post('sell_type');
        $no_of_piece = $this->input->post('no_of_piece');
        $min_qty = $this->input->post('min_qty');
        $price_per_set = $this->input->post('price_per_set');
        $gst = $this->input->post('gst');
        $mrp = $this->input->post('mrp');
        $DATA = array();


        if(!empty($sell_type)){
              $DATA['sell_type'] = $sell_type;
          }
        if(!empty($no_of_piece)){
              $DATA['no_of_piece'] = $no_of_piece;
          }
        if(!empty($min_qty)){
              $DATA['min_qty'] = $min_qty;
          }
        if(!empty($price_per_set)){
              $DATA['price_per_set'] = $price_per_set;
          }
        if(!empty($gst)){
              $DATA['gst'] = $gst;
          }
        if(!empty($mrp)){
              $DATA['mrp'] = $mrp;
              $DATA['rate'] = $mrp;
          }
          
          
          
          if(!empty($mrp) && !empty($price_per_set))
          {
              $DATA['total_price_per_set'] = $price_per_set * $min_qty;
              $DATA['total_mrp'] = $mrp * $min_qty;
          }
          
          
          
          
          

          if(sizeof($DATA)>0){
              $this->db->where('products.id',$product_id);
              $this->db->update('products', $DATA);
              $json['result'] = 'true';
              $json['msg']    = 'Updated Successfully';
              echo json_encode($json);
              exit;
          }
          else{

            $json['result'] = 'false';
            $json['msg']    = 'Please Atleast One from the tags: sell_type,no_of_piece,min_qty,price_per_set,gst,mrp';
            echo json_encode($json);
            exit;
          }
      }
      else{
        $json['result'] = 'false';
        $json['msg']    = 'REQ:userid,product_id OPTIONAL: sell_type=(Pieces OR Sets),no_of_piece,min_qty,price_per_set,gst,mrp';
        echo json_encode($json);
        exit;
      }
}


// public function get_pending_orders(){
//   extract($_POST);
//       if(!empty($userid))
//       {
//
//         $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
//         if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
//
//         $booked_data = $this->db->query("SELECT * FROM orders WHERE orders.vendor_id='$userid' AND orders.status=0");
//         if($booked_data->num_rows()>0){
//
//
//           $booking_data = $this->db->query(" SELECT  orders.id as order_id,
//             orders.booking_uniqe_id as order_id_string,
//                                                   orders.userid,
//                                                   (SELECT CONCAT(users.fname, ' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
//
//                                                   (SELECT address.address FROM address WHERE address.id=orders.address_id) as delivery_address,
//
//
//
//
//
//                                                   orders.address_id,
//                                                   orders.date,
//                                                   #booking.time_slote_id,
//                                                   orders.payment_id,
//                                                   #DATE_FORMAT(orders.date, '%d-%m-%Y') as order_date,
//                                                   #(SELECT CONCAT(booked_date,' ', booked_time)) as order_date_time,
//                                                   #50 as item_count,
//                                                   #200 as total_amount,
//                                                   orders.status as current_status,
//                                                   (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
//
//
//                                                   (  CASE
//                                                           WHEN orders.status = 0 THEN 'Pending'
//                                                           WHEN orders.status = 1 THEN 'Confirmed'
//                                                           WHEN orders.status = 2 THEN 'Completed'
//                                                           WHEN orders.status = 3 THEN 'Cancelled'
//                                                           ELSE 'NA'
//                                                       END) AS status_in_string,
//
//                                                   orders.cart_meta_data
//                                           FROM orders
//                                           WHERE
//                                           orders.vendor_id='$userid' AND orders.status=0 ORDER BY orders.id DESC");
//
//             // foreach ($booking_data->result() as $book_row) {
//             //       $CART = json_decode($book_row->cart_meta_data);
//             //
//             //       $book_row->item_count = count($CART);
//             //       $book_row->total_amount = 0;
//             //       $product_list = array();
//             //       foreach ($CART as $value) {
//             //
//             //         $book_row->total_amount+=$value->final_amount;
//             //
//             //         $TEMP['product_id'] =$value->product_id;
//             //         $TEMP['name'] =$value->name;
//             //         $TEMP['image'] = $value->image;
//             //         $TEMP['quantity'] =$value->quantity;
//             //         $TEMP['final_amount'] = $value->final_amount;
//             //
//             //         $addons_ids = $value->addon;
//             //         if(!empty($addons_ids)){
//             //           $ADONDATA = $this->db->query("SELECT * FROM addon_product WHERE id IN($addons_ids)");
//             //           $TEMP['addons'] = $ADONDATA->result();
//             //           $TEMP['addon_price'] = $value->addon_price;
//             //         }
//             //         else{
//             //           $TEMP['addons'] = array();
//             //           $TEMP['addon_price'] = $value->addon_price;
//             //
//             //         }
//             //
//             //
//             //         // var_dump($value);
//             //         // die();
//             //         array_push($product_list, $TEMP);
//             //       }
//             //       $book_row->product_list = $product_list;
//             //       unset($book_row->cart_meta_data);
//             //
//             //
//             //
//             // }
//
//             $json['result'] = true;
//             $json['msg']    = 'Get Successfully';
//             $json['data'] = $booking_data->result();
//             echo json_encode($json);
//             exit;
//         }else{
//           $json['result'] = false;
//           $json['msg']    = 'No Booking Yet';
//           echo json_encode($json);
//           exit;
//
//         }
//
//
//         }
//           else{
//             $json['result'] = false;
//             $json['msg']    = 'Req: (userid)';
//             echo json_encode($json);
//             exit;
//           }
// }

public function get_pending_orders(){
  extract($_POST);
      if(!empty($userid))
      {
        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $orders_data = $this->db->query(" SELECT  orders.id as orders_id,
          orders.booking_uniqe_id as orders_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                orders.paid_amount,
                                                ifnull(order_packed.order_money,'0') as shipping_charge,
                                                
                                                DATE_FORMAT(orders.date, '%d %b') as booked_date,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                orders.status as current_status,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,
                                                
                                                
                                               
                                                
                                                

                                                (SELECT users.image FROM users WHERE users.id=orders.vendor_id) as vendor_image,
                                                (  CASE
                                                    WHEN orders.status = 0 THEN 'Pending'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'returned'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,
                                                orders.cart_meta_data,
                                                DATE_FORMAT(orders.expected_date, '%d %b %Y') as expected_date,
                                                orders.order_money as order_money
                                                
                                        FROM orders
                                        #JOIN time_slots ON time_slots.id=booking.time_slote_id
                                        
                                        LEFT JOIN order_packed ON order_packed.order_id=orders.id

                                        WHERE
                                        orders.userid='$userid' AND orders.status!=4 ORDER BY orders.id DESC");

    // var_dump($orders_data->num_rows());
    // die();
        if($orders_data->num_rows()>0){

          foreach ($orders_data->result() as $book_row) {

            
            //var_dump($book_row->shipping_charge);
            //die();



                $CART = json_decode($book_row->cart_meta_data);

                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_names = array();
                $DISCOUNT_PRICE = 0;
                
                $SHIPPING_CHARGE = $book_row->order_money;
                
                
                foreach ($CART as $value) {

                  //var_dump($value->coupon_off_price);
                  $DISCOUNT_PRICE = $value->coupon_off_price;
                  //die();


                    $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                  // var_dump($product_data->row());
                  $p = $product_data->row();

                  //var_dump($p->mrp);

                  $qty = $value->quantity;
                  $dicount_percent = (($p->mrp - $p->price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);
                  $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  $book_row->discount_amount = ROUND($discount_amount);

                  $book_row->gst_string = $p->gst.'%';

                  $gst_amount = (((int)$p->price_per_set*(int)$p->gst)/100);
                  $GST_AMOUNT = $gst_amount*$qty;
                  $book_row->gst_amount = $GST_AMOUNT;



                  // price_per_set
                  // gst
                  // mrp
                  // min_qty
                  //
                  // die();
                  //var_dump($p->price_per_set*$qty);
                  if($SHIPPING_CHARGE >0){
                      $book_row->total_amount = $SHIPPING_CHARGE;
                  }
                  else{
                      $book_row->total_amount += $p->total_price_per_set*$qty;
                  }
                  
                  array_push($product_names, $value->name);
                }
                $book_row->total_amount = $book_row->total_amount-$DISCOUNT_PRICE;
                $book_row->discounted_amount = $DISCOUNT_PRICE;

                $book_row->product_names = implode(',',$product_names);





          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $orders_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}

public function accept_return_req(){


      extract($_POST);
          if(!empty($vendor_id) AND !empty($orderid) AND !empty($req_id))
          {
            $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
            if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}


            $pre_data = $this->db->query("SELECT * FROM return_order_requests
                              WHERE return_order_requests.orderid='$orderid'");
            if($pre_data->num_rows()>0){

              $pre_data = $pre_data->row();
              $request_id = $pre_data->id;

              $this->db->where('return_order_requests.id',$request_id);
              $this->db->update("return_order_requests", array('status'=>1));

              if($this->db->affected_rows()>0){

                //Sending Notifications start
                $this->Admin_model->on_return_req_accpet($orderid);
                //Sending Notification Ends

                $data['result'] = 'true';
                $data['msg']    = 'Updated Successfully';
                echo json_encode($data);
                exit;
              }
              else{
                $data['result'] = 'false';
                $data['msg']    = 'Could Not updated';
                echo json_encode($data);
                exit;
              }

            }
            else{

              $data['result'] = 'false';
              $data['msg']    = 'No Request Data Found';
              echo json_encode($data);
              exit;

            }

          }
          else{
            $json['result'] = 'false';
            $json['msg']    = 'REQ: vendor_id, orderid,req_id';
            echo json_encode($json);
            exit;
          }
}


public function reject_return_req(){
      extract($_POST);
          if(!empty($vendor_id) AND !empty($orderid) AND !empty($req_id))
          {
            $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
            if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}


            $pre_data = $this->db->query("SELECT * FROM return_order_requests
                              WHERE return_order_requests.orderid='$orderid'");
            if($pre_data->num_rows()>0){

              $pre_data = $pre_data->row();
              $request_id = $pre_data->id;

              $this->db->where('return_order_requests.id',$request_id);
              $this->db->update("return_order_requests", array('status'=>2));

              if($this->db->affected_rows()>0){

                //Sending Notifications start
                $this->Admin_model->on_return_req_reject($orderid);
                //Sending Notification Ends

                $data['result'] = 'true';
                $data['msg']    = 'Updated Successfully';
                echo json_encode($data);
                exit;
              }
              else{
                $data['result'] = 'false';
                $data['msg']    = 'Could Not updated';
                echo json_encode($data);
                exit;
              }

            }
            else{

              $data['result'] = 'false';
              $data['msg']    = 'No Request Data Found';
              echo json_encode($data);
              exit;

            }

          }
          else{
            $json['result'] = 'false';
            $json['msg']    = 'REQ: vendor_id, orderid,req_id';
            echo json_encode($json);
            exit;
          }
}


public function get_all_return_req_as_customer(){
  extract($_POST);
      if(!empty($userid))
      {

        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
                                                orders.booking_uniqe_id as orders_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                DATE_FORMAT(orders.date_time_stamp, '%Y-%m-%d') as booked_date,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                (SELECT CONCAT(users.fname,' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
                                                (SELECT address.address FROM address WHERE address.id=orders.address_id) as customer_full_address,


                                                orders.status as current_status,
                                                orders.cancelled_by as cancelled_by,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,
                                                (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id) as vendor_image,
                                                (  CASE
                                                      WHEN orders.status = 0 THEN 'Placed'
                                                      WHEN orders.status = 1 THEN 'Packed'
                                                      WHEN orders.status = 2 THEN 'Picked'
                                                      WHEN orders.status = 3 THEN 'Shipped'
                                                      WHEN orders.status = 4 THEN 'Delivered'
                                                      WHEN orders.status = 5 THEN 'canceled'
                                                      WHEN orders.status = 6 THEN 'return'
                                                      WHEN orders.status = 7 THEN 'rescheduled'
                                                      ELSE 'NA'
                                                  END) AS status_in_string,

                                                orders.cart_meta_data
                                        FROM orders
                                        JOIN return_order_requests ON return_order_requests.orderid=orders.id

                                        WHERE

                                        #orders.id=9 AND
                                        orders.userid='$userid'
                                        ORDER BY orders.id DESC");

    // var_dump($cart_data->num_rows());
    // die();
        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {

            $book_row->return_request_id = '';
            $book_row->return_unique_id = '';
            $book_row->return_request_status = '';
            $book_row->return_request_status_string = '';
            $book_row->return_request_dated = '';
            $book_row->refund_amount = 0;



            $r_data = $this->db->query("SELECT return_order_requests.*, DATE_FORMAT(return_order_requests.dated, '%Y-%m-%d %h:%i%p') as dated FROM return_order_requests
                              WHERE return_order_requests.orderid='$book_row->orders_id'");
            if($r_data->num_rows()>0){
               $r_data = $r_data->row();

               if($r_data->status == 0){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;

                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'pending';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;


               }
               if($r_data->status == 1){

                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'accepted';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;
               }
               if($r_data->status == 2){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'cancelled';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;
               }


            }

                $CART = json_decode($book_row->cart_meta_data);



                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_names = array();
                foreach ($CART as $value) {

                    $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                  //var_dump($product_data->row());
                  $p = $product_data->row();

                  $qty = $value->quantity;
                  $dicount_percent = (($p->mrp - $p->price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);
                  $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  $book_row->discount_amount = ROUND($discount_amount);

                  $book_row->gst_string = $p->gst.'%';

                  $gst_amount = (($p->price_per_set*$p->gst)/100);
                  $GST_AMOUNT = $gst_amount*$qty;
                  $book_row->gst_amount = $GST_AMOUNT;


                  // price_per_set
                  // gst
                  // mrp
                  // min_qty
                  //
                  // die();
                  $book_row->total_amount += (($p->price_per_set-$GST_AMOUNT)+$GST_AMOUNT)*$qty;;
                  array_push($product_names, $value->name);
                }
                // $book_row->product_names = implode($product_names, ',');
                $book_row->product_names = implode(',',$product_names);

                $book_row->refund_amount = $book_row->total_amount;

                unset($book_row->cart_meta_data);

          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}

public function get_all_return_req(){
  extract($_POST);
      if(!empty($vendor_id))
      {

        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
                                                orders.booking_uniqe_id as orders_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                DATE_FORMAT(orders.date_time_stamp, '%Y-%m-%d') as booked_date,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                (SELECT CONCAT(users.fname,' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
                                                (SELECT address.address FROM address WHERE address.id=orders.address_id) as customer_full_address,


                                                orders.status as current_status,
                                                orders.cancelled_by as cancelled_by,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,
                                                (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id) as vendor_image,
                                                (  CASE
                                                      WHEN orders.status = 0 THEN 'Placed'
                                                      WHEN orders.status = 1 THEN 'Packed'
                                                      WHEN orders.status = 2 THEN 'Picked'
                                                      WHEN orders.status = 3 THEN 'Shipped'
                                                      WHEN orders.status = 4 THEN 'Delivered'
                                                      WHEN orders.status = 5 THEN 'canceled'
                                                      WHEN orders.status = 6 THEN 'return'
                                                      WHEN orders.status = 7 THEN 'rescheduled'
                                                      ELSE 'NA'
                                                  END) AS status_in_string,

                                                orders.cart_meta_data
                                        FROM orders
                                        JOIN return_order_requests ON return_order_requests.orderid=orders.id

                                        WHERE

                                        #orders.id=9 AND
                                        orders.vendor_id='$vendor_id'
                                        ORDER BY orders.id DESC");

    // var_dump($cart_data->num_rows());
    // die();
        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {

            $book_row->return_request_id = '';
            $book_row->return_unique_id = '';
            $book_row->return_request_status = '';
            $book_row->return_request_status_string = '';
            $book_row->return_request_dated = '';
            $book_row->refund_amount = 0;



            $r_data = $this->db->query("SELECT return_order_requests.*, DATE_FORMAT(return_order_requests.dated, '%Y-%m-%d %h:%i%p') as dated FROM return_order_requests
                              WHERE return_order_requests.orderid='$book_row->orders_id'");
            if($r_data->num_rows()>0){
               $r_data = $r_data->row();

               if($r_data->status == 0){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;

                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'pending';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;


               }
               if($r_data->status == 1){

                 // var_dump($r_data);
                 // die();


                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'accepted';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;

               }
               if($r_data->status == 2){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'cancelled';
                 $book_row->return_request_dated = $r_data->dated;
                 $book_row->return_request_image = $r_data->image;
                 $book_row->return_request_video = $r_data->video;
                 $book_row->return_request_reason = $r_data->reason;
               }


            }

                $CART = json_decode($book_row->cart_meta_data);



                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_names = array();
                foreach ($CART as $value) {

                    $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                  //var_dump($product_data->row());
                  $p = $product_data->row();

                  $qty = $value->quantity;
                  $dicount_percent = (($p->mrp - $p->price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);
                  $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  $book_row->discount_amount = ROUND($discount_amount);

                  $book_row->gst_string = $p->gst.'%';

                  $gst_amount = (($p->price_per_set*$p->gst)/100);
                  $GST_AMOUNT = $gst_amount*$qty;
                  $book_row->gst_amount = $GST_AMOUNT;


                  // price_per_set
                  // gst
                  // mrp
                  // min_qty
                  //
                  // die();
                  $book_row->total_amount += (($p->price_per_set-$GST_AMOUNT)+$GST_AMOUNT)*$qty;;
                  array_push($product_names, $value->name);
                }
                // $book_row->product_names = implode($product_names, ',');

                $book_row->product_names = implode(',',$product_names);

                $book_row->refund_amount = $book_row->total_amount;

                unset($book_row->cart_meta_data);

          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: vendor_id';
        echo json_encode($json);
        exit;
      }
}

public function get_orders_list(){
  extract($_POST);
      if(!empty($vendor_id) && !empty($type))
      {

        $order_type = 'all';
        $joins = '';
        if($type == 'all'){
          $order_type = "";
        }
        if($type == 'placed'){
          $order_type = "AND orders.status='0'";
        }
        if($type == 'packed'){
          $order_type = "AND orders.status='1'";
        }
        if($type == 'picked'){
          $order_type = "AND orders.status='2'";
        }
        if($type == 'shipped'){
          $order_type = "AND orders.status='3'";
        }
        if($type == 'delivered'){
          $order_type = "AND orders.status='4'";
        }
        if($type == 'cancelled'){
          $order_type = "AND orders.status='5'";
        }
        if($type == 'return'){
          $joins = "JOIN return_order_requests ON return_order_requests.orderid=orders.id";
          $order_type ="";
        }
        if($type == 'reschedule'){
          $order_type = "AND orders.status='7'";
        }

        // var_dump($joins);
        // die();



        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
                                                orders.booking_uniqe_id as orders_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                
                                                ifnull(order_packed.order_money,'0') as shipping_charge,
                                                
                                                DATE_FORMAT(orders.date_time_stamp, '%Y-%m-%d') as booked_date,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                (SELECT CONCAT(users.fname,' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
                                                (SELECT address.address FROM address WHERE address.id=orders.address_id) as customer_full_address,


                                                orders.status as current_status,
                                                orders.cancelled_by as cancelled_by,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,


                                                (SELECT payment_method.is_transportation_seen FROM payment_method WHERE payment_method.id=orders.payment_id) as is_transportation_seen,
                                                
                                                
                                               
                                                
                                                

                                                (SELECT users.image FROM users WHERE users.id=orders.vendor_id) as vendor_image,
                                                (  CASE
                                                      WHEN orders.status = 0 THEN 'Placed'
                                                      WHEN orders.status = 1 THEN 'Packed'
                                                      WHEN orders.status = 2 THEN 'Picked'
                                                      WHEN orders.status = 3 THEN 'Shipped'
                                                      WHEN orders.status = 4 THEN 'Delivered'
                                                      WHEN orders.status = 5 THEN 'canceled'
                                                      WHEN orders.status = 6 THEN 'return'
                                                      WHEN orders.status = 7 THEN 'rescheduled'
                                                      ELSE 'NA'
                                                  END) AS status_in_string,



                                                orders.cart_meta_data,
                                                orders.order_money
                                                
                                        FROM orders
                                        $joins
                                        
                                        LEFT JOIN order_packed ON order_packed.order_id=orders.id

                                        WHERE
                                        #orders.id=9 AND
                                        orders.vendor_id='$vendor_id'
                                        $order_type
                                        ORDER BY orders.id DESC");






    // var_dump($cart_data->num_rows());
    // die();
        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {

            $book_row->return_request_id = '';
            $book_row->return_unique_id = '';
            $book_row->return_request_status = '';
            $book_row->return_request_status_string = '';
            $book_row->return_request_dated = '';
            $book_row->refund_amount = 0;



            $r_data = $this->db->query("SELECT return_order_requests.*,
                                        DATE_FORMAT(return_order_requests.dated, '%Y-%m-%d %h:%i%p') as dated FROM return_order_requests
                              WHERE return_order_requests.orderid='$book_row->orders_id'");
            if($r_data->num_rows()>0){
               $r_data = $r_data->row();

               if($r_data->status == 0){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;

                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'pending';
                 $book_row->return_request_dated = $r_data->dated;


               }
               if($r_data->status == 1){

                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'accepted';
                 $book_row->return_request_dated = $r_data->dated;
               }
               if($r_data->status == 2){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_unique_id = $r_data->unique_id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'cancelled';
                 $book_row->return_request_dated = $r_data->dated;
               }


            }

                $CART = json_decode($book_row->cart_meta_data);

                // var_dump($CART);
                // die();

                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_names = array();
                $DISCOUNT_PRICE = 0;
                $TOTAL_AMOUNT = 0;

                foreach ($CART as $value) {


                      $DISCOUNT_PRICE = $value->coupon_off_price;


                    // var_dump($value->product_id);
                    $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                //   var_dump($product_data->row());



                  $p = $product_data->row();

                  $qty = $value->quantity;

                //   var_dump($p->mrp);
                  $dicount_percent = (($p->total_mrp - $p->total_price_per_set)*100)/($p->mrp);
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);
                  $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  $book_row->discount_amount = ROUND($discount_amount);

                  $book_row->gst_string = $p->gst.'%';


                  if($p->gst == "No GST")
                  {
                      $p->gst = 0;
                  }
                  else
                  {
                      $p->gst = $p->gst;
                  }


                  $gst_amount = (($p->total_price_per_set*$p->gst)/100);
                  $GST_AMOUNT = $gst_amount*$qty;
                  $book_row->gst_amount = $GST_AMOUNT;


                  // price_per_set
                  // gst
                  // mrp
                  // min_qty
                  //
                  // die();
                  $book_row->total_amount += (($p->total_price_per_set-$GST_AMOUNT)+$GST_AMOUNT)*$qty;

                  $TOTAL_AMOUNT += (($p->total_price_per_set-$GST_AMOUNT)+$GST_AMOUNT)*$qty;
                  array_push($product_names, $value->name);
                }


                //$TOTAL_AMOUNT =  $TOTAL_AMOUNT-$DISCOUNT_PRICE;
                
                if($book_row->order_money >0 ){
                   $book_row->total_amount = $book_row->order_money; 
                }
                else{
                   $book_row->total_amount = $TOTAL_AMOUNT-$DISCOUNT_PRICE; 
                }
                
                //$book_row->total_amount = $TOTAL_AMOUNT-$DISCOUNT_PRICE;
                // $book_row->product_names = implode($product_names, ',');

                $book_row->product_names = implode(',',$product_names);



                unset($book_row->cart_meta_data);

          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: vendor_id, type';
        echo json_encode($json);
        exit;
      }
}




public function get_completed_orders(){
  extract($_POST);
      if(!empty($userid))
      {
        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
          orders.booking_uniqe_id as orders_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                ifnull(order_packed.order_money,'0') as shipping_charge,
                                                DATE_FORMAT(orders.date, '%d %b') as booked_date,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                orders.status as current_status,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,

                                                (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id) as vendor_image,
                                                (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,
                                                orders.cart_meta_data,
                                                orders.order_money
                                        FROM orders
                                        #JOIN time_slots ON time_slots.id=booking.time_slote_id
                                        
                                        LEFT JOIN order_packed ON order_packed.order_id=orders.id

                                        WHERE
                                        orders.userid='$userid' AND orders.status=4 ORDER BY orders.id DESC");

    // var_dump($cart_data->num_rows());
    // die();
        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {

            $book_row->return_request_id = '';
            $book_row->return_request_status = '';
            $book_row->return_request_status_string = '';


            $r_data = $this->db->query("SELECT * FROM return_order_requests
                              WHERE return_order_requests.orderid='$book_row->orders_id'");
            if($r_data->num_rows()>0){
               $r_data = $r_data->row();

               if($r_data->status == 0){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'pending';
               }
               if($r_data->status == 1){

                 $book_row->return_request_id = $r_data->id;

                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'accepted';
               }
               if($r_data->status == 2){
                 $book_row->return_request_id = $r_data->id;

                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'cancelled';
               }

            }




                $CART = json_decode($book_row->cart_meta_data);

                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_names = array();
                $Discount_Amount = 0;

                foreach ($CART as $value) {


                    $Discount_Amount = $value->coupon_off_price;

                    $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                  //var_dump($product_data->row());
                  $p = $product_data->row();

                  $qty = $value->quantity;
                  $dicount_percent = (($p->mrp - $p->price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);
                  $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  $book_row->discount_amount = ROUND($discount_amount);

                  $book_row->gst_string = $p->gst.'%';

                  $gst_amount = ((intval($p->price_per_set)*intval($p->gst))/100);
                  $GST_AMOUNT = $gst_amount*$qty;
                  $book_row->gst_amount = $GST_AMOUNT;



                  // price_per_set
                  // gst
                  // mrp
                  // min_qty
                  //
                  // die();
                  $book_row->total_amount += $p->total_price_per_set*$qty;
                  array_push($product_names, $value->name);
                }
                
                if($book_row->order_money > 0){
                    $book_row->total_amount = $book_row->order_money;
                }
                else{
                    $book_row->total_amount = $book_row->total_amount-$Discount_Amount;
                }
                
                
                
                $book_row->discount_amount = $Discount_Amount;

                // $book_row->product_names = implode($product_names, ',');

                $book_row->product_names = implode(',',$product_names);





          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}
// public function get_completed_orders(){
//   extract($_POST);
//       if(!empty($userid))
//       {
//
//         $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
//         if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
//
//         $booked_data = $this->db->query("SELECT * FROM orders WHERE orders.vendor_id='$userid'");
//         if($booked_data->num_rows()>0){
//
//
//           $booking_data = $this->db->query(" SELECT  orders.id as order_id,
//             orders.booking_uniqe_id as order_id_string,
//                                                   orders.userid,
//                                                   (SELECT CONCAT(users.fname, ' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
//
//                                                   (SELECT address.address FROM address WHERE address.id=orders.address_id) as delivery_address,
//
//
//
//
//
//                                                   orders.address_id,
//                                                   orders.date,
//                                                   #booking.time_slote_id,
//                                                   orders.payment_id,
//                                                   #DATE_FORMAT(orders.date, '%d-%m-%Y') as order_date,
//                                                   #(SELECT CONCAT(booked_date,' ', booked_time)) as order_date_time,
//                                                   #50 as item_count,
//                                                   #200 as total_amount,
//                                                   orders.status as current_status,
//                                                   (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
//
//
//                                                   (  CASE
//                                                           WHEN orders.status = 0 THEN 'Pending'
//                                                           WHEN orders.status = 1 THEN 'Confirmed'
//                                                           WHEN orders.status = 2 THEN 'Completed'
//                                                           WHEN orders.status = 3 THEN 'Cancelled'
//                                                           ELSE 'NA'
//                                                       END) AS status_in_string,
//
//                                                   orders.cart_meta_data
//                                           FROM orders
//                                           WHERE
//                                           orders.vendor_id='$userid' ORDER BY orders.id DESC");
//
//             // foreach ($booking_data->result() as $book_row) {
//             //       $CART = json_decode($book_row->cart_meta_data);
//             //
//             //       $book_row->item_count = count($CART);
//             //       $book_row->total_amount = 0;
//             //       $product_list = array();
//             //       foreach ($CART as $value) {
//             //
//             //         $book_row->total_amount+=$value->final_amount;
//             //
//             //         $TEMP['product_id'] =$value->product_id;
//             //         $TEMP['name'] =$value->name;
//             //         $TEMP['image'] = $value->image;
//             //         $TEMP['quantity'] =$value->quantity;
//             //         $TEMP['final_amount'] = $value->final_amount;
//             //
//             //         $addons_ids = $value->addon;
//             //         if(!empty($addons_ids)){
//             //           $ADONDATA = $this->db->query("SELECT * FROM addon_product WHERE id IN($addons_ids)");
//             //           $TEMP['addons'] = $ADONDATA->result();
//             //           $TEMP['addon_price'] = $value->addon_price;
//             //         }
//             //         else{
//             //           $TEMP['addons'] = array();
//             //           $TEMP['addon_price'] = $value->addon_price;
//             //
//             //         }
//             //
//             //
//             //         // var_dump($value);
//             //         // die();
//             //         array_push($product_list, $TEMP);
//             //       }
//             //       $book_row->product_list = $product_list;
//             //       unset($book_row->cart_meta_data);
//             //
//             //
//             //
//             // }
//
//             $json['result'] = true;
//             $json['msg']    = 'Get Successfully';
//             $json['data'] = $booking_data->result();
//             echo json_encode($json);
//             exit;
//         }else{
//           $json['result'] = false;
//           $json['msg']    = 'No Booking Yet';
//           echo json_encode($json);
//           exit;
//
//         }
//
//
//         }
//           else{
//             $json['result'] = false;
//             $json['msg']    = 'Req: (userid)';
//             echo json_encode($json);
//             exit;
//           }
// }


public function get_order_detail(){
  extract($_POST);
      if(!empty($userid) && !empty($order_id))
      {
        $vendor_id = $userid;
        $booked_id = $order_id;
        //count_previous address
        $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $booked_data = $this->db->query("SELECT * FROM orders WHERE orders.id='$booked_id'");
        if($booked_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong booked_id';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as Booking_id,
          orders.vendor_id,
          (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id limit 1) as vendor_image,
          (SELECT vendor.name FROM vendor WHERE vendor.id=orders.vendor_id limit 1) as vendor_name,
          users.id as user_id,
          CONCAT(users.fname,' ', users.lname) as user_name,
          users.image as user_image,
          users.mobile as user_mobile,
          '5.0' as user_rating,






                                                orders.booking_uniqe_id as Booking_id_string,
                                                orders.userid,
                                                orders.address_id,
                                                orders.date,
                                                orders.time_slote_id,
                                                orders.payment_id,
                                                DATE_FORMAT(orders.date_time_stamp, '%d-%m-%Y %h:%i%p') as booked_date,
                                                50 as item_count,
                                                200 as total_amount,
                                                orders.status as current_status,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,


                                                (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,

                                                orders.cart_meta_data
                                        FROM orders
                                        JOIN users ON users.id=orders.userid

                                        WHERE
                                        orders.id='$booked_id'");
        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {
                $CART = json_decode($book_row->cart_meta_data);

                // var_dump($CART);
                // die();
                $book_row->item_count = count($CART);
                $book_row->total_amount = 0;
                $product_list = array();
                foreach ($CART as $value) {
                  // var_dump($value);
                  // die();

                  $book_row->total_amount+=$value->final_amount;

                  $TEMP['product_id'] =$value->product_id;
                  $TEMP['name'] =$value->name;
                  $TEMP['image'] = $value->image;
                  $TEMP['quantity'] =$value->quantity;
                  $TEMP['final_amount'] = $value->final_amount;
                  $TEMP['addon_price'] = $value->addon_price;
                  $addon_ids = $value->addon;

                  if(!empty($addon_ids)){
                    $ADDON = $this->db->query("SELECT * FROM addon_product WHERE id IN ($value->addon)");
                    if($ADDON->num_rows()>0){
                      $TEMP['addons'] = $ADDON->result();
                    }
                    else{
                      $TEMP['addons'] = array();
                    }
                  }
                  else{
                    $TEMP['addons'] = array();
                  }




                  // var_dump($value->addon);
                  // die();


                  array_push($product_list, $TEMP);
                }
                $book_row->product_list = $product_list;

                unset($book_row->cart_meta_data);


          }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->result();
            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid, order_id';
        echo json_encode($json);
        exit;
      }
}






  public function get_best_offers(){
    $offers = $this->db->query("SELECT * FROM offers ORDER BY offers.id DESC");
    if($offers->num_rows()>0){
      $data['result'] = 'true';
      $data['msg']    = 'get successfully';
      $data['data']    = $offers->result();

      echo json_encode($data);
      exit;

    }
    else{
      $data['result'] = 'false';
      $data['msg']    = 'No offer Found';
      echo json_encode($data);
      exit;
    }
  }

  public function get_vendors(){
    extract($_POST);
        if(!empty($userid) && !empty($cat_id))
        {
          //count_previous address
          $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
          if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
          $vendors = $this->db->query("SELECT  id,
                                                users.id as vendor_id,
                                                (SELECT category.category_name FROM category WHERE category.id=users.cat_id) as cat_name,
                                                (SELECT category.id FROM category WHERE category.id=users.cat_id) as cat_id,
                                                CONCAT(fname, ' ', lname) as vendor_name,
                                                image, about, '5.0' as rating
                                         FROM users
                                         WHERE users.user_type='vendor'
                                         ORDER BY users.id DESC ");

          // if(!empty($this->input->post('scat_id'))){
          //
          //   $scat_id = $this->input->post('scat_id');
          //
          //   $vendors = $this->db->query(" SELECT  vendor.id,
          //                                         vendor.id as vendor_id,
          //                                         (SELECT category.category_name FROM category WHERE category.id=vendor.cat_id) as cat_name,
          //                                         (SELECT category.id FROM category WHERE category.id=vendor.cat_id) as cat_id,
          //                                         vendor.name,
          //                                         vendor.image,
          //                                         vendor.about,
          //                                         '5.0' as rating
          //                                  FROM products
          //                                  JOIN vendor ON products.vendor_id=vendor.id
          //                                  WHERE products.scat_id='$scat_id' ORDER BY vendor.id DESC ");
          //
          // }


          if($vendors->num_rows()>0){

              $json['result'] = true;
              $json['msg']    = 'Get Successfully';

              $json['cat_name']    = $vendors->row()->cat_name;
              $json['cat_id']    = $vendors->row()->cat_id;
              $json['data']    = $vendors->result();


              echo json_encode($json);
              exit;
          }
          else{
            $json['result'] = false;
            $json['msg']    = 'No Vendor Found';
            echo json_encode($json);
            exit;
          }
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'REQ: userid, cat_id';
          echo json_encode($json);
          exit;
        }
  }


  public function add_to_cart(){
    extract($_POST);
    if(!empty($cat_id) &&  !empty($product_id) && !empty($userid)) //&& !empty($qty)
    {
      //!empty($vendor_id) &&

      $product = $this->db->query("SELECT * FROM products WHERE products.id='$product_id'");
      if($product->num_rows()==0){$data['result'] = 'false';$data['msg']    = 'No Any Product Found on this Id ';echo json_encode($data);exit;}

      $product = $product->row();

      //cart pre Data
      //msg 0 - empty cart, cart have current vendors products
      $msg = $this->Api_Model->pre_cart_data($cat_id, $userid, $product_id);

      // var_dump($msg);
      //
      // die();

      if($msg == 0){

        if(empty($qty)){
            $this->db->where('cart.user_id',$userid);
            $this->db->where('cart.product_id',$product_id);
            $this->db->delete('cart');

            $data['result'] = 'true';
            $data['msg']    = 'Updated Successfully';
            $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
            $data['cart_total']   = $CART_DETAIL['cart_total'];
            $data['qty'] = $CART_DETAIL['qty'];
            $data['addon_price'] = $CART_DETAIL['addon_price'];
            $data['product_total_price'] = $CART_DETAIL['product_total_price'];
            echo json_encode($data);
            exit;
        }


        $addons = $this->input->post('addons');
        //echo 'update';
        $ADDON_PRICE = null;
        if(!empty($addons)){
          $addons = explode(',', $addons);
          $addons = array_filter($addons);
        //   $addons_ids = implode($addons, ',');

        $addons_ids = implode(',',$addons);

          $addons_data = $this->db->query("SELECT addon_product.*, SUM(price) as addons_sum
                                        FROM addon_product
                                        WHERE addon_product.id IN($addons_ids)");
          $addons_data = $addons_data->row();
          $ADDON_PRICE = $addons_data->addons_sum;
          //var_dump($addons_data);
          $DB['addon'] = $addons_ids;
          $DB['addon_price'] = $addons_data->addons_sum;
          // var_dump($DB);
          // die()
        }



        $DB['user_id'] = $userid;
        $DB['vendor_id'] = $product->vendor_id;
        $DB['product_id'] = $product_id;
        $DB['name'] = $product->product_name;
        $DB['image'] = $product->image;
        $DB['price'] = $product->rate;
        $DB['total_price_per_set'] = $product->total_price_per_set;
        $DB['total_mrp'] = $product->total_mrp;
        $DB['quantity '] = $qty;
        // $DB['total_amount'] = $qty*$product->rate;
        // $DB['final_amount'] = $qty*$product->rate;

        if(!empty($ADDON_PRICE)){
          $total = ($qty*$product->rate)+$ADDON_PRICE;
          $DB['total_amount'] = $total;
          $DB['final_amount'] = $total;
        }
        else{
          $DB['total_amount'] = $qty*$product->rate;
          $DB['final_amount'] = $qty*$product->rate;
        }



        $this->db->insert("cart", $DB);
        if($this->db->affected_rows()>0){

          $data['result'] = 'true';
          $data['msg']    = 'Added Successfully';
          $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
          $data['cart_total']   = $CART_DETAIL['cart_total'];
          $data['qty'] = $CART_DETAIL['qty'];
          $data['addon_price'] = $CART_DETAIL['addon_price'];
          $data['product_total_price'] = $CART_DETAIL['product_total_price'];


          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'Adding Failed to Cart';
          echo json_encode($data);
          exit;
        }


      }
      elseif($msg == 1){

        if(empty($qty)){
            $this->db->where('cart.user_id',$userid);
            $this->db->where('cart.product_id',$product_id);
            $this->db->delete('cart');

            $data['result'] = 'true';
            $data['msg']    = 'Updated Successfully';
            $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
            $data['cart_total']   = $CART_DETAIL['cart_total'];
            $data['qty'] = $CART_DETAIL['qty'];
            $data['addon_price'] = $CART_DETAIL['addon_price'];
            $data['product_total_price'] = $CART_DETAIL['product_total_price'];
            echo json_encode($data);
            exit;
        }
          //check product is already exits with current vendor id;
          // var_dump($product_id);
          // var_dump($userid);
          $c_product_if_exits = $this->db->query("SELECT *
                                        FROM cart
                                        WHERE cart.user_id='$userid' AND cart.product_id='$product_id'");
          // var_dump($c_product_if_exits->num_rows());
          // die();


          if($c_product_if_exits->num_rows()>0){
            //update here
            $addons = $this->input->post('addons');
            //echo 'update';
            $ADDON_PRICE = null;
            if(!empty($addons)){
              $addons = explode(',', $addons);
              $addons = array_filter($addons);
            //   $addons_ids = implode($addons, ',');

            $addons_ids = implode(',',$addons);
              $addons_data = $this->db->query("SELECT addon_product.*, SUM(price) as addons_sum
                                            FROM addon_product
                                            WHERE addon_product.id IN($addons_ids)");
              $addons_data = $addons_data->row();
              $ADDON_PRICE = $addons_data->addons_sum;
              //var_dump($addons_data);
              $DB['addon'] = $addons_ids;
              $DB['addon_price'] = $addons_data->addons_sum;
              // var_dump($DB);
              // die()
            }



            $DB['user_id'] = $userid;
            //$DB['vendor_id'] = $vendor_id;
            $DB['product_id'] = $product_id;
            $DB['name'] = $product->product_name;
            $DB['image'] = $product->image;
            $DB['price'] = $product->rate;
            $DB['quantity'] = $qty;

            if(!empty($ADDON_PRICE)){
              $total = ($qty*$product->rate)+$ADDON_PRICE;
              $DB['total_amount'] = $total;
              $DB['final_amount'] = $total;
            }
            else{
              $DB['total_amount'] = $qty*$product->rate;
              $DB['final_amount'] = $qty*$product->rate;
            }
            
            
            
            $DB['total_price_per_set'] = $product->total_price_per_set;
            $DB['total_mrp'] = $product->total_mrp;
            

            $this->db->where("cart.product_id", $product_id);
            $this->db->update("cart", $DB);

            $data['result'] = 'true';
            $data['msg']    = 'Update Successfully';
            $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
            $data['cart_total']   = $CART_DETAIL['cart_total'];
            $data['qty'] = $CART_DETAIL['qty'];
            $data['addon_price'] = $CART_DETAIL['addon_price'];
            $data['product_total_price'] = $CART_DETAIL['product_total_price'];

            echo json_encode($data);
            exit;
            //echo 'update';
            //exit;
          }
          else{

            if(empty($qty)){
                $this->db->where('cart.user_id',$userid);
                $this->db->where('cart.product_id',$product_id);
                $this->db->delete('cart');

                $data['result'] = 'true';
                $data['msg']    = 'Updated Successfully';
                $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
                $data['cart_total']   = $CART_DETAIL['cart_total'];
                $data['qty'] = $CART_DETAIL['qty'];
                $data['addon_price'] = $CART_DETAIL['addon_price'];
                $data['product_total_price'] = $CART_DETAIL['product_total_price'];
                echo json_encode($data);
                exit;
            }


            $addons = $this->input->post('addons');
            //echo 'update';
            $ADDON_PRICE = null;
            if(!empty($addons)){
              $addons = explode(',', $addons);
              $addons = array_filter($addons);
            //   $addons_ids = implode($addons, ',');

            $addons_ids = implode(',',$addons);

              $addons_data = $this->db->query("SELECT addon_product.*, SUM(price) as addons_sum
                                            FROM addon_product
                                            WHERE addon_product.id IN($addons_ids)");
              $addons_data = $addons_data->row();
              $ADDON_PRICE = $addons_data->addons_sum;
              //var_dump($addons_data);
              $DB['addon'] = $addons_ids;
              $DB['addon_price'] = $addons_data->addons_sum;
              // var_dump($DB);
              // die()
            }
            //die();

            $DB['user_id'] = $userid;
            $DB['vendor_id'] = $product->vendor_id;
            $DB['product_id'] = $product_id;
            $DB['name'] = $product->product_name;
            $DB['image'] = $product->image;
            $DB['price'] = $product->rate;
            $DB['quantity '] = $qty;


            if(!empty($ADDON_PRICE)){
              $total = ($qty*$product->rate)+$ADDON_PRICE;
              $DB['total_amount'] = $total;
              $DB['final_amount'] = $total;
            }
            else{
              $DB['total_amount'] = $qty*$product->rate;
              $DB['final_amount'] = $qty*$product->rate;
            }
            
            
            $DB['total_price_per_set'] = $product->total_price_per_set;
            $DB['total_mrp'] = $product->total_mrp;

            $this->db->insert("cart", $DB);
            if($this->db->affected_rows()>0){
              $data['result'] = 'true';
              $data['msg']    = 'Added Successfully';
              $CART_DETAIL = $this->Api_Model->cart_detail($cat_id, $userid, $product_id);
              $data['cart_total']   = $CART_DETAIL['cart_total'];
              $data['qty'] = $CART_DETAIL['qty'];
              $data['addon_price'] = $CART_DETAIL['addon_price'];
              $data['product_total_price'] = $CART_DETAIL['product_total_price'];

              echo json_encode($data);
              exit;

            }
            else{
              $data['result'] = 'false';
              $data['msg']    = 'Adding Failed to Cart';
              echo json_encode($data);
              exit;
            }
          }
      }
      else{
        $data['result'] = 'false';
        $data['msg']    = 'Something Went Wrong';
        echo json_encode($data);
        exit;
      }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(cat_id,product_id,qty,userid)';
      echo json_encode($data);
      exit;

    }
  }



  public function wishlist(){
      extract($_POST);
      if(!empty($userid))
      {

          $products = $this->Api_Model->get_wish_products($userid);

          if($products->num_rows()>0){

            foreach ($products->result() as $p) {


              $p->is_in_wishlist = 'false';
              $is_in_wishlist = $this->db->query("SELECT * FROM wish_list WHERE wish_list.product_id='$p->id'");
              if($is_in_wishlist->num_rows()>0){
                $p->is_in_wishlist = 'true';
              }



              $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
              $f_image = false;

              if($images->num_rows()>0){

                $p->images = $images->result();
                $first_image = $images->row();
                if($f_image == false){
                  $p->image = $first_image->image;
                  $f_image = TRUE;
                }
                // var_dump($p->image);
                // var_dump($f_image);
                // die();

              }
              else{

                  $p->images = array();
              }




            }
  //          var_dump($products);



            $data['result'] = 'true';
            $data['msg']    = 'get successfully';
            // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
            // $data['cat_name']    = (string) $cat_name;
            // $data['start_from']    = $start_from;
            $data['data']    = $products->result();

            echo json_encode($data);
            exit;

          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'No Data Found';
            echo json_encode($data);
            exit;
          }

      }
      else{

        $data['result'] = 'false';
        $data['msg']    = 'Please provide parameters(userid)';
        echo json_encode($data);
        exit;

      }
  }


public function all_product_id_name(){

$products = $this->db->query("SELECT products.id, products.product_name FROM products
                              WHERE products.is_deleted=0
                              AND products.is_submited=1
                              AND products.status=1");
if($products->num_rows()>0){
  $data['result'] = 'true';
  $data['msg']    = 'get successfully';
  $data['data']    = $products->result();

  echo json_encode($data);
  exit;
}
else{
  $data['result'] = 'false';
  $data['msg']    = 'No Data Found';
  echo json_encode($data);
  exit;

}



}


public function get_finance_detail(){

  extract($_POST);
  if(!empty($userid))
  {

        $data['result'] = 'true';
        $data['msg']    = 'get successfully';
        $data['total_ordered_amount']  = $this->Api_Model->total_ordered_amount($userid);
        $data['data']  = $this->Api_Model->finance_detail($userid);

        echo json_encode($data);
        exit;
  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'Req: userid';
    echo json_encode($data);
    exit;
  }
}
public function get_buying_details(){

  extract($_POST);
  if(!empty($userid))
  {
    // Buying Details Start
    // 1. Total Ordered Count
    // 2. Total Returned Count
    // 3. Total Pending Orderd Count
    // 4. Total Completed Ordered Count
    // 5. Total Ordered Ammount
    // 6. Total Returned Amount
    // 7. Total Cancelled Order Count
    // End

        $data['result'] = 'true';
        $data['msg']    = 'get successfully';
        $data['total_orders_count']  = $this->Api_Model->total_orders_count($userid);
        $data['total_returned_count']  = $this->Api_Model->total_returned_count($userid);
        $data['total_inprocess_order_count']  = $this->Api_Model->total_inprocess_order_count($userid);
        $data['total_completed_order_count']  = $this->Api_Model->total_completed_order_count($userid);
        $data['total_ordered_amount']  = $this->Api_Model->total_ordered_amount($userid);
        $data['total_returned_amount']  = $this->Api_Model->total_returned_amount($userid);
        $data['total_cancelled_count']  = $this->Api_Model->total_cancelled_count($userid);


        echo json_encode($data);
        exit;
  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'Req: userid';
    echo json_encode($data);
    exit;
  }
}

  public function search_products(){
      extract($_POST);
      if(!empty($keyword) && !empty($userid))
      {


          $products = $this->Api_Model->search_products($keyword, $userid);

          if($products->num_rows()>0){

            foreach ($products->result() as $p) {


              $p->is_in_wishlist = 'false';
              $is_in_wishlist = $this->db->query("SELECT * FROM wish_list WHERE wish_list.product_id='$p->id'");
              if($is_in_wishlist->num_rows()>0){
                $p->is_in_wishlist = 'true';
              }



              $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
              $f_image = false;

              if($images->num_rows()>0){

                $p->images = $images->result();
                $first_image = $images->row();
                if($f_image == false){
                  $p->image = $first_image->image;
                  $f_image = TRUE;
                }
                // var_dump($p->image);
                // var_dump($f_image);
                // die();

              }
              else{

                  $p->images = array();
              }




            }
  //          var_dump($products);



            $data['result'] = 'true';
            $data['msg']    = 'get successfully';
            // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
            // $data['cat_name']    = (string) $cat_name;
            // $data['start_from']    = $start_from;
            $data['data']    = $products->result();

            echo json_encode($data);
            exit;

          }
          else{
            $data['result'] = 'false';
            $data['msg']    = 'No Data Found';
            echo json_encode($data);
            exit;
          }

      }
      else{

        $data['result'] = 'false';
        $data['msg']    = 'Please provide parameters(keyword, userid)';
        echo json_encode($data);
        exit;

      }
  }

public function get_products(){
    extract($_POST);
    if(!empty($userid) && !empty($r_cat))
    {



        //$cat_name = $this->db->query("SELECT * FROM category WHERE category.id=16")->row()->category_name;
        // $start_from = $this->db->query("SELECT MIN(products.rate) as start_from
        //                               FROM products
        //                               WHERE  products.vendor_id='$vendor_id' AND products.status=1")->row()->start_from;

        $products = $this->Api_Model->get_products($userid, $r_cat, $scat_id=null);





        if($products->num_rows()>0){

          foreach ($products->result() as $p) {


            $p->is_in_wishlist = 'false';
            $is_in_wishlist = $this->db->query("SELECT * FROM wish_list WHERE wish_list.product_id='$p->id'");
            if($is_in_wishlist->num_rows()>0){
              $p->is_in_wishlist = 'true';
            }



            $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
            $f_image = false;

            if($images->num_rows()>0){

              $p->images = $images->result();
              $first_image = $images->row();
              if($f_image == false){
                $p->image = $first_image->image;
                $f_image = TRUE;
              }
              // var_dump($p->image);
              // var_dump($f_image);
              // die();

            }
            else{

                $p->images = array();
            }




          }
//          var_dump($products);



          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Data Found';
          echo json_encode($data);
          exit;
        }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(userid,r_cat)';
      echo json_encode($data);
      exit;

    }
}



public function get_vendor_products_list(){
    extract($_POST);
    if(!empty($userid))
    {

        $products = $this->Api_Model->get_vendor_products_list($userid);

        if($products->num_rows()>0){

          foreach ($products->result() as $p) {


            //var_dump($p->sell_type);
            if(strtolower($p->sell_type) == 'kg'){
                $p->min_qty = $p->min_qty.' Kgs';
            }
            if(strtolower($p->sell_type) == 'piece'){
              if($p->min_qty > 1){
                $p->min_qty = $p->min_qty.' Pieces';
              }
              else{
              $p->min_qty = $p->min_qty.' Piece';
              }

            }



            $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
            $f_image = false;
            if($images->num_rows()>0){

              $p->images = $images->result();

              $first_image = $images->row();
              if($f_image == false){
                $p->image = $first_image->image;
                $f_image = TRUE;
              }
              // var_dump($p->image);
              // var_dump($f_image);
              // die();





            }
            else{

                $p->images = array();
            }




          }
//          var_dump($products);



          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Data Found';
          echo json_encode($data);
          exit;
        }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(userid,r_cat)';
      echo json_encode($data);
      exit;

    }
}

public function get_vendor_active_products_list(){
    extract($_POST);
    if(!empty($userid))
    {
        $products = $this->Api_Model->get_vendor_active_products_list($userid);
        if($products->num_rows()>0){
          foreach ($products->result() as $p) {
            $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
            $f_image = false;
            if($images->num_rows()>0){
              $p->images = $images->result();
              $first_image = $images->row();
              if($f_image == false){
                $p->image = $first_image->image;
                $f_image = TRUE;
              }
            }
            else{
                $p->images = array();
            }
          }
          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Data Found';
          echo json_encode($data);
          exit;
        }
    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(userid)';
      echo json_encode($data);
      exit;

    }
}

public function get_vendor_inactive_products_list(){
    extract($_POST);
    if(!empty($userid))
    {
        $products = $this->Api_Model->get_vendor_inactive_products_list($userid);
        if($products->num_rows()>0){
          foreach ($products->result() as $p) {
            $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
            $f_image = false;
            if($images->num_rows()>0){
              $p->images = $images->result();
              $first_image = $images->row();
              if($f_image == false){
                $p->image = $first_image->image;
                $f_image = TRUE;
              }
            }
            else{
                $p->images = array();
            }
          }
          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Data Found';
          echo json_encode($data);
          exit;
        }
    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(userid)';
      echo json_encode($data);
      exit;

    }
}

public function active_deactive_product(){
  extract($_POST);
      if(!empty($product_id))
      {

        $product = $this->db->query("SELECT * FROM products WHERE products.id='$product_id'");
        if($product->num_rows()==0){$data['result'] = "false"; $data['msg'] = "Wrong product_id"; echo json_encode($data); exit;}

          $current_status = $product->row()->status;

          $STATUS = 0;

          if($current_status == 0){
            $STATUS = 1;
          }
          if($current_status == 1){
            $STATUS = 0;
          }


          //$DATA['post_code'] = $post_code;
          $this->db->where("products.id", $product_id);
          $this->db->update("products", array('status'=>$STATUS));

          if($this->db->affected_rows()>0){

            $json['result']   = true;
            $json['msg']      = 'Status Changed';
            $json['status']   = $STATUS;

            echo json_encode($json);
            exit;

          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Something Went Wrong';
            echo json_encode($json);
            exit;
          }

        }
          else{
            $json['result'] = false;
            $json['msg']    = 'Req: product_id';
            echo json_encode($json);
            exit;
          }
}

public function get_product_detail(){
    extract($_POST);
    if(!empty($product_id) && !empty($userid))
    {

        $products = $this->Api_Model->get_product_detail($product_id, $userid);

        if($products->num_rows()>0){

          foreach ($products->result() as $row) {



            if($row->vendor_id == $userid){
              $row->is_my_product = 'true';
            }
            else{
              $row->is_my_product = 'false';

            }


            $PID = $row->id;
            $Images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$PID'");
            if($Images->num_rows()>0){
              $row->slider_images = $Images->result();
            }
            else{
              $row->slider_images = array();
            }

          }



          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->row();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Data Found';
          echo json_encode($data);
          exit;
        }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(product_id, userid)';
      echo json_encode($data);
      exit;

    }
}


public function get_related_products(){
    extract($_POST);
    if(!empty($product_id) && !empty($userid))
    {
        $products = $this->Api_Model->get_related_products($product_id, $userid);
        if($products->num_rows()>0){
          foreach ($products->result() as $row) {
            $PID = $row->id;
            $Images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$PID'");
            $f_image = false;
            if($Images->num_rows()>0){
              $row->slider_images = $Images->result();

              $first_image = $Images->row();
              if($f_image == false){
                $row->image = $first_image->image;
                $f_image = TRUE;
              }
              // var_dump($p->image);
              // var_dump($f_image);
              // die();
            }
            else{
              $row->slider_images = array();
            }

          }


          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          // $data['cart_total'] = (float) $this->db->query("SELECT SUM(cart.final_amount) as total FROM cart WHERE cart.user_id='$userid' LIMIT 1")->row()->total;
          // $data['cat_name']    = (string) $cat_name;
          // $data['start_from']    = $start_from;
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Related Products Found';
          echo json_encode($data);
          exit;
        }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(product_id, userid)';
      echo json_encode($data);
      exit;

    }
}


public function get_product_rating_review(){
  extract($_POST);

  if(!empty($product_id))
  {


      $p_ratings = $this->Api_Model->get_product_rating_review($product_id);
      if ($p_ratings->num_rows()>0){
          $data['rating'] = $p_ratings->row();

          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          $data['customers'] = $this->Api_Model->get_customer_rating_review($product_id);
          echo json_encode($data);
          exit;


      }
      else{
        $data['result'] = 'false';
        $data['msg']    = 'No Rating Found';
        echo json_encode($data);
        exit;
      }




  }
  else{

    $data['result'] = 'false';
    $data['msg']    = 'Please provide parameters(product_id)';
    echo json_encode($data);
    exit;

  }

}



// public function show_cart(){
//   extract($_POST);
//   if(!empty($userid)) //&& !empty($qty)
//   {
//     $cat_id = "";
//     $cart_product_list = $this->db->query("SELECT products.price_per_set,
//       products.mrp,
//       products.gst,
//       products.id as productid,
//       products.category_id,
//                                                   products.id,
//                                                   products.product_name,
//                                                   products.total_price_per_set,
//                                                   products.total_mrp,
//                                                   cart.quantity,
//                                                   products.price_per_set as final_amount,
//                                                   cart.coupon_applied,
//                                                   cart.coupon_id
//                                   FROM cart
//                                   JOIN products ON products.id=cart.product_id
//                                   WHERE cart.user_id='$userid'");
//     $cart_detail = $this->db->query("SELECT products.price_per_set, products.mrp,products.gst,products.id as productid, cart.vendor_id as vendor_id, SUM(cart.final_amount) as item_total, 5 as service_fee, 10 as discount, cart.coupon_applied, cart.coupon_id
//                                       FROM cart
//                                       JOIN products ON products.id=cart.product_id
//                                       WHERE cart.user_id='$userid'");
//     $CART_total = 0;
//     $GST_STRING = '';
//     $GST_AMOUNT = 0;
//     $ITEM_AMOUNT = 0;
//     $ITEM_QTY = 0;
//     $GSTAMOUNT = 0;

//     if($cart_product_list->num_rows()>0){
//         //var_dump($cart_detail->num_rows());
//         foreach ($cart_product_list->result() as $cart_item) {
//           //var_dump($cart_item);

//           $MRP = (int) $cart_item->mrp;
//           $SELL_PRICE = (int)  $cart_item->price_per_set;
//           $GST = (int) $cart_item->gst;
//           $QTY = (int) $cart_item->quantity;

//           $qty = $cart_item->quantity;
//           $ITEM_QTY += $qty;

//           //var_dump($MRP);

//           $dicount_percent = (($MRP - $SELL_PRICE)*100) /$MRP ;
          
          
          
//         //  print_r($dicount_percent);die;
          
          
//           $discount_amount = (($SELL_PRICE*$dicount_percent)/100);

//           $gst_amount = (($SELL_PRICE*$GST)/100);
//           $GST_AMOUNT = $gst_amount*$QTY;
//           //var_dump($GST_AMOUNT);

//           $cart_item->gst = $cart_item->gst.'%';
//           $cart_item->product_price = number_format((float) ($SELL_PRICE-$GST_AMOUNT), 2, '.', '');
//           $ONE_TIME_PRICE = ($SELL_PRICE-$GST_AMOUNT);
//           //var_dump($ONE_TIME_PRICE);
//           $cart_item->gst_amount = $GST_AMOUNT;
//           $cart_item->dicount_percent = ROUND($dicount_percent).'%';
//           $cart_item->discount_amount = ROUND($discount_amount);
//           $single_product_amount = ($SELL_PRICE-$gst_amount);
//           //$single_product_amount = ($QTY*($single_product_amount));
//           //var_dump($single_product_amount);
//           // var_dump($SELL_PRICE);
//           // die();
//           $cart_item->final_amount = $SELL_PRICE;
//           //var_dump($cart_item->final_amount);
//           //var_dump($SELL_PRICE);
//           //var_dump($ONE_TIME_PRICE);
//           // var_dump($qty);
//           $ITEM_AMOUNT += $single_product_amount*$qty;//$SELL_PRICE*$qty;
//           //var_dump($SELL_PRICE);
//           $CART_total += $SELL_PRICE*$qty;
//           // var_dump($dicount_percent);
//           // var_dump($discount_amount);
//           //die();
//           $GSTAMOUNT += $gst_amount*$qty;
//           //var_dump($gst_amount);
//         }

//         // var_dump($CART_total);
//         // die();

//         $cat_id = $cart_product_list->row()->category_id;
//         $coupon_applied = $cart_detail->row()->coupon_applied;
//         if($coupon_applied == 1){
//           $COUPON_ID = $cart_detail->row()->coupon_id;
//           $coupon_code_detail =  $coupon = $this->db->query(" SELECT  cupon_code.* FROM cupon_code WHERE cupon_code.id='$COUPON_ID'");
//           if($coupon_code_detail->num_rows()>0){
//             $data['discount'] =  $coupon_code_detail->row()->cupon_price;
//           }
//           else{
//             $data['discount'] = 0;
//           }
//         }
//         else{
//           $data['discount'] = 0;
//         }

//         // var_dump($cart_detail->row());
//         // die();

//       $data['result'] = 'true';
//       $data['msg']    = 'Updated Successfully';
//       $data['qty']     = $ITEM_QTY;
//       $data['gst_string'] = '';//$GST_STRING;
//       $data['gst_amount'] = $GSTAMOUNT;

//       $data['cart_products'] = $cart_product_list->result();
//       $data['item_total']   = number_format((float) $ITEM_AMOUNT, 2, '.', '');  // Outputs -> 105.00
//       $data['service_fee'] = $cart_detail->row()->service_fee;
//       //$data['discount'] = $cart_detail->row()->discount;
//       //var_dump($CART_total);
//       $data['cart_total'] = ROUND($CART_total-$data['discount']);//($cart_detail->row()->item_total+$cart_detail->row()->service_fee)-($data['discount']);
//       $data['vendor_id'] = $cart_detail->row()->vendor_id;
//       $data['cat_id'] = $cat_id;
//       $data['cart_item_count'] = $cart_product_list->num_rows();

//       $coupon_id = $cart_detail->row()->coupon_id;

//       if(!empty($coupon_id)){
//         $data['coupon_id'] = $coupon_id;
//         $data['coupon_code_name'] = $this->db->query("SELECT cupon_code.code as code_name FROM cupon_code WHERE cupon_code.id='$coupon_id'")->row()->code_name;
//       }
//       else{
//         $data['coupon_id'] = '';
//         $data['coupon_code_name'] = '';
//       }
//       //echo "FDFDfsd";
//       echo json_encode($data);
//       exit;
//     }
//     else{
//       $data['result'] = 'false';
//       $data['msg']    = 'cart is empty';
//       echo json_encode($data);
//       exit;
//     }
//   }
//   else{
//     $data['result'] = 'false';
//     $data['msg']    = 'Please provide parameters(userid)';
//     echo json_encode($data);
//     exit;

//   }
// }









public function show_cart(){
  extract($_POST);
  if(!empty($userid)) //&& !empty($qty)
  {
    $cat_id = "";
    $cart_product_list = $this->db->query("SELECT products.price_per_set,
      products.mrp,
      products.gst,
      products.id as productid,
      products.category_id,
                                                  products.id,
                                                  products.product_name,
                                                  cart.total_price_per_set,
                                                  cart.total_mrp,
                                                  cart.quantity,
                                                  products.price_per_set as final_amount,
                                                  cart.coupon_applied,
                                                  cart.coupon_id
                                   FROM cart
                                   JOIN products ON products.id=cart.product_id
                                   WHERE cart.user_id='$userid'");
    $cart_detail = $this->db->query("SELECT products.price_per_set, products.mrp,products.gst,products.id as productid, cart.vendor_id as vendor_id, SUM(cart.final_amount) as item_total, 5 as service_fee, 10 as discount, cart.coupon_applied, cart.coupon_id
                                      FROM cart
                                      JOIN products ON products.id=cart.product_id
                                      WHERE cart.user_id='$userid'");
    $CART_total = 0;
    $GST_STRING = '';
    $GST_AMOUNT = 0;
    $ITEM_AMOUNT = 0;
    $ITEM_QTY = 0;
    $GSTAMOUNT = 0;

    if($cart_product_list->num_rows()>0){
        //var_dump($cart_detail->num_rows());
        foreach ($cart_product_list->result() as $cart_item) {
          //var_dump($cart_item);

          $MRP = (int) $cart_item->total_mrp;
          
          
          
        //   $SELL_PRICE = (int)  $cart_item->price_per_set;
        
        $SELL_PRICE = (int)  $cart_item->total_price_per_set;
          
          
          
          $GST = (int) $cart_item->gst;
          $QTY = (int) $cart_item->quantity;

          $qty = $cart_item->quantity;
          $ITEM_QTY += $qty;

          //var_dump($MRP);
        //   print_r($MRP);die;
        
        
          if($MRP != 0)
          {
              $dicount_percent = (($MRP - $SELL_PRICE)*100) /$MRP ;
          }
          else
          {
              $dicount_percent = 0;
          }

          
          
          
         
        
          
          
          $discount_amount = (($SELL_PRICE*$dicount_percent)/100);

          $gst_amount = (($SELL_PRICE*$GST)/100);
          $GST_AMOUNT = $gst_amount*$QTY;
          //var_dump($GST_AMOUNT);

          $cart_item->gst = $cart_item->gst.'%';
          $cart_item->product_price = number_format((float) ($SELL_PRICE-$GST_AMOUNT), 2, '.', '');
          $ONE_TIME_PRICE = ($SELL_PRICE-$GST_AMOUNT);
          //var_dump($ONE_TIME_PRICE);
          $cart_item->gst_amount = $GST_AMOUNT;
          $cart_item->dicount_percent = ROUND($dicount_percent).'%';
          $cart_item->discount_amount = ROUND($discount_amount);
          $single_product_amount = ($SELL_PRICE-$gst_amount);
          //$single_product_amount = ($QTY*($single_product_amount));
          //var_dump($single_product_amount);
          // var_dump($SELL_PRICE);
          // die();
          $cart_item->final_amount = $SELL_PRICE;
          //var_dump($cart_item->final_amount);
          //var_dump($SELL_PRICE);
          //var_dump($ONE_TIME_PRICE);
          // var_dump($qty);
          $ITEM_AMOUNT += $single_product_amount*$qty;//$SELL_PRICE*$qty;
          //var_dump($SELL_PRICE);
          $CART_total += $SELL_PRICE*$qty;
          // var_dump($dicount_percent);
          // var_dump($discount_amount);
          //die();
          $GSTAMOUNT += $gst_amount*$qty;
          //var_dump($gst_amount);
        }

        // var_dump($CART_total);
        // die();

        $cat_id = $cart_product_list->row()->category_id;
        $coupon_applied = $cart_detail->row()->coupon_applied;
        if($coupon_applied == 1){
          $COUPON_ID = $cart_detail->row()->coupon_id;
          $coupon_code_detail =  $coupon = $this->db->query(" SELECT  cupon_code.* FROM cupon_code WHERE cupon_code.id='$COUPON_ID'");
          if($coupon_code_detail->num_rows()>0){
            $data['discount'] =  $coupon_code_detail->row()->cupon_price;
          }
          else{
            $data['discount'] = 0;
          }
        }
        else{
          $data['discount'] = 0;
        }

        // var_dump($cart_detail->row());
        // die();

      $data['result'] = 'true';
      $data['msg']    = 'Updated Successfully';
      $data['qty']     = $ITEM_QTY;
      $data['gst_string'] = '';//$GST_STRING;
      $data['gst_amount'] = $GSTAMOUNT;

      $data['cart_products'] = $cart_product_list->result();
      $data['item_total']   = number_format((float) $ITEM_AMOUNT, 2, '.', '');  // Outputs -> 105.00
      $data['service_fee'] = $cart_detail->row()->service_fee;
      //$data['discount'] = $cart_detail->row()->discount;
      //var_dump($CART_total);
      $data['cart_total'] = ROUND($CART_total-$data['discount']);//($cart_detail->row()->item_total+$cart_detail->row()->service_fee)-($data['discount']);
      $data['vendor_id'] = $cart_detail->row()->vendor_id;
      $data['cat_id'] = $cat_id;
      $data['cart_item_count'] = $cart_product_list->num_rows();

      $coupon_id = $cart_detail->row()->coupon_id;

      if(!empty($coupon_id)){
        $data['coupon_id'] = $coupon_id;
        $data['coupon_code_name'] = $this->db->query("SELECT cupon_code.code as code_name FROM cupon_code WHERE cupon_code.id='$coupon_id'")->row()->code_name;
      }
      else{
        $data['coupon_id'] = '';
        $data['coupon_code_name'] = '';
      }
      //echo "FDFDfsd";
      echo json_encode($data);
      exit;
    }
    else{
      $data['result'] = 'false';
      $data['msg']    = 'cart is empty';
      echo json_encode($data);
      exit;
    }
  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'Please provide parameters(userid)';
    echo json_encode($data);
    exit;

  }
}



















public function get_coupon_codes(){
  extract($_POST);
      if(!empty($userid))
      {
        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");

        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $codes = $this->db->query(" SELECT  cupon_code.*, #'false' as is_applied,
                                    #        DATEDIFF(expired_at, created_at) as expire_in
                                    ifnull((SELECT  'true' FROM cart WHERE cart.coupon_id=cupon_code.id AND cart.user_id='$userid'),'false') as is_applied
                                    FROM cupon_code

        #WHERE DATE_FORMAT(NOW(), '%Y-%m-%d') >= created_at
        #AND DATE_FORMAT(NOW(), '%Y-%m-%d') >= expired_at") ;

        if($codes->num_rows()>0){

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data']    = $codes->result();

            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Coupon Code Found';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}


public function appy_coupon(){
  extract($_POST);
      if(!empty($userid) && !empty($coupon_code_id))
      {
        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");

        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        //check cart must have at least 1 itme start
        $cart = $this->db->query(" SELECT  * FROM cart WHERE cart.user_id='$userid'");
        if($cart->num_rows()==0){$json['result']=false;$json['msg']='Please Add Product In Cart';echo json_encode($json);exit;}
        //check cart must have at least 1 itme End

        //check coupon detail is exits start
        $CURRENT_DATE = date('Y-m-d');
        $coupon_code_detail =  $coupon = $this->db->query(" SELECT cupon_code.*,DATE_FORMAT(cupon_code.expired_at, '%Y-%m-%d') as ex_date
          FROM cupon_code
          WHERE DATE_FORMAT(cupon_code.expired_at, '%Y-%m-%d') > '$CURRENT_DATE' AND cupon_code.id='$coupon_code_id'");
        if($coupon_code_detail->num_rows()==0){$json['result']=false;$json['msg']='This Coupon Code Is Expired';echo json_encode($json);exit;}
        $Coupon_id = $coupon_code_detail->row()->id;
        $Coupon_code = $coupon_code_detail->row()->code;
        $Coupon_off_price = $coupon_code_detail->row()->cupon_price;

        // var_dump($coupon_code_detail->row());
        // die();
        //check coupon detail is exits End


        //check coupon Already Applied Start
        //$is_apllied =  $coupon = $this->db->query(" SELECT  * FROM apply_coupan WHERE apply_coupan.user_id='$userid' AND apply_coupan.coupan_id='$coupon_code_id' AND apply_coupan.status=1");
        //if($is_apllied->num_rows()>0){$json['result']=false;$json['msg']='This Coupon Code Already Applied';echo json_encode($json);exit;}
        //check coupon Already Applied Start



        // $this->db->where('apply_coupan.user_id',$userid);
        // $this->db->where('apply_coupan.coupan_id',$coupon_code_id);
        // $this->db->where('apply_coupan.status!=',1);
        // $this->db->delete('apply_coupan');


        //check couponcode amount is loweer then total cart amount
        $cart = $this->db->query(" SELECT  SUM(price) as total_count FROM cart WHERE cart.user_id='$userid'");



        if(($cart->row()->total_count) < $Coupon_off_price){
          $json['result']=false;$json['msg']='Coupon Code Cannot be apply, Cart Amount is too low';echo json_encode($json);exit;
        }



        $CP['user_id'] = $userid;
        $CP['coupan_id'] = $Coupon_id;
        $CP['code'] = $Coupon_code;
        $CP['status'] = 0;
        $this->db->insert('apply_coupan', $CP);

        $this->db->where('cart.user_id',$userid);
        $this->db->update('cart',array('coupon_applied'=>1, 'coupon_id'=>$Coupon_id, 'coupon_off_price'=>$Coupon_off_price));

        if($this->db->affected_rows()>0){

            $json['result'] = true;
            $json['msg']    = 'Applied Successfully';

            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Coupon Code Found';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid,coupon_code_id';
        echo json_encode($json);
        exit;
      }
}


public function remove_coupon(){
extract($_POST);

  if(!empty($userid)) //&& !empty($qty)
  {
    $this->db->where('cart.user_id',$userid);
    $this->db->update('cart',array('coupon_applied'=>0,'coupon_id'=>null));

    $data['result'] = 'true';
    $data['msg']    = 'Coupon Code Removed';
    echo json_encode($data);
    exit;
  }
  else{
      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(userid)';
      echo json_encode($data);
      exit;
  }
}


public function book_now(){

  extract($_POST);
      if( //!empty($vendor_id) &&
          !empty($address_id) &&
          !empty($userid) &&
          !empty($payment_method) &&
          !empty($payment_id) &&
          !empty($paid_amount)
          )
      {



          
          
        if(!empty($transport_state_id))
        {
            $transport_state_id = $transport_state_id;
        }
        else
        {
            $transport_state_id = 0;
        }
        
        
        
        
        
        
        if(!empty($transport_area_id))
        {
            $transport_area_id = $transport_area_id;
        }
        else
        {
            $transport_area_id = 0;
        }
        
        
        
        
        
        if(!empty($transportation_id))
        {
            $transportation_id = $transportation_id;
        }
        else
        {
            $transportation_id = 0;
        }
        
        
        
        
        
        if(!empty($transport_status))
        {
            $transport_status = $transport_status;
        }
        else
        {
            $transport_status = 0;
        }
        
          
          
          
          
          



        $date = date('Y-m-d');

       

        $transaction_id = $this->input->post('transaction_id');
        $amount         = $this->input->post('amount');


    
        $response = $this->book_now_with_cod($payment_id,$address_id,$date,$userid,$transaction_id,$amount,$payment_method,$transport_state_id,$transport_area_id,$transportation_id,$transport_status,$paid_amount);
    
      echo json_encode($response);
      exit;


        

      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: paid_amount,payment_id,address_id,userid,payment_method OPTIONAL:(transaction_id, amount)';
        echo json_encode($json);
        exit;
      }
}



public function book_now_with_payment($payment_id,$address_id,$date,$userid,$transaction_id,$amount,$payment_method){

  extract($_POST);
      if( //!empty($vendor_id) &&
          !empty($address_id) &&
          !empty($payment_id) &&
          !empty($date) &&
          ///!empty($time_slote_id) &&
          !empty($userid) &&
          !empty($transaction_id) &&
          !empty($amount) &&
          !empty($payment_method) && $payment_method == 'online'
          )
      {


        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid'; return $json;}


        $cart_data = $this->db->query("SELECT * FROM cart WHERE cart.user_id='$userid'");
        if($cart_data->num_rows()==0){$json['result']=false;$json['msg']='Cart is Empty, Please Add Some Item in Cart Before Book Now'; return $json; }

        $cart_vendors = $this->db->query("SELECT DISTINCT cart.vendor_id FROM cart WHERE cart.user_id='$userid'");
        //$cart_data = $this->db->query("SELECT cart.*,products.rate as product_rate  FROM cart JOIN products ON products.id=cart.product_id WHERE cart.user_id='$userid'");



        $IN['transaction'] = $transaction_id;
        $IN['amount'] = $amount;
        $IN['userid'] = $userid;
        $this->db->insert('payment_details', $IN);
        $payment_id = $this->db->insert_id();


        $ORDER_IDS = array();
        $VENDOR_IDS = array();


        if($cart_vendors->num_rows()>0){

          foreach ($cart_vendors->result() as $vendor_id) {

            $VENDOR_ID = $vendor_id->vendor_id;

            $CART_DATA = $this->db->query("SELECT * FROM cart WHERE cart.user_id='$userid' AND cart.vendor_id='$VENDOR_ID'");

            // var_dump(json_encode($vendor_id->vendor_id));
            // die();


            //$otp = rand(1000,9999);

            $ORDER['booking_uniqe_id'] = "ORDER".substr(time(),-6);
            $ORDER['userid'] = 	$userid;
            $ORDER['address_id'] =  $address_id;
            $ORDER['date'] = 	$date;
            $ORDER['payment_id'] = $payment_id;
            $ORDER['payment_method'] = 'online';
            $ORDER['paid_amount'] = $amount;

            $ORDER['cart_meta_data'] = json_encode($CART_DATA->result());
            //$ORDER['cat_id'] = $cat_id;
            $ORDER['vendor_id'] = $VENDOR_ID;
            $ORDER['payment_id'] = $payment_id;

            //$ORDER['otp'] = $otp;

            //$is_applied_coupon = $cart_data->row()->coupon_applied;
            //$COUPON_ID = $cart_data->row()->coupon_id;
            // if($is_applied_coupon == 1){
            //   $this->db->where('apply_coupan.coupan_id',$COUPON_ID);
            //   $this->db->where('apply_coupan.user_id',$userid);
            //   $this->db->update('apply_coupan', array('status'=>1));
            // }
            // var_dump($is_applied_coupon);
            // var_dump($COUPON_ID);
            // die();
            $this->db->insert('orders', $ORDER);
            $order_id = $this->db->insert_id();

            array_push($ORDER_IDS, $order_id);
            array_push($VENDOR_IDS, $VENDOR_ID);







            //// sam start logic ////


         $this->db->where("cart.user_id",$userid);
         $this->db->select("cart.product_id");
         $this->db->from("cart");
         $rrss = $this->db->get()->result();



         foreach($rrss as $value)
         {
             $product_id = $value->product_id;
             $user_id    = $userid;

             $ddtt = array(
                 'user_id' => $user_id,
                 'product_id' => $product_id,
                 'order_id' => $ORDER['booking_uniqe_id']
                 );

             $this->db->insert("orders_product_data",$ddtt);

         }



         //// sam end logic ////





          }


          //////

          $ORDER_IDS = implode(',', $ORDER_IDS);
          //$VENDOR_IDS = implode(',', $VENDOR_IDS);

          $this->db->where('payment_details.id', $payment_id);
          $this->db->update('payment_details', array('order_ids'=>$ORDER_IDS));


          $this->db->where('cart.user_id', $userid);
          $this->db->delete('cart');


          if($this->db->affected_rows()>0){
              //Sending Notifications start
              $this->Admin_model->on_order_booked($userid,$user_data, $VENDOR_IDS, $ORDER_IDS);
              //Sending Notification Ends

              $json['result'] = true;
              $json['msg']    = 'Order Recieved uccessfully';

              return $json;

              //echo json_encode($json);
              //exit;
          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Something Went Wrong';
            return $json;
            // echo json_encode($json);
            // exit;
          }

        }

      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: address_id,date,userid,transaction_id,amount, payment_method=online';
        return $json;
        // echo json_encode($json);
        // exit;
      }
}
public function book_now_with_cod($payment_id,$address_id,$date,$userid,$transaction_id,$amount,$payment_method,$transport_state_id,$transport_area_id,$transportation_id,$transport_status,$paid_amount){

  extract($_POST);
      if( //!empty($vendor_id) &&
          !empty($address_id) &&
          !empty($payment_id) &&
          !empty($date) &&
          ///!empty($time_slote_id) &&
          !empty($userid) &&
          !empty($transaction_id) &&
          !empty($amount) &&
          !empty($payment_method)
          )
      {

        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid'; return $json;}

        $cart_data = $this->db->query("SELECT * FROM cart WHERE cart.user_id='$userid'");
        if($cart_data->num_rows()==0){$json['result']=false;$json['msg']='Cart is Empty, Please Add Some Item in Cart Before Book Now'; return $json; }

        $cart_data = $this->db->query("SELECT * FROM cart WHERE cart.user_id='$userid'");
        if($cart_data->num_rows()>0){
            $this->check_before_book($userid);
        }


        $cart_vendors = $this->db->query("SELECT DISTINCT cart.vendor_id FROM cart WHERE cart.user_id='$userid'");
        //$cart_data = $this->db->query("SELECT cart.*,products.rate as product_rate  FROM cart JOIN products ON products.id=cart.product_id WHERE cart.user_id='$userid'");

        // $IN['transaction'] = $transaction_id;
        // $IN['amount'] = $amount;
        // $IN['userid'] = $userid;
        // $this->db->insert('payment_details', $IN);
        // $payment_id = $this->db->insert_id();

        $ORDER_IDS = array();
        $ORDER_IDS_UNIQUE = array();

        $VENDOR_IDS = array();
        $OTPS = array();


        if($cart_vendors->num_rows()>0){

          foreach ($cart_vendors->result() as $vendor_id) {

            $VENDOR_ID = $vendor_id->vendor_id;
            
            

            $CART_DATA = $this->db->query("SELECT * FROM cart WHERE cart.user_id='$userid' AND cart.vendor_id='$VENDOR_ID'");

            // var_dump(json_encode($vendor_id->vendor_id));
            // die();


            $otp = rand(1000,9999);

            $ORDER['booking_uniqe_id'] = "ORDER".substr(time(),-6);
            $ORDER['userid'] = 	$userid;
            $ORDER['address_id'] =  $address_id;
            $ORDER['date'] = 	$date;
            //$ORDER['payment_id'] = $payment_id;
            $ORDER['payment_method'] = $payment_method;

            $ORDER['cart_meta_data'] = json_encode($CART_DATA->result());
            //$ORDER['cat_id'] = $cat_id;
            $ORDER['vendor_id'] = $VENDOR_ID;
            $ORDER['payment_id'] = $payment_id;
            $ORDER['paid_amount'] = $amount;
            
            
            
            $ORDER['transport_state_id'] = $transport_state_id;
            $ORDER['transport_area_id']  = $transport_area_id;
            $ORDER['transportation_id']  = $transportation_id;
            $ORDER['transport_status']   = $transport_status;
            $ORDER['pay_amount']         = $paid_amount;






            $ORDER['cart_meta_data'] = json_encode($CART_DATA->result());












            $v_row = $this->db->query("SELECT * FROM users WHERE users.id='$VENDOR_ID'");



          //  $v_row_1 = $this->db->query("SELECT * FROM products WHERE products.id='$VENDOR_ID'");

            if($v_row->num_rows()>0){
              $ORDER['cat_id'] = $v_row->row()->cat_id;
            }


            $ORDER['otp'] = $otp;

            //$is_applied_coupon = $cart_data->row()->coupon_applied;
            //$COUPON_ID = $cart_data->row()->coupon_id;
            // if($is_applied_coupon == 1){
            //   $this->db->where('apply_coupan.coupan_id',$COUPON_ID);
            //   $this->db->where('apply_coupan.user_id',$userid);
            //   $this->db->update('apply_coupan', array('status'=>1));
            // }
            // var_dump($is_applied_coupon);
            // var_dump($COUPON_ID);
            // die();
            $this->db->insert('orders', $ORDER);
            $order_id = $this->db->insert_id();

            array_push($ORDER_IDS_UNIQUE,$ORDER['booking_uniqe_id']);
            array_push($ORDER_IDS, $order_id);
            array_push($VENDOR_IDS, $VENDOR_ID);
            array_push($OTPS, $otp);

            //$this->Admin_model->send_order_otp($userid,$user_data, $VENDOR_IDS, $ORDER_IDS);








            //// sam start logic ////


         $this->db->where("cart.user_id",$userid);
         $this->db->select("cart.product_id");
         $this->db->from("cart");
         $rrss = $this->db->get()->result();



         foreach($rrss as $value)
         {
             $product_id = $value->product_id;
             $user_id    = $userid;

             $ddtt = array(
                 'user_id' => $user_id,
                 'product_id' => $product_id,
                 'order_id' => $ORDER['booking_uniqe_id']
                 );

             $this->db->insert("orders_product_data",$ddtt);

         }



         //// sam end logic ////



          }

          //////
          $ORDER_IDS = implode(',', $ORDER_IDS);
          $ORDER_IDS_UNIQUE = implode(',', $ORDER_IDS_UNIQUE);
          //$VENDOR_IDS = implode(',', $VENDOR_IDS);

          // $this->db->where('payment_details.id', $payment_id);
          // $this->db->update('payment_details', array('order_ids'=>$ORDER_IDS));


          $this->db->where('cart.user_id', $userid);
          $this->db->delete('cart');


          if($this->db->affected_rows()>0){
              //Sending Notifications start
              $this->Admin_model->on_order_booked($userid,$user_data, $VENDOR_IDS, $ORDER_IDS, $OTPS,$ORDER_IDS_UNIQUE);
              //Sending Notification Ends
              $json['result'] = true;
              $json['msg']    = 'Order Recieved uccessfully';
              $json['order_id']  = $ORDER_IDS;
              return $json;
              //echo json_encode($json);
              //exit;
          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Something Went Wrong';
            return $json;
            // echo json_encode($json);
            // exit;
          }

        }

      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: address_id,date,userid,payment_method';
        return $json;
        // echo json_encode($json);
        // exit;
      }
}



public function check_before_book($userid){

    if(!empty($userid)) //&& !empty($qty)
    {
      $cat_id = "";
      $cart_product_list = $this->db->query("SELECT products.price_per_set,
        products.mrp,
        products.gst,
        products.id as productid,
        products.category_id,
                                                    products.id,
                                                    products.product_name,
                                                    cart.quantity,
                                                    products.price_per_set as final_amount,
                                                    cart.coupon_applied,
                                                    cart.coupon_id,
                                                    cart.total_mrp,
                                                    cart.total_price_per_set
                                     FROM cart
                                     JOIN products ON products.id=cart.product_id
                                     WHERE cart.user_id='$userid'");
      $cart_detail = $this->db->query("SELECT products.price_per_set, products.mrp,products.gst,products.id as productid, cart.vendor_id as vendor_id, SUM(cart.final_amount) as item_total, 5 as service_fee, 10 as discount, cart.coupon_applied, cart.coupon_id
                                        FROM cart
                                        JOIN products ON products.id=cart.product_id
                                        WHERE cart.user_id='$userid'");
      $CART_total = 0;
      $GST_STRING = '';
      $GST_AMOUNT = 0;
      $ITEM_AMOUNT = 0;
      $ITEM_QTY = 0;
      $GSTAMOUNT = 0;

      if($cart_product_list->num_rows()>0){
          //var_dump($cart_detail->num_rows());
          foreach ($cart_product_list->result() as $cart_item) {
            //var_dump($cart_item);

           // $MRP = (int) $cart_item->mrp;
            
            
            $MRP = (int) $cart_item->total_mrp;
            
            
            $SELL_PRICE = (int)  $cart_item->total_price_per_set;
            $GST = (int) $cart_item->gst;
            $QTY = (int) $cart_item->quantity;

            $qty = $cart_item->quantity;
            $ITEM_QTY += $qty;

            //var_dump($MRP);

            $dicount_percent = (($MRP - $SELL_PRICE)*100) /$MRP ;
            $discount_amount = (($SELL_PRICE*$dicount_percent)/100);

            $gst_amount = (($SELL_PRICE*$GST)/100);
            $GST_AMOUNT = $gst_amount*$QTY;
            //var_dump($GST_AMOUNT);

            $cart_item->gst = $cart_item->gst.'%';
            $cart_item->product_price = number_format((float) ($SELL_PRICE-$GST_AMOUNT), 2, '.', '');
            $ONE_TIME_PRICE = ($SELL_PRICE-$GST_AMOUNT);
            //var_dump($ONE_TIME_PRICE);
            $cart_item->gst_amount = $GST_AMOUNT;
            $cart_item->dicount_percent = ROUND($dicount_percent).'%';
            $cart_item->discount_amount = ROUND($discount_amount);
            $single_product_amount = ($SELL_PRICE-$gst_amount);
            //$single_product_amount = ($QTY*($single_product_amount));
            //var_dump($single_product_amount);
            // var_dump($SELL_PRICE);
            // die();
            $cart_item->final_amount = $SELL_PRICE;
            //var_dump($cart_item->final_amount);
            //var_dump($SELL_PRICE);
            //var_dump($ONE_TIME_PRICE);
            // var_dump($qty);
            $ITEM_AMOUNT += $single_product_amount*$qty;//$SELL_PRICE*$qty;
            //var_dump($SELL_PRICE);
            $CART_total += $SELL_PRICE*$qty;
            // var_dump($dicount_percent);
            // var_dump($discount_amount);
            //die();
            $GSTAMOUNT += $gst_amount*$qty;
            //var_dump($gst_amount);
          }

          // var_dump($CART_total);
          // die();

          $cat_id = $cart_product_list->row()->category_id;
          $coupon_applied = $cart_detail->row()->coupon_applied;
          if($coupon_applied == 1){
            $COUPON_ID = $cart_detail->row()->coupon_id;
            $coupon_code_detail =  $coupon = $this->db->query("SELECT  cupon_code.* FROM cupon_code WHERE cupon_code.id='$COUPON_ID'");
            if($coupon_code_detail->num_rows()>0){
              $data['discount'] =  $coupon_code_detail->row()->cupon_price;
            }
            else{
              $data['discount'] = 0;
            }
          }
          else{
            $data['discount'] = 0;
          }

          // var_dump($cart_detail->row());
          // die();

        $data['result'] = 'true';
        $data['msg']    = 'Updated Successfully';
        $data['qty']     = $ITEM_QTY;
        $data['gst_string'] = '';//$GST_STRING;
        $data['gst_amount'] = $GSTAMOUNT;

        $data['cart_products'] = $cart_product_list->result();
        $data['item_total']   = number_format((float) $ITEM_AMOUNT, 2, '.', '');  // Outputs -> 105.00
        $data['service_fee'] = $cart_detail->row()->service_fee;
        //$data['discount'] = $cart_detail->row()->discount;
        //var_dump($CART_total);
        $data['cart_total'] = ROUND($CART_total-$data['discount']);//($cart_detail->row()->item_total+$cart_detail->row()->service_fee)-($data['discount']);
        $data['vendor_id'] = $cart_detail->row()->vendor_id;
        $data['cat_id'] = $cat_id;
        $data['cart_item_count'] = $cart_product_list->num_rows();

        $coupon_id = $cart_detail->row()->coupon_id;

        if(!empty($coupon_id)){
          $data['coupon_id'] = $coupon_id;
          $data['coupon_code_name'] = $this->db->query("SELECT cupon_code.code as code_name FROM cupon_code WHERE cupon_code.id='$coupon_id'")->row()->code_name;
        }
        else{
          $data['coupon_id'] = '';
          $data['coupon_code_name'] = '';
        }
        return $data;
      }
      else{
        $data['result'] = 'false';
        $data['msg']    = 'cart is empty';
        echo json_encode($data);
        exit;
      }
    }


}


public function add_address(){
  extract($_POST);
      if(!empty($userid) && !empty($address) && !empty($lat) && !empty($lang) )
      {


        $query = "SELECT * FROM address
                  WHERE address.user_id='$userid' AND
                  CAST(address.lat AS DECIMAL) = CAST($lat AS DECIMAL)
                  AND
                  CAST(address.lang AS DECIMAL) = CAST($lang AS DECIMAL)";
          //var_dump($query);
        $pre_data = $this->db->query($query);




        // var_dump($pre_data->num_rows());
        // die();
        if($pre_data->num_rows() > 0){
          $ID = $pre_data->row()->id;
          $DATA['address'] = $address;
          $DATA['lat'] = $lat;
          $DATA['lang'] = $lang;
          $this->db->where("address.id",$ID);
          $this->db->update("address",$DATA);
          $json['result'] = true;
          $json['msg']    = 'Address Updated Successfully';
          echo json_encode($json);
          exit;
        }

        //
        // die();



        //count_previous address
        $pre_data = $this->db->query("SELECT * FROM address WHERE address.user_id='$userid'");

        if($pre_data->num_rows() >= 3){

          $data = $this->db->query("SELECT address.id
                                        FROM address
                                        WHERE address.user_id='$userid'
                                        ORDER BY address.id ASC LIMIT 1");
          $ID = $data->row()->id;

          //var_dump($ID);

          // var_dump($pre_data->num_rows());
          // die();

          $this->db->where('address.id',$ID);
          $this->db->delete('address');

          //die();


          $DATA['user_id'] = $userid;
          $DATA['address'] = $address;
          $DATA['lat'] = $lat;
          $DATA['lang'] = $lang;
          $this->db->insert("address",$DATA);
          if($this->db->affected_rows()>0){
              $json['result'] = true;
              $json['msg']    = 'Added Successfully';
              echo json_encode($json);
              exit;
          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Could Not update';
            echo json_encode($json);
            exit;
          }
        }
        else{

          $DATA['user_id'] = $userid;
          $DATA['address'] = $address;
          $DATA['lat'] = $lat;
          $DATA['lang'] = $lang;
          $this->db->insert("address",$DATA);
          if($this->db->affected_rows()>0){
              $json['result'] = true;
              $json['msg']    = 'Added Successfully';
              echo json_encode($json);
              exit;
          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Could Not update';
            echo json_encode($json);
            exit;
          }

        }




      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid, address, lat, lang';
        echo json_encode($json);
        exit;
      }
}






public function add_address_line()
{
    $user_id   = $this->input->post('user_id');
    $shop_name = $this->input->post('shop_name');
    $line_1    = $this->input->post('line_1');
    $line_2    = $this->input->post('line_2');
    $pin_code  = $this->input->post('pin_code');

    if(isset($user_id))
    {
               $post_data = array();

                 if(!empty($shop_name))
                 {
                   $shop_name = $shop_name;
                 }

                 if(!empty($line_1))
                 {
                   $line_1 = $line_1;
                 }

                 if(!empty($line_2))
                 {
                   $line_2 = $line_2;
                 }

                 if(!empty($pin_code))
                 {
                   $pin_code = $pin_code;
                 }


                 if(!empty($user_id))
                 {
                   $post_data['user_id'] = $user_id;
                 }





                 $post_data['address'] = $shop_name.","." ".$line_1.","." ".$line_2.","."   ".$pin_code;


             if(sizeof($post_data)>0)
             {


                 $insert =  $this->db->insert('address',$post_data);


                 if($insert)
                 {
                   $json['result'] = "true";
                   $json['msg']    = "Address added!";
                 }
                 else
                 {
                   $json['result']  = "false";
                   $json['msg']     = "something went wrong";
                 }


             }
             else
             {

                $json['result'] = "true";
                $json['msg']    = "Address added!";
             }
    }
    else
    {
        $json['result'] = 'false';
        $json['msg']    = 'parameter required user_id,optional(shop_name,line_1,line_2,pin_code)';
    }


    echo json_encode($json);
}




public function get_my_addresses(){
  extract($_POST);
      if(!empty($userid))
      {
        $address_list = $this->db->query("SELECT address.* FROM address WHERE address.user_id='$userid' ORDER BY address.id DESC ");
        if($address_list->num_rows()>0){

          $json['result'] = true;
          $json['msg']    = 'Get Successfully';
          $json['data']    = $address_list->result();
          echo json_encode($json);
          exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No address Found Found';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}



  private function hash_password($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }


  /*
  @create string for set pass

  */

  function generateRandomString($length = 15) {
    $characters = substr(str_shuffle(str_repeat(MD5(microtime()), ceil($length/32))), 0, $length);
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }




  public function get_services(){
    extract($_POST);
    if(!empty($vendor_id))
    {

        $products = $this->Api_Model->get_services($vendor_id);

        if($products->num_rows()>0){
          $data['result'] = 'true';
          $data['msg']    = 'get successfully';
          $data['data']    = $products->result();

          echo json_encode($data);
          exit;

        }
        else{
          $data['result'] = 'false';
          $data['msg']    = 'No Service Found';
          echo json_encode($data);
          exit;
        }

    }
    else{

      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(vendor_id)';
      echo json_encode($data);
      exit;

    }
}



public function get_sub_categories(){

  extract($_POST);
  if(!empty($cat_id))
  {
      $sub_categories = $this->Api_Model->get_sub_categories($cat_id);
      if($sub_categories->num_rows()>0){
        $data['result'] = 'true';
        $data['msg']    = 'get successfully';
        $data['data']    = $sub_categories->result();

        echo json_encode($data);
        exit;

      }
      else{
        $data['result'] = 'false';
        $data['msg']    = 'No Sub Category Found';
        echo json_encode($data);
        exit;
      }

  }
  else{

    $data['result'] = 'false';
    $data['msg']    = 'Please provide parameters(cat_id)';
    echo json_encode($data);
    exit;

  }

}


public function active_deactive_service(){
  extract($_POST);
      if(!empty($service_id))
      {

        $product = $this->db->query("SELECT * FROM products WHERE products.id='$service_id'");
        if($product->num_rows()==0){$data['result'] = "false"; $data['msg'] = "Wrong service_id"; echo json_encode($data); exit;}

          $current_status = $product->row()->status;

          $STATUS = 0;

          if($current_status == 0){
            $STATUS = 1;
          }
          if($current_status == 1){
            $STATUS = 0;
          }


          //$DATA['post_code'] = $post_code;
          $this->db->where("products.id", $service_id);
          $this->db->update("products", array('status'=>$STATUS));

          if($this->db->affected_rows()>0){

            $json['result']   = true;
            $json['msg']      = 'Status Changed';
            $json['status']   = $STATUS;

            echo json_encode($json);
            exit;

          }
          else{
            $json['result'] = false;
            $json['msg']    = 'Something Went Wrong';
            echo json_encode($json);
            exit;
          }

        }
          else{
            $json['result'] = false;
            $json['msg']    = 'Req: service_id';
            echo json_encode($json);
            exit;
          }
}


// public function update_profile(){
//   extract($_POST);
//       if(!empty($vendor_id) && !empty($name) && !empty($email) && !empty($password) && !empty($phone) && !empty($country_code))
//       {
//         $otp = rand(1000,9999);
//         $v_exist = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
//         if($v_exist->num_rows()==0){$data['result'] = "false"; $data['msg'] = "Wrong Vendor Id"; echo json_encode($data); exit;}
//
//           $DATA['name'] = $name;
//           $DATA['email_id'] = $email;
//           $DATA['password'] = $password;
//           $DATA['mobile'] = $phone;
//           $DATA['country_code'] = $country_code;
//           $DATA['otp'] = $otp;
//           $DATA['verify_otp'] = 0;
//
//           //$DATA['post_code'] = $post_code;
//           $this->db->where("vendor.id", $vendor_id);
//           $this->db->insert("vendor", $DATA);
//
//
//           if($this->db->affected_rows()>0){
//
//             $json['result'] = true;
//             $json['msg']    = 'Updated Successfully';
//             echo json_encode($json);
//             exit;
//
//           }
//           else{
//             $json['result'] = false;
//             $json['msg']    = 'Could Not saved';
//             echo json_encode($json);
//             exit;
//           }
//
//         }
//           else{
//             $json['result'] = false;
//             $json['msg']    = 'Please give parameters (vendor_id, name,email,password,phone,country_code,post_code)';
//             echo json_encode($json);
//             exit;
//           }
// }

public function complete_order_now(){
  extract($_POST);
  if(!empty($vendor_id) && !empty($order_id))
  {

    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'");
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid otp"; echo json_encode($json);exit;}
    //check otp is valid or not end


    //check vendor is valid or not start
    $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
    if($vendor_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid Vendor_id"; echo json_encode($json);exit;}
    //check vendor is valid or not end


    // var_dump($vendor_data->num_rows());
    // die();

    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.vendor_id',$vendor_id);
    $this->db->update('orders', array('status'=>4, 'delivered_date'=>date('Y-m-d H:i:s')));

    //Sending Notifications start
    $this->Nf_Model->On_order_completed($booking_data, $vendor_data, $order_id);
    //Sending Notification Ends


          $review = $this->input->post('review');
          $vendor_id = $this->input->post('vendor_id');
          $rating = $this->input->post('rating');
          $user_id = $booking_data->row()->userid;
          $order_id = $this->input->post('order_id');

          if(!empty($review)){
            $Rating['review'] = $review;

            }
          if(!empty($vendor_id)){
            $Rating['vendor_id'] = $vendor_id;

            }
          if(!empty($rating)){
            $Rating['rating'] = $rating;

            }
          if(!empty($user_id)){
            $Rating['user_id'] = $user_id;

            }
          if(!empty($order_id)){
            $Rating['booked_id'] = $order_id;
            }
            if(sizeof($Rating)>0){
              $this->db->insert('user_rating_review',$Rating);
            }















    $json['result'] = "true";
    $json['msg'] = "Order Completed Successfully";
    echo json_encode($json);
    exit;



  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: vendor_id,order_id, OPTIONAL(review,rating,user_id)";
    echo json_encode($json);
    exit;
  }
}


public function reschedule_order_now(){
  extract($_POST);
  if(!empty($userid) && !empty($days) && !empty($order_id) )
  {
    $vendor_id = $userid; // This is User/Customer
    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id' AND orders.status=3 OR orders.status=7"); //3-shipped
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid order_id"; echo json_encode($json);exit;}
    //check otp is valid or not end

    //check vendor is valid or not start
    $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
    if($vendor_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid userid"; echo json_encode($json);exit;}
    //check vendor is valid or not end

    $expected_date = $booking_data->row()->expected_date;
    if(!empty($expected_date)){
      //var_dump($expected_date);
      $Date = $expected_date;//"2010-09-17";
      $expected_date = date('Y-m-d', strtotime($Date. ' + '.$days.' days'));
    }
    else{
      $expected_date = date('Y-m-d');
    }


    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.userid',$vendor_id);
    $this->db->update('orders', array('status'=>7, 'reschedule_date'=>$expected_date));

    //Sending Notifications start
    $this->Nf_Model->On_order_rescheduled($booking_data, $vendor_data, $order_id);
    //Sending Notification Ends



    $json['result'] = "true";
    $json['msg'] = "Order Rescheduled Successfully";
    echo json_encode($json);
    exit;



  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: userid,days,order_id";
    echo json_encode($json);
    exit;
  }
}


public function add_service(){

  extract($_POST);
  if( !empty($vendor_id) &&
      !empty($sub_cat_id) &&
      !empty($service_name) &&
      !empty($_FILES['image']['name']) &&
      !empty($price) &&
      !empty($description))
  {
      $vendor = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
      if($vendor->num_rows()==0){$json['result']="false";$json['msg']="Vendor Not Found";echo json_encode($json);exit;}

      $vendor_show = $this->db->query("SELECT * FROM shop_details WHERE shop_details.vendor_id='$vendor_id' LIMIT 1");
      if($vendor_show->num_rows()==0){$json['result']="false";$json['msg']="Vendor Shop Detail Not Found";echo json_encode($json);exit;}

    $post_data['vendor_id'] = $vendor_id;
    $post_data['category_id'] = $vendor->row()->cat_id;
    $post_data['shop_id'] = $vendor_show->row()->id;
    $post_data['product_name'] = $service_name;
    $post_data['about'] = $description;
    $post_data['rate'] =  $price;
    $post_data['scat_id'] =  $sub_cat_id;


    $this->db->insert('products', $post_data);
    $last_id = $this->db->insert_id();

    if($this->db->affected_rows()>0){

      if(!empty($_FILES['image']['name']))
      {
        $_FILES['file']['name']     = $_FILES['image']['name'];
        $_FILES['file']['type']     = $_FILES['image']['type'];
        $_FILES['file']['tmp_name'] = $_FILES['image']['tmp_name'];
        $_FILES['file']['error']    = $_FILES['image']['error'];
        $_FILES['file']['size']     =  $_FILES['image']['size'];
        // File upload configuration
        $uploadPath = 'assets/images/';
        $config['upload_path'] = $uploadPath;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        // Upload file to server
        if($this->upload->do_upload('file')){
          $fileData = $this->upload->data();
            $service_image = $fileData['file_name'];
            //var_dump($userid);
            $this->db->where('products.id', $last_id);
            $this->db->update('products', array('image'=>$service_image));
        }
      }

      $json['result'] = "true";
      $json['msg'] = "Service Created";
      echo json_encode($json);
      exit;

    }
    else{
      $json['result'] = "false";
      $json['msg'] = "Something Went Wrong";
      echo json_encode($json);
      exit;


    }

  }else{
    $json['result'] = "false";
    $json['msg'] = "Req: vendor_id,  sub_cat_id,  service_name,  image,  price,  description";
    echo json_encode($json);
    exit;
  }
}


// public function upload_document(){

//   extract($_POST);
//   if(!empty($id_proof_type) && !empty($userid) && !empty($cat_id) && !empty($_FILES['photo']['name']))
//   {
//       $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
//       if($user_data->num_rows()==0){$json['result']="false";$json['msg']="Wrong userid";echo json_encode($json);exit;}



//       if(!empty($_FILES['photo']['name']))
//       {
//         $_FILES['file']['name']     = $_FILES['photo']['name'];
//         $_FILES['file']['type']     = $_FILES['photo']['type'];
//         $_FILES['file']['tmp_name'] = $_FILES['photo']['tmp_name'];
//         $_FILES['file']['error']    = $_FILES['photo']['error'];
//         $_FILES['file']['size']     =  $_FILES['photo']['size'];
//         // File upload configuration
//         $uploadPath = 'assets/images/';
//         $config['upload_path'] = $uploadPath;
//         $config['allowed_types'] = 'jpg|jpeg|png|gif';
//         $this->load->library('upload', $config);
//         $this->upload->initialize($config);
//         // Upload file to server
//         if($this->upload->do_upload('file')){
//           $fileData = $this->upload->data();
//             $photo = $fileData['file_name'];
//             //var_dump($userid);
//             $data_time = date('Y-m-d h:i:s');
//             $this->db->where('users.id', $userid);
//             $this->db->update('users', array('id_proof'=>$photo, 'cat_id'=>$cat_id, 'id_proof_type'=>$id_proof_type, 'doc_upload_date'=>$data_time, 'kyc_status'=>0));

//             if($this->db->affected_rows()>0){

//               $json['result'] = "true";
//               $json['msg'] = "Uppdated Successfully";
//               echo json_encode($json);
//               exit;

//             }

//         }
//       }

//       $json['result'] = "false";
//       $json['msg'] = "Could Not Updated";
//       echo json_encode($json);
//       exit;


//   }else{
//     $json['result'] = "false";
//     $json['msg'] = "Req: userid,  cat_id,  photo, id_proof_type";
//     echo json_encode($json);
//     exit;
//   }
// }





public function upload_document(){

  extract($_POST);
  if(!empty($id_proof_type) && !empty($userid) && !empty($_FILES['photo']['name']))
  {
      $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
      if($user_data->num_rows()==0){$json['result']="false";$json['msg']="Wrong userid";echo json_encode($json);exit;}



      if(!empty($_FILES['photo']['name']))
      {
        $_FILES['file']['name']     = $_FILES['photo']['name'];
        $_FILES['file']['type']     = $_FILES['photo']['type'];
        $_FILES['file']['tmp_name'] = $_FILES['photo']['tmp_name'];
        $_FILES['file']['error']    = $_FILES['photo']['error'];
        $_FILES['file']['size']     =  $_FILES['photo']['size'];
        // File upload configuration
        $uploadPath = 'assets/images/';
        $config['upload_path'] = $uploadPath;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        // Upload file to server
        if($this->upload->do_upload('file')){
          $fileData = $this->upload->data();
            $photo = $fileData['file_name'];
            //var_dump($userid);
            $data_time = date('Y-m-d h:i:s');
            $this->db->where('users.id', $userid);
            $this->db->update('users', array('id_proof'=>$photo, 'id_proof_type'=>$id_proof_type, 'doc_upload_date'=>$data_time, 'kyc_status'=>0));

            if($this->db->affected_rows()>0){

              $json['result'] = "true";
              $json['msg'] = "Uppdated Successfully";
              echo json_encode($json);
              exit;

            }

        }
      }

      $json['result'] = "false";
      $json['msg'] = "Could Not Updated";
      echo json_encode($json);
      exit;


  }else{
    $json['result'] = "false";
    $json['msg'] = "Req: userid,  cat_id,  photo, id_proof_type";
    echo json_encode($json);
    exit;
  }
}


public function get_varification_status(){
  extract($_POST);
  if(!empty($userid))
  {
      $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
      if($user_data->num_rows()==0){$json['result']="false";$json['msg']="Wrong userid";echo json_encode($json);exit;}
      $user_data = $user_data->row();

      $kyc_status = $user_data->is_kyc_verify;
      $id_proof = $user_data->id_proof;
      $id_proof_type = $user_data->id_proof_type;
      $doc_upload_date = $user_data->doc_upload_date;

      if(empty($id_proof) OR empty($id_proof_type)){
        $json['result'] = "false";
        $json['msg'] = "Please Upload KYC Document";
        echo json_encode($json);
        exit;
      }


      if(!empty($id_proof) AND !empty($id_proof_type)){

        if($kyc_status == 0){
          $json['result'] = "false";
          $json['msg'] = "Please Wait KYC Document in Review";
          $json['upload_date'] = $doc_upload_date;
          echo json_encode($json);
          exit;
        }
        if($kyc_status == 1){
          $json['result'] = "true";
          $json['msg'] = "Verified";
          $json['upload_date'] = $doc_upload_date;

          echo json_encode($json);
          exit;
        }
      }

      $json['result'] = "false";
      $json['msg'] = "Not Verified Yet";
      echo json_encode($json);
      exit;



  }else{
    $json['result'] = "false";
    $json['msg'] = "Req: userid";
    echo json_encode($json);
    exit;
  }

}



public function order_packed(){
  extract($_POST);
  if(!empty($vendor_id) && !empty($order_id))
  {
    $DATA = array();
    $DATA['vendor_id'] = $vendor_id;
    $DATA['order_id'] = $order_id;

    if(!empty($this->input->post('hsn'))){
          $DATA['hsn'] = $hsn;
    }
    if(!empty($this->input->post('no_of_boxes'))){
          $DATA['no_of_boxes'] = $no_of_boxes;
    }



    if(!empty($this->input->post('order_money'))){
          $DATA['order_money'] = $order_money;
          
          
         $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'"); 
         $samc['fcm_id']= $booking_data->row()->fcm_id; 
          
          $notification = array(
                  'title' =>'Your Order Price changed',
                  'body' => 'Your Order'.' '.$order_id.' '.'Price changed to '.$order_money,
                );
   
    send_notification($notification,$samc['fcm_id']);
          
          
    }



    if(!empty($this->input->post('total_boxes'))){
          $DATA['total_boxes'] = $total_boxes;
    }


    if(!empty($this->input->post('weight'))){
          $DATA['weight'] = $weight;
    }
    if(!empty($this->input->post('invoice'))){
          $DATA['invoice'] = $invoice;
    }
    if(!empty($this->input->post('box_length'))){
          $DATA['box_length'] = $box_length;
    }
    if(!empty($this->input->post('box_breadth'))){
          $DATA['box_breadth'] = $box_breadth;
    }
    if(!empty($this->input->post('boxhieght'))){
          $DATA['boxhieght'] = $boxhieght;
    }






    if(!empty($this->input->post('box_length2'))){
          $DATA['box_length2'] = $box_length2;
    }
    if(!empty($this->input->post('box_breadth2'))){
          $DATA['box_breadth2'] = $box_breadth2;
    }
    if(!empty($this->input->post('boxhieght2'))){
          $DATA['boxhieght2'] = $boxhieght2;
    }


    if(!empty($this->input->post('box_length3'))){
          $DATA['box_length3'] = $box_length3;
    }
    if(!empty($this->input->post('box_breadth3'))){
          $DATA['box_breadth3'] = $box_breadth3;
    }
    if(!empty($this->input->post('boxhieght3'))){
          $DATA['boxhieght3'] = $boxhieght3;
    }





    if(!empty($this->input->post('box_length4'))){
          $DATA['box_length4'] = $box_length4;
    }
    if(!empty($this->input->post('box_breadth4'))){
          $DATA['box_breadth4'] = $box_breadth4;
    }
    if(!empty($this->input->post('boxhieght4'))){
          $DATA['boxhieght4'] = $boxhieght4;
    }





    if(!empty($this->input->post('box_length5'))){
          $DATA['box_length5'] = $box_length5;
    }
    if(!empty($this->input->post('box_breadth5'))){
          $DATA['box_breadth5'] = $box_breadth5;
    }
    if(!empty($this->input->post('boxhieght5'))){
          $DATA['boxhieght5'] = $boxhieght5;
    }



    $this->db->insert('order_packed', $DATA);
    $last_id = $this->db->insert_id();



    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'");
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid order_id"; echo json_encode($json);exit;}
    //check otp is valid or not end
    //check users is valid or not start
    $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
    if($vendor_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid userid"; echo json_encode($json);exit;}
    //check users is valid or not end
    
    
    
    
    
    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.vendor_id',$vendor_id);
    $this->db->update('orders', array('status'=>1, 'packed_id'=>$last_id,'order_money' => $DATA['order_money']));

    //Sending Notifications start
    $this->Nf_Model->On_order_packed($booking_data, $vendor_data, $order_id);
    //Sending Notification Ends
    
    
    
    
    
    
    
    //// start generate invoice pdf ////


      $data['data'] = "hello";
      $Pre_data = $this->load->view('super-admin/invoice', $data, true);

      $this->load->library('pdf');
      $dompdf = new pdf();
      //
      // $dompdf->loadHtml($Pre_data);
      // $output = $dompdf->output();
      // $path = "assets/prescription/pdf/pre_".$consult_id.".pdf";
      // file_put_contents($path, $output);

      $unique = uniqid();


      $filename = "pre_".$unique;
      $path     = "assets/pdf/pre_".$unique.".pdf";
      $dompdf->loadHtml($Pre_data);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->set_option('isHtml5ParserEnabled', true);


      $dompdf->render();
      //$dompdf->stream($filename.'.pdf');

      $output = $dompdf->output();
      //file_put_contents($filename.'.pdf', $output);
      $path = "assets/pdf/pre_".$unique.".pdf";
      file_put_contents($path, $output);



      $dd = array(
          'invoice' => "pre_".$unique.".pdf"
          );

      $this->db->where("orders.id",$order_id);
      $this->db->update("orders",$dd);

    //   echo json_encode(array('resp'=>true, 'file_path'=>base_url().$path));

//// end generate invoice pdf ////
    
    
    
    
    
    
    
    
    


    $json['result'] = "true";
    $json['msg'] = "Order Packed Successfully";
    echo json_encode($json);
    exit;
  }else{
    $json['result'] = "false";
    $json['msg'] = "REQ: vendor_id, order_id, OPTIONAL:-hsn,no_of_boxes,weight,invoice,box_length,box_breadth,boxhieght,order_money,total_boxes,box_length2,box_breadth2,boxhieght2,box_length3,box_breadth3,boxhieght3,box_length4,box_breadth4,boxhieght4,box_length5,box_breadth5,boxhieght5";
    echo json_encode($json);
    exit;
  }
}

public function order_picked(){
  extract($_POST);
  if(!empty($vendor_id) && !empty($order_id) && !empty($expected_date) && isset($ref_number))
  {


    $DATA = array();
    $DATA['vendor_id'] = $vendor_id;
    $DATA['order_id'] = $order_id;



    if(!empty($transport_state_id))
    {
        $transport_state_id = $transport_state_id;
    }
    else
    {
        $transport_state_id = 0;
    }






    if(!empty($transport_area_id))
    {
        $transport_area_id = $transport_area_id;
    }
    else
    {
        $transport_area_id = 0;
    }




    if(!empty($transportation_id))
    {
        $transportation_id = $transportation_id;
    }
    else
    {
        $transportation_id = 0;
    }



    $packed_data = $this->db->query("SELECT * FROM order_packed WHERE order_packed.order_id='$order_id' AND order_packed.vendor_id='$vendor_id' LIMIT 1");
    if($packed_data->num_rows()==0){$json['result'] = "false";$json['msg'] = "No Order Packed In History On this Ids";echo json_encode($json);exit;}

    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'");
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid order_id"; echo json_encode($json);exit;}
    //check otp is valid or not end
    //check users is valid or not start
    $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
    if($vendor_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid userid"; echo json_encode($json);exit;}
    //check users is valid or not end

    $packed_id = $packed_data->row()->id;

    $expected_date = date('Y-m-d', strtotime($expected_date));

    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.vendor_id',$vendor_id);
    $this->db->update('orders', array('status'=>2, 'picked_date'=>date('Y-m-d H:i:s'), 'expected_date'=> $expected_date,'transport_state_id' => $transport_state_id,'transport_area_id' => $transport_area_id,'transportation_id' => $transportation_id,'reference_number' => $ref_number));

    //Sending Notifications start
    $this->Nf_Model->On_order_picked($booking_data, $vendor_data, $order_id, $expected_date);
    //Sending Notification Ends


    $json['result'] = "true";
    $json['msg'] = "Order Picked Successfully";
    echo json_encode($json);
    exit;
  }else{
    $json['result'] = "false";
    $json['msg'] = "REQ: vendor_id, order_id, expected_date,ref_number,optional(transport_state_id,transport_area_id,transportation_id)";
    echo json_encode($json);
    exit;
  }
}


public function cancel_order_by_seller(){
  extract($_POST);
  if(!empty($vendor_id) && !empty($order_id) && !empty($reason))
  {

    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'");
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid order_id"; echo json_encode($json);exit;}
    //check otp is valid or not end

    //check users is valid or not start
    $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
    if($vendor_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid userid"; echo json_encode($json);exit;}
    //check users is valid or not end

    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.vendor_id',$vendor_id);
    $this->db->update('orders', array('status'=>5, 'vendor_cancel_reason'=>$reason, 'cancelled_by'=>'vendor', 'cancel_date'=>date('Y-m-d')));

    //Sending Notifications start
    //$this->Nf_Model->On_order_cenceled($booking_data, $vendor_data, $booking_id);
    //Sending Notification Ends


    $json['result'] = "true";
    $json['msg'] = "Order Canceled Successfully";
    echo json_encode($json);
    exit;



  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: vendor_id,order_id,reason";
    echo json_encode($json);
    exit;
  }
}

public function cancel_order_by_user(){
  extract($_POST);
  if(!empty($userid) && !empty($order_id) && !empty($reason))
  {
    $vendor_id = $userid;
    $booking_id = $order_id;
    //check otp is valid or not start
    $booking_data = $this->db->query("SELECT orders.*,users.*  FROM orders JOIN users On users.id=orders.userid WHERE orders.id='$order_id'");
    if($booking_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid order_id"; echo json_encode($json);exit;}
    //check otp is valid or not end

    //check users is valid or not start
    $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
    if($user_data->num_rows() == 0){$json['result'] = "false"; $json['msg'] = "Invalid userid"; echo json_encode($json);exit;}
    //check users is valid or not end

    $this->db->where('orders.id',$order_id);
    $this->db->where('orders.userid',$userid);
    $this->db->update('orders', array('status'=>5, 'user_cancel_reason'=>$reason, 'cancelled_by'=>'user','cancel_date'=>date('Y-m-d')));

    //Sending Notifications start
    //$this->Nf_Model->On_order_cenceled($booking_data, $vendor_data, $booking_id);
    //Sending Notification Ends


    $json['result'] = "true";
    $json['msg'] = "Order Canceled Successfully";
    echo json_encode($json);
    exit;



  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: userid,order_id,reason";
    echo json_encode($json);
    exit;
  }
}


public function get_notifications(){
  extract($_POST);
  if(isset($userid))
  {
      $data = $this->db->query("SELECT  notifications.id,notifications.title,
        notifications.message,
        notifications.types,
        notifications.order_ids,
        notifications.reciever_id,
        DATE_FORMAT(notifications.date, '%Y/%m/%d %h:%i%p') as dated,
        CONCAT(users.fname,' ',users.lname) as name, users.image
                                FROM notifications
                                JOIN users ON users.id=notifications.reciever_id
                                WHERE notifications.reciever_id='$userid' ORDER BY notifications.id DESC ");
      if($data->num_rows()>0){
        $json['result'] = "true";
        $json['msg'] = "get successfully";
        $json['data'] = $data->result();
        $json['count'] = $this->db->query("SELECT  * FROM notifications WHERE notifications.reciever_id='$userid' AND notifications.seen=0")->num_rows();
        echo json_encode($json);
        exit;
      }
      else{
        $json['result'] = "false";
        $json['msg'] = "No Notification Yet";
        $json['count'] = "0";

        echo json_encode($json);
        exit;
      }

  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: userid";
    echo json_encode($json);
    exit;
  }
}

public function mark_as_seen(){

  extract($_POST);
      if(!empty($userid))
      {
        //count_previous address
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}


        $this->db->where("notifications.reciever_id",$userid);
        $this->db->update("notifications", array('seen'=>1));


        $json['result'] = true;
        $json['msg']    = 'Successfully';

        echo json_encode($json);
        exit;

      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid';
        echo json_encode($json);
        exit;
      }
}


public function logout(){
  extract($_POST);
  if(isset($userid))
  {
    $this->db->where('users.id',$userid);
    $this->db->update('users', array('fcm_id'=>''));
    $json['result'] = "true";
    $json['msg'] = "logout successfully.";
    echo json_encode($json);
    exit;
  }else{
    $json['result'] = "false";
    $json['msg'] = "required parameters: userid";
    echo json_encode($json);
    exit;
  }

}

public function order_accept(){
  extract($_POST);
      if(!empty($vendor_id) && !empty($booking_id))
      {


        $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $booking_data = $this->db->query("SELECT * FROM booking WHERE booking.id='$booking_id'");
        if($booking_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong booking_id';echo json_encode($json);exit;}

        $userid = $booking_data->row()->userid;
        $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($user_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $this->db->where('booking.id',$booking_id);
        $this->db->update('booking', array('status'=>1));

        if($this->db->affected_rows()>0){

          //Sending Notifications start
          $this->Nf_Model->On_order_accept($user_data, $vendor_data, $booking_id);
          //Sending Notification Ends

          $json['result'] = true;
          $json['msg']    = 'Order Status Updated Successfully';
          echo json_encode($json);
          exit;



        }
        else{
          $json['result'] = false;
          $json['msg']    = 'Something Went Wrong';
          echo json_encode($json);
          exit;
        }

      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: vendor_id,booking_id';
        echo json_encode($json);
        exit;
      }
}

public function my_earnings(){
  extract($_POST);
      if(!empty($vendor_id))
      {

        $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
        if($vendor_data->num_rows()>0)
        {
          $json['result'] = true;
          $json['earning']    = $vendor_data->row()->earning;
          $json['data']    = $vendor_data->row();

          echo json_encode($json);
          exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'Please give parameters (vendor_id)';
          echo json_encode($json);
          exit;

        }



        }
          else{
            $json['result'] = false;
            $json['msg']    = 'Please give parameters (vendor_id)';
            echo json_encode($json);
            exit;
          }
}
public function get_pending_booking(){
  extract($_POST);
      if(!empty($vendor_id))
      {

        $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $booked_data = $this->db->query("SELECT * FROM booking WHERE booking.vendor_id='$vendor_id' AND booking.status=0");
        if($booked_data->num_rows()>0){


          $booking_data = $this->db->query(" SELECT  booking.id as Booking_id,
            booking.booking_uniqe_id as Booking_id_string,
                                                  booking.userid,
                                                  (SELECT CONCAT(users.fname, ' ',users.lname) FROM users WHERE users.id=booking.userid) as customer_name,

                                                  (SELECT address.address FROM address WHERE address.id=booking.address_id) as booking_address,



                                                  (SELECT payment_details.amount FROM payment_details WHERE payment_details.id=booking.payment_id) as paid_amount,
                                                  (SELECT payment_details.transaction FROM payment_details WHERE payment_details.id=booking.payment_id) as transaction,


                                                  booking.address_id,
                                                  booking.date,
                                                  booking.time_slote_id,
                                                  booking.payment_id,
                                                  DATE_FORMAT(booking.date, '%d-%m-%Y') as booked_date,
                                                  DATE_FORMAT(time_slots.time_slote, '%h:%i %p') as booked_time,
                                                  (SELECT CONCAT(booked_date,' ', booked_time)) as booked_date_time,
                                                  50 as item_count,
                                                  200 as total_amount,
                                                  booking.status as current_status,
                                                  (SELECT category.category_name FROM category WHERE category.id=booking.cat_id) as rcat_name,


                                                  (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,

                                                  booking.cart_meta_data
                                          FROM booking
                                          JOIN time_slots ON time_slots.id=booking.time_slote_id
                                          WHERE
                                          booking.vendor_id='$vendor_id' AND booking.status=0 ORDER BY booking.id DESC");

            foreach ($booking_data->result() as $book_row) {
                  $CART = json_decode($book_row->cart_meta_data);

                  $book_row->item_count = count($CART);
                  $book_row->total_amount = 0;
                  $product_list = array();
                  foreach ($CART as $value) {

                    $book_row->total_amount+=$value->final_amount;

                    $TEMP['product_id'] =$value->product_id;
                    $TEMP['name'] =$value->name;
                    $TEMP['image'] = $value->image;
                    $TEMP['quantity'] =$value->quantity;
                    $TEMP['final_amount'] = $value->final_amount;

                    $addons_ids = $value->addon;
                    if(!empty($addons_ids)){
                      $ADONDATA = $this->db->query("SELECT * FROM addon_product WHERE id IN($addons_ids)");
                      $TEMP['addons'] = $ADONDATA->result();
                      $TEMP['addon_price'] = $value->addon_price;
                    }
                    else{
                      $TEMP['addons'] = array();
                      $TEMP['addon_price'] = $value->addon_price;

                    }


                    // var_dump($value);
                    // die();
                    array_push($product_list, $TEMP);
                  }
                  $book_row->product_list = $product_list;
                  unset($book_row->cart_meta_data);



            }

            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $booking_data->result();
            echo json_encode($json);
            exit;
        }else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;

        }


        }
          else{
            $json['result'] = false;
            $json['msg']    = 'Please give parameters (vendor_id)';
            echo json_encode($json);
            exit;
          }
}



public function get_inprocessing_bookings(){
  extract($_POST);
      if(!empty($vendor_id))
      {
        $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong vendor_id';echo json_encode($json);exit;}

        $booked_data = $this->db->query("SELECT * FROM booking WHERE booking.vendor_id='$vendor_id' AND booking.status=1");
        if($booked_data->num_rows()>0){


          $booking_data = $this->db->query(" SELECT  booking.id as Booking_id,
                                                  booking.booking_uniqe_id as Booking_id_string,
                                                  address.address as booking_address,
                                                  booking.userid,
                                                  users.mobile,
                                                  (SELECT CONCAT(users.fname, ' ',users.lname) FROM users WHERE users.id=booking.userid) as customer_name,

                                                  (SELECT payment_details.amount FROM payment_details WHERE payment_details.id=booking.payment_id) as paid_amount,
                                                  (SELECT payment_details.transaction FROM payment_details WHERE payment_details.id=booking.payment_id) as transaction,


                                                  booking.address_id,
                                                  booking.date,
                                                  booking.time_slote_id,
                                                  booking.payment_id,
                                                  DATE_FORMAT(booking.date, '%d-%m-%Y') as booked_date,
                                                  DATE_FORMAT(time_slots.time_slote, '%h:%i %p') as booked_time,
                                                  (SELECT CONCAT(booked_date,' ', booked_time)) as booked_date_time,
                                                  50 as item_count,
                                                  200 as total_amount,
                                                  booking.status as current_status,
                                                  (SELECT category.category_name FROM category WHERE category.id=booking.cat_id) as rcat_name,


                                                  (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,

                                                  booking.cart_meta_data
                                          FROM booking
                                          JOIN time_slots ON time_slots.id=booking.time_slote_id
                                          JOIN address ON address.id=booking.address_id
                                          JOIN users ON users.id=booking.userid

                                          WHERE
                                          booking.vendor_id='$vendor_id' AND booking.status=1 ORDER BY booking.id DESC");
            foreach ($booking_data->result() as $book_row) {
                  $CART = json_decode($book_row->cart_meta_data);
                  $book_row->item_count = count($CART);
                  $book_row->total_amount = 0;
                  $product_list = array();
                  $product_names = array();
                  foreach ($CART as $value) {
                    // var_dump($value);
                    // die();
                    $book_row->total_amount+=$value->final_amount;
                    $TEMP['product_id'] =$value->product_id;
                    $TEMP['name'] =$value->name;
                    $TEMP['image'] = $value->image;
                    $TEMP['quantity'] =$value->quantity;
                    $TEMP['final_amount'] = $value->final_amount;
                    array_push($product_list, $TEMP);
                    array_push($product_names, $value->name);
                  }
                  $book_row->product_list = $product_list;
                //   $book_row->product_names = implode($product_names, ',');

                $book_row->product_names = implode(',',$product_names);
            }
            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $booking_data->result();
            $json['total_earning'] = '10000';
            $json['total_order'] = '20';
            $json['rating'] = '5.0';



            echo json_encode($json);
            exit;
        }else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;

        }


        }
          else{
            $json['result'] = false;
            $json['msg']    = 'Please give parameters (vendor_id)';
            echo json_encode($json);
            exit;
          }
}

public function get_categories(){
  $cat_data = $this->db->query("SELECT id,category_name,category_image,status FROM category WHERE category.status=1");

  if($cat_data->num_rows()>0){
    $data['result'] = 'true';
    $data['msg']    = 'get successfully';
    $data['data']    = $cat_data->result();

    echo json_encode($data);
    exit;

  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'User Not Found';
    echo json_encode($data);
    exit;
  }
}


public function get_vendor_list(){
  $vendors = $this->db->query("SELECT * FROM users WHERE users.user_type='vendor'");
  if($vendors->num_rows()>0){
    $data['result'] = 'true';
    $data['msg']    = 'get successfully';
    $data['data']    = $vendors->result();
    echo json_encode($data);
    exit;

  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'No Vendor found';
    echo json_encode($data);
    exit;
  }
}

public function get_return_req_detail(){

  extract($_POST);
      if(!empty($userid) && !empty($return_req_id))
      {



        //count_previous address
        $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $r_data = $this->db->query("SELECT return_order_requests.*, DATE_FORMAT(return_order_requests.dated, '%Y-%m-%d %h:%i%p') as dated FROM return_order_requests WHERE return_order_requests.id='$return_req_id'");
        if($r_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong return_req_id';echo json_encode($json);exit;}


        if($r_data->num_rows()>0){
           $r_data = $r_data->row();

           if($r_data->status == 0){
             $r_data->return_request_id = $r_data->id;
             $r_data->return_request_status = $r_data->status;
             $r_data->return_request_status_string = 'pending';
           }
           if($r_data->status == 1){
             $r_data->return_request_id = $r_data->id;
             $r_data->return_request_status = $r_data->status;
             $r_data->return_request_status_string = 'accepted';
           }
           if($r_data->status == 2){
             $r_data->return_request_id = $r_data->id;
             $r_data->return_request_status = $r_data->status;
             $r_data->return_request_status_string = 'cancelled';
           }

           $json['result'] = true;
           $json['msg']    = 'Get Successfully';
           $json['data'] = $r_data;



           echo json_encode($json);
           exit;

        }

        else{
          $json['result'] = false;
          $json['msg']    = 'No Return Request Found';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: userid, return_req_id';
        echo json_encode($json);
        exit;
      }


}
public function my_order_detail(){
  extract($_POST);
      if(!empty($userid) && !empty($order_id))
      {

        $booked_id = $order_id;
        $vendor_id =  $userid;
        //count_previous address
        $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}
        $booked_data = $this->db->query("SELECT * FROM orders WHERE orders.id='$order_id'");
        if($booked_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong order_id';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
          orders.booking_uniqe_id as orders_id_string,

                                                orders.userid,
                                                orders.address_id,
                                                orders.payment_id,
                                                orders.invoice,
                                                orders.reference_number,
                                              
                                                
                                                
                                                DATE_FORMAT(orders.date_time_stamp, '%d-%m-%Y') as order_date,

                                                DATE_FORMAT(orders.date_time_stamp, '%Y %M %h:%i%p ') as order_date_time,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                orders.status,
                                                orders.status as current_status,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id LIMIT 1) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id LIMIT 1) as rcat_image,

                                                (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id LIMIT 1) as vendor_image,
                                                
                                                
                                                
                                                ifnull((SELECT order_packed.order_money FROM order_packed WHERE order_packed.order_id=orders.id LIMIT 1),'') as shipping_charge,




                                                 (SELECT transport_state.name FROM transport_state WHERE transport_state.id=orders.transport_state_id) as transport_state_name,




                                                (SELECT areas.name FROM areas WHERE areas.id=orders.transport_area_id) as transport_area_name,




                                                (SELECT transportation.name FROM transportation WHERE transportation.id=orders.transportation_id) as transportation_name,



                                                (SELECT payment_method.is_transportation_seen FROM payment_method WHERE payment_method.id=orders.payment_id) as is_transportation_seen,




                                                (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'cancelled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,

                                                orders.cart_meta_data,
                                                orders.payment_method,
                                                DATE_FORMAT(orders.expected_date, '%d %b %Y') as expected_date,
                                                ifnull(DATE_FORMAT(orders.reschedule_date, '%d %b %Y'),'') as reschedule_date,
                                                orders.reschedule_date as r_date,
                                                orders.order_money as order_money

                                        FROM orders
                                        JOIN users ON users.id=orders.userid

                                        WHERE
                                        orders.id='$booked_id'");


        $PAYMENT_METHOD = "";
        $SUB_TOTAL = 0;
        $DELIVERY_CHARGE = 0;
        $COUPON_CODE_DISCOUNT = 0;
        $ITEMS_QTY = 0;
        $ORDER_STATUS = "";
        $ORDER_STATUS_STRING = "";
        
        
        $SHIPPING_CHARGE = 0;

         $CART_total = 0;
         $GST_STRING = '';
         $GST_AMOUNT = 0;
         $ITEM_AMOUNT = 0;
         $ITEM_QTY = 0;
         $GSTAMOUNT = 0;

        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {
              
            //   var_dump($book_row);
            //   die();
              
              $SHIPPING_CHARGE = $book_row->order_money;
              
              
              
            //var_dump($book_row->orders_id);
            $book_row->return_request_id = '';
            $book_row->return_request_status = '';
            $book_row->return_request_status_string = '';


            $r_data = $this->db->query("SELECT * FROM return_order_requests
                              WHERE return_order_requests.orderid='$book_row->orders_id'
                              AND return_order_requests.userid='$userid'");
            if($r_data->num_rows()>0){
               $r_data = $r_data->row();

               if($r_data->status == 0){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'pending';
               }
               if($r_data->status == 1){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'accepted';
               }
               if($r_data->status == 2){
                 $book_row->return_request_id = $r_data->id;
                 $book_row->return_request_status = $r_data->status;
                 $book_row->return_request_status_string = 'cancelled';
               }


            }


            //die();

              $book_row->can_rescheduled = 'true';
              if($book_row->status == 7){
                $current_date = date('Y-m-d');
                //$current_date = date('Y-m-d', strtotime('2021-11-15'));
                //var_dump($current_date);
                if(!empty($book_row->r_date)){
                  if(strtotime($book_row->r_date) == strtotime($current_date)){
                    $book_row->can_rescheduled = 'false';
                  }
                  if(strtotime($book_row->r_date) < strtotime($current_date)  ){
                    $book_row->can_rescheduled = 'false';
                  }
                }
              }
              unset($book_row->r_date);



              $PAYMENT_METHOD = $book_row->payment_method;
              $ORDER_STATUS = $book_row->current_status;
              $ORDER_STATUS_STRING = $book_row->status_in_string;

                $CART = json_decode($book_row->cart_meta_data);

                // var_dump($CART);
                // die();

                $product_list = array();
                foreach ($CART as $value) {

                  $COUPON_CODE_DISCOUNT = $value->coupon_off_price;

                  $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                //var_dump($product_data->row());
                  $p = $product_data->row();


                  $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
                  if($images->num_rows()>0){
                    $first_image = $images->row();
                    $TEMP['image'] = $first_image->image;
                  }
                  else{
                    $TEMP['image'] = $value->image;
                  }

                  $GST = (int) $p->gst;




                  $qty = $value->quantity;
                  $dicount_percent = (($p->mrp - $p->price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->price_per_set*$dicount_percent)/100);

                  $QTY = (int) $value->quantity;
                  $MRP = (int)  $p->mrp;
                  $SELL_PRICE = (int)  $p->total_price_per_set;


                  $gst_amount = (($SELL_PRICE*$GST)/100);
                  $GST_AMOUNT = $gst_amount*$QTY;


                  // $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  // $book_row->discount_amount = ROUND($discount_amount);

                  $TEMP['dicount_percent'] = ROUND($dicount_percent).'%';
                  $TEMP['discount_amount'] = ROUND($discount_amount);
                  $TEMP['gst_string'] = $p->gst.'%';
                  $TEMP['gst_amount'] = ((intval($p->price_per_set)*intval($p->gst))/100);
                  $TEMP['product_price'] = ($SELL_PRICE-$GST_AMOUNT);
                  $TEMP['MRP'] = $MRP;
                  $TEMP['rate'] = $p->rate;
                  $TEMP['price_p_set'] = $p->price_per_set;

                  // $book_row->gst_string = $p->gst.'%';
                  // $gst_amount = (($p->price_per_set*$p->gst)/100);
                  // $GST_AMOUNT = $gst_amount*$qty;
                  // $book_row->gst_amount = $GST_AMOUNT;


                  $TEMP['product_id'] =$value->product_id;
                  $TEMP['name'] =$value->name;
                  $TEMP['quantity'] =$value->quantity;
                  $TEMP['final_amount'] = $QTY*$SELL_PRICE;

                  $SUB_TOTAL += $TEMP['final_amount'];

                  $ITEMS_QTY += $QTY;

                  // var_dump($value->addon);
                  // die();
                  array_push($product_list, $TEMP);
                }
                $book_row->product_list = $product_list;

                unset($book_row->cart_meta_data);
                unset($book_row->total_amount);
                unset($book_row->item_count);
          }




            // var_dump($SUB_TOTAL);
            // var_dump($SHIPPING_CHARGE);
            // var_dump($DELIVERY_CHARGE);
            // var_dump($COUPON_CODE_DISCOUNT);
            
            if($SHIPPING_CHARGE > 0 ){
                $json['Total_amount'] = $SHIPPING_CHARGE;
                
            }
            else{
                $json['Total_amount'] = (($SUB_TOTAL+$DELIVERY_CHARGE)-$COUPON_CODE_DISCOUNT);
            }
            
            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->row();
            $json['items_qty'] = $ITEMS_QTY;
            $json['Subtotal'] = $SUB_TOTAL;
            $json['discounted_amount'] = $COUPON_CODE_DISCOUNT;
            //$json['Total_amount'] = (($SUB_TOTAL+$DELIVERY_CHARGE)-$COUPON_CODE_DISCOUNT);
            $json['Payment_type'] = $PAYMENT_METHOD;
            $json['order_status'] = $ORDER_STATUS;
            $json['order_status_string'] = $ORDER_STATUS_STRING;


            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: order_id, userid';
        echo json_encode($json);
        exit;
      }
}


public function myproduct_order_details(){
  extract($_POST);
      if(!empty($vendor_id) && !empty($order_id))
      {

        $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
        if($vendor_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong userid';echo json_encode($json);exit;}

        $booked_data = $this->db->query("SELECT * FROM orders WHERE orders.id='$order_id'");
        if($booked_data->num_rows()==0){$json['result']=false;$json['msg']='Wrong order_id';echo json_encode($json);exit;}

        $cart_data = $this->db->query(" SELECT  orders.id as orders_id,
          orders.booking_uniqe_id as orders_id_string,

                                                orders.userid,
                                                orders.address_id,
                                                orders.payment_id,
                                                orders.invoice,
                                                orders.reference_number,
                                                orders.transport_status,
                                                
                                                ifnull(order_packed.order_money,'0') as shipping_charge,
                                                
                                                
                                                DATE_FORMAT(orders.date_time_stamp, '%d-%m-%Y') as order_date,

                                                DATE_FORMAT(orders.date_time_stamp, '%d %M %Y %h:%i%p ') as order_date_time,
                                                #50 as item_count,
                                                #200 as total_amount,
                                                (SELECT CONCAT(users.fname,' ',users.lname) FROM users WHERE users.id=orders.userid) as customer_name,
                                                (SELECT address.address FROM address WHERE address.id=orders.address_id) as customer_full_address,


                                                orders.status as current_status,
                                                orders.cancelled_by as cancelled_by,
                                                orders.cancel_date as cancel_date,
                                                (SELECT category.category_name FROM category WHERE category.id=orders.cat_id) as rcat_name,
                                                (SELECT category.category_image FROM category WHERE category.id=orders.cat_id) as rcat_image,

                                                (SELECT vendor.image FROM vendor WHERE vendor.id=orders.vendor_id) as vendor_image,


                                                (SELECT payment_method.is_transportation_seen FROM payment_method WHERE payment_method.id=orders.payment_id) as is_transportation_seen,



                                                (SELECT transport_state.name FROM transport_state WHERE transport_state.id=orders.transport_state_id) as transport_state_name,
                                                
                                                
                                                
                                               




                                                (SELECT areas.name FROM areas WHERE areas.id=orders.transport_area_id) as transport_area_name,




                                                (SELECT transportation.name FROM transportation WHERE transportation.id=orders.transportation_id) as transportation_name,
                                                
                                                
                                                
                                                
                                                (SELECT transport_state.name FROM transport_state WHERE transport_state.id=orders.transport_state_id) as transport_state_name,
                                                
                                                
                                                
                                                
                                                
                                                (SELECT areas.name FROM areas WHERE areas.id=orders.transport_area_id) as transport_area_name,
                                                
                                                
                                                
                                                
                                                
                                                (SELECT transportation.name FROM transportation WHERE transportation.id=orders.transportation_id) as transportation_name,
                                                
                                                
                                                
                                                
                                                




                                                (  CASE
                                                    WHEN orders.status = 0 THEN 'Placed'
                                                    WHEN orders.status = 1 THEN 'Packed'
                                                    WHEN orders.status = 2 THEN 'Picked'
                                                    WHEN orders.status = 3 THEN 'Shipped'
                                                    WHEN orders.status = 4 THEN 'Delivered'
                                                    WHEN orders.status = 5 THEN 'canceled'
                                                    WHEN orders.status = 6 THEN 'return'
                                                    WHEN orders.status = 7 THEN 'rescheduled'
                                                    ELSE 'NA'
                                                END) AS status_in_string,

                                                orders.cart_meta_data,
                                                orders.payment_method,
                                                DATE_FORMAT(orders.expected_date, '%d %b %Y') as expected_date,
                                                ifnull(DATE_FORMAT(orders.reschedule_date, '%d %b %Y'),'') as reschedule_date,
                                                
                                                orders.order_money as order_money


                                        FROM orders
                                        JOIN users ON users.id=orders.userid
                                        
                                        LEFT JOIN order_packed ON order_packed.order_id=orders.id

                                        WHERE
                                        orders.id='$order_id'");


        $PAYMENT_METHOD = "";
        $SUB_TOTAL = 0;
        $DELIVERY_CHARGE = 0;
        $COUPON_CODE_DISCOUNT = 0;
        $ITEMS_QTY = 0;
        $ORDER_STATUS = "";
        $ORDER_STATUS_STRING = "";
        
        $ORDER_MONEY = 0;




         $CART_total = 0;
         $GST_STRING = '';
         $GST_AMOUNT = 0;
         $ITEM_AMOUNT = 0;
         $ITEM_QTY = 0;
         $GSTAMOUNT = 0;

        if($cart_data->num_rows()>0){

          foreach ($cart_data->result() as $book_row) {
              
              
              $ORDER_MONEY = $book_row->order_money;

              $PAYMENT_METHOD = $book_row->payment_method;
              $ORDER_STATUS = $book_row->current_status;
              $ORDER_STATUS_STRING = $book_row->status_in_string;

                $CART = json_decode($book_row->cart_meta_data);

                // var_dump($CART);
                // die();

                $product_list = array();
                foreach ($CART as $value) {

                  $COUPON_CODE_DISCOUNT =  $value->coupon_off_price;

                  $product_data = $this->db->query("SELECT * FROM products WHERE products.id='$value->product_id'");
                //var_dump($product_data->row());
                  $p = $product_data->row();


                  $images = $this->db->query("SELECT * FROM product_images WHERE product_images.product_id='$p->id'");
                  if($images->num_rows()>0){
                    $first_image = $images->row();
                    $TEMP['image'] = $first_image->image;
                  }
                  else{
                    $TEMP['image'] = $value->image;
                  }

                  $GST = (int) $p->gst;




                  $qty = $value->quantity;
                  $dicount_percent = (($p->total_mrp - $p->total_price_per_set)*100) /$p->mrp ;
                  $discount_amount = (($p->total_price_per_set*$dicount_percent)/100);

                  $QTY = (int) $value->quantity;
                  $MRP = (int)  $p->total_mrp;
                  $SELL_PRICE = (int)  $p->total_price_per_set;


                  $gst_amount = (($SELL_PRICE*$GST)/100);
                  $GST_AMOUNT = $gst_amount*$QTY;


                  // $book_row->dicount_percent = ROUND($dicount_percent).'%';
                  // $book_row->discount_amount = ROUND($discount_amount);

                  $TEMP['dicount_percent'] = ROUND($dicount_percent).'%';
                  $TEMP['discount_amount'] = ROUND($discount_amount);
                  $TEMP['gst_string'] = $p->gst.'%';
                  $TEMP['gst_amount'] = ((intval($p->total_price_per_set)*intval($p->gst))/100);
                  $TEMP['product_price'] = ($SELL_PRICE-$GST_AMOUNT);
                  $TEMP['MRP'] = $MRP;

                  // $book_row->gst_string = $p->gst.'%';
                  // $gst_amount = (($p->price_per_set*$p->gst)/100);
                  // $GST_AMOUNT = $gst_amount*$qty;
                  // $book_row->gst_amount = $GST_AMOUNT;


                  $TEMP['product_id'] =$value->product_id;
                  $TEMP['name'] =$value->name;
                  $TEMP['quantity'] =$value->quantity;
                  $TEMP['final_amount'] = $QTY*$SELL_PRICE;

                  $SUB_TOTAL += $TEMP['final_amount'];

                  $ITEMS_QTY += $QTY;

                  // var_dump($value->addon);
                  // die();
                  array_push($product_list, $TEMP);
                }
                $book_row->product_list = $product_list;

                unset($book_row->cart_meta_data);
                unset($book_row->total_amount);
                unset($book_row->item_count);
          }


            $json['result'] = true;
            $json['msg']    = 'Get Successfully';
            $json['data'] = $cart_data->row();
            $json['items_qty'] = $ITEMS_QTY;
            $json['Subtotal'] = $SUB_TOTAL;
            $json['Discount_Amount'] = $COUPON_CODE_DISCOUNT;
            
            if($ORDER_MONEY >0 ){
                $json['Total_amount'] = $ORDER_MONEY;
                
            }
            else{
                 $json['Total_amount'] = (($SUB_TOTAL+$DELIVERY_CHARGE)-$COUPON_CODE_DISCOUNT);
            }
            
           
            $json['Payment_type'] = $PAYMENT_METHOD;
            $json['order_status'] = $ORDER_STATUS;
            $json['order_status_string'] = $ORDER_STATUS_STRING;


            echo json_encode($json);
            exit;
        }
        else{
          $json['result'] = false;
          $json['msg']    = 'No Booking Yet';
          echo json_encode($json);
          exit;
        }
      }
      else{
        $json['result'] = false;
        $json['msg']    = 'REQ: order_id, vendor_id';
        echo json_encode($json);
        exit;
      }
}

public function update_profile(){
  extract($_POST);
      if(!empty($userid)) //&& !empty($fname) && !empty($lname) && !empty($busines_name) && !empty($gst)
      {
        $u_exist = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        if($u_exist->num_rows()==0){$data['result'] = "false"; $data['msg'] = "Wrong userid"; echo json_encode($data); exit;}


        if(!empty($_FILES['image']['name']))
        {
          $_FILES['file']['name']     = $_FILES['image']['name'];
          $_FILES['file']['type']     = $_FILES['image']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['image']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['image']['error'];
          $_FILES['file']['size']     =  $_FILES['image']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $this->load->library('upload', $config);
          $this->upload->initialize($config);
          // Upload file to server
          if($this->upload->do_upload('file')){
            $fileData = $this->upload->data();
              $profile_image = $fileData['file_name'];
              $this->db->where("users.id", $userid);
              $this->db->update("users", array('image'=>$profile_image));
          }
        }
        $DATA = array();
        if(!empty($this->input->post('fname'))){
          $DATA['fname'] = $fname;
        }
        if(!empty($this->input->post('lname'))){
          $DATA['lname'] = $lname;
        }
        if(!empty($this->input->post('busines_name'))){
          $DATA['busines_name'] = $busines_name;
        }
        if(!empty($this->input->post('gst'))){
          $DATA['gst'] = $gst;
        }

        if(!empty($this->input->post('email'))){
          $DATA['email'] = $email;
        }

        if(!empty($this->input->post('post_code'))){
          $DATA['post_code'] = $post_code;
        }

        if(sizeof($DATA)>0){
          $this->db->where("users.id", $userid);
          $this->db->update("users", $DATA);
        }

        $json['result'] = true;
        $json['msg']    = 'Updated Successfully';
        echo json_encode($json);
        exit;


        }
          else{
            $json['result'] = false;
            $json['msg']    = 'REQ:userid OPTIONAL(fname,lname,busines_name,gst,email,post_code, image)';
            echo json_encode($json);
            exit;
          }
}

public function get_cities(){
  $city_list = $this->db->query("SELECT * FROM city_list");

  if($city_list->num_rows()>0){
    $data['result'] = 'true';
    $data['msg']    = 'get successfully';
    $data['data']    = $city_list->result();

    echo json_encode($data);
    exit;

  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'User Not Found';
    echo json_encode($data);
    exit;
  }
}

public function save_shop_detail(){

  //vendor_id:2
  //name:dee
  //shop_name:Shop9
  //email:dpkdhariwal@gmail.com
  //image - optional
  extract($_POST);

      if(!empty($vendor_id))
      {

        $vendor = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
        if($vendor->num_rows() == 0){$data['result'] = "false";$data['msg'] = "No Vendor Found on this id"; echo json_encode($data);exit;}




        //Optional Values Start
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        //------------------
        $shop_name = $this->input->post('shop_name');
        //Optional Values End

        $DATA = array();
        if(!empty($name)){
          $DATA['name'] = $name;
        }
        if(!empty($email)){
          $DATA['email_id'] = $email;
        }

        if(sizeof($DATA) > 0){
          $this->db->where("vendor.id", $vendor_id);
          $this->db->update("vendor", $DATA);
        }


        if(!empty($_FILES['image']['name']))
        {
          $_FILES['file']['name']     = $_FILES['image']['name'];
          $_FILES['file']['type']     = $_FILES['image']['type'];
          $_FILES['file']['tmp_name'] = $_FILES['image']['tmp_name'];
          $_FILES['file']['error']    = $_FILES['image']['error'];
          $_FILES['file']['size']     =  $_FILES['image']['size'];
          // File upload configuration
          $uploadPath = 'assets/images/';
          $config['upload_path'] = $uploadPath;
          $config['allowed_types'] = 'jpg|jpeg|png|gif';
          $this->load->library('upload', $config);
          $this->upload->initialize($config);
          // Upload file to server
          if($this->upload->do_upload('file')){
            $fileData = $this->upload->data();
              $profile_image = $fileData['file_name'];
              //var_dump($userid);
              $this->db->where('vendor.id', $vendor_id);
              $this->db->update('vendor', array('image'=>$profile_image));
          }
        }


        if(!empty($shop_name)){

          $shop = $this->db->query(" SELECT *
                                        FROM shop_details
                                        WHERE shop_details.vendor_id='$vendor_id'");
          if($shop->num_rows()>0){
            $this->db->where('shop_details.vendor_id', $vendor_id);
            $this->db->update('shop_details', array('shop_name'=>$shop_name));
          }
          else{
            //SP => SHOP
            $SP['shop_name'] = $shop_name;
            $SP['vendor_id'] = $vendor_id;
            $SP['category_id'] = $vendor->row()->cat_id;
            //$DATA['post_code'] = $post_code;
            $this->db->insert("shop_details", $SP);

          }
        }

        $json['result'] = 'true';
        $json['msg']    = 'Updated Successfully';
        echo json_encode($json);
        exit;

        }
          else{

            $json['result'] = 'false';
            $json['msg']    = 'REQUIRED:vendor_id, OPTIONAL: name,shop_name,email,image';
            echo json_encode($json);
            exit;
          }


}


  public function user_signup()
  {

    extract($_POST);

        if(!empty($name) && !empty($email) && !empty($password) && !empty($phone) && !empty($country_code))
        {
          $otp = rand(1000,9999);
          $v_exist = $this->db->query("SELECT * FROM vendor WHERE vendor.email_id='$email' OR vendor.mobile='$phone'");
          if($v_exist->num_rows() > 0){$data['result'] = "false"; $data['msg'] = "User Already Exits On email Or Phone"; echo json_encode($data);exit;}

            $DATA['name'] = $name;
            $DATA['email_id'] = $email;
            $DATA['password'] = $password;
            $DATA['mobile'] = $phone;
            $DATA['country_code'] = $country_code;
            $DATA['otp'] = $otp;
            $DATA['verify_otp'] = 0;

            //$DATA['post_code'] = $post_code;
            $this->db->insert("vendor", $DATA);

            $last_id = $this->db->insert_id();


            if($this->db->affected_rows()>0){

              $json['result'] = true;
              $json['msg']    = 'saved Successfully';
              $json['vendor_id'] = $last_id;
              $json['otp'] = $otp;
              echo json_encode($json);
              exit;

            }
            else{
              $json['result'] = false;
              $json['msg']    = 'Could Not saved';
              echo json_encode($json);
              exit;
            }

          }
            else{
              $json['result'] = false;
              $json['msg']    = 'Please give parameters (name,email,password,phone,country_code,post_code)';
              echo json_encode($json);
              exit;
            }







    exit;
    $json = array();
    extract($_POST);
    $this->form_validation->set_rules('name','Name','trim|required');
    $this->form_validation->set_rules('email','Email','trim|required');
    $this->form_validation->set_rules('mobile','Mobile','trim|required');
    $this->form_validation->set_rules('password','Password','trim|required');
    //$this->form_validation->set_rules('fcm_id','fcm Id','trim|required');
    //$this->form_validation->set_rules('device_token','Device token','trim|required');

    if($this->form_validation->run()==false){
      $json["result"] = false;
      $json["msg"] =  "Please provide parameters(name mobile  email,password,(optional parameter)fcm_id,device_tocken,lat,lang)";
    }else{
      $post_data = (array) $this->input->post();
      $user_id_data = $this->User_Model->get_last_creted_id('user_id','users');
      if($user_id_data)
      {
        $user_id = $user_id_data->user_id;
        $user_id++;
      }else{
        $user_id = "200001";
      }
      $post_data['user_id'] = $user_id;
      $post_data['status'] = 'Active';
      $random_no = rand(1000,9999);
      $post_data['otp'] = $random_no;
      $post_data['password'] = $this->hash_password($post_data['password']);
      if(!$this->User_Model->is_record_exist('users','email', "{$post_data['email']}" ))
      {

        if(!$this->User_Model->is_record_exist('users','mobile', "{$post_data['mobile']}" ))
        {


          $result = $this->User_Model->insertAllData('users', $post_data);
          if($result)
          {

	            $ri_email = 'tastyhasty27feb@gmail.com';
	            $to       = $this->input->post('email');
	            $subject  = 'OTP';
	            $headers = "From: <" . $ri_email . ">" . "\r\n";
	            $headers .= "MIME-Version: 1.0\r\n";
	            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
	            $message = '';
	            $message .= '<!DOCTYPE html>
	            <html>
	              <head>
	              <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	              <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	                </head>
	                <body style="font-family:roboto !important;">
	                <div style="width:100%; text-align:center; margin:0px auto;">
	                <div style="width: 550px; height: auto;  margin: 10px auto;;">
	                <div style="padding:2px 2px 8px 2px; background:#042954; text-align:center;">
	                <div style="font-size: 20px; line-height: 40px; padding: 0;  margin-top: 5px;text-align: center; color: #fff;">Tasty Hasty</div>
	                </div>
	                <div style="background:whitesmoke;padding: 15px 0;font-family: sans-serif;">
	                <h3 style="text-align: left;padding-left: 100px; font-size: 18px;  margin-top: 0px;margin-bottom: 10px; color: #2dbba4;">OTP</h3>
	                <center>
	                <p>Dear ' . $name . '.</p>
	                <p>
	                Your OTP is: ' . $random_no . '.</p>
	                </center>
	                </div>
	                <footer>
	                <div style="background: #042954; padding: 20px 5px 25px 5px;">
	                <div style="width:100%; text-align:center;">
	                <div style="font-size: 13px; line-height: 7px; padding:0;margin-top: 0px;text-align: center; color: #fff;"> © Hasty N Tasty 2020. All Rights Reserved.
	                </div>
	                </div>
	                </footer>
	                </div>
	                </div>
	                </body>
	            </html>';
	            //$message1 = "Your delivery Phone varification OTP :{$random_no}";
	            //$phone_number=$post_data['mobile'];
	            //$country_code="+974";
	            //$ph_num=$country_code."".$phone_number;
	            //$send_sms = $this->sendSMS($message1,$ph_num);
	            if (mail($to, $subject, $message, $headers)) {
	            	$json['result'] = 'true';
	            	// $json['msg']    = 'mail success';
	            	$json['data'] = $this->Admin_model->select_single_row('users','id',$result);
	            	// $json['msg'] = "We send OTP to $to, Thanks";
	                $json['msg'] = "We sent OTP to your Mobile No., Thanks";
	                $json['email']  = $to;
	                $json['OTP'] = $random_no;
	            } else {
	                $json['result'] = 'false';
	                $json['msg']    = "Emailexist.";
	            }

			}else{
				$json['result'] = "false";
				$json['msg'] = "Something went wrong. Please try later.";
			}
        }else{
            $json['result'] = "false" ;
            $json['msg'] = "Mobile number already exits. Please Try another.";
        }
      }else{
        $json['result'] = "false";
        $json['msg'] = "Email already exits. Please Try another.";
      }

   	}
    echo json_encode($json);

  }




	/*
	@user login api
	*/
// 	public function login()
// 	{
// 	    extract($_POST);
// 	    if(isset($mobile_email) && isset($password) && isset($fcm_id))

// 	    {
// 			$lat=$this->input->post('lat');
// 			$lang=$this->input->post('lang');
// 			$result =  $this->User_Model->check_credentials($mobile_email);

// 			if($result)

// 			{

// 				if (password_verify($this->input->post('password'), $result->password))

// 				{

// 	            	$check_otp=$this->db->query("SELECT mobile,email,verify_otp FROM `users` WHERE (`mobile`='$mobile_email' OR `Email`='$mobile_email') AND `verify_otp`= 1 ");
// 	          		if ($check_otp->num_rows()>0) {
// 						if($result->status!="Inactive")
// 						{

// 				            $wheredata=array('id'=>$result->id);
// 				            $datas=array('fcm_id'=>$fcm_id,'lat'=>$lat,'lang'=>$lang);
// 				            $res=$this->User_Model->updates('users',$datas,$wheredata);
// 				            $result =  $this->User_Model->check_credentials($mobile_email);
// 				            $data['result'] = "true";
// 				            $data['data'] = $result;
// 				            $data['msg']    = 'Successfully logged in.';
// 						}else{
// 							$data['result'] = "false";
// 							$data['msg']    = 'Your account currently Inactive';
// 						}
// 					}else{
// 						$data['result'] = "false";
// 						$data['msg']    = 'Otp not verify so plz verify otp';
// 					}
// 	        	}else{
// 	        		$data['result'] = "false";
// 	        		$data['msg']    = 'Invalid Password';
// 	        	}
// 	      	}else{
// 	      		$data['result'] = "false";
// 	      		$data['msg']    = 'Invalid email or mobile.';
// 	      	}
// 		}else{
// 			$data['result'] = 'false';
// 			$data['msg']    = 'Please provide parameters(mobile_email(mobile or email),password,fcm_id)';
// 		}
// 		echo json_encode($data);
// 	}




// sameer start //

public function login()
{
  extract($_POST);
  if(!empty($mobile) && !empty($fcm_id))
  {
      $otp = rand(1000,9999);

      $userdata = $this->db->query("SELECT * FROM users WHERE users.mobile='$mobile'");

      if($userdata->num_rows() > 0)
      {
        $userid = $userdata->row()->id;
        $post_data = array('otp' => $otp,'verify_otp'=>0,'fcm_id' => $fcm_id);

        $this->db->where("users.id", $userid);
        $this->db->update("users", $post_data);



        send_opt($mobile,$otp);
        
        
        if($userdata->row()->form_status == 1)
        {
            $st = "true";
        }
        else
        {
            $st = "false";
        }



             $json['result'] = "true";
             $json['otp'] = $otp;
             $json['msg'] = 'Please verify otp';
             $json['userid'] = $userid;
             $json['name'] = $userdata->row()->fname.' '.$userdata->row()->lname;
             $json['phone'] = $userdata->row()->mobile;

             $json['is_new_user'] = 'false';
             $json['user_type'] = $userdata->row()->user_type;
             $json['is_basic_filled'] = $this->Api_Model->check_is_basic_detail_filled($userid);
             echo json_encode($json);
             exit;




      }
      else
      {

        $unique_id = substr(time(), -6);

        $post_data = array('unique_id'=> $unique_id, 'mobile' => $mobile, 'otp' => $otp,'verify_otp'=>0,'fcm_id' => $fcm_id);
        $this->db->insert("users", $post_data);
        $userid = $this->db->insert_id();

        $userdata = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
        send_opt($mobile,$otp);
        $json['result'] = "true";
        $json['otp'] = $otp;
        $json['msg'] = 'Please verify otp';
        $json['userid'] = $userid;
        $json['name'] = $userdata->row()->fname.' '.$userdata->row()->lname;
        $json['phone'] = $userdata->row()->mobile;

        $json['is_new_user'] = 'true';
        $json['user_type'] = $userdata->row()->user_type;

        echo json_encode($json);
        exit;


      }
  }
  else
  {
      $json['result'] = 'false';
      $json['msg']    = 'Please provide parameters(mobile,fcm_id)';
      echo json_encode($json);
      exit;
  }



}


public function mark_as_seen_nf(){

  extract($_POST);
  if(!empty($vendor_id))
  {
      //$otp = rand(1000,9999);

      $this->db->where("notifications.reciever_id", $vendor_id);
      $this->db->update("notifications", array('seen'=>1));

      $data['result'] = "true";
      $data['msg'] = 'success';
      echo json_encode($data);
      exit;
  }
  else
  {
      $data['result'] = 'false';
      $data['msg']    = 'Please provide parameters(vendor_id)';
      echo json_encode($data);
      exit;
  }



}


public function save_basic_detail(){

    extract($_POST);

         if (!empty($userid) && !empty($fname) && !empty($lname) && !empty($busines_name) && !empty($pincode)) {

            $user_is_exit = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
            if($user_is_exit->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong User id";echo json_encode($data);exit;}



              $DATA['fname'] = $fname;
              $DATA['lname']  = $lname;
              $DATA['busines_name']  = $busines_name;
              if(!empty($this->input->post('gst'))){
                $DATA['gst']  = $gst;
              }
              $DATA['pincode']  = $pincode;
              
              $DATA['form_status']  = 1;

              $DATA['last_seen'] = date('Y-m-d h:i:s');
              $this->db->where("users.id",$userid);
              $this->db->update("users",$DATA);


           if($this->db->affected_rows()>0){
             $data['result']="true";
             $data['msg'] ="Update Successfully";
             $data['data']= $this->db->query("SELECT * FROM users WHERE users.id='$userid'")->row();
             echo json_encode($data);
             exit;

           }
           else{

             $data['result'] ="false";
             $data['msg'] ="Could Not Saved";
             echo json_encode($data);
             exit;


           }



         }
         else{

           $data['result']="false";
           $data['msg'] ="Req:userid,fname,lname,busines_name,pincode, OPTIONAL: (gst)";
           echo json_encode($data);
           exit;

         }
}

public function get_home_slider(){
  $sliders = $this->db->query("SELECT * FROM home_sliders ORDER BY home_sliders.id DESC");
  if($sliders->num_rows()>0){
    $data['result'] = 'true';
    $data['msg']    = 'get successfully';
    $data['data']    = $sliders->result();

    echo json_encode($data);
    exit;

  }
  else{
    $data['result'] = 'false';
    $data['msg']    = 'No slider Found';
    echo json_encode($data);
    exit;
  }
}

public function get_profile(){
  extract($_POST);

  if (!empty($userid)) {

          $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");


          if($user_data->num_rows()>0){

            foreach ($user_data->result() as $row) {

              $row->business_images = array();
              $images = $this->db->query("SELECT * FROM business_images WHERE business_images.vendor_id='$userid'");
              if($images->num_rows() > 0){
                  $row->business_images = $images->result();
              }
            }

                        $data['result'] ="true";
                        $data['msg'] ="get successfully";
                        $data['data'] =$user_data->row();

                        echo json_encode($data);
                        exit;
          }
          else{

                        $data['result'] ="false";
                        $data['msg'] ="Data Not Found";

                        echo json_encode($data);
                        exit;

          }

       }
       else{

         $data['result']="false";
         $data['msg'] ="required: userid";
         echo json_encode($data);
         exit;

       }
}



public function verify_otp(){
  //die();
  extract($_POST);
       if (!empty($userid) && !empty($otp)) {
          $user_is_exit = $this->db->query("SELECT * FROM users WHERE users.id='$userid'");
          if($user_is_exit->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong User id";echo json_encode($data);exit;}
          $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid' AND users.otp='$otp' AND users.verify_otp=1");
          if($user_data->num_rows()>0){
              $USERID = $user_data->row()->id;
              $is_kyc_verify = $user_data->row()->is_kyc_verify;

              if($is_kyc_verify == 0){
                $is_kyc_verify = 'false';
              }
              if($is_kyc_verify == 1){
                $is_kyc_verify = 'true';
              }

            $data['result'] ="true";
            $data['msg'] ="Otp already verify";
            $data['is_kyc_verified'] =$is_kyc_verify;

            $data['is_basic_filled'] = $this->Api_Model->check_is_basic_detail_filled($USERID);
            $data['data']= $this->db->query("SELECT * FROM users WHERE users.id='$USERID'")->row();

            //var_dump($USERID);
            //$data['basic_detail']= $this->is_filled_basic_detail($USERID);
            echo json_encode($data);
            exit;
          }

          $user_data = $this->db->query("SELECT * FROM users WHERE users.id='$userid' AND users.otp='$otp'");
            //var_dump($user_data->num_rows());
         if($user_data->num_rows()>0){

           $USERID = $user_data->row()->id;

           $is_kyc_verify = $user_data->row()->is_kyc_verify;
           if($is_kyc_verify == 0){
             $is_kyc_verify = 'false';
           }
           if($is_kyc_verify == 1){
             $is_kyc_verify = 'true';
           }

           $DATA['verify_otp'] = 1;
           $DATA['last_seen'] = date('Y-m-d h:i:s');
           $this->db->where("users.id",$USERID);
           $this->db->update("users",$DATA);

           $data['result']="true";
           $data['msg'] ="Otp verify Successfully";
           $data['is_kyc_verified'] =$is_kyc_verify;
           $data['is_basic_filled'] = $this->Api_Model->check_is_basic_detail_filled($USERID);
           $data['data']= $this->db->query("SELECT * FROM users WHERE users.id='$USERID'")->row();
           //die();
           //$data['basic_detail']= $this->is_filled_basic_detail($USERID);


           echo json_encode($data);
           exit;

         }
         else{

           $data['result'] ="false";
           $data['msg'] ="sorry otp not valid.";
           echo json_encode($data);
           exit;


         }



       }
       else{

         $data['result']="false";
         $data['msg'] ="parameter required userid,otp";
         echo json_encode($data);
         exit;

       }



}
public function get_policies(){
  $privacy = $this->db->query("SELECT * FROM rules WHERE rules.type='Privacy' LIMIT 1");
  $term = $this->db->query("SELECT * FROM rules WHERE rules.type='term' LIMIT 1");
  $about = $this->db->query("SELECT * FROM rules WHERE rules.type='about' LIMIT 1");

  if($about->num_rows()>0){
    $data['result'] = "true";
    $data['privacy'] = $privacy->row();
    $data['term'] = $term->row();
    $data['about'] = $about->row();

    echo json_encode($data);
    exit;
  }
  else{
    $data['result'] ="false";
    $data['msg'] ="Data Not Found";
    echo json_encode($data);
    exit;
  }
}

public function get_user_detail(){
  extract($_POST);

  if (!empty($vendor_id)) {

          $vendor_data = $this->db->query("SELECT vendor.*,
                                            ifnull((SELECT city_name FROM city_list WHERE city_list.id=vendor.city_id), '') as city_name,
                                            ifnull((SELECT category_name FROM category WHERE category.id=vendor.cat_id), '') as category_name,
                                            ifnull((SELECT shop_name FROM shop_details WHERE shop_details.vendor_id=vendor.id LIMIT 1), '') as shop_name


                                            FROM vendor
                                            WHERE vendor.id='$vendor_id'");


          if($vendor_data->num_rows()>0){

                        $data['result'] ="true";
                        $data['msg'] ="get successfully";
                        $data['data'] =$vendor_data->row();

                        echo json_encode($data);
                        exit;
          }
          else{

                        $data['result'] ="false";
                        $data['msg'] ="Data Not Found";

                        echo json_encode($data);
                        exit;

          }

       }
       else{

         $data['result']="false";
         $data['msg'] ="required: vendor_id";
         echo json_encode($data);
         exit;

       }
}

public function save_bank_detail(){
  extract($_POST);

  if (!empty($vendor_id) && !empty($acc_holder_name) && !empty($acc_no) && !empty($branch_name) && !empty($ifsc_code)) {

          $vendor_data = $this->db->query("SELECT * FROM users WHERE users.id='$vendor_id'");
          if($vendor_data->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong vendor_id";echo json_encode($data);exit;}


            $DATA['acc_holder_name'] = $acc_holder_name;
            $DATA['account_number'] = $acc_no;
            $DATA['branch_name'] = $branch_name;
            $DATA['ifsc_code'] = $ifsc_code;

            $this->db->where('users.id', $vendor_id);
            $this->db->update('users',$DATA);

            $data['result'] ="true";
            $data['msg'] ="update successfully";
            echo json_encode($data);
            exit;

       }
       else{

         $data['result']="false";
         $data['msg'] ="required: (vendor_id, acc_holder_name, acc_no, branch_name, ifsc_code)";
         echo json_encode($data);
         exit;

       }
}

public function get_bank_detail(){
  extract($_POST);

  if (!empty($vendor_id)) {

          $vendor_data = $this->db->query("SELECT ifnull(acc_holder_name,'') as acc_holder_name,
                                                  ifnull(account_number,'') as account_number,
                                                  ifnull(branch_name,'') as branch_name,
                                                  ifnull(ifsc_code, '') as ifsc_code FROM users WHERE users.id='$vendor_id'");
          if($vendor_data->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong vendor_id";echo json_encode($data);exit;}

            $data['result'] ="true";
            $data['msg'] ="get successfully";
            $data['data'] =$vendor_data->row();

            echo json_encode($data);
            exit;

       }
       else{

         $data['result']="false";
         $data['msg'] ="required: (vendor_id)";
         echo json_encode($data);
         exit;

       }
}

public function save_enquiry(){
  extract($_POST);

  if (!empty($userid) && !empty($user_type) && !empty($title) && !empty($msg))  {

          $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$userid'");
          if($vendor_data->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong vendor_id";echo json_encode($data);exit;}



            $DATA['userid'] = $userid;
            $DATA['usertype'] = $user_type;
            $DATA['title'] = $title;
            $DATA['msg'] = $msg;

            $this->db->insert('enquiry', $DATA);

            if($this->db->affected_rows()>0){
              $data['result'] ="true";
              $data['msg'] ="Saved successfully";
              echo json_encode($data);
              exit;
            }
            else{
              $data['result'] ="false";
              $data['msg'] ="Could Not Saved";
              echo json_encode($data);
              exit;
            }

       }
       else{

         $data['result']="false";
         $data['msg'] ="required: (userid,user_type,title,msg)";
         echo json_encode($data);
         exit;

       }
}




public function save_detail(){
  extract($_POST);

  if (!empty($vendor_id)) {

          $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
          if($vendor_data->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong Vendor id";echo json_encode($data);exit;}

            $result = $this->images_uploads();
            //var_dump($result);
            $cat_id = $this->input->post('cat_id');
            $city_id = $this->input->post('city_id');

            $DATA = array();
            if(!empty($cat_id)){
              $DATA['cat_id'] = $cat_id;
            }

            if(!empty($city_id)){
              $DATA['city_id'] = $city_id;
            }

            if(sizeof($DATA)>0){
              $this->db->where('vendor.id', $vendor_id);
              $this->db->update('vendor',$DATA);
            }

            $data['result'] ="true";
            $data['msg'] ="update successfully";
            //$data['']
            //$data['is_uploaded'] = $result;
            echo json_encode($data);
            exit;
       }
       else{
         $data['result']="false";
         $data['msg'] ="required: vendor_id,  --- optional(cat_id, city_id, profile_image, id_proof, id_proof_two)";
         echo json_encode($data);
         exit;
       }
}

public function save_new_product(){
  extract($_POST);

  if (!empty($vendor_id)) {

          $vendor_data = $this->db->query("SELECT * FROM vendor WHERE vendor.id='$vendor_id'");
          if($vendor_data->num_rows()==0){$data['result'] ="false";$data['msg'] ="Wrong Vendor id";echo json_encode($data);exit;}

            $result = $this->images_uploads();
            //var_dump($result);
            $cat_id = $this->input->post('cat_id');
            $city_id = $this->input->post('city_id');

            $DATA = array();
            if(!empty($cat_id)){
              $DATA['cat_id'] = $cat_id;
            }

            if(!empty($city_id)){
              $DATA['city_id'] = $city_id;
            }

            if(sizeof($DATA)>0){
              $this->db->where('vendor.id', $vendor_id);
              $this->db->update('vendor',$DATA);
            }

            $data['result'] ="true";
            $data['msg'] ="update successfully";
            //$data['']
            //$data['is_uploaded'] = $result;
            echo json_encode($data);
            exit;
       }
       else{
         $data['result']="false";
         $data['msg'] ="required: vendor_id,  --- optional(cat_id, city_id, profile_image, id_proof, id_proof_two)";
         echo json_encode($data);
         exit;
       }
}

public function images_uploads()
{
  extract($_POST);
  if (!empty($vendor_id)) {
  {

    if(!empty($_FILES['profile_image']['name']))
    {
      $_FILES['file']['name']     = $_FILES['profile_image']['name'];
      $_FILES['file']['type']     = $_FILES['profile_image']['type'];
      $_FILES['file']['tmp_name'] = $_FILES['profile_image']['tmp_name'];
      $_FILES['file']['error']    = $_FILES['profile_image']['error'];
      $_FILES['file']['size']     =  $_FILES['profile_image']['size'];
      // File upload configuration
      $uploadPath = 'assets/images/';
      $config['upload_path'] = $uploadPath;
      $config['allowed_types'] = 'jpg|jpeg|png|gif';
      $this->load->library('upload', $config);
      $this->upload->initialize($config);
      // Upload file to server
      if($this->upload->do_upload('file')){
        $fileData = $this->upload->data();
          $profile_image = $fileData['file_name'];
          //var_dump($userid);
          $this->db->where('vendor.id', $vendor_id);
          $this->db->update('vendor', array('image'=>$profile_image));
          $RESULT['uploaded_profile_image'] = true;


      }
      else{
        $RESULT['uploaded_profile_image'] = false;

      }
    }
    else{
      $RESULT['uploaded_profile_image'] = false;

    }

    if(!empty($_FILES['id_proof']['name']))
    {
      $_FILES['file']['name']     = $_FILES['id_proof']['name'];
      $_FILES['file']['type']     = $_FILES['id_proof']['type'];
      $_FILES['file']['tmp_name'] = $_FILES['id_proof']['tmp_name'];
      $_FILES['file']['error']    = $_FILES['id_proof']['error'];
      $_FILES['file']['size']     =  $_FILES['id_proof']['size'];
      // File upload configuration
      $uploadPath = 'assets/images/';
      $config['upload_path'] = $uploadPath;
      $config['allowed_types'] = 'jpg|jpeg|png|gif';
      $this->load->library('upload', $config);
      $this->upload->initialize($config);
      // Upload file to server
      if($this->upload->do_upload('file')){
        $fileData = $this->upload->data();
          $id_proof = $fileData['file_name'];
                //var_dump($userid);
          $this->db->where('vendor.id', $vendor_id);
          $this->db->update('vendor', array('id_proof'=>$id_proof));
          $RESULT['uploaded_id_proof'] = true;

      }
      else{
        $RESULT['uploaded_id_proof'] = false;

      }
    }
    else{
      $RESULT['uploaded_id_proof'] = false;
    }
    if(!empty($_FILES['id_proof_two']['name']))
    {
      $_FILES['file']['name']     = $_FILES['id_proof_two']['name'];
      $_FILES['file']['type']     = $_FILES['id_proof_two']['type'];
      $_FILES['file']['tmp_name'] = $_FILES['id_proof_two']['tmp_name'];
      $_FILES['file']['error']    = $_FILES['id_proof_two']['error'];
      $_FILES['file']['size']     =  $_FILES['id_proof_two']['size'];
      // File upload configuration
      $uploadPath = 'assets/images/';
      $config['upload_path'] = $uploadPath;
      $config['allowed_types'] = 'jpg|jpeg|png|gif';
      $this->load->library('upload', $config);
      $this->upload->initialize($config);
      // Upload file to server
      if($this->upload->do_upload('file')){
        $fileData = $this->upload->data();
          $id_proof_two = $fileData['file_name'];
                //var_dump($userid);
          $this->db->where('vendor.id', $vendor_id);
          $this->db->update('vendor', array('id_proof_two'=>$id_proof_two));
          $RESULT['uploaded_id_proof_two'] = true;

      }
      else{
        $RESULT['uploaded_id_proof_two'] = false;

      }
    }
    else{

      $RESULT['uploaded_id_proof_two'] = false;

    }


  return $RESULT;
}
}
}

// public function login()
// 	{
// 	    extract($_POST);
// 	    if(isset($mobile_email) && isset($password) && isset($fcm_id))
//
// 	    {
// 			$lat=$this->input->post('lat');
// 			$lang=$this->input->post('lang');
// 			$result =  $this->User_Model->check_credentials($mobile_email);
//
// 			if($result)
//
// 			{
//
// 				if (password_verify($this->input->post('password'), $result->password))
//
// 				{
//
//
//
// 	            	$check_otp=$this->db->query("SELECT mobile,email,verify_otp FROM `users` WHERE (`mobile`='$mobile_email' OR `Email`='$mobile_email') ");
// 	          		if ($check_otp->num_rows()>0) {
// 						if($result->status!="Inactive")
// 						{
//
// 				            $wheredata=array('id'=>$result->id);
// 				            $datas=array('fcm_id'=>$fcm_id,'lat'=>$lat,'lang'=>$lang);
// 				            $res=$this->User_Model->updates('users',$datas,$wheredata);
// 				            $result =  $this->User_Model->check_credentials($mobile_email);
// 				            $data['result'] = "true";
// 				            $data['data'] = $result;
// 				            $data['msg']    = 'Successfully logged in.';
// 						}else{
// 							$data['result'] = "false";
// 							$data['msg']    = 'Your account currently Inactive';
// 						}
// 					}else{
// 						$data['result'] = "false";
// 						$data['msg']    = 'Otp not verify so plz verify otp';
// 					}
// 	        	}else{
// 	        		$data['result'] = "false";
// 	        		$data['msg']    = 'Invalid Password';
// 	        	}
// 	      	}else{
// 	      		$data['result'] = "false";
// 	      		$data['msg']    = 'Invalid email or mobile.';
// 	      	}
// 		}else{
// 			$data['result'] = 'false';
// 			$data['msg']    = 'Please provide parameters(mobile_email(mobile or email),password,fcm_id)';
// 		}
// 		echo json_encode($data);
// 	}



// sameer end //










  // #otp verfy

  // public function verify_otp(){
  //
  //   $user_id=$this->input->post('user_id');
  //
  //   $otp =$this->input->post('otp');
  //
  //   // $device_token =$this->input->post('device_token');
  //
  //   if (isset($user_id) && isset($otp)) {
  //
  //     $check_otp=$this->db->query("SELECT * FROM `users` WHERE `id`='$user_id' AND `otp`='$otp' AND `verify_otp`= 1");
  //
  //     if ($check_otp->num_rows()>0) {
  //
  //       $data['result']="true";
  //
  //       $data['msg']="Otp already verify";
  //
  //     }else{
  //
  //       $wheredata = array('field'=>'id',
  //
  //         'table'=>'users',
  //
  //         'where'=>array('id'=>$user_id,'otp'=>$otp)
  //
  //       );
  //
  //
  //
  //       $datas = array('verify_otp'=>1);
  //
  //       $wheredatas = array('id'=>$user_id);
  //
  //       $result=$this->User_Model->getAllData($wheredata);
  //
  //       if($result) {
  //
  //         $results=$this->User_Model->UpdateAllData('users',$wheredatas,$datas);
  //
  //         $data['result']="true";
  //
  //         $data['data']=$this->Admin_model->select_single_row('users','id',$user_id);
  //
  //         $data['msg'] ="Otp verify Successfully.";
  //
  //       }else{
  //
  //         $data['result'] ="false";
  //
  //         $data['msg'] ="sorry otp not verify.";
  //
  //       }
  //
  //
  //
  //     }
  //
  //
  //
  //   }else{
  //
  //
  //
  //     $data['result']="false";
  //
  //
  //
  //     $data['msg'] ="parameter required user_id,otp";
  //
  //
  //
  //   }
  //
  //
  //
  //   echo json_encode($data);
  //
  //
  //
  // }







































    /*







    @edit profile







    */







    public function edit_user_profile()
    {







      $user_id=$this->input->post('user_id');







      if(isset($user_id))







      {







        $res =  $this->User_Model->select_single_row('users','id',$user_id);







        if($res)







        {







          $json['result'] = "true";







          $json['msg'] = "profile data.";







          $json['data'] = $res;







        }else{







          $json['result'] = "false";







          $json['msg'] = "Something went wrong.";







        }







      }else{







        $json['result'] = "false";







        $json['msg'] = "Please give parameters(user_id)";







      }







      echo json_encode($json);







    }































  //====================== vendor list get method===================================//







  public function vendor_list()

  {



    $result=$this->Api_Model->get_List('vendor');

    if($result){



      $data["result"] = "true";



      $data["data"] = $result;



    }else{



      $data["result"] = "false";



      $data["msg"] = "Somethings Went Wrong";



    }







      echo json_encode($data);















   }















  //====================== services list get method===================================//







  public function service_list()



  {



    $lang_id=$this->input->post('lang_id');



    if (isset($lang_id)) {



      if ($lang_id==1) {



        $result=$this->db->query('select id,ar_name as name,image from services limit 6')->result();



      }else{



        $result=$this->db->query('select id,name,image from services limit 6')->result();



      }



      if($result){



        $data["result"] = "true";



        $data["data"] = $result;



      }else{



        $data["result"] = "false";



        $data["msg"] = "Somethings Went Wrong";



      }



    }else{



      $data["result"] = "false";



      $data["msg"] = "parameter required lang_id";



    }







    echo json_encode($data);



  }















    //====================== shop list get method===================================//







   //lat, lang, service_id, (optinal )







//   public function shop_list()



//   {







//       $fav_status='';



//       $result=$this->Api_Model->get_shop_List();



//       print_r($result);exit();



//       if($result){



//         foreach($result as $value):







//           if(!$this->input->post('user_id')==""):







//             $fav=$this->db->query('Select * from fev_shop Where shop_id ='.$value->id.' and user_id='.$this->input->post('user_id').'')->row();







//             if($fav):



//               $fav_status='1';



//               else:



//               $fav_status='0';



//             endif;



//           endif;



//           $new_shop[]=array(



//             'id'=>$value->id,



//             'Service_id'=>$value->Service_id,



//             'vendor_id'=>$value->vendor_id,



//             'shop_name'=>$value->shop_name,



//             'shop_image'=>$value->shop_image,



//             'shop_about'=>$value->shop_about,



//             'status'=>$value->status,



//             'fav_status'=>$fav_status,



//             'delivery'=>$value->delivery,



//             'address'=>$value->address,



//             'lat'=>$value->lat,



//             'lang'=>$value->lang,



//             'avg_rating'=>$this->Api_Model->s_avg_rating($value->id) ? :'0',



//             'created_date'=>$value->created_date



//           );



//         endforeach;







//         $data["result"] = "true";



//         $data["data"] = $new_shop;



//         $data["msg"]="data found";



//       }else{



//         $data["result"] = "false";



//         $data["data"]=array();



//         $data["msg"] = "Data not found";



//       }



//       echo json_encode($data);



//     }





 public function shop_list()



   {





       extract($_POST);

       if(isset($service_id) && isset($lat) && isset($lng) && isset($lang_id)){

           $fav_status='';



      $result=$this->Api_Model->get_shop_listByLatLon($service_id,$lat,$lng,$lang_id);

    //   $result=$this->Api_Model->get_shop_List();



    //   print_r($result);exit();



      if($result){



        foreach($result as $value):







          if(!$this->input->post('user_id')==""):



            $fav=$this->db->query('Select * from fev_shop Where shop_id ='.$value->id.' and user_id='.$this->input->post('user_id').'')->row();







            if($fav):



              $fav_status='1';



              else:



              $fav_status='0';



            endif;



          endif;



          $new_shop[]=array(



            'id'=>$value->id,



            // 'Service_id'=>$value->Service_id,

            'Service_id'=>$service_id,



            'vendor_id'=>$value->vendor_id,



            'shop_name'=>$value->shop_name,



            'shop_image'=>$value->shop_image,



            'shop_about'=>$value->shop_about,



            'status'=>$value->status,



            'fav_status'=>$fav_status,



            'delivery'=>$value->delivery,



            'address'=>$value->address,



            'lat'=>$value->lat,



            'lang'=>$value->lang,

            'delivery_time'=>$value->delivery_time,



            'avg_rating'=>$this->Api_Model->s_avg_rating($value->id) ? :'0',



            'created_date'=>$value->created_date



          );



        endforeach;







        $data["result"] = "true";

         $data["msg"]="data found";

        $data["data"] = $new_shop;







      }else{



        $data["result"] = "false";



        // $data["data"]=array();



        $data["msg"] = "Data not found";



      }

       }else{

            $data["data"]="false";



            $data["msg"] = "parameter required service_id,lat,lng,lang_id optional (user_id)";

       }





      echo json_encode($data);



    }









    //====================== shop list get method===================================//







   //shop_id, vendor_id, service_id, (optinal )







//     public function product_list(){

//       $shop_id= $this->input->post('shop_id');

//       $category_id= $this->input->post('category_id');



//           $lang_id= $this->input->post('lang_id');



//       if(!empty($shop_id) && !empty($lang_id) && !empty($category_id)){



//         $result=$this->Api_Model->get_product_List($shop_id,$category_id,$lang_id);



//         if (count($result)) {



//           $json['result']="true";



//           $json['msg']="All product list.";



//           $json['data']=$result;



//         }else{



//           $json['result']="false";



//           $json['msg']="sorry not any products here!!";



//         }



//       }else{



//         $json['result']="false";



//         $json['msg']="parameter required shop_id,category_id,lang_id";



//       }



//       echo json_encode($json);













//   }

/*use ios side*/

public function product_list()

  {

    $shop_id= $this->input->post('shop_id');

    $service_id= $this->input->post('service_id');

    $category_id= $this->input->post('category_id');

    $lang_id= $this->input->post('lang_id');

    // if(!empty($shop_id) && !empty($lang_id) && !empty($category_id)){

    if(!empty($lang_id)){



      if ($service_id) {

        $result=$this->Api_Model->get_product_ListByService($service_id,$lang_id);

      }else{

        $result=$this->Api_Model->get_product_List($shop_id,$category_id,$lang_id);

      }

      if (count($result)) {

        $json['result']="true";

        $json['msg']="All product list.";

        $json['data']=$result;

      }else{

        $json['result']="false";

        $json['msg']="sorry not any products here!!";

      }

    }else{

      $json['result']="false";

      $json['msg']="parameter required optional(shop_id,category_id),lang_id service_id(optional but check on maintance)";

    }



    echo json_encode($json);

  }





//  public function product_list()







//   {







//       $result=$this->Api_Model->get_product_List();



//         // print_r($result);exit();



//       if($result){







//           $data["result"] = "true";







//           $data["data"] = $result;







//           $data["message"]="data found";







//       }else{







//           $data["result"] = "false";







//           $data["data"]=array();







//           $data["message"] = "Data not found";







//       }







//       echo json_encode($data);















//   }























    //====================== services list by vendor get method===================================//







   public function service_list_by_vendor()







   {







     $this->form_validation->set_rules('vendor_id','Vendor id', 'trim|required');







     if($this->form_validation->run()==false){







        $data['result']=false;







        $data['msgresult']=strip_tags($this->form_validation->error_string());







     }  else{







              $result=$this->Api_Model->get_List('services');







              if($result){







                   $data["result"] = "true";







                   $data["data"] = $result;







              }else{







                   $data["result"] = "false";







                   $data["msgresult"] = "Somethings Went Wrong";







              }







            }







      echo json_encode($data);















   }







    public function product_detail(){

        extract($_POST);

        $this->form_validation->set_rules('product_id','Product_id','trim|required');

        $this->form_validation->set_rules('lang_id','lang_id','trim|required');



        if($this->form_validation->run()==false){

            $data['result']=false;

            $data['msgresult']=strip_tags($this->form_validation->error_string());

        } else{

            // $result=$this->Api_Model->Get_Detail_list('products',$this->input->post('product_id'));

            $result=$this->Api_Model->product_details($product_id,$lang_id);



            if($result){

               $data['result']="true";

               $data['msgresult']="data found";

               $data['data']=$result;

            }else{

               $data['result']="true";

               $data['msgresult']="Somethings went Wrong";

            }



        }



        echo json_encode($data);

    }































  public function shop_detail(){







    $this->form_validation->set_rules('shop_id','shop_id','trim|required');







    if($this->form_validation->run()==false){







             $data['result']=false;







             $data['msg']=strip_tags($this->form_validation->error_string());







    } else{







        $result=$this->Api_Model->Get_Detail_list('shop_details',$this->input->post('shop_id'));















      if($result){







           $data['result']="true";







           $data['data']=$result;







      }else{







           $data['result']="true";







           $data['msg']="Somethings went Wrong";







      }







    }







      echo json_encode($data);















  }















  //vendor detail







   public function vendor_detail(){







     $this->form_validation->set_rules('vendor_id','Vendor_id','trim|required');







      if($this->form_validation->run()==false){







             $data['result']="false";







             $data['msg']=strip_tags($this->form_validation->error_string());







      } else{







        $result=$this->Api_Model->Get_Detail_list('vendor',$this->input->post('vendor_id'));















      if($result){







           $data['result']="true";







           $data['data']=$result;







      }else{







           $data['result']="true";







           $data['msg']="Somethings went Wrong";







      }







    }







      echo json_encode($data);















  }















  public function search_shop(){







    //  $this->form_validation->set_rules('shop_name','Shop Name','trim|required');







    //   if($this->form_validation->run()==false){







    //          $data['result']=false;







    //          $data['message']=strip_tags($this->form_validation->error_string());







    //   } else{







        $result=$this->Api_Model->Get_shop_list();















      if($result){







           $data['result']="true";







           $data['data']=$result;







      }else{







           $data['result']="true";







           $data['msg']="Somethings went Wrong";







      }















      echo json_encode($data);







  }







  // public function search_product(){
  //
  //
  //
  //
  //
  //
  //
  //    $this->form_validation->set_rules('product_name','Product Name','trim|required');
  //
  //
  //
  //
  //
  //
  //
  //     if($this->form_validation->run()==false){
  //
  //
  //
  //
  //
  //
  //
  //            $data['result']="false";
  //
  //
  //
  //
  //
  //
  //
  //            $data['msg']=strip_tags($this->form_validation->error_string());
  //
  //
  //
  //
  //
  //
  //
  //     } else{
  //
  //
  //
  //
  //
  //
  //
  //       $result=$this->Api_Model->Get_product_list();
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //     if($result){
  //
  //
  //
  //
  //
  //
  //
  //          $data['result']="true";
  //
  //
  //
  //
  //
  //
  //
  //          $data['data']=$result;
  //
  //
  //
  //
  //
  //
  //
  //     }else{
  //
  //
  //
  //
  //
  //
  //
  //          $data['result']="true";
  //
  //
  //
  //
  //
  //
  //
  //          $data['msg']="Somethings went Wrong";
  //
  //
  //
  //
  //
  //
  //
  //     }
  //
  //
  //
  //
  //
  //
  //
  //   }
  //
  //
  //
  //
  //
  //
  //
  //     echo json_encode($data);
  //
  //
  //
  //
  //
  //
  //
  // }


















































  /*



  @Add To Cart



  */






























	/*public function add_address(){
    // $this->form_validation->set_rules('state','state','trim|required');
    $this->form_validation->set_rules('user_id','user_id','trim|required');
    $this->form_validation->set_rules('city','city','trim|required');
    $this->form_validation->set_rules('Apartments_No','Apartments_No','trim|required');
    $this->form_validation->set_rules('Building_No','Building_No','trim|required');



    $this->form_validation->set_rules('Street_No','Street_No','trim|required');

    $this->form_validation->set_rules('Zone','Zone','trim|required');



    $this->form_validation->set_rules('landmark','landmark','trim|required');



    $this->form_validation->set_rules('mobile','mobile','trim|required');



    $this->form_validation->set_rules('type','type','trim|required');



    if($this->form_validation->run()==false){







       $data['result']="false";



        //   $data['message']=strip_tags($this->form_validation->error_string());



       $data['message']="parameter required (user_id,city,type,mobile,landmark,Apartments_No,Building_No,Street_No,Zone)";







    } else{







      $result=$this->Api_Model->add_address();















      if($result){







           $data['result']="true";







           $data['msg']=" address is successfully added";







      }else{







           $data['result']="true";







           $data['msg']="Somethings went Wrong";







      }







    }







  echo json_encode($data);







  }
*/














  public function get_address_detail(){







    $this->form_validation->set_rules('address_id','address_id','trim|required');







    $this->form_validation->set_rules('user_id','user_id','trim|required');







    if($this->form_validation->run()==false){

      $data['result']="false";

      $data['msg']="parameter required user_id,address_id";

    //   $data['message']=strip_tags($this->form_validation->error_string());







    } else{







      $result=$this->Api_Model->get_address();



      if($result){







        $data['result']="true";







        $data['msg']="Addrss detail";







        $data['data']=$result;







      }else{

        $data['result']="false";

        $data['msg']="Somethings went Wrong";

        // $data['data']=array();

      }







    }







    echo json_encode($data);







  }















  public function Updaate_Address(){







        $this->form_validation->set_rules('address_id','address_id','trim|required');







        $this->form_validation->set_rules('Apartments_No','Apartments_No','trim|required');







        $this->form_validation->set_rules('user_id','user_id','trim|required');







        $this->form_validation->set_rules('city','city','trim|required');







        $this->form_validation->set_rules('Building_No','Building_No','trim|required');







        $this->form_validation->set_rules('Street_No','Street_No.','trim|required');







        $this->form_validation->set_rules('Zone','Zone','trim|required');







        $this->form_validation->set_rules('landmark','landmark','trim|required');







        $this->form_validation->set_rules('mobile','mobile','trim|required');







        $this->form_validation->set_rules('type','type','trim|required');







      if($this->form_validation->run()==false){







             $data['result']="false";







             $data['msg']=strip_tags($this->form_validation->error_string());







      } else{







        $result=$this->Api_Model->update_address();















      if($result){







           $data['result']="true";







           $data['msg']="Addrss updated successfully.";















      }else{







           $data['result']="true";







           $data['msg']="Somethings went Wrong";















      }







    }







    echo json_encode($data);







  }









  public function get_address(){

    $this->form_validation->set_rules('user_id','user_id','trim|required');



    if($this->form_validation->run()==false){



        $data['result']="false";



       $data['msg']=strip_tags($this->form_validation->error_string());



    } else{

      $result=$this->Api_Model->get_address_list();



      if($result){



           $data['result']="true";



           $data['msg']="Addrss list ";



           $data['data']=$result;

      }else{

           $data['result']="false";

           $data['msg']="user address not exist";

        //   $data['data']=array();

      }



    }







    echo json_encode($data);







  }



    /*delete address*/



    public function delete_address(){

        $this->form_validation->set_rules('user_id','user_id','trim|required');

        $this->form_validation->set_rules('address_id','address_id','trim|required');

        if($this->form_validation->run()==false){

            $data['result']="false";

            $data['msg']=strip_tags($this->form_validation->error_string());

        } else{

            $id=$this->input->post('address_id');

            $result=$this->db->query('DELETE from address WHERE id='.$id.'');

            if($result){

                $data['result']="true";

                $data['msg']="Addrss Deleted ";

            }else{

                $data['result']="false";

                $data['msg']="Somethings went Wrong";

            }

        }

        echo json_encode($data);



    }





























  public function shop_fav(){



    $this->form_validation->set_rules('user_id','user_id','trim|required');



    $this->form_validation->set_rules('shop_id','shop_id','trim|required');



    if($this->form_validation->run()==false){



      $data1['result']="false";



      $data1['msg']="required user_id,shop_id";



    }else{



      $shop_id=$this->input->post('shop_id');



      $user_id=$this->input->post('user_id');



      $data=$this->db->query('Select * from fev_shop Where shop_id ='.$shop_id.' and user_id='.$user_id.'')->row();







      if($data):







        $this->db->query('DELETE FROM `fev_shop` WHERE  `shop_id` ='.$shop_id.' and `user_id`='.$user_id.'');



        $data1['result']="true";



        $data1['status']='0';



        $data1['msg']="shop is remove from  from faverite list. ";







      else:















      $this->db->insert('fev_shop', array('shop_id'=>$shop_id,'user_id'=>$user_id,'status'=>'1'));







      $data1['result']="true";







      $data1['status']='1';







      $data1['msg']="shop is added in faverite list. ";







      endif;



    }







    echo json_encode($data1);


  }


/*

	public function shop_unfav(){
	    $this->form_validation->set_rules('user_id','user_id','trim|required');
	    $this->form_validation->set_rules('shop_id','shop_id','trim|required');
	    if($this->form_validation->run()==false){
	    	$datas['result']="false";
	    	$datas['msg']="required user_id,shop_id";
	    } else{
	    	$shop_id=$this->input->post('shop_id');
	    	$user_id=$this->input->post('user_id');
	    	$data=$this->db->query('Select * from fev_shop Where shop_id ='.$shop_id.' and user_id='.$user_id.' ')->row();
	    	if($data):
	    		$this->db->where('shop_id',$shop_id);
	    		$this->db->where('user_id',$user_id);
	    		$this->db->update('fev_shop',array('status'=>'0'));
	    	else:
	    		$this->db->insert('fev_shop',array('shop_id'=>$shop_id,'user_id'=>$user_id,'status'=>'0'));
	    	endif;
	    	$datas['result']="true";
	    	// $datas['status']='0';
	    	$datas['msg']="shop is remove from faverite list. ";
	    }
	    echo json_encode($datas);
	}
*/

	public function shop_unfav(){
	    $this->form_validation->set_rules('user_id','user_id','trim|required');
	    $this->form_validation->set_rules('shop_id','shop_id','trim|required');
	    if($this->form_validation->run()==false){
	    	$datas['result']="false";
	    	$datas['msg']="required user_id,shop_id";
	    } else{
	    	$shop_id=$this->input->post('shop_id');
	    	$user_id=$this->input->post('user_id');
	    /*	$data=$this->db->query('Select * from fev_shop Where shop_id ='.$shop_id.' and user_id='.$user_id.' ')->row();*/


			$this->db->select('*');

			$this->db->from('fev_shop');

			$this->db->where('user_id',$user_id);

			$this->db->where('shop_id',$shop_id);


			$query = $this->db->get();

			if($query->num_rows() > 0){
			// 	if($data):
			$this->db->where('shop_id',$shop_id);
			$this->db->where('user_id',$user_id);
			$this->db->delete('fev_shop');

			$datas['result']="true";
			// $datas['status']='0';
			$datas['msg']="shop is remove from faverite list. ";

                 }
	    	else{
	    		$this->db->insert('fev_shop',array('shop_id'=>$shop_id,'user_id'=>$user_id,'status'=>'0'));

	    	$datas['result']="true";
	    	// $datas['status']='0';
	    	$datas['msg']="shop is remove from faverite list. ";
	    	}
	    }
	    echo json_encode($datas);
	}


public function shop_fav_list(){
    extract($_POST);
    $this->form_validation->set_rules('user_id','user_id','trim|required');

    if($this->form_validation->run()==false){
    	$data1['result']="false";
    	$data1['msg']=strip_tags($this->form_validation->error_string());
    } else{
    	$user_id=$this->input->post('user_id');

        $dataasaas=$this->db->query('Select * from fev_shop Where  user_id='.$user_id.' ')->result();
	//print_r($data);exit();
		if($dataasaas):
			foreach($dataasaas as $value):

        $shop[]=$this->db->query('select shop_details.id,shop_details.Service_id,shop_details.vendor_id,shop_details.category_id,shop_details.shop_name,shop_details.shop_image,shop_details.logo,shop_details.shop_about,shop_details.status,shop_details.fav_status,shop_details.delivery,shop_details.address,shop_details.lat,shop_details.lang,shop_details.created_date from shop_details Where id ='.$value->shop_id.'')->row();


        endforeach;
        $data1['result']="true";
        $data1['msg']="shop faverite list ";
        $data1['data']=$shop;
    else:
    	$data1['result']="false";
    	$data1['msg']="somthing went wrong ";
    	//$data1['data']=array();
    endif;
}
echo json_encode($data1);
}



//shop_category_lista
public function shop_category_lista(){
    $shop_id=$this->input->post('shop_id');
    $lang_id=$this->input->post('lang_id');
    if (isset($shop_id) && isset($lang_id)) {

      $result=$this->Api_Model->get_shop_categories($shop_id,$lang_id);

      if ($result) {

        $data1['result']="true";
        $data1['msg']="shop category list";
        $data1['data']=$result;
      }else{
        $data1['result']="false";
        $data1['msg']="sorry not any list";
      }

    }else{

      $data1['result']="false";

      $data1['msg']="parameter required shop_id,lang_id";

    }
    echo json_encode($data1);

  }





  public function about_us(){

    $data=$this->db->query('Select * from about_us ')->row();



    $data1['result']="true";



    $data1['msg']="About us";



    $data1['data']=$data;



    echo json_encode($data1);



  }



















  // public function sub_services_list(){



  //   $data1=array();



  //   $this->form_validation->set_rules('service_id','service_id','trim|required');



  //   if($this->form_validation->run()==false){



  //     $data1['result']="false";



  //     $data1['msg']="service_id is required parameter";



  //   } else{



  //     $service_id=$this->input->post('service_id');



  //     $data=$this->db->query('Select * from subservices Where  service_id='.$service_id.'')->result();



  //     if($data):



  //       $data1['result']="true";



  //       $data1['msg']="service_id category list ";



  //       $data1['data']=$data;



  //     else:



  //       $data1['result']="true";



  //       $data1['msg']="somthing went wrong ";



  //       $data1['data']=array();



  //     endif;



  //   }



  //   echo json_encode($data1);



  // }























































  public function addon_iteam_list(){
    extract($_POST);
    $data1=array();
    $this->form_validation->set_rules('product_id','product_id','trim|required');
    $this->form_validation->set_rules('lang_id','lang_id','trim|required');
    if($this->form_validation->run()==false){
      $data1['result']="false";
      $data1['msg']="product_id,lang_id is required parameter";
    } else{
      //   $product_id=$this->input->post('product_id');
      //   $lang_id=$this->input->post('lang_id');
      //   $data=$this->db->query('Select * from addon_product Where  product_id='.$product_id.'')->result();
      $data=$this->Api_Model->addon_products($product_id,$lang_id);
    if($data):
      $data1['result']="true";
      $data1['msg']="Addon product list";
      $data1['data']=$data;
      else:
      $data1['result']="false";
      $data1['msg']="no data found ";
      //$data1['data']=array();
      endif;
    }
    echo json_encode($data1);
  }































































  public function cart_count(){







    $data1=array();







    $this->form_validation->set_rules('user_id','user_id','trim|required');







    if($this->form_validation->run()==false){







      $data['result']="false";







      $data['msg']="product_id is required parameter";







    }else{







      $user_id=$this->input->post('user_id');







      $data=$this->db->query('Select * from cart Where  user_id='.$user_id.'')->result();







      if($data):







        $data1['result']="true";



        $data1['msg']="cart count ";



        $data1['data']=count($data);



      else:







        $data1['result']="true";



        $data1['msg']="somthing went wrong ";



        $data1['data']=0;



      endif;



    }



    echo json_encode($data1);



  }

















  // order history get result data to database and then create new array and sent in json formate







  public function Orderhistory(){







    $data1=array();







    $this->form_validation->set_rules('user_id','user_id','trim|required');







    if($this->form_validation->run()==false){



      $data1['result']="false";



      $data1['msg']="user_id is required";



    } else{



      $user_id=$this->input->post('user_id');



      $total_pay=0;



      $price=0;



      $data=$this->db->query('select * from orders where user_id='.$user_id.'')->result();



      if($data):

        $this->db->select('orders.*, COUNT(orderid) as totaliteam');

        $this->db->where('user_id',$user_id);

        $this->db->group_by('orderid');

        $datanew=$this->db->get('orders')->result();

        foreach($datanew as $value):

          $selectShop=$this->db->query("SELECT orders.order_id,shop_details.shop_name FROM orders LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

          WHERE orders.ordersID='".$value->ordersID."'")->row();

          $newarray[]=array(

            'ordersID'=>$value->ordersID,

            'order_id'=>$value->order_id,

            'orderid'=>$value->orderid,

            'shop_name'=>$selectShop->shop_name,

            'created_date'=>$value->created_date,

            'totaliteam'=>$value->totaliteam,

            'total'=>$value->total,

            'sub_total'=>$value->sub_total,

            'order_status'=>$value->order_status

          );



        endforeach;



        $data1['result']="true";







        $data1['msg']="Order Detail";







        $data1['data']=$newarray;







      else:



      $data1['result']="true";



      $data1['msg']="somthing went wrong ";



      $data1['data']=array();



      endif;



    }



    echo json_encode($data1);



  }















  /*All services created by admin*/



  public function All_services(){







    $data1=array();







    $this->form_validation->set_rules('service_id','service_id','trim|required');







    $this->form_validation->set_rules('lang_id','lang_id','trim|required');







    if($this->form_validation->run()==false){



      $data1['result']="false";



      // $data1['msg']="service_id is required parameter";



      $data1['msg']="service_id,lang_id is required parameter";



    } else{







      // $user_id=$this->input->post('user_id');



      $lang_id=$this->input->post('lang_id');



      if ($lang_id==1) {



       $data=$this->db->query('Select id,ar_name as name,status,ar_description as description,image,created_date from services Where   id not IN (1,2,3,4,5,6,7)')->result();



      }else{



        $data=$this->db->query('Select id,name,status,description,image,created_date from services Where   id not IN (1,2,3,4,5,6,7)')->result();



      }



      // $data=$this->db->query('Select * from services Where   id not IN (1,2,3,4,5,6,7)')->result();











      if($data):



        $data1['result']="true";



        $data1['msg']="All Service List ";



        $data1['data']=$data;



      else:



        $data1['result']="false";



        $data1['msg']="somthing went wrong ";



        $data1['data']=array();



      endif;



    }



    echo json_encode($data1);



  }



























  /*Add Vendor shop details*/



  public function AddVendor(){







        $data1=array();















    $this->form_validation->set_rules('shop_name', 'shop_name', 'trim|required');







    $this->form_validation->set_rules('description', 'description', 'trim|required');







    $this->form_validation->set_rules('address', 'address', 'trim|required');







    $this->form_validation->set_rules('name', 'name', 'trim|required');







    $this->form_validation->set_rules('email', 'email', 'trim|required');







    $this->form_validation->set_rules('mobile', 'mobile', 'trim|required');







    $this->form_validation->set_rules('service', 'service', 'trim|required');







    $this->form_validation->set_rules('password', 'Password', 'trim|required');







        if($this->form_validation->run()==false){







             $data1['result']="false";







             $data1['msg']="shop_name,description,address,name,email,mobile,service,password,image,lat,lang is required parameter";







       } else{























           $data = $this->Api_Model->AddVendor();







          if($data):















           $data1['result']="true";







           $data1['msg']="Vendor added successfully ";















             else:







           $data1['result']="false";







           $data1['msg']="somthing went wrong ";















           endif;







    }







      echo json_encode($data1);







   }























   /*







   @get all products







   */



    public function get_all_product(){



      $service_id= $this->input->post('service_id');





      $lang_id= $this->input->post('lang_id');



      if(isset($service_id) && isset($lang_id)){



        $result=$this->Api_Model->getAllProduct($service_id,$lang_id);



        if (count($result)) {



          $json['result']="true";



          $json['msg']="All product list.";



          $json['data']=$result;



        }else{



          $json['result']="false";



          $json['msg']="sorry not any products here!!";



        }



      }else{



        $json['result']="false";



        $json['msg']="parameter required service_id,lang_id";



      }



      echo json_encode($json);



    }

public function get_all_product1(){



      $shop_id= $this->input->post('shop_id');

      $category_id= $this->input->post('category_id');



      $lang_id= $this->input->post('lang_id');



      if(isset($shop_id) && isset($lang_id) && isset($category_id)){



        $result=$this->Api_Model->getAllProduct($shop_id,$category_id,$lang_id);



        if (count($result)) {



          $json['result']="true";



          $json['msg']="All product list.";



          $json['data']=$result;



        }else{



          $json['result']="false";



          $json['msg']="sorry not any products here!!";



        }



      }else{



        $json['result']="false";



        $json['msg']="parameter required shop_id,category_id,lang_id";



      }



      echo json_encode($json);



    }














    /*



    @laundry product list (like addon show) list



    */



    public function laundry_AddonProducts(){



      $service_id= $this->input->post('service_id');



      $product_id= $this->input->post('product_id');



      if(isset($service_id) && isset($product_id)){



        // $result=$this->Api_Model->laundryProductCategoryAddon($service_id,$product_id);



        $result=$this->Api_Model->laundryAddonProduct($service_id,$product_id);



        // print_r($result);exit();



        if (count($result)) {



          $json['result']="true";



          $json['msg']="Laundry Addon list.";



          $json['data']=$result;



        }else{



          $json['result']="false";



          $json['msg']="sorry not any addon here!!";



        }



      }else{



        $json['result']="false";



        $json['msg']="parameter required service_id,product_id";



      }



      echo json_encode($json);



    }











    /*



    @my orders detail



    */



    // public function OrderDetails(){



    //   $this->form_validation->set_rules('order_id','order_id','trim|required');



    //   if($this->form_validation->run()==false){



    //     $data['response']=false;



    //     $data['message']="order_id required parameter";



    //   }else{







    //     $result=$this->Api_Model->Get_order_list();



    //     $dataaddon=array();



    //     foreach($result as $value):







    //       if(!$value->addon==0):



    //         $addarray= explode(',',$value->addon);



    //         $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();



    //       endif;







    //       $resultnew[]=array(



    //         'id'=>$value->order_id,



    //         'id'=>$value->orderid,



    //         'user_id'=>$value->user_id,



    //         'product_id'=>$value->product_id,



    //         'vendor_id'=>$value->vendor_id,



    //         'name'=>$value->name,



    //         'image'=>$value->image,



    //         'addon_price'=>$value->addon_price,







    //         'price'=>$value->price,



    //         'quantity'=>$value->quanitity,



    //         'total_amount'=>$value->sub_total,



    //         'final_amount'=>$value->sub_total+$value->addon_price,



    //         'order_status'=>$value->order_status,



    //         'payment_method'=>$value->payment_method,



    //         'delivery_fee'=>$value->delivery_fee,



    //         'order_date'=>date('d-m-y', strtotime(str_replace('/', '-', $value->created_date))),



    //         'addon'=>$dataaddon



    //       );







    //     endforeach;







    //     if($result){



    //       $data['result']="true";



    //       $data['data']=$resultnew;



    //     }else{



    //     $data['result']="false";



    //     $data['message']="Order empty";



    //   }



    // }



    // echo json_encode($data);



    // }










































  /*
  @do rating review to shop
  */
  public function rating_review(){
    extract($_POST);
    if (isset($user_id) && isset($shop_id) && isset($rating) && isset($review)) {
      $result=$this->Api_Model->insertAllData('rating_review',$_POST);
      if ($result) {
        $data['result']="true";
        $data['msg']="Submitted your rating review";
      }else{
        $data['result']="false";
        $data['msg']="Not submit your rating review";
      }
    }else{
      $data['result']="false";
      $data['msg']="parameter required user_id,shop_id.rating,review";
    }
    echo json_encode($data);
  }











  /*



  @shop rating review list



  */



















  /*@filter rating by api*/
  public function filter_shopsByRating(){


    extract($_POST);
    // $category_id=$this->input->post('category_id');
    // $lat=$this->input->post('lat');
    // $lng=$this->input->post('lng');



    if (isset($type) && isset($lat) && isset($lng) && isset($user_id)) {



      // $result=$this->Api_Model->shop_listByavg_rating($lat,$lang);



      //$result=$this->Api_Model->getShopListByRating($type);
      $result=$this->Api_Model->getShopListByRating($type,$lat,$lng);



      if ($result) {



       $json['result']="true";



       $json['msg']="shop list by rating";



       $json['data']=$result;



      }else{



        $json['result']="false";



        $json['msg']="sorry not any list";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required type,user_id,lat,lng";



    }







    echo json_encode($json);



  }





  /*filter AtoZ by service id and language id*/

  public function filter_AtoZ11(){



    $service_id=$this->input->post('service_id');

    $lang_id=$this->input->post('lang_id');



    if (isset($service_id) && isset($lang_id)) {







      $result=$this->Api_Model->getShopListByAtoZ($service_id,$lang_id);



      if ($result) {



       $json['result']="true";



       $json['msg']="shop list by albhabetic";



       $json['data']=$result;



      }else{



        $json['result']="false";



        $json['msg']="sorry not any list";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required service_id,lang_id";



    }







    echo json_encode($json);



  }











  /*
  @vendor signup
  */
public function vendor_signup(){
    extract($_POST);
    if (
      isset($name) &&
      isset($email_id) &&
      isset($mobile) &&
      isset($password) &&
      isset($address) &&
      isset($shop_name) &&
      isset($shop_about) &&
      isset($fcm_id) &&
      isset($lat) &&
      isset($lang) &&
      isset($category_id) &&
      isset($type) &&
      isset($delivery_time)
    ) {
    	if(!$this->User_Model->is_record_exist('vendor','email_id',$email_id ))

      {





      if(!$this->User_Model->is_record_exist('vendor','mobile',$email_id ))

      {



      if(!empty($_FILES['shop_image']['name'])){

        $config['upload_path'] = 'uploads/';

        $config['allowed_types'] = 'jpg|jpeg|png';

        //Load upload library and initialize configuration

        $this->load->library('upload',$config);

        $this->upload->initialize($config);

        if($this->upload->do_upload('shop_image')){
          $uploadData = $this->upload->data();
          $shop_image = $uploadData['file_name'];
        }else{
          $shop_image = '';
        }
          $post_data['shop_image'] = $shop_image;
        }




        $vendorData=array(
          'name'=>$name,
          'email_id'=>$email_id,
          'mobile'=>$mobile,
          'password'=>$password,

          'status'=>1,
          'fcm_id'=>$fcm_id
        );
        $result=$this->Api_Model->insertAllData('vendor',$vendorData);
        $lastid=$this->db->insert_id();
        $this->db->where('id',$lastid);
        $this->db->update('vendor',array('vendor_id' =>"HstyId0".$lastid));
        if(!empty($_FILES['logo']['name'])){
          $config['upload_path'] = 'uploads/';
          $config['allowed_types'] = 'jpg|jpeg|png';
          //Load upload library and initialize configuration
          $this->load->library('upload',$config);
          $this->upload->initialize($config);
          if($this->upload->do_upload('logo')){
          	$uploadData = $this->upload->data();
          	$logo = $uploadData['file_name'];
          }else{
          	$logo = '';
          }
          $post_data['logo'] = $logo;
      	}
      	$logo='';
        if ($result) {
          $shopData=array(
          'vendor_id'=>$lastid,
          'address'=>$address,
          'shop_name'=>$shop_name,
          'shop_image'=>$shop_image,
          'category_id'=>$category_id,
          'logo'=>$logo,
           'type'=>$type,
          'shop_about'=>$shop_about,
          'delivery_time'=>$delivery_time,
          // 'status'=>0,

          'lat'=>$lat,
          'lang'=>$lang
        );



          $res=$this->Api_Model->insertAllData('shop_details',$shopData);



          if ($result) {

              $vendorNoti=array('vendor_id'=>$lastid,'message'=>'New Vendor','sender_type'=>'vendor','receiver_type'=>'Admin','types'=>'registration');



            $res=$this->Api_Model->insertAllData('notifications',$vendorNoti);



            $ri_email = 'tastyhasty27feb@gmail.com';







            $to       = $email_id;







            $subject  = 'confirmation';















            $headers = "From: <" . $ri_email . ">" . "\r\n";







            $headers .= "MIME-Version: 1.0\r\n";







            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";







            $message = '';







            $message .= '<!DOCTYPE html>







            <html>







                <head>







                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />







                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">







                </head>















                <body style="font-family:roboto !important;">







                    <div style="width:100%; text-align:center; margin:0px auto;">







                    <div style="width: 550px; height: auto;  margin: 10px auto;;">















                    <div style="background:whitesmoke;padding: 15px 0;font-family: sans-serif;">















                    <center>







                    <p>Dear ' . ucfirst($name) . '.</p>







                    <p>







                    Your are successfully regisgter with us.</p>



                    <p>Your account is verify by Admin so please wait for login.</p>







                    </center>







                    </div>















                    </div>







                    </div>







                </body>







            </html>';







           if(mail($to, $subject, $message, $headers)){
           	$json['result']="true";
            // $json['msg']="mail send";
            // $json['result']="true";
            $json['msg']="vendor successfully register with us";
            $json['shop_img_url']="/assets/uploaded/users/";
           }else{
            $json['result']="false";
            $json['msg']="sorry mail not send";
           }
          }else{
            $json['result']="false";
            $json['msg']="sorry vendor not register with us";
          }

          // }
          // $json['result']="true";
          // $json['msg']="vendor successfully register with us";
          // $json['shop_img_url']="http://logicaltest.in/Easydelivery/assets/uploaded/users/";
        // }else{
        //   $json['result']="false";
        //   $json['msg']="shop name alredy register";
        // }
        }else{
          $json['result']="false";
          $json['msg']="sorry vendor not register with us";
        }
        }else{
          $json['result']="false";
          $json['msg']="sorry Mobile No alreay exist!!";
        }
        }else{
          $json['result']="false";
          $json['msg']="sorry Email alreay exist!!";
        }
    }else{
      $json['result']="false";
      $json['msg']="parameter required name,email_id,password,mobile,address,shop_name,shop_about,lat,lang,shop_image,optional(logo),fcm_id,delivery_time,category_id,type(1=delivery,2=pickup,3=table-book)";
    }
    echo json_encode($json);
  }







  /*get user completed order history*/

  public function user_complete_order_history(){
    $user_id= $this->input->post('user_id');
    if(isset($user_id)){
        $result=$this->Api_Model->userCompleteOrders($user_id);
        foreach ($result as $key => $value) {
          $value->rating_status=$this->Api_Model->userRatingRivew($value->vendor_id,$user_id);
        }
        foreach($result as $value){
        	$wheredata=array('ordersID'=>$value->ordersID);
     		$sub=  $this->Api_Model->selectData('orders',$wheredata);
	     	if(!empty($sub)){

			$amo=[];

			$add_on=[];

			$quanitity=[];

			foreach($sub as $sub1){
				$amo1 =$sub1->price*$sub1->quanitity;
				$amo[]=$amo1;
				$add_on[]=$sub1->addon_price;
				$quanitity[]=$sub1->quanitity;
			}

	       $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();
	       $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;
	       $arrya_sum=array_sum($amo);
	       $arrya_add_on=array_sum($add_on);
	       $qua=array_sum($quanitity);
	       $ad_on_plus=$arrya_sum+$arrya_add_on;
	       $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;
		}else{
			$final_amo=0;
		}



    $array[]=array(
        'order_id'=>$value->order_id,
        'ordersID'=>$value->ordersID,
        'order_status'=>$value->order_status,
        'addon_price'=>$arrya_add_on,
        'quanitity'=>$qua,
        'payment_method'=>$value->payment_method,
        'transaction_id'=>$value->transaction_id,
        'sub_total'=>$final_amo,
        'total'=>$ad_on_plus,
        'shop_name'=>$value->shop_name,
        'order_date'=>$value->order_date,
        'vendor_id'=>$value->vendor_id,





        );





}

        if (count($result)) {

          $json['result']="true";

          $json['msg']="user's order list.";

          $json['data']=$array;

        }else{

          $json['result']="false";

          $json['msg']="sorry not any order here!!";

        }

      }else{

        $json['result']="false";

        $json['msg']="parameter required user_id";

      }

      echo json_encode($json);

    }











    // public function cancel_order(){



    //   $user_id= $this->input->post('user_id');



    //   $order_id= $this->input->post('order_id');







    //   if(isset($user_id) && isset($order_id)){



    //     $result=$this->Api_Model->userCompleteOrders($user_id);



    //     if (count($result)) {



    //       $json['result']="true";



    //       $json['msg']="user's order list.";



    //       $json['data']=$result;



    //     }else{



    //       $json['result']="false";



    //       $json['msg']="sorry not any order here!!";



    //     }



    //   }else{



    //     $json['result']="false";



    //     $json['msg']="parameter required user_id";



    //   }



    //   echo json_encode($json);



    // }















  // public function CompleteOrderhistory(){







  //   $data1=array();







  //   $this->form_validation->set_rules('user_id','user_id','trim|required');







  //   if($this->form_validation->run()==false){



  //     $data1['response']="false";



  //     $data1['message']="user_id is required";



  //   } else{



  //     $user_id=$this->input->post('user_id');



  //     $total_pay=0;



  //     $price=0;



  //     $data=$this->db->query('select * from orders where user_id='.$user_id.'')->result();















  //     if($data):







  //       $this->db->select('orders.*, COUNT(orderid) as totaliteam');







  //       $this->db->where('user_id',$user_id);



  //       $this->db->where('orders.order_status',4);







  //       $this->db->group_by('orderid');







  //       $datanew=$this->db->get('orders')->result();



  //       if (count($datanew)) {



  //         foreach($datanew as $value):







  //         $newarray[]=array(







  //          'order_id'=>$value->order_id,







  //           'orderid'=>$value->orderid,







  //           'created_date'=>$value->created_date,







  //           'totaliteam'=>$value->totaliteam,







  //           'total'=>$value->total,







  //           'sub_total'=>$value->sub_total,







  //           'order_status'=>$value->order_status







  //         );



  //       endforeach;



  //       $data1['result']="true";







  //       $data1['msg']="Order Detail ";







  //       $data1['data']=$newarray;



  //       }else{



  //         $data1['result']="false";



  //         $data1['msg']="sorry not any data";



  //       }











  //     else:



  //     $data1['result']="true";



  //     $data1['msg']="somthing went wrong ";



  //     $data1['data']=array();



  //     endif;



  //   }



  //   echo json_encode($data1);



  // }























  /*#############DELIVERY BOY SECTION##########*/



  /*#############DELIVERY BOY SECTION##########*/















	/*Driver Signup*/
	function delivery_boy_signup(){
	    extract($_POST);
	    // print_r($_POST);exit();
	    if(
	      isset($name) &&
	      isset($email) &&
	      isset($mobile) &&
	      isset($address) &&
	      isset($password) &&
	      isset($insurence) &&
	      isset($work_exp) &&
	      isset($vehicle_no) &&
	      isset($vehicle_year) &&
	      isset($vehicle_model) &&
	      isset($driving_licence) &&
	      isset($loading_capacity) &&
	      isset($vehicle_type) &&
	      isset($lat) &&
	      isset($lang) &&
	      isset($delivery_fee) &&
	      isset($fcm_id)
	    ){

	    	if(!$this->User_Model->is_record_exist('delivery_boy','email',$email ))

	      	{

	      		if(!$this->User_Model->is_record_exist('delivery_boy','mobile',$mobile ))
	      		{

	      			if(!empty($_FILES['image']['name'])){
	        			$config['upload_path'] = 'assets/uploaded/users/';
	        			$config['allowed_types'] = 'jpg|jpeg|png';
				        //Load upload library and initialize configuration
				        $this->load->library('upload',$config);
				        $this->upload->initialize($config);
				        if($this->upload->do_upload('image')){
				          $uploadData = $this->upload->data();
				          $image = $uploadData['file_name'];
				        }else{
				          $image = '';
				        }
	        			$post_data['image'] = $image;
	      			}


					/*front*/
					if(!empty($_FILES['id_proof']['name'])){
						$config['upload_path'] = 'assets/uploaded/users/';
						// $config['allowed_types'] = 'jpg|jpeg|png';
						$config['allowed_types'] = '*';
						//Load upload library and initialize configuration
						$this->load->library('upload',$config);
						$this->upload->initialize($config);
						if($this->upload->do_upload('id_proof')){
							$uploadData = $this->upload->data();
							$id_proof = $uploadData['file_name'];
						}else{
							$id_proof = '';
						}
							$post_data['id_proof'] = $id_proof;
					}

			      /*back*/
			      if(!empty($_FILES['id_proof_bck']['name'])){
			      	$config['upload_path'] = 'assets/uploaded/users/';
			        $config['allowed_types'] = '*';
			        //Load upload library and initialize configuration
			        $this->load->library('upload',$config);
			        $this->upload->initialize($config);
			        if($this->upload->do_upload('id_proof_bck')){
			          $uploadData = $this->upload->data();
			          $id_proof_bck = $uploadData['file_name'];
			        }else{
			          $id_proof_bck = '';
			        }
			        $post_data['id_proof_bck'] = $id_proof_bck;
			      }
			      $post_data['password'] = $password;
			      $post_data['name'] = $name;
			      $post_data['email'] = $email;
			      $post_data['mobile'] = $mobile;
			      $post_data['address'] = $address;
			      $post_data['lat'] = $lat;
			      $post_data['lng'] = $lang;
			      $post_data['insurence'] = $insurence;
			      $post_data['work_exp'] = $work_exp;
			      $post_data['vehicle_no'] = $vehicle_no;
			      $post_data['vehicle_year'] = $vehicle_year;
			      $post_data['vehicle_model'] = $vehicle_model;
			      $post_data['driving_licence'] = $driving_licence;
			      $post_data['loading_capacity'] = $loading_capacity;
			      $post_data['vehicle_type'] = $vehicle_type;
			      $post_data['delivery_fee'] = $delivery_fee;
			      $post_data['fcm_id'] = $fcm_id;
			      $post_data['status'] = 'Inactive';
			      $result = $this->Api_Model->insertAllData('delivery_boy',$post_data);
			      if ($result) {
			        $json['result']="true";
			        $json['msg']="driver registered successfully";
			        $json['img_url']='/assets/uploaded/users/';
			        $json['data'] = $this->Admin_model->select_single_row('delivery_boy','id',$result);
			      }else{
			        $json['result']="false";
			        $json['msg']="sorry not registered";
			      }
				}else{
					$json['result']="false";
					$json['msg']="mobile alreay exist";
				}
			}else{
				$json['result']="false";
				$json['msg']="email alreay exist";
			}
	    }else{
	      	$json['result']="false";
	    	$json['msg']="paramter required (name,email,mobile,address,password,insurence,work_exp,vehicle_no,vehicle_year,vehicle_model,driving_licence,loading_capacity,vehicle_type,lat,lang,delivery_fee,fcm_id ,id_proof,id_proof_bck,image)";
	    }
	    echo json_encode($json);
	}















	public function login_deliveryBoy(){
		extract($_POST);
	    $email  = $this->input->post('email');
	    $password = $this->input->post('password');
	    $fcm_id  = $this->input->post('fcm_id');
	    $lat = $this->input->post('lat');
	    $lng = $this->input->post('lng');
	    if (isset($email) && isset($password) && isset($fcm_id) && isset($lat) &&isset($lng)) {
	    	$result1 =  $this->User_Model->check_drivers($email);
			$wheredata=array(
			'email'     =>$email
			);

			$data = array(
				'fcm_id'   =>$fcm_id,
				'lat'      =>$lat,
				'lng'      =>$lng
			);
			$result=$this->User_Model->updates('delivery_boy',$data,$wheredata);
	    	$result2=$this->db->query("SELECT * FROM delivery_boy WHERE email='$email' AND password='$password' ")->row();
        if ($result1->status=='Active') {
          if (!empty($result)) {
            $data_result['result'] = 'true';
            $data_result['msg']    = 'Login Successfully!!';
            $data_result['data']   =  $result1;
          } else {
            $data_result['result'] = 'false';
            $data_result['msg']    = 'Invalid credentials!!';
          }
        }else{
          $data_result['result'] = 'false';
            $data_result['msg']    = 'Plaease verify your profile!!';
        }

		}else{
			$data_result['result']='false';
			$data_result['msg']='parameter (email,password,fcm_id,lat,lng) required';
		}
	  	echo json_encode($data_result);

	}



	/*
	@get profile delivery boy
	*/
    public function getProfileDliveryBoy(){
        $delivery_boy_id  = $this->input->post('delivery_boy_id');
        if (isset($delivery_boy_id)) {
            $result=$this->Api_Model->getDeliveryBoyProfileDetails($delivery_boy_id);
          	if (!empty($result)) {
	            $data_result['result'] = 'true';
	            $data_result['msg']    = 'delivery boy profile!!';
	            $data_result['data']   = $result;
          	} else {
	            $data_result['result'] = 'false';
	            $data_result['msg']    = 'Delivery boy not exist!!';
          	}
      	}else{
      		$data_result['result']='false';
      		$data_result['msg']='parameter required delivery_boy_id';
    	}
    	echo json_encode($data_result);
    }


	/*
	@get delivery boy profile
	*/
	public function edit_delivery_boy_profile(){
    	$delivery_boy_id  = $this->input->post('delivery_boy_id');
    	if (isset($delivery_boy_id)) {
			$wheredata=array(
			'id'     =>$delivery_boy_id
			);
	      	$result=$this->User_Model->selectDataById('delivery_boy',$wheredata);
			if (!empty($result)) {
				$data_result['result'] = 'true';
				$data_result['msg']    = 'edit delivery boy profile!!';
				$data_result['data']   = $result;
			} else {
				$data_result['result'] = 'false';
				$data_result['msg']    = 'Delivery boy not exist!!';
			}

	    }else{
	      $data_result['result']='false';
	      $data_result['msg']='parameter required delivery_boy_id';
	    }
	    echo json_encode($data_result);
	}







  /*
  Update delivery boy profile
  */
  public function update_deliveryBoyProfile(){
    extract($_POST);
    if (
      isset($delivery_boy_id) &&
      isset($mobile) &&
      isset($name) &&
      isset($address) &&
      // isset($password) &&
      isset($insurence) &&
      isset($work_exp) &&
      isset($vehicle_no) &&
      isset($vehicle_year) &&
      isset($vehicle_model) &&
      isset($driving_licence) &&
      isset($loading_capacity) &&
      isset($vehicle_type) ) {

      if(!empty($_FILES['image']['name'])){



        //$config['upload_path'] = 'uploads/';
        $config['upload_path'] = 'assets/uploaded/users/';



        $config['allowed_types'] = 'jpg|jpeg|png';



        //Load upload library and initialize configuration



        $this->load->library('upload',$config);



        $this->upload->initialize($config);



        if($this->upload->do_upload('image')){



          $uploadData = $this->upload->data();



          $image = $uploadData['file_name'];



        }else{



          $image = '';



        }



        $post_data['image'] = $image;



      }







      $post_data['name'] = $name;



      $post_data['mobile'] = $mobile;



      $post_data['address'] = $address;



      $post_data['insurence'] = $insurence;



      $post_data['work_exp'] = $work_exp;



      $post_data['vehicle_no'] = $vehicle_no;



      $post_data['vehicle_year'] = $vehicle_year;



      $post_data['vehicle_model'] = $vehicle_model;



      $post_data['driving_licence'] = $driving_licence;



      $post_data['loading_capacity'] = $loading_capacity;



      $post_data['vehicle_type'] = $vehicle_type;







      $update =$this->User_Model->updateData('delivery_boy',$post_data,$delivery_boy_id);



      if ($update) {



        $json['result']="true";



        $json['msg']="profile updated successfully";
        $json['data']= $this->Api_Model->select_single_row('delivery_boy','id',$delivery_boy_id);



      }else{



        $json['result']="false";



        $json['msg']="sorry somthing went wrong!!";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id,name,mobile,address,insurence,work_exp,vehicle_no,vehicle_model,vehicle_year,driving_licence,loading_capacity,vehicle_type,image(optional)";



    }



    echo json_encode($json);



  }































  /*



  @re Order



  */



  public function ReOrder12(){



    extract($_POST);



    if (isset($user_id) && isset($ordersID) ){







      $reorder_id_data = $this->db->query('SELECT MAX(re_orderid) as re_orderid FROM reOrder ')->row();



      $re_ordersID=$reorder_id_data->re_orderid;







      if($re_ordersID)



      {



        $re_ordersID++;



      }else{



        $re_ordersID = "90001";



      }



      $data=array(



        'user_id'=>$user_id,



        're_orderid'=>$re_ordersID,



        'ordersID'=>$ordersID



      );







      $result=$this->Api_Model->insertAllData('reOrder',$data);



      if ($result) {



        $json['result']="true";



        $json['msg']="order success";



      }else{



        $json['result']="false";



        $json['msg']="something went wrong";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required user_id,ordersID";



    }



    echo json_encode($json);



  }











  // public function re_order(){



  //   extract($_POST);



  //   if (isset($user_id) && isset($order_id) ){



  //     $data=array(



  //       'user_id'=>$user_id,



  //       'order_id'=>$order_id



  //     );



  //     $result=$this->Api_Model->insertAllData('reOrder',$data);



  //     if ($result) {



  //       $json['result']="true";



  //       $json['msg']="order success";



  //     }else{



  //       $json['result']="false";



  //       $json['msg']="something went wrong";



  //     }



  //   }else{



  //     $json['result']="false";



  //     $json['msg']="parameter required user_id,order_id";



  //   }



  //   echo json_encode($json);



  // }



























  /*



  @shop rating list



  */



  public function shop_rating_list(){
    $shop_id=$this->input->post('shop_id');
    if (isset($shop_id)) {
      $result=$this->Api_Model->ratingDetailsByShopId($shop_id);
      $total_rating=$this->Api_Model->total_rating($shop_id);
      $result2=$this->Api_Model->s_avg_rating($shop_id);
      $rating1=$this->Api_Model->s_avg_rating1($shop_id);
      $rating2=$this->Api_Model->s_avg_rating2($shop_id);
      $rating3=$this->Api_Model->s_avg_rating3($shop_id);
      $rating4=$this->Api_Model->s_avg_rating4($shop_id);
      $rating5=$this->Api_Model->s_avg_rating5($shop_id);
      if ($result) {
        $json['result']="true";
        $json['msg']="rating reviews";
        $json['total_rating']=$total_rating;
        $json['avg_rating']=$result;
        $json['rating1']=$rating1;
        $json['rating2']=$rating2;
        $json['rating3']=$rating3;
        $json['rating4']=$rating4;
        $json['rating5']=$rating5;
        $json['data']=$result;
      }else{
        $json['result']="false";
        $json['msg']="Not any rating reviews";
      }
    }else{
    	$json['result']="false";
        $json['msg']="parameter required shop_id";
    }
    echo json_encode($json);
}















  public function reOrder_billing_detail(){



    $data1=array();



    $user_id=$this->input->post('user_id');



    $ordersID=$this->input->post('ordersID');



    if(isset($user_id) && isset($ordersID)){



      $total_pay=0;



      $price=0;



      $data=$this->db->query('select * from orders where user_id='.$user_id.' AND ordersID='.$ordersID.' ')->result();



      if($data):



        foreach($data as $value):



          if(!$value->addon==0){



            $addarray= explode(',',$value->addon);



            $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();



            foreach($dataaddon as $value1):



            $price+=$value1->price;



            endforeach;



          }else{







          }



          $total_pay+=$value->total;



        endforeach;



        $total_pay+=$price;



        $newarray=array(



          'cart_iteam'=>count($data),



          'total_pay'=>$total_pay,



          'sub_total'=>$total_pay,



          'delivery_charge'=>0,



          'total_discount'=>0,



          'final_amount'=>$total_pay



        );







        $data1['result']="true";



        $data1['msg']="Billing Detail ";



        $data1['data']=$newarray;



      else:



      $data1['result']="false";



      $data1['msg']="somthing went wrong ";



      $data1['data']=array();



      endif;



    }else{



      $data1['result']="false";



      $data1['msg']="parameter required user_id,ordersID";



    }



    echo json_encode($data1);



  }











  /*Delivery Boy Order's detail when order assign by vendor*/
  function deliveryBoy_orders(){
    $delivery_boy_id=$this->input->post('delivery_boy_id');

    if (isset($delivery_boy_id)) {



      $result=$this->Api_Model->deliveryBoyOrder($delivery_boy_id);

      foreach($result as $value){
     	$wheredata=array('ordersID'=>$value->ordersID);
     	$sub=  $this->Api_Model->selectData('orders',$wheredata);
      $value->total_item=$this->Api_Model->total_item($value->ordersID);
		// $value->distance=  $this->Api_Model->get_ShopDistance($result['vendor_id'],$value->shop_lat,$value->shop_lang);
		if(!empty($sub)){

         $amo=[];

         $add_on=[];

         $quanitity=[];

       foreach($sub as $sub1){



      $amo1 =$sub1->price*$sub1->quanitity;

     $amo[]=$amo1;



     $add_on[]=$sub1->addon_price;

        $quanitity[]=$sub1->quanitity;



       }

       $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();

        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;



       $arrya_sum=array_sum($amo);

       $arrya_add_on=array_sum($add_on);

       $qua=array_sum($quanitity);

       $ad_on_plus=$arrya_sum+$arrya_add_on;

       $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;

     }else{



         $final_amo=0;

     }



     $array[]=array(

           'order_id'=>$value->order_id,

           'ordersID'=>$value->ordersID,

           'order_status'=>$value->order_status,

           'orderid'=>$value->orderid,

           'shop_name'=>$value->shop_name,
           'shop_image'=>$value->shop_image,

           'user_id'=>$value->user_id,

           'vendor_id'=>$value->vendor_id,



           'addon'=>$value->addon    ,

           'addon_price'=>$value->addon_price    ,

           'quanitity'=>$qua,

           'total'=>$final_amo,

           'sub_total'=>$final_amo,

           'payment_method'=>$value->payment_method,

           'transaction_id'=>$value->transaction_id,

           'order_date'=>$value->created_date,

           'vendor_name'=>$value->vendor_name,

           'vendor_contact'=>$value->vendor_contact,

           'shop_address'=>$value->shop_address,

           'shop_lat'=>$value->shop_lat,

           'shop_lang'=>$value->shop_lang,

           'name'=>$value->name,

           'mobile'=>$value->mobile,

           'address'=>$value->address,

           'image'=>$value->image,

           'trax_id'=>$value->trax_id,

           'instruction'=>$value->instruction,

           'food_prepare_time'=>$value->food_prepare_time,

           'extra_maintance_fee'=>$value->extra_maintance_fee,

           'extra_maintance_detail'=>$value->extra_maintance_detail,

           'delivery_charge'=>$value->delivery_charge,

           'type'=>$value->type,

           'otp'=>$value->otp,

           'otp_verify'=>$value->otp,

           'address_id'=>$value->address_id,

           'cancel_reason'=>$value->cancel_reason,

           'product_id'=>$value->product_id,

           're_order'=>$value->re_order,

           'modified_at'=>$value->modified_at,

           'delivery_boy_id'=>$value->delivery_boy_id,

           'wash_price'=>$value->wash_price,

           'dry_clean_price'=>$value->dry_clean_price,

           'iron_price'=>$value->iron_price,

           'exp_wash_price'=>$value->exp_wash_price,

           'exp_dry_clean_price'=>$value->exp_dry_clean_price,

           'exp_iron_price'=>$value->exp_iron_price,

           'maintenance_file'=>$value->maintenance_file,

           'lat'=>$value->lat,

           'lng'=>$value->lng,
           'distance'=>"5",
           'total_item'=>$value->total_item

            );







      }





      if ($result) {



        $json['result']="true";



        $json['msg']="orders";



        $json['data']=$array;



      }else{



        $json['result']="false";



        $json['msg']="not data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id";



    }



    echo json_encode($json);



  }



















  /*



  user delete cart item



  */



  public function user_delete_cart_item(){



    $user_id=$this->input->post('user_id');



    if (isset($user_id)){



      $result=$this->db->query("DELETE FROM cart WHERE user_id=$user_id");



      if ($result) {



        $json['result']="true";



        $json['msg']="cart empty successfully";



      }else{



        $json['result']="false";



        $json['msg']="sorry something went wrong";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required user_id";



    }



    echo json_encode($json);



  }











  /*



  laundry add on api



  */







  /*add to cart api*/



  public function LaundryAddToCart(){



    $this->form_validation->set_rules('user_id','User id','trim|required');



    $this->form_validation->set_rules('product_id','product_id','trim|required');



    $this->form_validation->set_rules('name','Name','trim|required');



    $this->form_validation->set_rules('total_amount','total amount','trim|required');



    $this->form_validation->set_rules('final_amount','final_amount','trim|required');



    if($this->form_validation->run()==false){



      $data['result']=false;



      $data['msg']="parameter required user_id,product_id,name,total_amount,final_amount,instruction,optinal(wash_price,dry_clean_price,iron_price,exp_wash_price,exp_dry_clean_price,exp_iron_price)";



    } else{



      $result=$this->Api_Model->Add_to_cart_laundry();



      if($result){



        $data['result']="true";



        $data['msg']="Added iteam  in cart Successfully";



      }else{



        $data['result']="false";



        $data['msg']="This product is already added in cart  ";



      }



    }



    echo json_encode($data);



  }







  /*



  laundry cart check



  */



  function laundry_check_cart(){



    extract($_POST);



    if (isset($user_id) && isset($vendor_id)) {



      $check=$this->db->query("SELECT user_id,vendor_id FROM `laundry_cart` WHERE cart.user_id=$user_id AND cart.vendor_id=$vendor_id ");











      $dataexist=$this->db->query("SELECT user_id,vendor_id FROM `laundry_cart` WHERE cart.user_id=$user_id OR cart.user_id=$user_id AND cart.vendor_id=$vendor_id ")->result();







      if (!empty($dataexist)) {







        if($check->num_rows()>0){



          $json['result']="true";



          $json['msg']="same item in cart";



        }else{



          $json['result']="false";



          $json['msg']="Remove cart Items for add products from another vendor";



        }







      }else{



        $json['result']="true";



        $json['msg']="add data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required user_id,vendor_id";



    }



    echo json_encode($json);



  }











  public function laundry_place_order(){







    $data1=array();







    $this->form_validation->set_rules('user_id','user_id','trim|required');







    $this->form_validation->set_rules('payment_method','payment_method','trim|required');







    $this->form_validation->set_rules('total_amount','total_amount','trim|required');







    $this->form_validation->set_rules('address_id','address_id','trim|required');







    if($this->form_validation->run()==false){







      $data1['result']="false";







      $data1['msg']="user_id,payment_method,total_amount,address_id is required parameter";







    } else{



      $user_id=$this->input->post('user_id');



      $data=$this->Api_Model->laundry_Place_order();



      if($data):



        $data1['result']="true";



        $data1['msg']="Order is Placed ";



      else:



        $data1['result']="false";



        $data1['msg']="somthing went wrong ";



      endif;



    }



    echo json_encode($data1);



  }











  /* check add to cart at a time once*/



  function check_add_to_cart(){

    extract($_POST);

    if (isset($user_id) && isset($vendor_id)) {



      $check=$this->db->query("SELECT user_id,vendor_id FROM `laundry_cart` WHERE cart.user_id=$user_id AND cart.vendor_id=$vendor_id ");





      $dataexist=$this->db->query("SELECT user_id,vendor_id FROM `laundry_cart` WHERE cart.user_id=$user_id OR cart.user_id=$user_id AND cart.vendor_id=$vendor_id ")->result();







      if (!empty($dataexist)) {







        if($check->num_rows()>0){



          $json['result']="true";



          $json['msg']="same item in cart";



        }else{



          $json['result']="false";



          $json['msg']="Remove cart Items for add products from another vendor";



        }







      }else{



        $json['result']="true";



        $json['msg']="add data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required user_id,vendor_id";



    }



    echo json_encode($json);



  }















  /*



  @delivery Boy forget password



  */







//   public function deliveryBoy_forgot_password()

//   {



//     $mobile = $this->input->post('mobile');

//     if (isset($mobile) ) {



//       $wheredata = array(



//         'email' => $email



//       );







//       $result    = $this->User_Model->selectAllByIds('delivery_boy', $wheredata);



//       if ($result) {



//         $wherenewpass = array(



//           'email' => $email



//         );



//         $random_no = rand(100000,999999);



//         $otp['password'] = $random_no;



//         $res  = $this->User_Model->updatePass($wherenewpass,'delivery_boy', $otp);







//         $res1 = $this->User_Model->selectAllByIds('delivery_boy', $wherenewpass);







//             if ($res1) {







//                 foreach ($res1 as $key => $value) {







//                     $myotp    = $value['password'];







//                     $name     = $value['name'];







//                 }







//                 // print_r($name);exit();







//             }















//             $ri_email = 'easydelivery29@gmail.com';







//             $to       = $email;







//             $subject  = 'Reset Your Password';















//             $headers = "From: <" . $ri_email . ">" . "\r\n";







//             $headers .= "MIME-Version: 1.0\r\n";







//             $headers .= "Content-Type: text/html; charset=UTF-8\r\n";







//             $message = '';







//             $message .= '







//             <html><!doctype html>







//               <head>







//                 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">







//                 <meta name="viewport" content="width=device-width">







//                 <title>Simple Transactional Email</title>







//                 <style>







//                 @media only screen and (max-width: 620px) {







//                   table[class=body] h1 {







//                     font-size: 28px !important;







//                     margin-bottom: 10px !important;







//                   }







//                   table[class=body] p,







//                   table[class=body] ul,







//                   table[class=body] ol,







//                   table[class=body] td,







//                   table[class=body] span,







//                   table[class=body] a







//                   {







//                     font-size: 16px !important;







//                   }







//                   table[class=body] .wrapper,







//                   table[class=body] .article







//                   {







//                     padding: 10px !important;







//                   }















//                   table[class=body] .content {















//                   padding: 0 !important;















//                   }















//                   table[class=body] .container {















//                   padding: 0 !important;















//                   width: 100% !important;















//                   }















//                   table[class=body] .main {















//                     border-left-width: 0 !important;















//                     border-radius: 0 !important;















//                     border-right-width: 0 !important;















//                   }















//                   table[class=body] .btn table {















//                     width: 100% !important;















//                   }















//                   table[class=body] .btn a {















//                     width: 100% !important;















//                   }















//                   table[class=body] .img-responsive {















//                     height: auto !important;















//                     max-width: 100% !important;















//                     width: auto !important;















//                   }















//                 }















//                 @media all {















//                   .ExternalClass {















//                     width: 100%;















//                   }















//                   .ExternalClass,















//                   .ExternalClass p,















//                   .ExternalClass span,















//                   .ExternalClass font,















//                   .ExternalClass td,















//                   .ExternalClass div {















//                   line-height: 100%;















//                   }















//                   .apple-link a {















//                     color: inherit !important;















//                     font-family: inherit !important;















//                     font-size: inherit !important;















//                     font-weight: inherit !important;















//                     line-height: inherit !important;















//                     text-decoration: none !important;















//                   }















//                   #MessageViewBody a {















//                     color: inherit;















//                     text-decoration: none;















//                     font-size: inherit;















//                     font-family: inherit;















//                     font-weight: inherit;















//                     line-height: inherit;















//                   }















//                   .btn-primary table td:hover {















//                     background-color: #34495e !important;















//                   }















//                   .btn-primary a:hover {















//                     background-color: #34495e !important;















//                     border-color: #34495e !important;















//                   }















//                 }















//                 </style>















//               </head>















//               <body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">















//               <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">















//                   <tr>















//                     <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>















//                     <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">















//                       <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">















//                         <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;"></span>















//                         <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">















//                           <tr>







//                             <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">















//                                 <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">















//                                   <tr>







//                                     <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">







//                                     <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Hi '.ucfirst($name).'</p>















//                                     <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"></p>















//                                     <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Please sign in to with your New Password.</p>















//                                     <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">















//                                     <tbody>















//                                       <tr>















//                                         <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">















//                                             <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">















//                                               <tbody>















//                                                 <tr>















//                                                 <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">Your New password is: <b>' . $random_no . '.</b>







//                                                 </td>















//                                               </tr>















//                                             </tbody>















//                                           </table>















//                                         </td>















//                                       </tr>















//                                     </tbody>















//                                   </table>















//                                   <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Thanks and Regards.</p>







//                                   <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Easy Delivery Team.</p>







//                                 </td>















//                               </tr>















//                             </table>















//                           </td>















//                         </tr>















//                       </table>















//                       <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">















//                       <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">































//                     </table>















//                         </div>















//                       </div>















//                     </td>















//                     <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>















//                   </tr>















//                 </table>















//               </body>















//             </html>';







//             if (mail($to, $subject, $message, $headers)) {







//                 $data_result['result'] = 'true';







//                 $data_result['msg']    = 'Please check your mail for new password';







//                 // $data_result['email']  = $to;







//                  $data_result['password']    = $random_no;















//             } else {







//                 $data_result['result'] = 'false';







//                 $data_result['msg']    = "something went wrong!!.";







//             }







//         } else {







//             $data_result['result'] = 'false';







//             $data_result['msg']    = "Email not exist.";







//         }







//         }else{







//           $data_result['result']='false';







//           $data_result['msg']='parameter email required!!';







//         }





//         echo json_encode($data_result);







//     }







public function deliveryBoy_forgot_password(){

  $mobile=$this->input->post('mobile');

  if (!empty($mobile)) {



    $random_no = mt_rand(10000, 99999);



    //$message = "Please Login to delivery with current password :{$random_no}";



    //$country_code="+974";

    //$ph_num=$country_code."".$mobile;

    //$send_sms = $this->sendSMS($message,$ph_num);



    $post_data['otp']=$random_no;

    $post_data['password']=$random_no;

    $post_data['otp_verify']=0;

    $where=array('mobile'=>$mobile);

    $resupdt=$this->Api_Model->updates('delivery_boy',$post_data,$where);

    if ($resupdt) {

      $json['result']="true";

      $json['msg']="OTP sent on your mobile no";

      $json['otp']=$random_no;

    }else{

      $json['result']="false";

      $json['msg']="sorry something went wrong";

    }

  }else{

    $json['result']="false";

    $json['result']="parameter required mobile";

  }

  echo json_encode($json);

}







     /*change password*/







  function delivey_boy_change_password(){



    extract($_POST);

     if (isset($delivery_boy_id) && isset($current_pass) && isset($new_password)) {



        $old_psw = $this->Api_Model->select_single_row('delivery_boy','id',$delivery_boy_id);



            $old_pswd=$old_psw->password;

            $mobile=$old_psw->mobile;



        if ($old_pswd==$current_pass) {



          if ($new_password==$confirm_password) {


              // $random_no = mt_rand(10000, 99999);
              // $post_data['password'] = $this->hash_password($new_password);

              // $post_data['otp'] = $random_no;
                $post_data['password']=$new_password;



                 unset($post_data['new_password']);



                $result = $this->Api_Model->updateData('delivery_boy',$post_data,$delivery_boy_id);



            if ($result) {



              $json['result']="true";



               $json['msg'] = "change password successfully";

                // $json['otp'] =  $random_no;



            }else{



              $json['result']="false";



            $json['msg']="sorry something went wrong";



            }



          }else{



            $json['result']="false";



            $json['msg']="new password & comfirm password not matched";



          }







       }else{



        $json['result']="false";



        $json['msg']="current password not matched";



       }



     }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id,current_pass,new_password,confirm_password";



     }



     echo json_encode($json);



  }



   public function deliveryBoy_otp_match(){

    $delivery_boy_id=$this->input->post('delivery_boy_id');

    $new_password=$this->input->post('new_password');

    $otp=$this->input->post('otp');

    if (!empty($otp) && !empty($delivery_boy_id) && !empty($new_password)) {



      $user =  $this->Admin_model->select_single_row('delivery_boy','id',$delivery_boy_id);

      $userOTP=$user->otp;



      if ($otp==$userOTP) {

        $post_data['otp_verify']=1;

        $post_data['password'] = $new_password;

        $result = $this->User_Model->updateData('delivery_boy',$post_data,$delivery_boy_id);

        if ($result) {

          $json['result']="true";

          $json['msg']="otp verified";

        }else{

         $json['result']="false";

         $json['msg']="something went wrong";

        }



      }else{

        $json['result']="false";

        $json['msg']="otp not matched";

      }

    }else{

      $json['result']="false";

      $json['msg']="delivery_boy_id,otp,new_password required";

    }

    echo json_encode($json);

  }







//     /*change delivery boy status*/

//     public function change_delivery_boy_status(){



//         $delivery_boy_id=$this->input->post('delivery_boy_id');



//         $ordersID=$this->input->post('ordersID');



//         $order_status=$this->input->post('order_status');



//         if (



//           isset($delivery_boy_id) &&



//           isset($order_status) &&



//           isset($ordersID)



//         ) {







//             $resCheck=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID'")->row();



//             // print_r($resCheck->type);exit();







//           if ($resCheck->type='Maintance') {







//             $random_no = rand(100000,999999);







//             $dataas = array(



//             'delivery_boy_id'=> $delivery_boy_id,



//             'order_status' => $order_status,



//             'otp'=>$random_no



//             );



//             $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);







//             $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name



//             FROM orders



//             LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id



//             LEFT JOIN users ON users.id=orders.user_id



//             WHERE ordersID='$ordersID' ")->row();







//             $getUserId=$getUser->user_id;



//             $shop_name=$getUser->shop_name;



//             $Userfcm_id=$getUser->fcm_id;







//             $notification = array(



//               'title' =>"Maintance order accepted.",



//               'body'  =>"Maintance By" .$shop_name.' & OTP is:'.$random_no



//               );







//               $resss = send_notification($notification,$Userfcm_id);



//               // print_r($resss);exit();







//               $datanotification = array(



//                 'ordersID'    => $ordersID,



//                 'sender_id'    => $delivery_boy_id,



//                 'reciever_id'  => $getUserId,



//                 'message'      => "Maintance order accepted otp :".$random_no,



//                 'sender_type'  => 'Driver',



//                 'receiver_type'=> 'customer',



//                 'types'        => "accepted order",



//                 'date'         => date("Y-m-d H:i:s")



//               );







//             $this->User_Model->insertAllData('notifications',$datanotification);



//           }else{







//             $dataas = array(



//             'delivery_boy_id'=> $delivery_boy_id,



//             'order_status' => $order_status



//             );







//             $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);







//             $getVendor=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID' ")->row();



//             $getVendorId=$getVendor->vendor_id;







//             $datanotification = array(



//               'ordersID'    => $ordersID,



//               'sender_id'    => $delivery_boy_id,



//               'reciever_id'  => $getVendorId,



//               'message'      => "order accepted",



//               'sender_type'  => 'Driver',



//               'receiver_type'=> 'Vendor',



//               'types'        => "accepted order",



//               'date'         => date("Y-m-d H:i:s")



//             );







//             $this->User_Model->insertAllData('notifications',$datanotification);







//         }







//         //   if ($result) {



//         if ($order_status==2) {

//             $getVendor=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID' ")->row();



//         $getVendorId=$getVendor->vendor_id;

//              $selectDeliveryBoy=$this->db->query('select id,name from delivery_boy where id='.$delivery_boy_id.' ')->row();

//         $DeliveryBoy=$selectDeliveryBoy->name;

//         $datas11=array('ordersID'=>$ordersID,'sender_id'=>$delivery_boy_id,'reciever_id'=>$getVendorId,'message'=>'order accepted by '.ucwords($DeliveryBoy).' ','types'=>'driver' );

//         $res=$this->db->insert('notifications',$datas11);



//         $json['result']="true";



//         $json['msg']="delivery boy accept order";

//         $json['status']=2;

//       }else{

//           $selectDeliveryBoy=$this->db->query('select id,name from delivery_boy where id='.$delivery_boy_id.' ')->row();

//         $DeliveryBoy=$selectDeliveryBoy->name;

//         $datas11=array('ordersID'=>$ordersID,'sender_id'=>$delivery_boy_id,'reciever_id'=>$getVendorId,'message'=>'order cancel by '.ucwords($DeliveryBoy).' ','types'=>'driver' );

//         $res=$this->db->insert('notifications',$datas11);

//          $json['result']="true";



//         $json['msg']="delivery boy order cancel";

//         $json['status']=6;

//       }



//         // $json['result']="true";



//         // $json['msg']="delivery boy accept order";



//     //   }else{



//     //     $json['result']="false";



//     //     $json['msg']="something went wrong";



//     //   }







//     }else{



//       $json['result']="false";



//       $json['msg']="parameter required delivery_boy_id,ordersID,order_status(2=accept,6=cancel)";



//     }



//     echo json_encode($json);



//   }



/*NEW API*/

//   public function deliveryBoy_accepted_orders(){

//     $delivery_boy_id=$this->input->post('delivery_boy_id');

//     $ordersID=$this->input->post('ordersID');

//     $order_status=$this->input->post('order_status');

//     if (isset($delivery_boy_id) && isset($order_status) && isset($ordersID)

//     ){

//       if ($order_status==2) {

//         $resCheck=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID'")->row();

//         // print_r($resCheck->type);exit();

//         if ($resCheck->type=='Maintance') {

//           $random_no = rand(10000,99999);

//           $dataas = array(

//             'delivery_boy_id'=> $delivery_boy_id,

//             'order_status' => $order_status,

//             'otp'=>$random_no

//           );

//           $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);

//           /*not*/

//           $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

//           FROM orders

//           LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

//           LEFT JOIN users ON users.id=orders.user_id

//           WHERE ordersID='$ordersID' ")->row();

//           $getUserId=$getUser->user_id;

//           $shop_name=$getUser->shop_name;

//           $Userfcm_id=$getUser->fcm_id;



//           $notification = array(

//             'title' =>"Maintance order accepted.",

//             'body'  =>"Maintance By" .$shop_name.' & OTP is:'.$random_no

//           );

//           $resss = send_notification($notification,$Userfcm_id);

//           $datanotification = array(

//             'ordersID'    => $ordersID,

//             'sender_id'    => $delivery_boy_id,

//             'reciever_id'  => $getUserId,

//             'message'      => "Maintance order accepted otp :".$random_no,

//             'sender_type'  => 'Driver',

//             'receiver_type'=> 'customer',

//             'types'        => "accepted order",

//             'date'         => date("Y-m-d H:i:s")

//           );

//           $this->User_Model->insertAllData('notifications',$datanotification);



//           /*/noti*/



//           $json['result']="true";

//           $json['msg']="accept order";



//         }else{

//           $dataa = array(

//           'delivery_boy_id'=> $delivery_boy_id,

//           'order_status' => $order_status

//           );

//           $result= $this->Api_Model->updateOrder('orders',$dataa,$ordersID);



//           /*not*/

//           $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

//           FROM orders

//           LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

//           LEFT JOIN users ON users.id=orders.user_id

//           WHERE ordersID='$ordersID' ")->row();

//           $getUserId=$getUser->user_id;

//           $shop_name=$getUser->shop_name;

//           $Userfcm_id=$getUser->fcm_id;



//           $getDeliveryBoy=$this->db->query("SELECT * FROM delivery_boy WHERE id=$delivery_boy_id")->row();

//           $DeliveryBoyName=$getDeliveryBoy->name;



//           $notification = array(

//             'title' =>"order accepted.",

//             'body'  =>"Order By DeliveryBoyName"

//           );

//           $resss = send_notification($notification,$Userfcm_id);

//           $datanotification = array(

//             'ordersID'    => $ordersID,

//             'sender_id'    => $delivery_boy_id,

//             'reciever_id'  => $getUserId,

//             'message'      => "order accepted",

//             'sender_type'  => 'Driver',

//             'receiver_type'=> 'customer',

//             'types'        => "accepted order",

//             'date'         => date("Y-m-d H:i:s")

//           );

//           $this->User_Model->insertAllData('notifications',$datanotification);



//           /*/noti*/



//           $json['result']="true";

//           $json['msg']="accept order";

//         }

//       }else{

//         $dataas = array(

//           'delivery_boy_id'=> $delivery_boy_id,

//           'order_status' => $order_status

//         );

//         $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);



//         $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

//           FROM orders

//           LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

//           LEFT JOIN users ON users.id=orders.user_id

//           WHERE ordersID='$ordersID' ")->row();

//           $getUserId=$getUser->user_id;

//           $shop_name=$getUser->shop_name;

//           $Userfcm_id=$getUser->fcm_id;



//           $getDeliveryBoy=$this->db->query("SELECT * FROM delivery_boy WHERE id=$delivery_boy_id")->row();

//           $DeliveryBoyName=$getDeliveryBoy->name;



//           $notification = array(

//             'title' =>"order cancel.",

//             'body'  =>"Order cancel By DeliveryBoyName"

//           );

//           $resss = send_notification($notification,$Userfcm_id);

//           $datanotification = array(

//             'ordersID'    => $ordersID,

//             'sender_id'    => $delivery_boy_id,

//             'reciever_id'  => $getUserId,

//             'message'      => "order cancel",

//             'sender_type'  => 'Driver',

//             'receiver_type'=> 'customer',

//             'types'        => "accepted order",

//             'date'         => date("Y-m-d H:i:s")

//           );

//           $this->User_Model->insertAllData('notifications',$datanotification);



//         $json['result']="true";

//         $json['msg']="cancel order";





//       }





//     }else{

//       $json['result']="false";

//       $json['msg']="parameters required delivery_boy_id,order_status,ordersID";

//     }



//     echo json_encode($json);

//   }







  /*get accepted order by driver*/
  public function order_accept_reject(){



    $delivery_boy_id=$this->input->post('delivery_boy_id');


    if (isset($delivery_boy_id)) {



      $result=$this->Api_Model->deliveryAcceptedBoyOrder($delivery_boy_id);



      foreach($result as $value){



        $wheredata=array('ordersID'=>$value->ordersID);



     $sub=  $this->Api_Model->selectData('orders',$wheredata);



     if(!empty($sub)){

         $amo=[];

         $add_on=[];

         $quanitity=[];

       foreach($sub as $sub1){



      $amo1 =$sub1->price*$sub1->quanitity;

     $amo[]=$amo1;



     $add_on[]=$sub1->addon_price;

 $quanitity[]=$sub1->quanitity;



       }

       $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();

        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;



       $arrya_sum=array_sum($amo);

       $arrya_add_on=array_sum($add_on);

       $qua=array_sum($quanitity);

       $ad_on_plus=$arrya_sum+$arrya_add_on;

       $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;

     }else{



         $final_amo=0;

     }



     $array[]=array(

           'order_id'=>$value->order_id,

           'ordersID'=>$value->ordersID,

           'order_status'=>$value->order_status,

           'orderid'=>$value->orderid,

           'shop_name'=>$value->shop_name,

           'user_id'=>$value->user_id,

           'vendor_id'=>$value->vendor_id,



           'addon'=>$value->addon    ,

           'addon_price'=>$value->addon_price    ,

           'quanitity'=>$qua,

           'total'=>$final_amo,

           'sub_total'=>$final_amo,

           'payment_method'=>$value->payment_method,

           'transaction_id'=>$value->transaction_id,

           'order_date'=>$value->created_date,

           'vendor_name'=>$value->vendor_name,

           'vendor_contact'=>$value->vendor_contact,

           'vendor_address'=>$value->vendor_address,

           'shop_lat'=>$value->shop_lat,

           'shop_lang'=>$value->shop_lang,

           'name'=>$value->name,

           'mobile'=>$value->mobile,

           'address'=>$value->address,

           'image'=>$value->image,

           'trax_id'=>$value->trax_id,

           'instruction'=>$value->instruction,

           'food_prepare_time'=>$value->food_prepare_time,

           'extra_maintance_fee'=>$value->extra_maintance_fee,

           'extra_maintance_detail'=>$value->extra_maintance_detail,

           'delivery_charge'=>$value->delivery_charge,

           'type'=>$value->type,

           'otp'=>$value->otp,

           'otp_verify'=>$value->otp,

           'address_id'=>$value->address_id,

           'cancel_reason'=>$value->cancel_reason,

           'product_id'=>$value->product_id,

           're_order'=>$value->re_order,

           'modified_at'=>$value->modified_at,

           'delivery_boy_id'=>$value->delivery_boy_id,

           'wash_price'=>$value->wash_price,

           'dry_clean_price'=>$value->dry_clean_price,

           'iron_price'=>$value->iron_price,

           'exp_wash_price'=>$value->exp_wash_price,

           'exp_dry_clean_price'=>$value->exp_dry_clean_price,

           'exp_iron_price'=>$value->exp_iron_price,

           'maintenance_file'=>$value->maintenance_file,

           'delivery_fee'=>$value->delivery_charge,

           'lat'=>$value->lat,

           'lng'=>$value->lng,

            );







      }



      if ($result) {

        $json['result']="true";



        $json['msg']="orders list";



        $json['data']=$array;



      }else{



        $json['result']="false";



        $json['msg']="not data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id";



    }



    echo json_encode($json);



  }



  /*
  deliveryBoy change availbility
  */

  public function deliveryBoy_change_availbility(){
    $delivery_boy_id=$this->input->post('delivery_boy_id');
    $availability_status=$this->input->post('availability_status');
    if (isset($delivery_boy_id)) {
      $post_data['availability_status']=$availability_status;
      $update =  $this->Api_Model->updateData('delivery_boy',$post_data, $delivery_boy_id);
      if ($update) {
        $json['result']="true";
        $json['msg']="changed availbility status";
        $json['availability_status']=$availability_status;
      }else{
        $json['result']="false";
        $json['msg']="something went wrong";
      }
    }else{
      $json['result']="false";
      $json['msg']="parameter required delivery_boy_id,availability_status(0=not,1=available)";
    }
    echo json_encode($json);
  }



    /*
    @Logout
    */
    public function delivery_boy_logout(){
      $delivery_boy_id = $this->input->post('delivery_boy_id');
      date_default_timezone_set('Asia/Calcutta');
      $created_at = date('Y-m-d h:i:s');
      if (isset($delivery_boy_id)) {
        $data['fcm_id']      = '';
        $data['created_at'] = $created_at;
        $result = $this->Api_Model->updateData('delivery_boy',$data,$delivery_boy_id);
        if ($result) {
        	$data_result['result'] ='true';
        	$data_result['msg']    ='Logout from Application.';
        }else{
        	$data_result['result'] ='false';
        	$data_result['msg'] ='something went wrong!!';
        }
      }else{
        $data_result['result'] ='false';
        $data_result['msg']    ='parameter required delivery_boy_id';
      }
      echo json_encode($data_result);
    }







    /*Logout user api*/



    public function user_logout(){



      $user_id = $this->input->post('user_id');



      date_default_timezone_set('Asia/Calcutta');



      $modify_at = date('Y-m-d h:i:s');







      if (isset($user_id)) {



        $data['fcm_id']      = '';



        $data['modify_at'] = $modify_at;



        $result = $this->Api_Model->updateData('users',$data,$user_id);



        if ($result) {



          $data_result['result'] ='true';



          $data_result['msg']    ='Logout from Application.';



        }else{



          $data_result['result'] ='false';



          $data_result['msg'] ='something went wrong!!';



        }



      }else{



        $data_result['result'] ='false';



        $data_result['msg']    ='parameter required user_id';



      }



      echo json_encode($data_result);



    }







    /*



    //update Delivery boy location



    for tracking by user////



    */



    public function update_location(){



      $delivery_boy_id=$this->input->post('delivery_boy_id');



      $lat=$this->input->post('lat');



      $lng=$this->input->post('lng');







      if (isset($delivery_boy_id) && isset($lat) && isset($lng)) {







        $data =array(



         'id'=>$delivery_boy_id,



         'lat'=>$lat,



         'lng'=>$lng



        );







        $result=$this->Api_Model->updateData('delivery_boy',$data,$delivery_boy_id);



        if ($result) {



          $json['result']="true";



          $json['msg']="location updated";



        }else{



           $json['result']="false";



           $json['msg']="sorry not updated";



        }







      }else{



        $json['result']="false";



        $json['msg']="parameter required delivery_boy_id,lat,lng";



      }



      echo json_encode($json);



    }











    /*



    Get Notification Api



    */



    public function getLatLng(){



      $ordersID=$this->input->post('ordersID');



      if (isset($ordersID)) {



        $result=$this->Api_Model->getlatlng($ordersID);



        if ($result) {



           $json['result']="true";



           $json['msg']="order details";



           $json['data']=$result;



        }else{



          $json['result']="false";



          $json['msg']="sorry something went wrong";



        }



      }else{



        $json['result']="false";



        $json['msg']="parameter required ordersID";



      }



      echo json_encode($json);



    }











  // #otp verfy & complete order by delivery boy



//   public function order_verify_otp(){



//     $ordersID=$this->input->post('ordersID');



//     $otp =$this->input->post('otp');



//     if (isset($ordersID) && isset($otp)) {



//       $check_otp=$this->db->query("SELECT * FROM `orders` WHERE `ordersID`='$ordersID' AND `otp`='$otp' AND `otp_verify`= 1");







//       if ($check_otp->num_rows()>0) {



//         $data['result']="true";



//         $data['msg']="Otp already verify";



//       }else{



//         $wheredata = array(



//           'field'=>'ordersID',



//           'table'=>'orders',



//           'where'=>array('ordersID'=>$ordersID,'otp'=>$otp)



//         );



//         $datas = array('otp_verify'=>1,'order_status'=>4);



//         $wheredatas = array('ordersID'=>$ordersID);



//         $result=$this->Api_Model->getAllData($wheredata);



//         if($result) {



//           $results=$this->Api_Model->UpdateAllData('orders',$wheredatas,$datas);



//           $getUser=$this->db->query("SELECT orders.order_id,orders.delivery_boy_id,orders.user_id,orders.ordersID,users.fcm_id FROM orders INNER JOIN users ON users.id=orders.user_id WHERE ordersID='$ordersID'")->row();



//           $user_id=$getUser->user_id;



//           $uesrFcm=$getUser->fcm_id;



//           $delivery_boy_id=$getUser->delivery_boy_id;







//           $notification = array(



//           'title' =>"Order completed.",



//           'body'  =>"Your order is completed"



//           );



//           $resss = send_notification($notification,$uesrFcm);



//           $datanotification = array(



//             'ordersID'    => $ordersID,



//             'sender_id'    => $delivery_boy_id,



//             'reciever_id'  => $user_id,



//             'message'      => "order completed",



//             'sender_type'  =>'Driver',



//             'receiver_type'=>'customer',



//             'types'        => "order completed",



//             'date'         => date("Y-m-d H:i:s")



//           );



//           $this->User_Model->insertAllData('notifications',$datanotification);



//           $data['result']="true";



//           $data['data']=$this->Admin_model->select_single_row('orders','ordersID',$ordersID);



//           $data['msg'] ="Otp verify & order completed";



//         }else{



//           $data['result'] ="false";



//           $data['msg'] ="sorry otp not verify.";



//         }



//       }



//     }else{



//       $data['result']="false";



//       $data['msg'] ="parameter required ordersID,otp";



//     }



//     echo json_encode($data);



//   }



 public function order_verify_otp(){
    $ordersID=$this->input->post('ordersID');
     $otp =$this->input->post('otp');
    if (isset($ordersID) && isset($otp)) {



      $check_otp=$this->db->query("SELECT * FROM `orders` WHERE `ordersID`='$ordersID' AND `otp`='$otp' ");

      //echo "SELECT * FROM `orders` WHERE `ordersID`='$ordersID' AND `otp`='$otp'";exit();





     if ($check_otp->num_rows() == 0) {



    $data['result']="true";



     $data['msg']="Otp Invalid";



     }else{



        $wheredata = array(



          'field'=>'ordersID',



          'table'=>'orders',



          'where'=>array('ordersID'=>$ordersID)



        );



        $datas = array('order_status'=>4,'otp_verify'=>1);



        $wheredatas = array('ordersID'=>$ordersID);



        $result=$this->Api_Model->getAllData($wheredata);



        if($result) {



          //$results=$this->Api_Model->UpdateAllData('orders',$wheredatas,$datas);
          $results=$this->Api_Model->updateOrder('orders',$datas,$ordersID);



          $getUser=$this->db->query("SELECT orders.order_id,orders.delivery_boy_id,orders.user_id,orders.ordersID,users.fcm_id FROM orders INNER JOIN users ON users.id=orders.user_id WHERE ordersID='$ordersID'")->row();



          $user_id=$getUser->user_id;



          $uesrFcm=$getUser->fcm_id;



          $delivery_boy_id=$getUser->delivery_boy_id;







          $notification = array(



          'title' =>"Order completed.",



          'body'  =>"Your order is completed"



          );



          $resss = send_notification($notification,$uesrFcm);



          $datanotification = array(



            'ordersID'    => $ordersID,



            'sender_id'    => $delivery_boy_id,



            'reciever_id'  => $user_id,



            'message'      => "order completed",



            'sender_type'  =>'Driver',



            'receiver_type'=>'customer',



            'types'        => "order completed",



            'date'         => date("Y-m-d H:i:s")



          );



          $this->User_Model->insertAllData('notifications',$datanotification);



          $data['result']="true";



          $data['data']=$this->Admin_model->select_single_row('orders','ordersID',$ordersID);



          $data['msg'] ="Otp verify & order completed";



        }else{



          $data['result'] ="false";



          $data['msg'] ="sorry otp not Invalid.";



         }



      }



    }else{



      $data['result']="false";



      $data['msg'] ="parameter required ordersID,otp";



    }



    echo json_encode($data);



  }























  /*complete order list*/



  public function driver_completed_orders(){



    $delivery_boy_id=$this->input->post('delivery_boy_id');

    //$lang_id=$this->input->post('lang_id');

    $from_date=$this->input->post('from_date');

    $todate=$this->input->post('todate');



    if (isset($delivery_boy_id)) {







      $total_earing=$this->db->query("SELECT price,quanitity,addon_price FROM `orders` WHERE delivery_boy_id=$delivery_boy_id AND otp_verify=1")->result();



      $result=$this->Api_Model->GetDriverCompletedOrderList($from_date,$todate,$delivery_boy_id);







      $dataaddon=array();



      foreach($result as $value):



           $wheredata=array('ordersID'=>$value->ordersID);



     $sub=  $this->Api_Model->selectData('orders',$wheredata);



     if(!empty($sub)){

         $amo=[];

         $add_on=[];

         $quanitity=[];

       foreach($sub as $sub1){



      $amo1 =$sub1->price*$sub1->quanitity;

     $amo[]=$amo1;



     $add_on[]=$sub1->addon_price;

 $quanitity[]=$sub1->quanitity;



       }







       $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();

        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;



       $arrya_sum=array_sum($amo);

       $arrya_add_on=array_sum($add_on);

       $qua=array_sum($quanitity);

       $ad_on_plus=$arrya_sum+$arrya_add_on;

       $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;

     }else{



         $final_amo=0;

     }







         $earning_income=0;

         $earning_income=[];

         $delivery_pri= count($result)*$selectOrderDeliverycharges;

foreach($total_earing as $earing){



  $earning_income[]=  $earing->price *$earing->quanitity+$earing->addon_price;





}

$income=array_sum($earning_income)+$delivery_pri;





        if(!$value->addon==0):



          $addarray= explode(',',$value->addon);



          $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();



        endif;



        $resultnew[]=array(



        'id'=>$value->order_id,



        'orderid'=>$value->orderid,



        'ordersID'=>$value->ordersID,



        'user_id'=>$value->user_id,



        'product_id'=>$value->product_id,



        'vendor_id'=>$value->vendor_id,



        'name'=>$value->name,



        'image'=>$value->image,



        'addon_price'=>$value->addon_price,



        'price'=>$value->price,



        'quantity'=>$qua,



        'total_amount'=>$ad_on_plus,



        'final_amount'=>$final_amo,



        'order_status'=>$value->order_status,



        'created_date'=>date('d-m-Y', strtotime(str_replace('/', '-', $value->created_date))),







        );







      endforeach;



      if($result){



        $data['result']="true";



        $data['msg']="All completed orders";



        $data['last_month_income']=$income;

        $data['weekly_data']=3;

        $data['total_income']=$income;



        $data['data']=$resultnew;



      }else{



        $data['result']="false";



        $data['msg']="Not any Completed Order";



      }



    }else{



      $data['result']="false";



      $data['msg']="parameter required delivery_boy_id,from_date,todate";



    }



    echo json_encode($data);



  }











  public function search_order(){



    $delivery_boy_id=$this->input->post('delivery_boy_id');



    $from_date=$this->input->post('from_date');



    $todate=$this->input->post('todate');



    if (isset($from_date) && isset($todate)) {



      $selectDate=$this->db->query("SELECT * FROM orders")->row();



      $modified_at=$selectDate->modified_at;



      $result=$this->Api_Model->orders_by_date($from_date,$todate,$delivery_boy_id);





foreach($result as $value){

         $wheredata=array('ordersID'=>$value->ordersID);



     $sub=  $this->Api_Model->selectData('orders',$wheredata);



     if(!empty($sub)){

         $amo=[];

         $add_on=[];

         $quanitity=[];

       foreach($sub as $sub1){



      $amo1 =$sub1->price*$sub1->quanitity;

     $amo[]=$amo1;



     $add_on[]=$sub1->addon_price;

 $quanitity[]=$sub1->quanitity;



       }

       $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();

        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;



       $arrya_sum=array_sum($amo);

       $arrya_add_on=array_sum($add_on);

       $qua=array_sum($quanitity);

       $ad_on_plus=$arrya_sum+$arrya_add_on;

       $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;

     }else{



         $final_amo=0;

     }



    $array[]=array(



        'order_id'=>$value->order_id,

        'ordersID'=>$value->ordersID,

        'order_status'=>$value->order_status,

        'order_status'=>$value->order_status,

        'orderid'=>$value->orderid,

        'address_id'=>$value->address_id,

        'user_id'=>$value->user_id,
        'user_name'=>$value->user_name,
        'user_mobile'=>$value->user_mobile,
        'user_address'=>$value->user_address,
        'user_lat'=>$value->user_lat,
        'user_lang'=>$value->user_lang,

        'vendor_id'=>$value->vendor_id,
        'shop_name'=>$value->shop_name,
        'shop_image'=>$value->shop_image,
        'shop_address'=>$value->shop_address,

        'addon'=>$value->addon,

        'addon_price'=>$arrya_add_on,

        'image'=>$value->image,

        'trax_id'=>$value->trax_id,

        'created_date'=>$value->created_date,

        'product_id'=>$value->product_id,

        'name'=>$value->name,

        'price'=>$value->price,

        'quanitity'=>$qua,

        'payment_method'=>$value->payment_method,

        'transaction_id'=>$value->transaction_id,

        'instruction'=>$value->instruction,

        'sub_total'=>$final_amo,

        'total'=>$ad_on_plus,

        'delivery_fee'=>$value->delivery_fee,

        'cancel_reason'=>$value->cancel_reason,

        're_order'=>$value->re_order,

        'modified_at'=>$value->modified_at,

        'delivery_boy_id'=>$value->delivery_boy_id,

        'wash_price'=>$value->wash_price,

        'dry_clean_price'=>$value->dry_clean_price,

        'iron_price'=>$value->iron_price,

        'exp_wash_price'=>$value->exp_wash_price,

        'exp_dry_clean_price'=>$value->exp_dry_clean_price,

        'exp_iron_price'=>$value->exp_iron_price,

        'maintenance_file'=>$value->maintenance_file,

        'lat'=>$value->lat,

        'lng'=>$value->lng,

        'type'=>$value->type,

        'otp'=>$value->otp,

        'otp_verify'=>$value->otp_verify,

        'food_prepare_time'=>$value->food_prepare_time,

        'extra_maintance_fee'=>$value->extra_maintance_fee,

        'extra_maintance_detail'=>$value->extra_maintance_detail,

        'delivery_charge'=>$value->delivery_charge,





        );





}

      if ($result) {



        $data_result['result']="true";



        $data_result['msg']="All completed orders";





        $data_result['data']=$array;



      }else{



        $data_result['result']="false";



        $data_result['msg']="sorry not any completed order";



      }



    }else{



      $data_result['result']="false";



      $data_result['msg']="parameter required delivery_boy_id,from_date,todate";



    }



    echo json_encode($data_result);



  }







  /*all vechile list*/



  public function get_vehicles(){



    // if (isset($lang_id) && isset($delivery_boy_id)) {



    //   if ($lang_id==1) {



    //     $selectDate=$this->db->query("SELECT id,ar_name FROM Vehicles")->result();



    //   }else{



    //     $selectDate=$this->db->query("SELECT * FROM Vehicles")->result();



    //   }



    // }else{



    //   $data_result['result']="false";



    //   $data_result['msg']="parameter required delivery_boy_id,lang_id";



    // }



    $selectDate=$this->db->query("SELECT * FROM Vehicles")->result();



    if ($selectDate) {



      $data_result['result']="true";



      $data_result['msg']="All Vehicles list";



      $data_result['data']=$selectDate;



    }else{



      $data_result['result']="false";



      $data_result['msg']="sorry not any list";



    }



    echo json_encode($data_result);



  }







  /*pickup for laundry from user address to */



  public function pickup_laundry(){



    $ordersID=$this->input->post('ordersID');



    $delivery_boy_id=$this->input->post('delivery_boy_id');



    if (isset($ordersID) && isset($delivery_boy_id)) {



      $orderType=$this->db->query("SELECT type FROM orders WHERE ordersID='$ordersID'")->row();



      $type=$orderType->type;



      if ($type='Laundry') {



         $dataas =array(



          'delivery_boy_id'=>$delivery_boy_id,



          'order_status'=>5



        );







        $result=$this->Api_Model->updateOrder('orders',$dataas,$ordersID);



        if ($result) {



          $json['result']="true";



          $json['msg']="picked up laundry item";



        }else{



          $json['result']="false";



          $json['msg']="something went wrong";



        }



      }else{



        $json['result']="false";



        $json['msg']="its not Laundry type service";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required ordersID,delivery_boy_id";



    }



    echo json_encode($json);



  }











  //change language



  public function change_language(){



    $user_id  = $this->input->post('user_id');



    $lang_id = $this->input->post('lang_id');



    if (isset($lang_id) && isset($user_id)) {







      $data = array('lang_id' => $lang_id );



      $result= $this->Api_Model->updateData('users',$data,$user_id);



      if ($result) {



        $data_result['result']='true';



        $data_result['msg']='You changed language';



        $data_result['data']=$this->Api_Model->select_single_row('users','id',$user_id);



      }else{



        $data_result['result']='false';



        $data_result['msg']='soory You not change your language';



      }



    }else{



      $data_result['result']='false';



      $data_result['msg']='parameter required... user_id,lang_id';



    }







    echo json_encode($data_result);



  }











  /*If exta charge of Maintance (total payment fee)*/



  public function extra_charge_of_maintance(){



    $delivery_boy_id=$this->input->post('delivery_boy_id');



    $ordersID=$this->input->post('ordersID');



    // $sub_total=$this->input->post('sub_total');



    $extra_maintance_detail=$this->input->post('extra_maintance_detail');



    $extra_maintance_fee=$this->input->post('extra_maintance_fee');







    if (isset($delivery_boy_id) && isset($ordersID) && isset($extra_maintance_detail) && isset($extra_maintance_fee)) {







      $postData= array(



        'delivery_boy_id' => $delivery_boy_id,



        'ordersID'=>$ordersID,



        'extra_maintance_detail'=>$extra_maintance_detail,



        'extra_maintance_fee'=>$extra_maintance_fee



        // 'sub_total' =>$sub_total



      );



      $wheredata=array('ordersID'=>$ordersID);







      $result =  $this->Api_Model->updates('orders',$postData,$wheredata);







      if ($result) {



        $json['result']="true";



        $json['msg']="Maintance final amount success";



        $json['data']=$extra_maintance_fee;



      }else{



        $json['result']="false";



        $json['msg']="sorry something went wrong";



      }







    }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id,ordersID,extra_maintance_detail,extra_maintance_fee";



    }



    echo json_encode($json);







  }







  /*Maintance otp verify*/



  public function maintance_order_verify_otp(){



    $ordersID=$this->input->post('ordersID');



    $otp =$this->input->post('otp');



    if (isset($ordersID) && isset($otp)) {



      $check_otp=$this->db->query("SELECT * FROM `orders` WHERE `ordersID`='$ordersID' AND `otp`='$otp' AND `otp_verify`= 1");







      if ($check_otp->num_rows()>0) {



        $data['result']="true";



        $data['msg']="Otp already verify";



      }else{



        $wheredata = array(



          'field'=>'ordersID',



          'table'=>'orders',



          'where'=>array('ordersID'=>$ordersID,'otp'=>$otp)



        );



        $datas = array('otp_verify'=>1,'order_status'=>3);



        $wheredatas = array('ordersID'=>$ordersID);



        $result=$this->Api_Model->getAllData($wheredata);







        if($result) {







          $results=$this->Api_Model->UpdateAllData('orders',$wheredatas,$datas);



          $getUser=$this->db->query("SELECT orders.order_id,orders.delivery_boy_id,orders.user_id,orders.ordersID,users.fcm_id FROM orders INNER JOIN users ON users.id=orders.user_id WHERE ordersID='$ordersID'")->row();



          $user_id=$getUser->user_id;



          $uesrFcm=$getUser->fcm_id;



          $delivery_boy_id=$getUser->delivery_boy_id;







          $notification = array(



            'title' =>"Maintance Order verify.",



            'body'  =>"Your maintance order is verify"



          );



          $resss = send_notification($notification,$uesrFcm);



          $datanotification = array(



            'ordersID'    => $ordersID,



            'sender_id'    => $delivery_boy_id,



            'reciever_id'  => $user_id,



            'message'      => "maintance order verify",



            'sender_type'  =>'Driver',



            'receiver_type'=>'customer',



            'types'        => "order completed",



            'date'         => date("Y-m-d H:i:s")



          );







          $this->User_Model->insertAllData('notifications',$datanotification);







          $data['result']="true";



          $data['data']=$this->Admin_model->select_single_row('orders','ordersID',$ordersID);



          $data['msg'] ="Otp verify for maintance";



        }else{



          $data['result'] ="false";



          $data['msg'] ="sorry otp not verify.";



        }



      }



    }else{



      $data['result']="false";



      $data['msg'] ="parameter required ordersID,otp";



    }



    echo json_encode($data);



  }







  /*Maintance order completed*/



  public function maintance_order_completed(){



    $ordersID=$this->input->post('ordersID');



    $delivery_boy_id=$this->input->post('delivery_boy_id');



    if (isset($ordersID)) {







        $wheredata = array(



          'field'=>'ordersID',



          'table'=>'orders',



          'where'=>array('ordersID'=>$ordersID)



        );



        $datas = array('order_status'=>4);



        $wheredatas = array('ordersID'=>$ordersID);



        $result=$this->Api_Model->getAllData($wheredata);



        if($result) {







          $results=$this->Api_Model->UpdateAllData('orders',$wheredatas,$datas);



          $getUser=$this->db->query("SELECT orders.order_id,orders.delivery_boy_id,orders.vendor_id,orders.user_id,orders.ordersID,users.fcm_id FROM orders INNER JOIN users ON users.id=orders.user_id WHERE ordersID='$ordersID'")->row();



          $vendor_id=$getUser->vendor_id;



          // $uesrFcm=$getUser->fcm_id;



          // $delivery_boy_id=$getUser->delivery_boy_id;







          // $notification = array(



          //   'title' =>"Maintance Order completed.",



          //   'body'  =>"Your maintance order is completed"



          // );



          // $resss = send_notification($notification,$uesrFcm);



          $datanotification = array(



            'ordersID'    => $ordersID,



            'sender_id'    => $delivery_boy_id,



            'reciever_id'  => $vendor_id,



            'message'      => "maintance order completed",



            'sender_type'  =>'Driver',



            'receiver_type'=>'vendor',



            'types'        => "order completed",



            'date'         => date("Y-m-d H:i:s")



          );







          $this->User_Model->insertAllData('notifications',$datanotification);







          $data['result']="true";



          // $data['data']=$this->Admin_model->select_single_row('orders','ordersID',$ordersID);



          $data['msg'] ="maintance order completed";



        }else{



          $data['result'] ="false";



          $data['msg'] ="sorry something went wrong.";



        }







    }else{



      $data['result']="false";



      $data['msg'] ="parameter required ordersID,delivery_boy_id";



    }



    echo json_encode($data);



  }







  /*



  get extra free of



  maintance Invoice Cash/QPay



  */



  public function get_extra_free_of_maintance(){



    $ordersID=$this->input->post('ordersID');



    if ($ordersID) {



      $res=$this->Api_Model->getXtraFeeMain($ordersID);



      $extra=$res->extra_maintance_detail;



      $check_payment=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID' AND (payment_method='Cash' OR payment_method='QPay') ")->row();



        // $check_payments=$check_payment->delivery_charge;

        // print_r($check_payments);exit();



      if ($check_payment->payment_method=='Cash') {



        $amount=$check_payment->sub_total+$check_payment->extra_maintance_fee;



      }else{



        $amount=$check_payment->extra_maintance_fee;



      }







      if ($res) {



        $json['result']="true";



        $json['msg']="show exta maintance order details";

        $json['order_id']=$check_payment->order_id;

        $json['ordersID']=$ordersID;



        $json['payment_method']=($check_payment->payment_method=='Cash') ? ('Cash') : ('QPay');



        $json['pay_amount']=($check_payment->payment_method=='Cash') ? ($amount) : ($check_payment->extra_maintance_fee);



        $json['cash_previous_amount']=($check_payment->payment_method=='Cash') ? ($check_payment->sub_total) : ($check_payment->sub_total);



        $json['sub_total']=($check_payment->payment_method=='Cash') ? ($check_payment->sub_total+$check_payment->extra_maintance_fee) : ($check_payment->sub_total+$check_payment->extra_maintance_fee);



        $json['exta_amount']=$res->extra_maintance_fee;



        $json['data']=json_decode(stripslashes($extra), true);



      }else{



        $json['result']="false";



        $json['msg']="sorry not any data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required ordersID";



    }



    echo json_encode($json);



  }











  /*



  Maintanance payment



  if extra charges/payement cash or online



  */



  public function maintanance_payment(){



    $ordersID=$this->input->post('ordersID');



    $payment_method=$this->input->post('payment_method');



    $transaction_id=$this->input->post('transaction_id');



    $amount=$this->input->post('amount');



    if (isset($ordersID) && isset($payment_method) && isset($transaction_id)) {



      $wheredatas=array(



        'ordersID'=>$ordersID



      );



      $datas=array(



        'payment_method'=>$payment_method,



        'transaction_id'=>$transaction_id,



        'sub_total'=>$amount



      );



      $results=$this->Api_Model->UpdateAllData('orders',$wheredatas,$datas);



      if ($results) {



        $json['result']="true";



        $json['msg']="payment success";



      }else{



        $json['result']="false";



        $json['msg']="sorry not any data";



      }



    }else{



      $json['result']="false";



      $json['result']="parameter required ordersID,payment_method,transaction_id,amount";



    }



    echo json_encode($json);



  }











  // function sendSMS($customerID,$userName,$userPassword,$originator,$smsText,$recipientPhone,$messageType,$defDate,$blink,$flash,$Private)



  // {



  //   global $arraySendMsg;



  //   $url = "https://messaging.ooredoo.qa/bms/soap/Messenger.asmx/HTTP_SendSms";







  //   $domainName = $_SERVER['SERVER_NAME'];



  //   $stringToPost = "customerID=".$customerID."&userName=".$userName."&userPassword=".$userPassword."&originator=".$originator."&smsText=".$smsText."&recipientPhone=".$recipientPhone."&messageType=".$messageType."&defDate=".$defDate."&blink=".$blink."&flash=".$flash."&Private=".$Private;







  //   $ch = curl_init();



  //   curl_setopt($ch, CURLOPT_URL, $url);



  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



  //   curl_setopt($ch, CURLOPT_HEADER, 0);



  //   curl_setopt($ch, CURLOPT_TIMEOUT, 5);



  //   curl_setopt($ch, CURLOPT_POST, 1);



  //   curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);



  //   $result = curl_exec($ch);







  //   // if($viewResult)



  //   //   $result = printStringResult(trim($result) , $arraySendMsg);



  //   print_r($result);



  // }





  function smsMessage(){

    $userName=$this->input->post('userName');

    $messageType=$this->input->post('messageType');

    $recipientPhone=$this->input->post('recipientPhone');

    $res=$this->sendSMS($userName,$messageType,$recipientPhone);

    print_r($res);exit();

    if ($res) {

      $json['result']="true";

      $json['msg']="send";

    }else{

      $json['result']="false";

      $json['msg']="message not send";

    }

    echo json_encode($json);



  }





  function sendSMS($message,$recipientPhone)



  {



    global $arraySendMsg;



    $url = "https://messaging.ooredoo.qa/bms/soap/Messenger.asmx/HTTP_SendSms";



  $customerID=2369;

   $userName='EasyDel';

  $userPassword='Yj3@s9rLvK4eM';

  $originator='Easy Del';

  $smsText=$message;

  // $recipientPhone

   $messageType=0;

  $defDate='';

  $blink='false';

  $flash='false';

  $Private='false';



    $domainName = $_SERVER['SERVER_NAME'];



    $stringToPost = "customerID=".$customerID."&userName=".$userName."&userPassword=".$userPassword."&originator=".$originator."&smsText=".$smsText."&recipientPhone=".$recipientPhone."&messageType=".$messageType."&defDate=".$defDate."&blink=".$blink."&flash=".$flash."&Private=".$Private;







    $ch = curl_init();



    curl_setopt($ch, CURLOPT_URL, $url);



    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



    curl_setopt($ch, CURLOPT_HEADER, 0);



    curl_setopt($ch, CURLOPT_TIMEOUT, 5);



    curl_setopt($ch, CURLOPT_POST, 1);



    curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);



    $result = curl_exec($ch);







    // if($viewResult)



    //   $result = printStringResult(trim($result) , $arraySendMsg);



 //print_r($result);



  }





  /*Rating to driver*/

  public function deliveryBoy_rating(){



    extract($_POST);



    if (isset($user_id) && isset($delivery_boy_id) && isset($rating) ) {



      $result=$this->Api_Model->insertAllData('delivery_boy_review',$_POST);



      if ($result) {



        $data['result']="true";



        $data['msg']="Submitted your rating";



      }else{



        $data['result']="false";



        $data['msg']="Not submit your rating";



      }



    }else{



      $data['result']="false";



      $data['msg']="parameter required user_id,delivery_boy_id.rating";



    }



    echo json_encode($data);



  }



  /*Avg Rating of driver*/

  public function delivery_rating_list(){



    $delivery_boy_id=$this->input->post('delivery_boy_id');



    if (isset($delivery_boy_id)) {

      $result=$this->Api_Model->DeliveryBoyRatingDetails($delivery_boy_id);



      $total_rating=$this->Api_Model->Del_total_rating($delivery_boy_id);



      if ($result) {

        $json['result']="true";

        $json['msg']="rating reviews";

        $json['total_rating']=$total_rating;

        $json['data']=$result;

      }else{

        $json['result']="false";

        $json['msg']="Not any rating";

      }

    }else{

      $json['result']="false";

      $json['msg']="parameter required delivery_boy_id";

    }



    echo json_encode($json);



  }







public function resend_otp(){



$phone = $this->input->post('phone');

if(isset($phone)){



$wheredata=array(

'mobile'=>$phone,

);



  $check= $this->Admin_model->select_single_row('users','mobile',$phone);







  if($check){



    $otp=mt_rand(10000, 99999);

$data=array("otp"=>$otp);



$update=$this->User_Model->update($wheredata,'users',$data);



$message = "Your EassyDelivery Phone varification OTP :{$otp}";



$phone_number=$phone;

$country_code="+974";

$ph_num=$country_code."".$phone_number;

$send_sms = $this->sendSMS($message,$ph_num);

 $data= $this->Admin_model->select_single_row('users','id', $check->id);







                    $array=array(

                      "id"=>$data->id,

                      "user_id"=>$data->user_id,

                      "name"=>$data->name,

                      "lname"=>$data->lname,



                      "email"=>$data->email,

                      "mobile"=>$data->mobile,



                     );

$data_result['result'] ='true';

$data_result['msg']    ='resend otp Successfully';

$data_result['otp']    =$otp;

$data_result['user_id']=$check->id;

$data_result['data']=$array;



  }else{



$data_result['result'] ='false';

$data_result['msg']    ='Mobile Number Not Found';





  }

}else{

$data_result['result'] ='false';

$data_result['msg']    ='parameter required phone';

}

echo json_encode($data_result);

}



function unique_array($my_array, $key) {

    $result = array();

    $i = 0;

    $key_array = array();



    foreach($my_array as $val) {

        if (!in_array($val[$key], $key_array)) {

            $key_array[$i] = $val[$key];

            $result[$i] = $val;

        }

        $i++;

    }

    return $result;

}





/*SEND OTP TO MOBILE*/

public function forget_password(){

  $mobile=$this->input->post('mobile');

  if (!empty($mobile)) {



    $random_no = mt_rand(10000, 99999);



    $message = "Please Login to Easy delivery with current password :{$random_no}";



    $country_code="+974";

    $ph_num=$country_code."".$mobile;

    $send_sms = $this->sendSMS($message,$ph_num);

    $post_data['password'] = $this->hash_password($random_no);

    $post_data['otp']=$random_no;

    $post_data['verify_otp']=1;

    $where=array('mobile'=>$mobile);

    $resupdt=$this->Api_Model->updates('users',$post_data,$where);

    if ($resupdt) {

      $json['result']="true";

      $json['msg']="This is your current password";

      $json['otp']=$random_no;

    }else{

      $json['result']="false";

      $json['msg']="sorry something went wrong";

    }

  }else{

    $json['result']="false";

    $json['result']="parameter required mobile";

  }

  echo json_encode($json);

}





	/*

  @change password

  */

public function change_user_password()

  {

    extract($_POST);

    if(isset($user_id) && isset($old_password) && isset($new_password) && isset($confirm_password))

    {

      $old_encript =  $this->Admin_model->select_single_row('users','id',$user_id);

      $old_encripted=$old_encript->password;

      $mobile=$old_encript->mobile;

      // print_r($mobile);exit();



      if(password_verify($old_password, $old_encripted))



      {

        if ($new_password==$confirm_password) {





          //$random_no = mt_rand(1000, 9999);



          //$message = "Your Easy delivery OTP :{$random_no}";



          //$country_code="+974";

          //$ph_num=$country_code."".$mobile;

          //$send_sms = $this->sendSMS($message,$ph_num);



         $post_data['password'] = $this->hash_password($new_password);

          //$post_data['otp'] = $random_no;
          //$post_data['verify_otp'] =0;



          $result = $this->User_Model->updateData('users', $post_data,$user_id);



          if($result)

          {

            $json['result'] = "true";

            //$json['msg'] = "Otp send on your mobile";
            $json['msg'] = "successfully changed your password";

            //$json['otp'] =  $random_no;

            // $json['data'] =  $this->Admin_model->select_single_row('users','id',$user_id);

          }else{

            $json['result'] = "false";

            $json['msg'] = "Something went wrong. Please try later.";

          }

        }else{

          $json['result'] = "false";

          $json['msg'] = "New password and confirm_password not matched.";

        }

      }else{

        $json['result'] = "false";

        $json['msg']    = "Invalid current Password";

      }

    }else

    {

      $json['result'] = "false";

      $json['msg'] = "Please give parameters(user_id,old_password,new_password,confirm_password)";

    }

    echo json_encode($json);

}



  /*otp match*/

  public function otp_match(){

    $user_id=$this->input->post('user_id');

    $new_password=$this->input->post('new_password');

    $otp=$this->input->post('otp');

    if (!empty($otp) && !empty($user_id) && !empty($new_password)) {



      $user =  $this->Admin_model->select_single_row('users','id',$user_id);

      $userOTP=$user->otp;



      if ($otp==$userOTP) {

        $post_data['verify_otp']=1;

        $post_data['password'] = $this->hash_password($new_password);

        $result = $this->User_Model->updateData('users',$post_data,$user_id);

        if ($result) {

          $json['result']="true";

          $json['msg']="otp verified";

        }else{

         $json['result']="false";

         $json['msg']="something went wrong";

        }



      }else{

        $json['result']="false";

        $json['msg']="otp not matched";

      }

    }else{

      $json['result']="false";

      $json['msg']="user_id,new_password,otp required";

    }

    echo json_encode($json);

  }



//   11/3/20

  /*change delivery boy status*/



//   public function change_delivery_boy_status(){



//     $delivery_boy_id=$this->input->post('delivery_boy_id');



//     $ordersID=$this->input->post('ordersID');



//     $order_status=$this->input->post('order_status');



//     if (



//       isset($delivery_boy_id) &&



//       isset($order_status) &&



//       isset($ordersID)



//     ) {







//       $resCheck=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID'")->row();



//       // print_r($resCheck->type);exit();







//       if ($resCheck->type='Maintance') {







//         $random_no = rand(100000,999999);







//         $dataas = array(



//         'delivery_boy_id'=> $delivery_boy_id,



//         'order_status' => $order_status,



//         'otp'=>$random_no



//         );



//         $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);







//         $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name



//         FROM orders



//         LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id



//         LEFT JOIN users ON users.id=orders.user_id



//         WHERE ordersID='$ordersID' ")->row();







//         $getUserId=$getUser->user_id;



//         $shop_name=$getUser->shop_name;



//         $Userfcm_id=$getUser->fcm_id;







//         $notification = array(



//           'title' =>"Maintance order accepted.",



//           'body'  =>"Maintance By".$shop_name.' & OTP is:'.$random_no



//           );







//           $resss = send_notification($notification,$Userfcm_id);



//           // print_r($resss);exit();







//           $datanotification = array(



//             'ordersID'    => $ordersID,



//             'sender_id'    => $delivery_boy_id,



//             'reciever_id'  => $getUserId,



//             'message'      => "Maintance order accepted otp :".$random_no,



//             'sender_type'  => 'Driver',



//             'receiver_type'=> 'customer',



//             'types'        => "accepted order",



//             'date'         => date("Y-m-d H:i:s")



//           );







//         $this->User_Model->insertAllData('notifications',$datanotification);



//       }else{







//         $dataas = array(



//         'delivery_boy_id'=> $delivery_boy_id,



//         'order_status' => $order_status



//         );







//         $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);







//         $getVendor=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID' ")->row();



//         $getVendorId=$getVendor->vendor_id;







//         $datanotification = array(



//           'ordersID'    => $ordersID,



//           'sender_id'    => $delivery_boy_id,



//           'reciever_id'  => $getVendorId,



//           'message'      => "order accepted",



//           'sender_type'  => 'Driver',



//           'receiver_type'=> 'Vendor',



//           'types'        => "accepted order",



//           'date'         => date("Y-m-d H:i:s")



//         );







//         $this->User_Model->insertAllData('notifications',$datanotification);







//       }







//       if ($result) {







//         $json['result']="true";



//         $json['msg']="delivery boy accept order";



//       }else{



//         $json['result']="false";



//         $json['msg']="something went wrong";



//       }







//     }else{



//       $json['result']="false";



//       $json['msg']="parameter required delivery_boy_id,ordersID,order_status(2=accept,6=cancel)";



//     }



//     echo json_encode($json);



//   }



  public function change_delivery_boy_status(){

    $delivery_boy_id=$this->input->post('delivery_boy_id');

    $ordersID=$this->input->post('ordersID');

    $order_status=$this->input->post('order_status');

    if (isset($delivery_boy_id) && isset($order_status) && isset($ordersID)

    ){

      if ($order_status==2) {

        $resCheck=$this->db->query("SELECT * FROM orders WHERE ordersID='$ordersID'")->row();



        if ($resCheck->type=='Maintance') {

          $random_no = rand(10000,99999);

          $dataas = array(

            'delivery_boy_id'=> $delivery_boy_id,

            'order_status' => $order_status,

            'otp'=>$random_no

          );

          $result= $this->Api_Model->updateOrder('orders',$dataas,$ordersID);

          /*not*/

          $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

          FROM orders

          LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

          LEFT JOIN users ON users.id=orders.user_id

          WHERE ordersID='$ordersID' ")->row();

          $getUserId=$getUser->user_id;

          $shop_name=$getUser->shop_name;

          $Userfcm_id=$getUser->fcm_id;



          $notification = array(

            'title' =>"Maintance order accepted.",

            'body'  =>"Maintance By" .$shop_name.' & OTP is:'.$random_no

          );

          $resss = send_notification($notification,$Userfcm_id);

          $datanotification = array(

            'ordersID'    => $ordersID,

            'sender_id'    => $delivery_boy_id,

            'reciever_id'  => $getUserId,

            'message'      => "Maintance order accepted otp :".$random_no,

            'sender_type'  => 'Driver',

            'receiver_type'=> 'customer',

            'types'        => "accepted order",

            'date'         => date("Y-m-d H:i:s")

          );

          $this->User_Model->insertAllData('notifications',$datanotification);



          /*/noti*/



          $json['result']="true";

          $json['msg']="accept order";



        }else{

          $dataa = array(

              'delivery_boy_id'=> $delivery_boy_id,

              'order_status' => $order_status



          );

          $result= $this->Api_Model->updateOrder('orders',$dataa,$ordersID);



          /*not*/

          $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

          FROM orders

          LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

          LEFT JOIN users ON users.id=orders.user_id

          WHERE ordersID='$ordersID' ")->row();

          $getUserId=$getUser->user_id;

          $shop_name=$getUser->shop_name;

          $Userfcm_id=$getUser->fcm_id;



          $getDeliveryBoy=$this->db->query("SELECT * FROM delivery_boy WHERE id=$delivery_boy_id")->row();

          $DeliveryBoyName=$getDeliveryBoy->name;



          $notification = array(

            'title' =>"order accepted.",

            'body'  =>"Order By DeliveryBoyName"

          );

          $resss = send_notification($notification,$Userfcm_id);

          $datanotification = array(

            'ordersID'    => $ordersID,

            'sender_id'    => $delivery_boy_id,

            'reciever_id'  => $getUserId,

            'message'      => "order accepted",

            'sender_type'  => 'Driver',

            'receiver_type'=> 'customer',

            'types'        => "accepted order",

            'date'         => date("Y-m-d H:i:s")

          );

          $this->User_Model->insertAllData('notifications',$datanotification);



          /*/noti*/



          $json['result']="true";

          $json['msg']="accept order";

        }

      }else{

        $wheredata=array(
          'ordersID'=>$ordersID,
        );

        $this->db->select('*');

   $this->db->from('orders');

   $this->db->Where('ordersID',$ordersID);



    $query = $this->db->get();



    $resultdata=$query->row()->delivery_boycancel;



    if (!empty($resultdata)) {



          $deliveryId=$resultdata.','.$delivery_boy_id;





        $dataas=array('delivery_boycancel'=>$deliveryId);



        $result=$this->Api_Model->updates('orders',$dataas,$wheredata);

  }

  else{



    $dataas=array('delivery_boycancel'=>$delivery_boy_id);



        $result=$this->Api_Model->updates('orders',$dataas,$wheredata);



   }

        $getUser=$this->db->query("SELECT orders.*,users.id,users.fcm_id,shop_details.shop_name

          FROM orders

          LEFT JOIN shop_details ON shop_details.vendor_id=orders.vendor_id

          LEFT JOIN users ON users.id=orders.user_id

          WHERE ordersID='$ordersID' ")->row();

          $getUserId=$getUser->user_id;

          $shop_name=$getUser->shop_name;

          $Userfcm_id=$getUser->fcm_id;



          $getDeliveryBoy=$this->db->query("SELECT * FROM delivery_boy WHERE id=$delivery_boy_id")->row();

          $DeliveryBoyName=$getDeliveryBoy->name;



          $notification = array(

            'title' =>"order cancel.",

            'body'  =>"Order cancel By DeliveryBoyName"

          );

          $resss = send_notification($notification,$Userfcm_id);

          $datanotification = array(

            'ordersID'    => $ordersID,

            'sender_id'    => $delivery_boy_id,

            'reciever_id'  => $getUserId,

            'message'      => "order cancel",

            'sender_type'  => 'Driver',

            'receiver_type'=> 'customer',

            'types'        => "accepted order",

            'date'         => date("Y-m-d H:i:s")

          );

          $this->User_Model->insertAllData('notifications',$datanotification);



        $json['result']="true";

        $json['msg']="cancel order";





      }





    }else{

      $json['result']="false";

      $json['msg']="parameters required delivery_boy_id,order_status,ordersID";

    }



    echo json_encode($json);

  }









  /*update app version*/

  public function update_app_version(){

      $versionnumber =$this->input->post('versionnumber');

      $type =$this->input->post('type');

      if(isset($versionnumber) && isset($type)){



          $datas=array(

              "versionnumber"=>$versionnumber,

              "type"=>$type

              );



              $wheredata=array(

                  'type'=>$type

                  );

         $res=$this->User_Model->updates('app_versions',$datas,$wheredata);

         if($res){

             $json['result']="true";

             $json['msg']="versions updated";



         }else{

             $json['result']="false";

             $json['msg']="sorry something went wrong";

         }

      }else{

          $json['result']="false";

          $json['msg']="parameter required versionnumber,type(user=1,driver=2)";

      }

      echo json_encode($json);

  }



  /*get app versions*/

  public function get_app_version(){

      $type =$this->input->post('type');

      if(isset($type)){

         $res=$this->db->query("select versionnumber from app_versions where type=$type")->row()->versionnumber;

         if($res){

             $json['result']="true";

             $json['msg']="Get Updated versions";

             $json['version']=$res;

         }else{

             $json['result']="false";

             $json['msg']="sorry something went wrong";

         }

      }else{

          $json['result']="false";

          $json['msg']="parameter required type(user=1,driver=2)";

      }

      echo json_encode($json);

  }



	// forgot password by email
	public function forgetPassword()
	{

      	$email = $this->input->post('email');
        if (isset($email)) {
         	$wheredata = array(
            	'email' => $email
        	);

        	$result    = $this->Api_Model->selectAllById('users', $wheredata);

        	if ($result) {

	            $wherenewpass = array(
	                'email' => $email
	            );
	            $random_no = rand(1000,9999);
	            $otp['otp'] = $random_no;
	            $res =$this->Api_Model->updatePass($wherenewpass,'users', $otp);

	            $res1 = $this->Api_Model->selectAllById('users', $wherenewpass);

	            if ($res1) {

	                foreach ($res1 as $key => $value) {

	                    $id    = $value['id'];

	                    $myotp    = $value['otp'];

	                    $name     = $value['name'];





	                }

	            }



            $ri_email = 'tastyhasty27feb@gmail.com';

            $to       = $email;

            $subject  = 'send OTP';



            $headers = "From: <" . $ri_email . ">" . "\r\n";

            $headers .= "MIME-Version: 1.0\r\n";

            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $message = '';

            $message .= '

            	<html><!doctype html>







                        <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">



                          <meta name="viewport" content="width=device-width">







                          <title>Simple Transactional Email</title>



                          <style>



                          @media only screen and (max-width: 620px) {



                            table[class=body] h1 {



                              font-size: 28px !important;



                              margin-bottom: 10px !important;



                            }



                            table[class=body] p,



                              table[class=body] ul,



                              table[class=body] ol,



                              table[class=body] td,



                              table[class=body] span,



                              table[class=body] a {



                              font-size: 16px !important;



                            }



                            table[class=body] .wrapper,



                              table[class=body] .article {



                              padding: 10px !important;



                            }



                            table[class=body] .content {



                              padding: 0 !important;



                            }



                            table[class=body] .container {



                              padding: 0 !important;



                              width: 100% !important;



                            }



                            table[class=body] .main {



                              border-left-width: 0 !important;



                              border-radius: 0 !important;



                              border-right-width: 0 !important;



                            }



                            table[class=body] .btn table {



                              width: 100% !important;



                            }



                            table[class=body] .btn a {



                              width: 100% !important;



                            }



                            table[class=body] .img-responsive {



                              height: auto !important;



                              max-width: 100% !important;



                              width: auto !important;



                            }



                          }



                          @media all {



                            .ExternalClass {



                              width: 100%;



                            }



                            .ExternalClass,



                                  .ExternalClass p,



                                  .ExternalClass span,



                                  .ExternalClass font,



                                  .ExternalClass td,



                                  .ExternalClass div {



                              line-height: 100%;



                            }



                            .apple-link a {



                              color: inherit !important;



                              font-family: inherit !important;



                              font-size: inherit !important;



                              font-weight: inherit !important;



                              line-height: inherit !important;



                              text-decoration: none !important;



                            }



                            #MessageViewBody a {



                              color: inherit;



                              text-decoration: none;



                              font-size: inherit;



                              font-family: inherit;



                              font-weight: inherit;



                              line-height: inherit;



                            }



                            .btn-primary table td:hover {



                              background-color: #34495e !important;



                            }



                            .btn-primary a:hover {



                              background-color: #34495e !important;



                              border-color: #34495e !important;



                            }



                          }



                          </style>



                        </head>



                        <body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">



                          <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">



                            <tr>



                              <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>



                              <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">



                                <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">



                                  <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;"></span>



                                  <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">



                                    <tr>



                                      <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">



                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">



                                          <tr>



                                            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">



                                              <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Hi ' .ucfirst($name). ' ,</p>



                                              <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"></p>



                                              <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Please sign in to validate your account with New Password.</p>



                                              <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">



                                                <tbody>



                                                  <tr>



                                                    <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">



                                                      <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">



                                                        <tbody>



                                                          <tr>



                                                            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">

                                                            Your current otp is: <b>' . $random_no . '.</b></td>



                                                          </tr>



                                                        </tbody>



                                                      </table>



                                                    </td>



                                                  </tr>



                                                </tbody>



                                              </table>



                                              <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Thanks and Regards.</p>

                                              <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Tasty & Hasty Team.</p>







                                            </td>



                                          </tr>



                                        </table>



                                      </td>



                                    </tr>



                                  </table>



                                  <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">



                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">







                                    </table>



                                  </div>



                                </div>



                              </td>



                              <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>



                            </tr>



                          </table>



                        </body>



                      </html>';

            if (mail($to, $subject, $message, $headers)) {

                $data_result['result'] = 'true';

                $data_result['msg']    = 'Please check your mail for otp';

               $data_result['user_id']  = $id;

                 $data_result['otp']    = $random_no;



            } else {

                $data_result['result'] = 'false';

                $data_result['msg']    = "something went wrong!!.";

            }

      } else {

          $data_result['result'] = 'false';

          $data_result['msg']    = "Email not exist.";

      }

    }else{
      $data_result['result']='false';
      $data_result['msg']='parameter email required!!';
    }
    echo json_encode($data_result);

  }




  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////
  ////////////////////////HASTY TASTY//////////////////////////////

  public function category_list(){
  	extract($_POST);
    if (isset($user_id)) {
      	$result=$this->Api_Model->get_shop_categories();
	      if ($result) {
	        $data1['result']="true";
	        $data1['msg']="category list";
	        $data1['data']=$result;
	      }else{
	        $data1['result']="false";
	        $data1['msg']="sorry not any list";
	      }
    }else{
      $data1['result']="false";
      $data1['msg']="parameter required user_id";
    }
    echo json_encode($data1);
  }


  public function shop_list_by_services(){
    $category_id=$this->input->post('category_id');
    if (isset($category_id)) {
      $result=$this->Api_Model->get_shop_Bycategories($category_id);
      if ($result) {
        $data1['result']="true";
        $data1['msg']="shop category list";
        $data1['data']=$result;
      }else{
        $data1['result']="false";
        $data1['msg']="sorry not any list";
      }
    }else{
      $data1['result']="false";
      $data1['msg']="parameter required category_id";

    }
    echo json_encode($data1);
  }



  public function shop_category_list(){

    $lat=$this->input->post('lat');
    $lng=$this->input->post('lng');
    $type=$this->input->post('type');
    $user_id=$this->input->post('user_id');
    $category_id=$this->input->post('category_id');

    if (isset($user_id) && isset($lat) && isset($lng) && isset($type) && isset($category_id)) {
    	if ($category_id!="") {
			$result=$this->Api_Model->getShopByCategories($category_id,$lat,$lng);
			foreach ($result as $key => $value) {
	      		$value->fav_status=$this->Api_Model->shopFavrt($user_id,$value->vendor_id);
	      		$value->rating=($this->Api_Model->ratingShopId($value->id))?round(($this->Api_Model->ratingShopId($value->id)),1):0;
            $value->total_rating=($this->Api_Model->Totalrating($value->id))?(($this->Api_Model->ratingShopId($value->id))):0;
      		}

    	}else{
    		$result=$this->Api_Model->shop_listByLatLon($lat,$lng,$type);
      		foreach ($result as $key => $value) {
	      		$value->fav_status=$this->Api_Model->shopFavrt($user_id,$value->vendor_id);
	      		$value->rating=($this->Api_Model->ratingShopId($value->id))?round(($this->Api_Model->ratingShopId($value->id)),1):0;
            $value->total_rating=($this->Api_Model->Totalrating($value->id))?(($this->Api_Model->ratingShopId($value->id))):0;
      		}
    	}

      if ($result) {
        $data1['result']="true";
        $data1['msg']="shop list";
        $data1['data']=$result;
      }else{
        $data1['result']="false";
        $data1['msg']="sorry not any list";
      }
    }else{
      $data1['result']="false";
      $data1['msg']="parameter required user_id,lat,lng,type(1=delivery,2=pickup,3=table-book),category_id(optional)";
    }
    echo json_encode($data1);
  }


  //Aboutus term condition
    public function privacy_policy(){

    	$user_id= $this->input->post("user_id");

		if($user_id)

		{
			$res=$this->db->query("SELECT id,title,content FROM rules WHERE type='Privacy' ")->row();
			if ($res) {
				$data['result'] ="true";
		  		$data['msg']    ="privacy policy data";
		  		$data['data']    =$res;
			}else{
				$data['result'] ="true";
				$data['msg'] ="data not found";
			}
		}else{
			$data['result'] ="true";
			$data['msg'] ="user_id required";
		}
	echo json_encode($data);
	}

	//term condition
	public function terms_condition(){

    	$user_id= $this->input->post("user_id");

		if($user_id)

		{
			$res=$this->db->query("SELECT id,title,content FROM rules WHERE type='term' ")->row();
			if ($res) {
				$data['result'] ="true";
		  		$data['msg']    ="term condition data";
		  		$data['data']    =$res;
			}else{
				$data['result'] ="true";
				$data['msg'] ="data not found";
			}
		}else{
			$data['result'] ="true";
			$data['msg'] ="user_id required";
		}
	echo json_encode($data);
	}


	//Aboutus



//  resend otp

public function resendotp()
{
      extract($_POST);
      if(isset($user_id))
      {
       	$this->db->select('*');
        $this->db->from('users');
        $this->db->where('id',$user_id);
        $query = $this->db->get();
     	if ($query->num_rows() > 0) {
			$result = $query->row();
			$random_no = rand(1000,9999);
			$otp['otp'] = $random_no;
			$this->db->where("id",$result->id);
			$this->db->update("users",$otp);
			$ri_email = 'tastyhasty27feb@gmail.com';
			$to       = $result->email;
            $subject  = 'OTP';
            $headers = "From: <" . $ri_email . ">" . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message = '';
            $message .= '<!DOCTYPE html>
            <html>



              <head>



              <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />







              <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">


                </head>

                <body style="font-family:roboto !important;">


                    <div style="width:100%; text-align:center; margin:0px auto;">

                    <div style="width: 550px; height: auto;  margin: 10px auto;;">

                    <div style="padding:2px 2px 8px 2px; background:#042954; text-align:center;">



                     <div style="font-size: 20px; line-height: 40px; padding: 0;  margin-top: 5px;text-align: center; color: #fff;">Tasty Hasty</div>

                     </div>


                    <div style="background:whitesmoke;padding: 15px 0;font-family: sans-serif;">


                    <h3 style="text-align: left;padding-left: 100px; font-size: 18px;  margin-top: 0px;

                    margin-bottom: 10px; color: #2dbba4;">OTP</h3>

                    <center>



                    <p>Dear ' . $result->name . '.</p>


                    <p>


                    Your OTP is: ' . $random_no . '.</p>

                    </center>


                    </div>


                    <footer>


                        <div style="background: #042954; padding: 20px 5px 25px 5px;">


                        <div style="width:100%; text-align:center;">


                        <div style="font-size: 13px; line-height: 7px; padding: 0;  margin-top: 0px;


                        text-align: center; color: #fff;"> © Hasty N Tasty 2020. All Rights Reserved.

                        </div>


                        </div>


                        </div>


                    </footer>

                    </div>
                    </div>
                    </body>
                </html>';
            if (@mail($to, $subject, $message, $headers)) {
                $data['result'] = true;
                $data['msg'] = "Successfully";
                $data['otp'] = $random_no;
              }
              else{
                 $data['result'] = false;
                $data['msg']    = "Email not exist.";
              }
    		}
    		else{
              $data['result'] = false;
              $data['msg'] = "user_id is not register";
            }
		}else{
			$data['result'] = false;
			$data['msg'] = "Please enter required field(user_id)";
		}

      	echo json_encode($data);
  	}


  	//google login
	public function Google_Login()
    {

      extract($_POST);
      if (isset($email) &&
        isset($name) &&
        isset($fcm_id) &&
        isset($google_id)
        )
      {
        $wheredata = array(
        'email'  =>$email
        );
        $res= $this->Api_Model->singleRowdata($wheredata,'users');
        if ($res){
          $data_result['result'] = 'true';
          $data_result['msg']    = 'Google Login successfully!';
          $data_result['data']   = $res;
        }else{
          $data = array(
            'email'  =>$email,
            'name'   =>$name,
            'google_id'=>$google_id,
            'fcm_id' =>$fcm_id
          );
          $result    = $this->Api_Model->insert('users',$data);
          $wheredata = array(
            'id' => $this->db->insert_id()
          );
          $res1 = $this->Api_Model->singleRowdata($wheredata,'users');
          if($result){
            $data_result['result'] = 'true';
            $data_result['data']   = $res1;
            $data_result['msg']    = 'Login google successfully!';
          }else{
            $data_result['result'] = 'false';
            $data_result['msg']    = 'Your record not insert!';
          }
        }
      }else{
        $data_result['result'] = 'false';
        $data_result['msg']    = 'parameter required email,name,fcm_id,google_id';
      }
      echo json_encode($data_result);

    }

    //facebook login
    // public function facebook_login()
    // {

    //   extract($_POST);
    //   if (isset($email) &&
    //     isset($name) &&
    //     isset($fcm_id) &&
    //     isset($facebook_id)
    //     )
    //   {
    //     $wheredata = array(
    //     'email'  =>$email
    //     );
    //     $res= $this->Api_Model->singleRowdata($wheredata,'users');
    //     if ($res){
    //       $data_result['result'] = 'true';
    //       $data_result['msg']    = 'Google Login successfully!';
    //       $data_result['data']   = $res;
    //     }else{
    //       $data = array(
    //         'email'  =>$email,
    //         'name'   =>$name,
    //         'facebook_id'=>$facebook_id,
    //         'fcm_id' =>$fcm_id
    //       );
    //       $result    = $this->Api_Model->insert('users',$data);
    //       $wheredata = array(
    //         'id' => $this->db->insert_id()
    //       );
    //       $res1 = $this->Api_Model->singleRowdata($wheredata,'users');
    //       if($result){
    //         $data_result['result'] = 'true';
    //         $data_result['data']   = $res1;
    //         $data_result['msg']    = 'Login google successfully!';
    //       }else{
    //         $data_result['result'] = 'false';
    //         $data_result['msg']    = 'Your record not insert!';
    //       }
    //     }
    //   }else{
    //     $data_result['result'] = 'false';
    //     $data_result['msg']    = 'parameter required email,name,fcm_id,facebook_id';
    //   }
    //   echo json_encode($data_result);

    // }




    public function facebook_login()
    {

      extract($_POST);
      if (isset($name) &&
        isset($fcm_id) &&
        isset($facebook_id)
        )
      {


              $email = $this->input->post('email');





        $wheredata = array(
        'fcm_id'  =>$fcm_id
        );
        $res= $this->Api_Model->singleRowdata($wheredata,'users');
        if ($res){
          $data_result['result'] = 'true';
          $data_result['msg']    = 'Facebook Login successfully!';
          $data_result['data']   = $res;
        }else{
          $data = array(
            'email'  =>$email,
            'name'   =>$name,
            'facebook_id'=>$facebook_id,
            'fcm_id' =>$fcm_id
          );
          $result    = $this->Api_Model->insert('users',$data);
          $wheredata = array(
            'id' => $this->db->insert_id()
          );
          $res1 = $this->Api_Model->singleRowdata($wheredata,'users');
          if($result){
            $data_result['result'] = 'true';
            $data_result['data']   = $res1;
            $data_result['msg']    = 'Facebook Login successfully!';
          }else{
            $data_result['result'] = 'false';
            $data_result['msg']    = 'Your record not insert!';
          }
        }



      }else{
        $data_result['result'] = 'false';
        $data_result['msg']    = 'parameter required email,name,fcm_id,facebook_id';
      }
      echo json_encode($data_result);

    }






// linkdin login


	public function Linkdin_Login()
    {

      extract($_POST);
      if (isset($email) &&
        isset($name) &&
        isset($fcm_id) &&
        isset($linkdin_id)
        )
      {
        $wheredata = array(
        'email'  =>$email
        );
        $res= $this->Api_Model->singleRowdata($wheredata,'users');
        if ($res){
          $data_result['result'] = 'true';
          $data_result['msg']    = 'linkdin Login successfully!';
          $data_result['data']   = $res;
        }else{
          $data = array(
            'email'  =>$email,
            'name'   =>$name,
            'linkdin_id'=>$linkdin_id,
            'fcm_id' =>$fcm_id
          );
          $result    = $this->Api_Model->insert('users',$data);
          $wheredata = array(
            'id' => $this->db->insert_id()
          );
          $res1 = $this->Api_Model->singleRowdata($wheredata,'users');
          if($result){
            $data_result['result'] = 'true';
            $data_result['data']   = $res1;
            $data_result['msg']    = 'Login linkdin successfully!';
          }else{
            $data_result['result'] = 'false';
            $data_result['msg']    = 'Your record not insert!';
          }
        }
      }else{
        $data_result['result'] = 'false';
        $data_result['msg']    = 'parameter required email,linkdin_id,name,fcm_id,google_id';
      }
      echo json_encode($data_result);

    }

    //product list by restro id
    public function get_restro_product_list(){
    	extract($_POST);
    	if (!empty($user_id) && !empty($shop_id)) {
    		$restro=$this->Api_Model->get_restro_details($shop_id);
    		$res=$this->Api_Model->get_foodType($shop_id);
    		foreach ($res as $key => $value) {
    			$value->products=$this->Api_Model->get_Product_foodType($user_id,$shop_id,$value->food_type);
    		}
    		if ($res) {
    			$data_result['result'] = 'true';
    			$data_result['msg'] = 'All product list';
    			$data_result['rating'] = "3";
    			$data_result['distance'] = "12";
    			$data_result['delivery_type'] = "free";
    			$data_result['shop_name'] = $restro->shop_name;
    			$data_result['delivery_time'] = $restro->delivery_time;
    			$data_result['category'] = $restro->category_name;
    			$data_result['address'] = $restro->address;
    			$data_result['data'] = $res;
    		}else{
    			$data_result['result'] = 'false';
    			$data_result['msg'] = 'sorry data not found';
    		}
    	}else{
    		$data_result['result'] = 'false';
        	$data_result['msg']    = 'parameter required user_id,shop_id';
    	}
    	echo json_encode($data_result);
    }

    // book a table
    public function book_table_data(){
    	extract($_POST);
    	if (!empty($shop_id)) {
    		$restro=$this->Api_Model->get_restro_details($shop_id);
    		$restro->rating=$this->Api_Model->get_restro_raing($shop_id);
    		if ($restro) {
    			$data_result['result'] = 'true';
    			$data_result['msg'] = 'shop details table book data';
    			$data_result['data'] = $restro;
    		}else{
    			$data_result['result'] = 'false';
    			$data_result['msg'] = 'sorry data not found';
    		}
    	}else{
    		$data_result['result'] = 'false';
    		$data_result['msg'] = 'param required shop_id';
    	}
    	echo json_encode($data_result);
    }

    //product_detail
    public function productDetail(){

        extract($_POST);

        $this->form_validation->set_rules('product_id','product_id','trim|required');

        if($this->form_validation->run()==false){

            $data['result']="false";

            $data['msgresult']=strip_tags($this->form_validation->error_string());

        } else{

            // $result=$this->Api_Model->Get_Detail_list('products',$this->input->post('product_id'));

            $result=$this->Api_Model->productDetails($product_id);



            if($result){

               $data['result']="true";

               $data['msgresult']="data found";

               $data['data']=$result;

            }else{

               $data['result']="true";

               $data['msgresult']="Somethings went Wrong";

            }



        }



        echo json_encode($data);

    }


    //addon_iteam_list
    public function product_addon_list(){
	    extract($_POST);
	    $data1=array();
	    $this->form_validation->set_rules('product_id','product_id','trim|required');
	    if($this->form_validation->run()==false){
	      	$data1['result']="false";
	    	$data1['msg']="product_id required parameter";
	    } else{
		    //   $product_id=$this->input->post('product_id');
		    //   $lang_id=$this->input->post('lang_id');
		    //   $data=$this->db->query('Select * from addon_product Where  product_id='.$product_id.'')->result();

		    $data=$this->Api_Model->addon_products_list($product_id);
	      	if($data):
				$data1['result']="true";
				$data1['msg']="Addon product  list ";
				$data1['total_addon']=count($data);
				$data1['data']=$data;
	      	else:
	      	$data1['result']="false";
	      	$data1['msg']="no data found ";
	    	//$data1['data']=array();
	      	endif;
	    }
	    echo json_encode($data1);
	}

	//AddToCart
	public function AddToCart(){
	    $this->form_validation->set_rules('user_id','User id','trim|required');
	    $this->form_validation->set_rules('product_id','product_id','trim|required');
      $this->form_validation->set_rules('types','types','trim|required');
	    $this->form_validation->set_rules('name','Name','trim|required');
	    //$this->form_validation->set_rules('price','price','trim|required');
	    $this->form_validation->set_rules('quantity','quantity','trim|required');
	    $this->form_validation->set_rules('final_amount','final_amount','trim|required');
	    if($this->form_validation->run()==false){
	    $data['result']=false;
	    $data['msg']=strip_tags($this->form_validation->error_string());
	    } else{
	      $result=$this->Api_Model->Add_to_cart();
	      if($result){
	        $data['result']="true";
	        $data['msg']="Added iteam  in cart Successfully";
	      }else{
	        $data['result']="false";
	        $data['msg']="This product is already added in cart";
	      }
	    }
	    echo json_encode($data);
	}

	//get my Cart list
	public function myCart(){
	  	$this->form_validation->set_rules('user_id','User id','trim|required');
	  	$user_id=$this->input->post('user_id');
	  	if($this->form_validation->run()==false){
	  		$data['result']=false;
	  		$data['msg']="user_id required parameter";
	  	} else{
	  		$result=$this->Api_Model->Get_cart_list($user_id);


	        $dataaddon=array();
	        foreach($result as $value):
	        	$addarray= explode(',',$value->addon);
	        	//$addarray= explode(',',$value->addon);

          		$dataaddon=$this->db->query('select addon_product.id,addon_product.product_id,addon_product. product_name,addon_product.price from addon_product where id IN ('.$value->addon.')')->result();

	        	$resultnew[]=array(
	        		'id'=>$value->id,
	        		'user_id'=>$value->user_id,
	        		'product_id'=>$value->product_id,
	        		'shop_name'=>$value->shop_name,
	        		'shop_image'=>$value->shop_image,
	        		'shop_address'=>$value->address,
	        		'vendor_id'=>$value->vendor_id,
	        		'addon'=>$dataaddon,
	        		'addon_price'=>$value->addon_price,
	        		'name'=>$value->name,
	        		'image'=>$value->image,
	        		'price'=>$value->price,
	        		'quantity'=>$value->quantity,
              // 'types'=>$value->types,
	        		'total'=>$value->total_amount+$value->addon_price,
	        		'final_amount'=>$value->price * $value->quantity + $value->addon_price,
	        		'apply_coupan'=>$value->apply_coupan
	        		//'code'=>$value->code
	          	);
	        endforeach;
	        if($result){
	        	$data['result']="true";
	        	$data['code_id']=($this->Api_Model->coupansId($value->user_id,$value->code))?$this->Api_Model->coupansId($value->user_id,$value->code):"";
	        	$data['code']=($value->code)?$value->code:"";
	        	$data['data']=$resultnew;
	       	}else{
	       		$data['result']="false";
	       		$data['msg']="cart empty";
	       	}
       	}
       echo json_encode($data);
    }

    //table booking
    public function userBookTable(){
    	extract($_POST);
    	if(!empty($user_id) && !empty($guest) && !empty($shop_id) && !empty($booking_time) && !empty($booking_date)){


          $order_id_data = $this->db->query('SELECT MAX(table_booking_id) as order_id FROM resto_table_book ')->row();
      $ordersID=$order_id_data->order_id;
      if($ordersID)
        {
          $ordersID++;
        }else{
          $ordersID = "300001";
        }
        $_POST['table_booking_id']=$ordersID;
    		$res=$this->db->insert('resto_table_book',$_POST);

        $this->db->select("*");
        $this->db->from("vendor");
        $this->db->where("id",$shop_id);
        $query= $this->db->get();
        $result= $query->row_array();
        // $Userfcm_id= $result['fcm_id'];
        $user=$this->db->query("SELECT * FROM users WHERE id=$user_id")->row();
        $Userfcm_id=$user->fcm_id;
            $notification = array(

            'title' =>"new order.",

            'body'  =>"Table booked"

          );

          $resss = send_notification($notification,$Userfcm_id);
          $data2 = array(
          'ordersID'=> $ordersID,
          'sender_id' => $user_id,
          'reciever_id'=>$shop_id,
          'message'=>'New order',
          'sender_type'=>'customer',
          'receiver_type'=>'Vendor',
          'types'=>'Table booking'
        );
        $this->db->insert('notifications',$data2);
    		if ($res) {
    			$json['result']="true";
    			$json['msg']="Table booked";
    		}else{
    			$json['result']="false";
    			$json['msg']="sorry something went wrong";
    		}
    	}else{
    		$json['result']="false";
    		$json['msg']="parameter required user_id,guest,shop_id,booking_time,booking_date";
    	}
    	echo json_encode($json);
    }


    //App Feedback submission
    public function AppFeedback(){
    	extract($_POST);
    	if (!empty($user_id) && isset($over_all_rating) && isset($professional_rating) && isset($service_rating) && isset($app_interface) && isset($customer_support) && isset($feedback)) {
    		if(!$this->User_Model->is_record_exist('app_rating','user_id',$user_id))
      		{
	    		$res=$this->db->insert('app_rating',$_POST);
	    		if ($res) {
	    			$json['result']="true";
	    			$json['msg']="App Feedback Submitted";
	    		}else{
	    			$json['result']="false";
	    			$json['msg']="Sorry something went wrong";
	    		}
    		}else{
    			$where=array('user_id'=>$user_id);
    			$datas=array('over_all_rating'=>$over_all_rating,'professional_rating'=>$professional_rating,'service_rating'=>$service_rating,'app_interface'=>$app_interface,'customer_support'=>$customer_support,'feedback'=>$feedback);
    			$res1=$this->User_Model->update($where,'app_rating',$datas);
    			if ($res1) {
    				$json['result']="true";
    				$json['msg']="App Feedback Submitted";
    			}else{
    				$json['result']="false";
    				$json['msg']="something went wrong";
    			}
    			//$json['result']="false";
    			//$json['msg']="Already rated";
    		}
    	}else{
    		$json['result']="false";
    		$json['msg']="parameter required user_id,over_all_rating,professional_rating,service_rating,app_interface,customer_support,feedback";
    	}
    	echo json_encode($json);
    }

    //user complaint
    public function UserComplaint(){
    	extract($_POST);
    	if (!empty($user_id) &&
    		isset($complain_type) &&
    		isset($message)
    	) {
    		if(!$this->User_Model->is_record_exist('complain','user_id',$user_id))
      		{
	    		$res=$this->db->insert('complain',$_POST);
	    		if ($res) {
	    			$json['result']="true";
	    			$json['msg']="Complaint Submitted";
	    		}else{
	    			$json['result']="false";
	    			$json['msg']="Sorry something went wrong";
	    		}
    		}else{
    			$where=array('user_id'=>$user_id);
    			$datas=array('complain_type'=>$complain_type,'message'=>$message);
    			$res1=$this->User_Model->update($where,'complain',$datas);
    			if ($res1) {
    				$json['result']="true";
    				$json['msg']="Complaint submitted";
    			}else{
    				$json['result']="false";
    				$json['msg']="something went wrong";
    			}

    		}
    	}else{
    		$json['result']="false";
    		$json['msg']="parameter required user_id,complain_type,message";
    	}
    	echo json_encode($json);
    }


    //get coupon code
    public function getCouponCode(){
    	extract($_POST);
    	if (!empty($user_id)) {
    		$getCouopn=$this->Api_Model->getCouponCode();
    		if ($getCouopn) {
    			$json['result']=true;
    			$json['result']="All coupon code";
    			$json['data']=$getCouopn;
    		}else{
    			$json['result']=false;
    			$json['result']="sorry not any list";
    		}
    	}else{
    		$json['result']=false;
    		$json['msg']="parameter required user_id";
    	}
    	echo json_encode($json);
    }


    //term condition
    public function TermCondition(){
    	extract($_POST);
    	if (!empty($user_id)) {
    		$getCouopn=$this->Api_Model->termCondition();
    		if ($getCouopn) {
    			$json['result']=true;
    			$json['result']="Term Condition";
    			$json['data']=$getCouopn;
    		}else{
    			$json['result']=false;
    			$json['result']="sorry not any list";
    		}
    	}else{
    		$json['result']=false;
    		$json['msg']="parameter required user_id";
    	}
    	echo json_encode($json);

    }

    //Privacy Policy
    public function PrivacyPolicy(){
    	extract($_POST);
    	if (!empty($user_id)) {
    		$getCouopn=$this->Api_Model->privacyPolicy();
    		if ($getCouopn) {
    			$json['result']=true;
    			$json['result']="Term Condition";
    			$json['data']=$getCouopn;
    		}else{
    			$json['result']=false;
    			$json['result']="sorry not any list";
    		}
    	}else{
    		$json['result']=false;
    		$json['msg']="parameter required user_id";
    	}
    	echo json_encode($json);

    }

    //AboutUs
    public function AboutUs(){
    	extract($_POST);
    	if (!empty($user_id)) {
    		$getCouopn=$this->Api_Model->AboutUs();
    		if ($getCouopn) {
    			$json['result']=true;
    			$json['result']="About us";
    			$json['data']=$getCouopn;
    		}else{
    			$json['result']=false;
    			$json['result']="sorry not any list";
    		}
    	}else{
    		$json['result']=false;
    		$json['msg']="parameter required user_id";
    	}
    	echo json_encode($json);

    }

    /*cart increase*/
	public function cart_increase(){
	    $this->form_validation->set_rules('cart_id','cart_id','trim|required');
	    $this->form_validation->set_rules('quantity','quantity','trim|required');
	    $this->form_validation->set_rules('final_amount','final_amount','trim|required');
	    if($this->form_validation->run()==false){
	      $data['result']=false;
	      $data['msg']=strip_tags($this->form_validation->error_string());
	    } else{
	      $result=$this->Api_Model->cart_value_increase();
	      if($result){
	        $data['result']=true;
	        $data['data']=" cart updated Successfully";
	      }else{
	        $data['result']=true;
	        $data['msg']="Somethings went Wrong";
	      }
	    }
	    echo json_encode($data);

	}


	//cart_decrease
	public function cart_decrease(){
		$this->form_validation->set_rules('cart_id','cart_id','trim|required');
		$this->form_validation->set_rules('quantity','quantity','trim|required');
		$this->form_validation->set_rules('user_id','user_id','trim|required');

		$this->form_validation->set_rules('final_amount','final_amount','trim|required');
		//$this->form_validation->set_rules('total_amount','total_amount','trim|required');
		if($this->form_validation->run()==false){
			$data['result']=false;
			$data['msg']=strip_tags($this->form_validation->error_string());
		}else{
			$result=$this->Api_Model->cart_value_decrease();
			if($result){
				$data['result']="true";
				$data['data']=" cart updated Successfully";
			}else{
				$data['result']="true";
				$data['msg']="Somethings went Wrong";
			}
		}
		echo json_encode($data);
	}



    //show cart item
    public function getCartTotal(){
    	extract($_POST);
    	if (!empty($user_id)) {
    		$res=$this->Api_Model->getCartItems($user_id);
    		if ($res) {
    			$json['result']=true;
    			$json['msg']='Cart Detail';
    			$json['data']=$res;
    		}else{
    			$json['result']=false;
    			$json['msg']='Sorry Your cart empty';
    		}
    	}else{
    		$json['result']=false;
    		$json['msg']='parameter required user_id';
    	}
    	echo json_encode($json);
    }

    //check cart
    public function check_cart(){
	    extract($_POST);
	    if (!empty($user_id) && !empty($vendor_id)) {
	      $check=$this->db->query("SELECT user_id,vendor_id FROM `cart` WHERE cart.user_id=$user_id AND cart.vendor_id=$vendor_id ");
	      $dataexist=$this->db->query("SELECT user_id,vendor_id FROM `cart` WHERE cart.user_id=$user_id OR cart.user_id=$user_id AND cart.vendor_id=$vendor_id ")->result();
	      	if (!empty($dataexist)) {

		        if($check->num_rows()>0){
		          $json['result']="true";
		          $json['msg']="same item in cart";
		        }else{
		        	$json['result']="false";
		        	$json['msg']="Remove cart Items for add products from another vendor";
		        }
		    }else{
		    	$json['result']="true";
		    	$json['msg']="add data";
		    }
		}else{
			$json['result']="false";
			$json['msg']="parameter required user_id,vendor_id";
		}
		echo json_encode($json);
	}



	/*delete cart item*/
    public function delete_cart(){
        $this->form_validation->set_rules('user_id','user_id','trim|required');
        //$this->form_validation->set_rules('cart_id','cart_id','trim|required');
        if($this->form_validation->run()==false){
            $data['result']="false";
            $data['msg']="param required user_id";
        } else{
            //$id=$this->input->post('cart_id');
            $user_id=$this->input->post('user_id');
            //$result=$this->db->query('DELETE from cart WHERE id='.$id.'');
            $result=$this->db->query('DELETE from cart WHERE user_id='.$user_id.'');
          	if($result){
            	$data['result']="true";
            	$data['msg']="Deleted from cart";
          	}else{
            	$data['result']="true";
            	$data['msg']="Somethings went Wrong";
        	}
        }
        echo json_encode($data);
    }


   	// order history get from cart added data
  	//and place to order
  	public function billing_detail(){
	    $data1=array();
	    $this->form_validation->set_rules('user_id','user_id','trim|required');
	    if($this->form_validation->run()==false){
	      $data['result']="false";
	      $data['msg']="param user_id";
	    } else{
	      $user_id=$this->input->post('user_id');
	      $total_pay=0;
	      $price=0;
	      $data=$this->db->query('select * from cart where user_id='.$user_id.'')->result();
	      if($data):
	      	$qua=0;
	      	$total_pay1=0;
	      	foreach($data as $value):
	      		// print_r($value->quantity);exit();
	      		if(!$value->addon==0):
		            $addarray= explode(',',$value->addon);
		            $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();
		            foreach($dataaddon as $value1):
		            $price+=$value1->price;
		            endforeach;
	          	endif;
	           	$total_pay1+=$value->final_amount;
	          	$total_pay+=$value->final_amount;
				$qua+=$value->quantity;
	    	endforeach;
	        $total_pay+=$price;
	        $delivery_charge=0;
	        $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();
	        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;
	        if ($selectDeliverycharge) {
	          $delivery_charge=$selectOrderDeliverycharges;
	          $total_pay+=$delivery_charge;
	        }else{
	          $total_pay;
	        }
			if($price !=""){
	    		$pri=$price;
	    	}else{
	    		$pri=0;
			}

			 $coupandata=$this->db->query('select t1.*,t2.cupon_price from apply_coupan as t1  LEFT JOIN cupon_code as t2 ON t1.code = t2.code  where user_id='.$user_id.'')->row();
			if($coupandata){
			    $coupandiscount= $coupandata->cupon_price;
			}
			else
			{
			    $coupandiscount= 0;

			}
	        $newarray=array(
	          'cart_iteam'=>count($data),
	          'id'=>$value->id,
	          'total_quantity'=>$qua,
	          'total_pay'=>$total_pay,
	          'sub_total'=>$total_pay1 - $coupandiscount+$pri,
	          'delivery_charge'=>$delivery_charge,
	          'add_on_price'=>$pri,
	          'total_discount'=>0,
	          'coupon_price'=>$coupandiscount,
	          'final_amount'=>$total_pay - $coupandiscount
	          //'final_amount'=>$total_pay
	        );
	        $data1['result']="true";
	        $data1['msg']="Billing Detail";
	        $data1['data']=$newarray;
	    else:
	      $data1['result']="false";
	      $data1['msg']="somthing went wrong ";
	      $data1['data']=array();
	      endif;
	    }
	    echo json_encode($data1);
  	}



	// userupdate address
 	public function updateuseraddres()
	{
		extract($_POST);
		if(isset($user_id))
		{
           	unset ($_POST['user_id']);
			$this->db->where("id",$user_id);
			$res = $this->db->update("users",$_POST);
			if($res)
			{
              $json['result'] = true;
              $json['msg'] = "Successfully";
          	}
          	else
              {
              $json['result'] = false;
              $json['msg'] = "something went wrong";
              }
              }else{
              $json['result'] = false;
              $json['msg'] = "Please enter required field(user_id option(address,lat,lang))";
              }

              echo json_encode($json);

        }


        //  add adress order
  //       public function add_address(){
  //   $this->form_validation->set_rules('address','address','trim|required');
  //   $this->form_validation->set_rules('user_id','user_id','trim|required');
  //   $this->form_validation->set_rules('lat','lat','trim|required');
  //
  //   $this->form_validation->set_rules('lang','lang','trim|required');
  // $this->form_validation->set_rules('landmark','landmark','trim|required');
  //   $this->form_validation->set_rules('type','type','trim|required');
  //   if($this->form_validation->run()==false){
  //      $datas['result']="false";
  //       //$data['message']=strip_tags($this->form_validation->error_string());
  //      $datas['message']="parameter required (user_id,address,lat,lang,type,landmark)";
  //
  //   } else{
  //       $data1=array(
  //       'user_id'=>$this->input->post('user_id'),
  //       'address'=>$this->input->post('address'),
  //       'lat'=>$this->input->post('lat'),
  //       'lang'=>$this->input->post('lang'),
  //       'type'=>$this->input->post('type'),
  //       'landmark'=>$this->input->post('landmark')
  //     );
  //
  //     $result= $this->db->insert('address',$data1);
  //     $insert_id = $this->db->insert_id();
  //
  //     if($result){
  //          $datas['result']="true";
  //          $datas['data']=$this->Admin_model->select_single_row('address','id',$insert_id);
  //          $datas['msg']=" address is successfully added";
  //
  //     }else{
  //
  //          $datas['result']="false";
  //
  //          $datas['msg']="Somethings went Wrong";
  //
  //     }
  //   }
  //
  // echo json_encode($datas);
  //
  //
  // }









    // public function search_restorent()
    // {
    //   $keyword = $this->input->post('keyword');




    //       $res =  $this->Api_Model->search_restorent($keyword);

    //       if($res)

    //       {
    //         $json['result'] = "true";
    //         $json['msg'] = "Restorent list.";
    //         $json['data'] = $res;
    //       }else{
    //         $json['result'] = "false";

    //         $json['msg'] = "no record found.";

    //       }




    //   echo json_encode($json);

    // }





	public function getuserBookTable() {

   	header('Content-Type: application/json');

 	$user_id = (trim($this->input->post('user_id')));

	if($user_id == ""){

	  $data['result'] = "false";

	  $data['msg'] = "Please enter required field(user_id)";
	}else{

	  $result = $this->Api_Model->getuserBookTable($user_id);

	if($result){

	   $data['result']="true";

	   $data['msg']="booking table list";
	   $data['data']=$result;

	}else{

	   $data['result']="false";

	   $data['msg']="Somethings went Wrong";

	}



	}

	echo json_encode($data);

	}



	/*
	@place_order
	*/
	public function place_order(){
	    $data1=array();
	    $this->form_validation->set_rules('vendor_id','vendor_id','trim|required');
	    $this->form_validation->set_rules('user_id','user_id','trim|required');
	    $this->form_validation->set_rules('payment_method','payment_method','trim|required');
	    $this->form_validation->set_rules('total_amount','total_amount','trim|required');
	    $this->form_validation->set_rules('address_id','address_id','trim|required');
	    //$this->form_validation->set_rules('instruction','instruction','trim|required');
	    if($this->form_validation->run()==false){
	    	$data1['result']="false";
	    	$data1['msg']="user_id,payment_method,total_amount,address_id,vendor_id,transaction_id,instruction(optional) is required parameter";
	    } else{
	    	$user_id=$this->input->post('user_id');
	    	$vendor_id=$this->input->post('vendor_id');

	    	$data=$this->Api_Model->Place_order();




	    	if($data):

	    	    	$this->db->select("*");
	    	$this->db->from("vendor");
	    	$this->db->where("id",$vendor_id);
	    	$query= $this->db->get();
	    	$result= $query->row_array();
	    	$Userfcm_id= $result['fcm_id'];
	    	    $notification = array(

            'title' =>"new order.",

            'body'  =>"new booking"

          );

          $resss = send_notification($notification,$Userfcm_id);


	        $data1['result']="true";
	        $data1['msg']="Order is Placed ";
	      else:
	        $data1['result']="false";
	        $data1['msg']="somthing went wrong ";
	      endif;
	    }
	    echo json_encode($data1);
  	}



  	/*
    @user order details/history(not com/can)
    */
    public function user_order_history(){
        $user_id= $this->input->post('user_id');
        if(!empty($user_id) ){
            $result=$this->Api_Model->userOrders($user_id);
            $re12=$this->db->query("SELECT count(id) as total_cart FROM cart WHERE user_id=$user_id")->row()->total_cart;
            //print_r($result);exit();
            $amo1=0;
            $add_on=0;
            $dataaddon=array();
            foreach($result as $value){

				if(!$value['addon']==0):
				$addarray= explode(',',$value['addon']);
				$dataaddon=$this->db->query('select * from addon_product where id IN ('.$value['addon'].')')->result();
				endif;
                $wheredata=array('ordersID'=>$value['ordersID']);
                $sub=  $this->Api_Model->selectData('orders',$wheredata);
                if(!empty($sub)){
                    $amo=[];
                    $add_on=[];
                    $quanitity=[];
                    foreach($sub as $sub1){
                        $amo1 =$sub1->price*$sub1->quanitity;
                        $amo[]=$amo1;
                        $add_on[]=$sub1->addon_price;
                        $quanitity[]=$sub1->quanitity;
                    }
                    $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();
                    $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;
                    $arrya_sum=array_sum($amo);
                    $arrya_add_on=array_sum($add_on);
                    $qua=array_sum($quanitity);
                    $ad_on_plus=$arrya_sum+$arrya_add_on;
                    $final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;
                }else{
                	$final_amo=0;
                }


                $array[]=array(
                	'cart_iteam'=>$re12,
                   'ordersID'=>$value['ordersID'],
                   'vendor_id'=>$value['vendor_id'],
                   'shop_name'=>$value['shop_name'],
                   'shop_image'=>$value['shop_image'],
                   'delivery_time'=>$value['delivery_time'],
                   'order_id'=>$value['order_id'],
                   'quanitity'=>$qua,
                   'total'=>$ad_on_plus,
                   'addon_price'=>$value['addon_price'],
                   'addon'=>$dataaddon,
                   'delivery_charge'=>$selectOrderDeliverycharges,
                   'sub_total'=>$final_amo,
                   'payment_method'=>$value['payment_method'],
                   'order_status'=>$value['order_status'],
                   'types'=>$value['types'],
                   'order_date'=>$value['order_date'],
                );
            }
            if (count($array)) {
              $json['result']="true";
              $json['msg']="user's order list.";
              $json['data']=$array;
            }else{
            	$json['result']="false";
            	$json['msg']="sorry not any order here!!";
            }
        }else{
            $json['result']="false";
            $json['msg']="parameter required user_id";
        }
        echo json_encode($json);

    }




  /*
  @User order history(complete)
  */
  public function usersCompletedOrderHistory(){
    $user_id=$this->input->post('user_id');
    if (isset($user_id)) {
      $result=$this->Api_Model->GetCompletedOrderList();
      //print_r($result);exit();
      $dataaddon=array();
      foreach($result as $value):
      	//$value->shops=$this->Api_Model->vendorDetails($value->vendor_id);

      	if(!$value->addon==0):
          $addarray= explode(',',$value->addon);
          $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();
        endif;

        $resultnew[]=array(
        'id'=>$value->order_id,
        'ordersID'=>$value->ordersID,
        'user_id'=>$value->user_id,
        'product_id'=>$value->product_id,
        'shop_name'=>$value->shop_name,
        'shop_image'=>$value->shop_image,
        'vendor_id'=>$value->vendor_id,
        'shop_id'=>$value->shop_id,
        'name'=>$value->name,
        'image'=>$value->image,
        'addon_price'=>$value->addon_price,
        'price'=>$value->price,
        'quantity'=>$value->quanitity,
        'delivery_charge'=>$value->delivery_charge,
        'modified_at'=>$value->modified_at,
        'total'=>$value->total,
        'total_amount'=>$value->sub_total,
        'final_amount'=>$value->sub_total+$value->addon_price,
        'order_status'=>$value->order_status,
        'user_rating_status'=>$value->user_rating_status=$this->Api_Model->shopRateByUser($user_id,$value->shop_id),
        'addon'=>$dataaddon
        );
      endforeach;
      if($result){
        $data['result']="true";
        $data['msg']="Completed/Canceled Orders";
        $data['data']=$resultnew;
      }else{
        $data['result']="false";
        $data['msg']="Not any Completed/Canceled Order";
      }
    }else{
      $data['result']="false";
      $data['msg']="parameter required user_id";
    }
    echo json_encode($data);
  }


	/*
	@CancelOrder
	*/
	public function CancelOrder(){
	    $order_id=$this->input->post('order_id');
	    $user_id=$this->input->post('user_id');
	    $cancel_reason=$this->input->post('cancel_reason');
	    if (isset($order_id) && isset($user_id) && isset($cancel_reason)) {
	        $wheredata=array(
	          'order_id'=>$order_id,
	          'user_id'=>$user_id
	      	);
	        $data=array('order_status'=>5,'cancel_reason'=>$cancel_reason);
	        $result=$this->Api_Model->updates('orders',$data,$wheredata);
	        if ($result) {
				$data1['result']="true";
				$data1['msg']="Order Canceled";
				$data1['order_status']=5;
				$data1['cancel_reason']=$cancel_reason;
	        }else{
	          	$data1['result']="false";
	        	$data1['msg']="somthing went wrong ";
	        }
	    }else{
	    	$data1['result']="false";
	    	$data1['msg']="parameter required user_id,order_id,cancel_reason";
	    }
	    echo json_encode($data1);
    }


    /*
    @get profile profile section start
    */
    public function get_user_profile()
    {
      $user_id = $this->input->post('user_id');
      extract($_POST);
      if(isset($user_id))
      {

	        if($this->User_Model->is_record_exist('users','id',"{$user_id}"))
	        {

	          	$res =$this->User_Model->select_single_row('users','id',$user_id);
				if($res)
				{
					$json['result'] = "true";
					$json['msg'] = "User profile details.";
					$json['data'] = $res;
				}else{
					$json['result'] = "false";
					$json['msg'] = "Something went wrong.";
				}
			}else{
				$json['result'] = "false";
				$json['msg'] = "User id not exist.";
			}
		}else{
			$json['result'] = "false";
			$json['msg'] = "Please give parameters(user_id)";
		}
		echo json_encode($json);
	}


	/*
  @submit changes profile
  */
	public function user_update_profile()
	{
		extract($_POST);
		if(isset($user_id) && isset($mobile) && isset($name))
		{
			if(!$this->User_Model->is_record_exist_update('users','mobile', "{$mobile}",$user_id))
          	{
	          	if(!empty($_FILES['image']['name'])){
	          		$config['upload_path'] = 'assets/uploaded/users/';
	          		$config['allowed_types'] = 'jpg|jpeg|png';
	          		//Load upload library and initialize configuration
	          		$this->load->library('upload',$config);
	          		$this->upload->initialize($config);
	          		if($this->upload->do_upload('image')){
	          			$uploadData = $this->upload->data();
	          			$image = $uploadData['file_name'];
	          		}else{
	          			$image = '';
	          		}
	          		$post_data['image'] = $image;
	          	}
	          	$post_data['name'] = $name;
	          	$post_data['mobile'] = $mobile;
	          	// $post_data['email'] = $email;
	          	// $post_data['address'] = $address;
	          	$update =  $this->User_Model->updateData('users',$post_data, $user_id);
	          	if($update)
	          	{
	          		$json['result'] = "true";
	          		$json['msg'] = "profile data.";
	          		$json['data']=  $this->User_Model->select_single_row('users','id',$user_id);
	          	}else{
	          		$json['result'] = "false";
	          		$json['msg'] = "Something went wrong.";
	          	}
			}else{
				$json['result'] = "false" ;
				$json['msg'] = "Mobile number already exits. Please Try another.";
			}
		}else{
			$json['result'] = "false";
			$json['msg'] = "Please give parameters(user_id,mobile,name,image(optional))";
		}
		echo json_encode($json);
  	}


    public function faq(){

      $user_id= $this->input->post("user_id");

    if($user_id)

    {
      $res=$this->db->query("SELECT id,question,answer FROM faq WHERE type='user' ")->result();
      if ($res) {
        $data['result'] ="true";
          $data['msg']    ="faq data";
          $data['data']    =$res;
      }else{
        $data['result'] ="true";
        $data['msg'] ="data not found";
      }
    }else{
      $data['result'] ="true";
      $data['msg'] ="user_id required";
    }
  echo json_encode($data);
  }


  public function ReOrder(){
    extract($_POST);
    if (isset($user_id) && isset($ordersID) ){

      $getOrderdata = $this->db->query('SELECT * FROM orders WHERE ordersID="'.$ordersID.'" ')->row();

      $user_id=$getOrderdata->user_id;
      $vendor_id=$getOrderdata->vendor_id;
      $addon=$getOrderdata->addon;
      $addon_price=$getOrderdata->addon_price;
      //$addon_price=$getOrderdata->addon_price;
      $product_id=$getOrderdata->product_id;
      $image=$getOrderdata->image;
      $name=$getOrderdata->name;
      $price=$getOrderdata->price;
      $quanitity=$getOrderdata->quanitity;


      $data=array(
        'user_id'=>$user_id,
        'vendor_id'=>$vendor_id,
        'addon'=>$addon,
        'addon_price'=>$addon_price,
        'product_id'=>$product_id,
        'image'=>$image,
        'name'=>$name,
        'price'=>$price,
        'quantity'=>$quanitity,
        'final_amount'=>$quanitity*$price
      );
      $result=$this->Api_Model->insertAllData('cart',$data);
      if ($result) {
        $json['result']="true";
        $json['msg']="Re-order success";
      }else{
        $json['result']="false";
        $json['msg']="something went wrong";
      }
    }else{
      $json['result']="false";
      $json['msg']="parameter required user_id,ordersID";
    }
    echo json_encode($json);
  }


  	//coupon code apply
  	public function apply_coupon(){
	    $data1=array();
	    $this->form_validation->set_rules('user_id','user_id','trim|required');
	    $this->form_validation->set_rules('code','code','trim|required');
	    if($this->form_validation->run()==false){
	      $data['result']="false";
	      $data['msg']="param user_id,code";
	    } else{
	      $user_id=$this->input->post('user_id');
	      $code=$this->input->post('code');
	      $total_pay=0;
	      $price=0;
	      $data=$this->db->query('select * from cart where user_id='.$user_id.'')->result();
	      if($data):
	      	$qua=0;
	      	$total_pay1=0;
	      	foreach($data as $value):

	      		if(!$value->addon==0):
		            $addarray= explode(',',$value->addon);
		            $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();
		            foreach($dataaddon as $value1):
		            $price+=$value1->price;
		            endforeach;
	          	endif;
	           	$total_pay1+=$value->final_amount;
	          	$total_pay+=$value->final_amount;
				$qua+=$value->quantity;
	    	endforeach;
	        $total_pay+=$price;
	        $delivery_charge=0;
	        $selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC limit 1")->row();
	        $selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;
	        if ($selectDeliverycharge) {
	          $delivery_charge=$selectOrderDeliverycharges;
	          $total_pay+=$delivery_charge;
	        }else{
	          $total_pay;
	        }
			//if($price !=""){
	    		//$pri=$price;
	    	//}else{
	    		//$pri=0;
			//}

			//$coupandata=$this->db->query('select t1.*,t2.cupon_price from apply_coupan as t1  LEFT JOIN cupon_code as t2 ON t1.coupan_id = t2.id  where user_id='.$user_id.'')->row();
			//if($coupandata){
			    //$coupandiscount= $coupandata->cupon_price;
			//}
			//else
			//{
			    //$coupandiscount= 0;
			//}

			$coupandata=$this->db->query('select t1.*,t2.cupon_price from apply_coupan as t1  LEFT JOIN cupon_code as t2 ON t1.coupan_id = t2.id  where t1.user_id='.$user_id.' and t2.code="'.$code.'"   ');
			if ($coupandata->num_rows() ==0) {
				if ($total_pay >200) {
	        	$newarray=array(
	          'cart_iteam'=>count($data),
	          'id'=>$value->id,
	          'total_quantity'=>$qua,
	          'total_pay'=>$total_pay,
	          'sub_total'=>$total_pay1,
	          'delivery_charge'=>$delivery_charge,
	          'add_on_price'=>$price,
	          'total_discount'=>0,
	          'final_amount'=>$total_pay
	          //'final_amount'=>$total_pay
	        );
	        	$data1['result']="true";
	        $data1['msg']="Billing Detail";
	        $data1['data']=$newarray;
	        }else{
	        	$newarray=array(
	          'cart_iteam'=>count($data),
	          'id'=>$value->id,
	          'total_quantity'=>$qua,
	          'total_pay'=>$total_pay,
	          'sub_total'=>$total_pay1,
	          'delivery_charge'=>$delivery_charge,
	          'add_on_price'=>$price,
	          'total_discount'=>0,
	          'final_amount'=>20
	          //'final_amount'=>$total_pay
	        );
			$data1['result']="false";
			$data1['msg']="Code Invalid";
			$data1['data']=$newarray;
	        }
			}else{
				$data1['result']="false";
	        	$data1['msg']="Code alredy applied";
			}



			//$data1['result']="true";
	        //$data1['msg']="Billing Detail";
	        //$data1['data']=$newarray;


    	else:
	      $data1['result']="false";
	      $data1['msg']="somthing went wrong ";
	      //$data1['data']=array();
	      endif;
	    }
	    echo json_encode($data1);
  	}

  	//apply_coupan
  	public function apply_coupan12() {
       	header('Content-Type: application/json');
     	$user_id = (trim($this->input->post('user_id')));
     	$coupan_code = (trim($this->input->post('coupan_code')));
        if($user_id == "" || $coupan_code == "" ){
          $data['result'] = "false";
          $data['msg'] = "Please enter required field(user_id,coupan_code)";
        }else{
			$this->db->select('*');
			$this->db->from('apply_coupan');
			$this->db->where('user_id',$user_id);
			$query = $this->db->get();
	      	if ($query->num_rows() == 0) {

		      	/*    $coupandata=$this->db->query('select * from cupon_code where code='.$coupan_code.'')->row();*/

		        $this->db->select('*');
		        $this->db->from('cupon_code');
		        $this->db->where('code',$coupan_code);
		        $query1 = $this->db->get();

				if ($query1->num_rows() > 0) {
		           		$coupandata =$query1->row();
						$data=array(
						'user_id'=>$user_id,
						'coupan_id'=>$coupandata->id
						);

					$result= $this->db->insert('apply_coupan',$data);

					if($result){
					   $data['result']="true";
					   $data['msg']="successfully added";
					}else{
					   $data['result']="false";
					   $data['msg']="Somethings went Wrong";
					}
			    }

		   	else{
		        $data['result']="false";
	           	$data['msg']="please valid coupan code";
		    }

	    }
	    else{
	        $data['result']="false";
	       	$data['msg']="Already apply coupon";
	    }
    }

    	echo json_encode($data);

 	}


 	/*
    @apply coupon code final
  	*/
    public function apply_coupan(){

        $currentdate = date('Y-m-d H:i:s');

        $date = date("Y-m-d H:i:s", strtotime($currentdate));

        extract($_POST);

        if(isset($user_id) && isset($coupan_code) && isset($cart_total))

        {

          $check_promo = $this->Api_Model->is_record_exist('cupon_code','code', $coupan_code);

          if($check_promo)

          {

            $get_promo = $this->Api_Model->select_single_row('cupon_code','code', $coupan_code);

            if($get_promo)

            {

            	$expiry = $get_promo->expired_at;

             	if($expiry!='')

             	{

                    if ($date < $expiry) {

                        $user_already_used = $this->Api_Model->user_already_used_promo($user_id,$coupan_code);

                        if($cart_total > 200){
                            if(!$user_already_used)
                            {
                            	$datas=array('user_id'=>$user_id,'code'=>$coupan_code);
                               $this->db->insert('apply_coupan',$datas);
                              $discount = $get_promo->cupon_price;
                              $discount_amt = $cart_total-$discount;
                              $dis_count_data = array(
                              	'payble_amount'=>$discount_amt,
                              	'discount_amt'=> $discount
                              );
                              $data_result['data'] = $dis_count_data;
                              $data_result['result'] ='true';
                              $data_result['msg'] ='Successfully applied.';
                            }else{
                              $data_result['result'] ='false';
                              $data_result['msg'] ='You have already used this code';
                            }
                        }else{
                            $data_result['result'] ='false';
                            $data_result['msg'] ='Sorry Please puchased at least amount Rs: 200';
                        }


                    }else{
                        $data_result['result'] ='false';
                        $data_result['msg'] ='Code date expired';
                    }
                }else
                {
                    $user_already_used = $this->Api_Model->user_already_used_promo($user_id,$coupan_code);
                  if(!$user_already_used)
                  {
                  	$datas=array('user_id'=>$user_id,'code'=>$coupan_code);
                               $this->db->insert('apply_coupan',$datas);
                    $discount = $get_promo->cupon_price;
                    $discount_amt = $cart_total - $discount;
                    $dis_count_data = array(
                    	'payble_amount'=>$discount,
                    	'discount_amt'=> $discount
                    );
                    $data_result['data'] = $dis_count_data;
                    $data_result['result'] ='true';
                    $data_result['msg'] ='Successfully applied.';
                  }else{
                    $data_result['result'] ='false';
                    $data_result['msg'] ='You have already used this code';
                  }

                }

            }else{
              $data_result['result'] ='false';
              $data_result['msg'] ='Invalid Codes';
            }
        }else{
            $data_result['result'] ='false';
            $data_result['msg'] ='Invalid Code';
        }
    }else{
  		$data_result['result'] ='false';
        $data_result['msg'] ='Please give parameters(user_id,coupan_code,cart_total)';
    }
    echo json_encode($data_result);
  }

  //remove_coupon
  // public function remove_coupon(){
	//     extract($_POST);
	//     if (!empty($user_id) && !empty($coupon_id)) {
	//       $dataexist=$this->db->query("SELECT id,user_id FROM `apply_coupan` WHERE apply_coupan.user_id=$user_id AND apply_coupan.id=$coupon_id ");
	//       	if (!empty($dataexist)) {
	//       		$this->db->query("DELETE FROM apply_coupan WHERE id=".$coupon_id." ");
  //
	// 	          $json['result']="true";
	// 	          $json['msg']="Removed coupon";
	// 	        }else{
	// 	        	$json['result']="false";
	// 	        	$json['msg']="Sorry Not Remove";
	// 	        }
  //
	// 	}else{
	// 		$json['result']="false";
	// 		$json['msg']="parameter required user_id,coupon_id";
	// 	}
	// 	echo json_encode($json);
	// }




	/*11/3/20*/
	/*get accepted order by driver*/
	function deliveryBoy_accepted_orders(){
    	$delivery_boy_id=$this->input->post('delivery_boy_id');

    	if (isset($delivery_boy_id)) {
      		$result=$this->Api_Model->deliveryAcceptedBoyOrder($delivery_boy_id);
          foreach ($result as $key => $value) {
            $value->total_item=$this->Api_Model->total_item($value->ordersID);
          }
			if ($result) {

			$json['result']="true";
			$json['msg']="orders list";
			$json['data']=$result;
			}else{
			$json['result']="false";
			$json['msg']="not data";
			}
	    }else{
	      $json['result']="false";
	      $json['msg']="parameter required delivery_boy_id";
	    }
    	echo json_encode($json);
  	}



	function deliveryBoy_accepted_orders_details(){
    $delivery_boy_id=$this->input->post('delivery_boy_id');
    $ordersID=$this->input->post('ordersID');
    if (isset($delivery_boy_id) && !empty($ordersID)) {
      $result=$this->Api_Model->deliveryAcceptedBoyOrderDetail($delivery_boy_id,$ordersID);
      foreach($result as $value){
        $wheredata=array('ordersID'=>$ordersID);
     	$sub=  $this->Api_Model->selectData('orders',$wheredata);

     	if(!empty($sub)){
			$amo=[];
			$add_on=[];
			$quanitity=[];
       		foreach($sub as $sub1){
				$amo1 =$sub1->price*$sub1->quanitity;
				$amo[]=$amo1;
				$add_on[]=$sub1->addon_price;
				$quanitity[]=$sub1->quanitity;

			}

			$selectDeliverycharge=$this->db->query("select id,delivery_charge from order_delivery_charge order by id DESC  limit 1")->row();
			$selectOrderDeliverycharges=$selectDeliverycharge->delivery_charge;
			$arrya_sum=array_sum($amo);
			$arrya_add_on=array_sum($add_on);
			$qua=array_sum($quanitity);
			$ad_on_plus=$arrya_sum+$arrya_add_on;
			$final_amo=$arrya_sum+$arrya_add_on+$selectOrderDeliverycharges;

	     }else{



	         $final_amo=0;

	     }



     $array[]=array(

           'order_id'=>$result->order_id,

           'ordersID'=>$result->ordersID,

           'order_status'=>$result->order_status,

           'orderid'=>$result->orderid,

           'shop_name'=>$result->shop_name,

           'user_id'=>$result->user_id,

           'vendor_id'=>$result->vendor_id,



           'addon'=>$result->addon    ,

           'addon_price'=>$result->addon_price    ,

           'quanitity'=>$qua,

           'total'=>$final_amo,

           'sub_total'=>$final_amo,

           'payment_method'=>$result->payment_method,

           'transaction_id'=>$result->transaction_id,

           'order_date'=>$result->created_date,

           'vendor_name'=>$result->vendor_name,

           'vendor_contact'=>$result->vendor_contact,

           'vendor_address'=>$result->vendor_address,

           'shop_lat'=>$result->shop_lat,

           'shop_lang'=>$result->shop_lang,

           'name'=>$result->name,

           'mobile'=>$result->mobile,

           'address'=>$result->address,

           'image'=>$result->image,

           'trax_id'=>$result->trax_id,

           'instruction'=>$result->instruction,

           'food_prepare_time'=>$result->food_prepare_time,

           'extra_maintance_fee'=>$result->extra_maintance_fee,

           'extra_maintance_detail'=>$result->extra_maintance_detail,

           'delivery_charge'=>$result->delivery_charge,

           'type'=>$result->type,

           'otp'=>$result->otp,

           'otp_verify'=>$result->otp,

           'address_id'=>$result->address_id,

           'cancel_reason'=>$result->cancel_reason,

           'product_id'=>$result->product_id,

           're_order'=>$result->re_order,

           'modified_at'=>$result->modified_at,

           'delivery_boy_id'=>$result->delivery_boy_id,

           'delivery_fee'=>$result->delivery_charge,

           'lat'=>$result->lat,

           'lng'=>$result->lng,

            );







      }



      if ($result) {

        $json['result']="true";



        $json['msg']="orders list";



        $json['data']=$result;



      }else{



        $json['result']="false";



        $json['msg']="not data";



      }



    }else{



      $json['result']="false";



      $json['msg']="parameter required delivery_boy_id,ordersID";



    }



    echo json_encode($json);



  }

  	//user chating
   	function user_chating(){
   		extract($_POST);
	   	if (isset($user_id) && isset($message) && isset($vendor_id) && isset($ordersID)) {
	   		$datas=array(
	   			'user_id'=>$user_id,
	   			'message'=>$message,
          'ordersID'=>$ordersID,
	   			'type'=>'User',
	   			'vendor_id'=>$vendor_id
	   		);
	   		$res=$this->db->insert('chat_list',$datas);
	   		if ($res) {
	   			$json['result']="true";
	   			$json['msg']="Msg sent";
	   			$json['chat_list']=$this->Api_Model->ChatList($user_id,$vendor_id,$ordersID);
	   		}else{
	   			$json['result']="false";
	   			$json['msg']="sorry something went wrong";
	   		}
	   	}else{
	   		$json['result']="false";
	   		$json['msg']="parameter required user_id,message,vendor_id,ordersID";
	   	}
	   	echo json_encode($json);
   	}


   //user chating list
   function user_chating_list(){
   	extract($_POST);
   	if (isset($user_id) && isset($vendor_id) && isset($ordersID)) {
   		$res=$this->Api_Model->ChatList($user_id,$vendor_id,$ordersID);
   		if ($res) {
   			$json['result']="true";
   			$json['msg']="Msg list";
   			$json['data']=$res;
   		}else{
   			$json['result']="false";
   			$json['msg']="sorry something went wrong";
   		}
   	}else{
   		$json['result']="false";
   		$json['msg']="parameter required user_id,vendor_id";
   	}
   	echo json_encode($json);
   }


   	/*complete order data*/
	public function get_user_notification(){
	    $user_id=$this->input->post('user_id');
	    if (isset($user_id)) {
	      $wheredata=array('reciever_id'=>$user_id,'receiver_type'=>'customer');
	      $userNotification=$this->Api_Model->selectData('notifications',$wheredata,'id desc');
	      if ($userNotification) {
	        $json['result']="true";
	        $json['msg']="All customer notifictaion";
	        $json['datas']=$userNotification;
	      }else{
	        $json['result']="false";
	        $json['msg']="sorry not any notification";
	      }
	    }else{
	      $json['result']="false";
	      $json['msg']="parameter required user_id";
	    }
	    echo json_encode($json);
	}

	/*get Notification*/
	public function get_driver_notification(){
	    $delivery_boy_id=$this->input->post('delivery_boy_id');
	    if (isset($delivery_boy_id) ) {
	    $deliveryBoyNotification=$this->Api_Model->selectNotification($delivery_boy_id);
	    if ($deliveryBoyNotification) {
	        $json['result']="true";
	        $json['msg']="All delivery boy notifictaion";
	        $json['datas']=$deliveryBoyNotification;
	      }else{
	        $json['result']="false";
	        $json['msg']="sorry not any notification";
	      }
	    }else{
	      $json['result']="false";
	      $json['msg']="parameter required delivery_boy_id";
	    }
	    echo json_encode($json);
	}


	//Order Details
	public function OrderDetails(){
      extract($_POST);
      $this->form_validation->set_rules('ordersID','ordersID','trim|required');
      if($this->form_validation->run()==false){
        $data['result']=false;
        $data['msg']="ordersID required parameter";
      }else{
        $result=$this->Api_Model->Get_order_list($ordersID);
        $resultDri=$this->Api_Model->get_driver_order();
        $result22=$this->Api_Model->Get_order_list1();
        $dataaddon=array();
        $total_amo=0;
        $add_on=0;
        foreach($result as $value):
          	if (!$value->addon==0) {
	            $addarray= explode(',',$value->addon);
	            $dataaddon=$this->db->query('select id,product_name,price from addon_product where id IN ('.$value->addon.')')->result();

            	$amo=((int)$value->price *$value->quanitity)+$value->addon_price;

            	$amo1=((int)$value->price *$value->quanitity);
				$resultnew[]=
				array(
				'id'=>$value->order_id,
				'orderid'=>$value->orderid,
				'instruction'=>$value->instruction,
				'user_id'=>$value->user_id,
				'product_id'=>$value->product_id,
				'vendor_id'=>$value->vendor_id,
				'name'=>$value->name,
				'image'=>$value->image,
				'addon_price'=>$value->addon_price,
				'price'=>(int)$value->price,
				'quantity'=>$value->quanitity,
				'total_amount'=>$amo,
				'addon'=>$dataaddon
				);
          	}else{
	            $amo1=(int)$value->price *$value->quanitity;
	            $resultnew[]=array(
	                'id'=>$value->order_id,
	                'orderid'=>$value->orderid,
	                'instruction'=>$value->instruction,
	                'user_id'=>$value->user_id,
	                'product_id'=>$value->product_id,
	                'vendor_id'=>$value->vendor_id,
	                'name'=>$value->name,
	                'image'=>$value->image,
	                'addon_price'=>$value->addon_price,
	                'price'=>(int)$value->price,
	                'quantity'=>$value->quanitity,
	                'total_amount'=>$amo1,

           		);

          	}
	        $total_amo+=$amo1;
	        $add_on+=$value->addon_price;
	        $final_amo=$add_on+$total_amo;
        endforeach;
        if($result){
			$data['result']="true";
			$data['total_amount']=$final_amo;
			$data['final_amount']=$final_amo+$result22->delivery_charge;
			$data['ordersID']=$result22->ordersID;
			$data['order_exta_status']=($result22->extra_maintance_fee!=='0') ? ('1') : ('0');
			$data['food_prepare_time']=$result22->food_prepare_time;
			$data['type']=$result22->type;
			$data['order_status']=$result22->order_status;
			$data['delivery_charge']=$result22->delivery_charge;
			$data['payment_method']=$result22->payment_method;
			$data['order_date']=date('d-m-Y', strtotime(str_replace('/', '-', $result22->created_date)));
			$data['driver_lat']=$resultDri->de_lat;
			$data['driver_lng']=$resultDri->de_lng;
			$data['driver_name']=$resultDri->delivery_boy_name;
			$data['driver_mobile']=$resultDri->mobile;
			$data['data']=$resultnew;
        }else{
	        $data['result']="false";
	        $data['msg']="Order empty";
  		}

    }
    echo json_encode($data);
    }




    //Order Details
	public function driver_order_details(){
      extract($_POST);
      $this->form_validation->set_rules('ordersID','ordersID','trim|required');
      $this->form_validation->set_rules('delivery_boy_id','delivery_boy_id','trim|required');
      if($this->form_validation->run()==false){
        $data['result']=false;
        $data['msg']="ordersID, delivery_boy_id required parameter";
      }else{
        $result=$this->Api_Model->Get_order_list($ordersID);
        $resultDri=$this->Api_Model->get_users_order();
        $result22=$this->Api_Model->Get_order_list1();
        $dataaddon=array();
        $total_amo=0;
        $add_on=0;
        foreach($result as $value):
          	if (!$value->addon==0) {
	            $addarray= explode(',',$value->addon);
	            $dataaddon=$this->db->query('select id,product_name,price from addon_product where id IN ('.$value->addon.')')->result();

            	$amo=((int)$value->price *$value->quanitity)+$value->addon_price;

            	$amo1=((int)$value->price *$value->quanitity);
				$resultnew[]=
				array(
				'id'=>$value->order_id,
				'orderid'=>$value->orderid,
				'instruction'=>$value->instruction,
				'user_id'=>$value->user_id,
				'product_id'=>$value->product_id,
				'vendor_id'=>$value->vendor_id,
				'name'=>$value->name,
				'image'=>$value->image,
				'addon_price'=>$value->addon_price,
				'price'=>(int)$value->price,
				'quantity'=>$value->quanitity,
				'total_amount'=>$amo,
				'addon'=>$dataaddon
				);
          	}else{
	            $amo1=(int)$value->price *$value->quanitity;
	            $resultnew[]=array(
	                'id'=>$value->order_id,
	                'orderid'=>$value->orderid,
	                'instruction'=>$value->instruction,
	                'user_id'=>$value->user_id,
	                'product_id'=>$value->product_id,
	                'vendor_id'=>$value->vendor_id,
	                'name'=>$value->name,
	                'image'=>$value->image,
	                'addon_price'=>$value->addon_price,
	                'price'=>(int)$value->price,
	                'quantity'=>$value->quanitity,
	                'total_amount'=>$amo1,

           		);

          	}
	        $total_amo+=$amo1;
	        $add_on+=$value->addon_price;
	        $final_amo=$add_on+$total_amo;
        endforeach;
        if($result){
			$data['result']="true";
			$data['total_amount']=$final_amo;
			$data['final_amount']=$final_amo+$result22->delivery_charge;
			$data['ordersID']=$result22->ordersID;
			$data['order_exta_status']=($result22->extra_maintance_fee!=='0') ? ('1') : ('0');
			$data['food_prepare_time']=$result22->food_prepare_time;
			$data['type']=$result22->type;
			$data['order_status']=$result22->order_status;
			$data['delivery_charge']=$result22->delivery_charge;
			$data['payment_method']=$result22->payment_method;
			$data['order_date']=date('d-m-Y', strtotime(str_replace('/', '-', $result22->created_date)));
			$data['user_lat']=$resultDri->u_lat;
			$data['user_lng']=$resultDri->u_lng;
			$data['user_name']=$resultDri->u_name;
			$data['user_mobile']=$resultDri->mobile;
			$data['data']=$resultnew;
        }else{
	        $data['result']="false";
	        $data['msg']="Order empty";
  		}

    }
    echo json_encode($data);
    }



//   new 21/04/2021

             public function delivery_boy_complete_earning() {


         $delivery_boy_id = (trim($this->input->post('delivery_boy_id')));

        if($delivery_boy_id == ""){

          $data['result'] = "false";

          $data['msg'] = "Please enter required field(delivery_boy_id)";

        }else{

            if ($getResult = $this->Api_Model->delivery_boy_complete_earning($delivery_boy_id))

            {
            $data['result'] = "true";

          $data['data'] = $getResult;

          $data['msg'] = "success";

            }

             else {

                $data['result'] = "false";

                $data['msg'] = "record not found";
            }
    }

    echo json_encode($data);

     }



  public function driver_notifiction_count() {

    $delivery_boy_id = (trim($this->input->post('delivery_boy_id')));

    if($delivery_boy_id == ""){
      $data['result'] = "false";
      $data['msg'] = "Please enter required field(delivery_boy_id)";
    }else{

    $res=$this->Admin_model->select_single_row('orders','delivery_boy_id',$delivery_boy_id);

      $notification_count = $this->db->query("SELECT count(id) as notification_count FROM `notifications` WHERE app_seen_status=0 AND types='New Order Assign' AND reciever_id=$delivery_boy_id ")->row()->notification_count;
      if ($res)
      {
        $data['result'] = "true";
        $data['completed_order'] = $this->db->query("SELECT count(order_id) as completed_order FROM `orders` WHERE order_status=4 AND delivery_boy_id=$delivery_boy_id")->row()->completed_order;
        $data['notification_count'] = ($notification_count!=="") ? $notification_count : 0;
        $data['msg'] = "success";
      }else {
        $data['result'] = "false";
        $data['msg'] = "record not found";
      }
    }

    echo json_encode($data);

  }



  public function filter_AtoZ(){
    //$category_id=$this->input->post('category_id');
    $type=$this->input->post('type');
    $lat=$this->input->post('lat');
    $lng=$this->input->post('lng');
    //$lang_id=$this->input->post('lang_id');
    if (isset($type) && isset($lat) && isset($lng)) {
       // $result=$this->Api_Model->getShopListByAtoZs($category_id);
      $result=$this->Api_Model->getShopListByAtoZs($type,$lat,$lng);
      if ($result) {
        $json['result']="true";
       $json['msg']="shop list by albhabetic";
       $json['data']=$result;
     }else{
        $json['result']="false";
        $json['msg']="sorry not any list";
      }
    }else{
      $json['result']="false";
      $json['msg']="parameter required type,lat,lng";
    }
    echo json_encode($json);
  }



  public function shop_fav_list11(){

    extract($_POST);

    $this->form_validation->set_rules('user_id','user_id','trim|required');
    $this->form_validation->set_rules('lang_id','lang_id','trim|required');

    if($this->form_validation->run()==false){



             $data1['result']="false";



             $data1['msg']=strip_tags($this->form_validation->error_string());



      } else{







          $user_id=$this->input->post('user_id');
          $lang_id=$this->input->post('lang_id');



          $data=$this->db->query('Select * from fev_shop Where  user_id='.$user_id.' ')->result();

        // print_r($data);exit();

          if($data):

             foreach($data as $value):

                    if($lang_id==1){
                        $shop[]=$this->db->query('Select shop_details.id,shop_details.Service_id,shop_details.vendor_id,shop_details.category_id,shop_details.ar_shop_name as shop_name,shop_details.shop_image,shop_details.logo,shop_details.ar_shop_about as shop_about,shop_details.status,shop_details.fav_status,shop_details.delivery,shop_details.address,shop_details.lat,shop_details.lang,shop_details.created_date from shop_details Where id ='.$value->shop_id.'')->row();
                    }else{
                        $shop[]=$this->db->query('Select shop_details.id,shop_details.Service_id,shop_details.vendor_id,shop_details.category_id,shop_details.shop_name,shop_details.shop_image,shop_details.logo,shop_details.shop_about,shop_details.status,shop_details.fav_status,shop_details.delivery,shop_details.address,shop_details.lat,shop_details.lang,shop_details.created_date from shop_details Where id ='.$value->shop_id.'')->row();
                    }

                 endforeach;





           $data1['result']="true";



           $data1['msg']="shop faverite list ";



           $data1['data']=$shop;



           else:



          $data1['result']="true";



           $data1['msg']="somthing went wrong ";



           $data1['data']=array();



           endif;



    }



      echo json_encode($data1);



  }



  public function shop_details(){

    $lat=$this->input->post('lat');
    $lng=$this->input->post('lng');
    //$type=$this->input->post('type');
    $user_id=$this->input->post('user_id');
    $shop_id=$this->input->post('shop_id');

    if (isset($user_id) && isset($lat) && isset($lng) && isset($shop_id)) {

        $result=$this->Api_Model->shopByLatLon($lat,$lng,$shop_id);

        $result->rating=($this->Api_Model->ratingShopId($result->id))?round($this->Api_Model->ratingShopId($result->id)):0;
        $result->features=$this->Api_Model->featuresList($shop_id);


      if ($result) {
        $data1['result']="true";
        $data1['msg']="shop detail";
        $data1['data']=$result;
      }else{
        $data1['result']="false";
        $data1['msg']="sorry shop not found";
      }
    }else{
      $data1['result']="false";
      $data1['msg']="parameter required user_id,lat,lng,shop_id";
    }
    echo json_encode($data1);
  }


  public function restro_features_list(){

      $res=$this->db->query("SELECT * FROM restro_features")->result();
      if ($res) {
        $data['result'] ="true";
          $data['msg']    ="Restro features list";
          $data['data']    =$res;
      }else{
        $data['result'] ="true";
        $data['msg'] ="data not found";
      }

  echo json_encode($data);
  }
  public function categoryList(){
    extract($_POST);

        $result=$this->Api_Model->get_shop_categories();
        if ($result) {
          $data1['result']="true";
          $data1['msg']="category list";
          $data1['data']=$result;
        }else{
          $data1['result']="false";
          $data1['msg']="sorry not any list";
        }

    echo json_encode($data1);
  }

  //driver

  public function deliveryBoyCompletedOrderHistory(){
    $delivery_boy_id=$this->input->post('delivery_boy_id');
    if (isset($delivery_boy_id)) {
      $result=$this->Api_Model->GetDeliveryCompletedOrderList();
      //print_r($result);exit();
      $dataaddon=array();
      foreach($result as $value):
        //$value->shops=$this->Api_Model->vendorDetails($value->vendor_id);

        if(!$value->addon==0):
          $addarray= explode(',',$value->addon);
          $dataaddon=$this->db->query('select * from addon_product where id IN ('.$value->addon.')')->result();
        endif;

        $resultnew[]=array(
        'id'=>$value->order_id,
        'ordersID'=>$value->ordersID,
        'delivery_boy_id'=>$value->delivery_boy_id,
        'product_id'=>$value->product_id,
        'shop_name'=>$value->shop_name,
        'shop_address'=>$value->shop_address,
        'shop_image'=>$value->shop_image,
        'vendor_id'=>$value->vendor_id,
        'shop_id'=>$value->shop_id,
        'name'=>$value->name,
        'image'=>$value->image,
        'user_id'=>$value->user_id,
        'user_name'=>$value->user_name,
        'user_image'=>$value->user_image,
        'user_address'=>$value->user_address,
        'addon_price'=>$value->addon_price,
        'price'=>$value->price,
        'quantity'=>$value->quanitity,
        'delivery_charge'=>$value->delivery_charge,
        'modified_at'=>$value->modified_at,
        'total'=>$value->total,
        'total_amount'=>$value->sub_total,
        'final_amount'=>$value->sub_total+$value->addon_price,
        'order_status'=>$value->order_status,
        // 'user_rating_status'=>$value->user_rating_status=$this->Api_Model->shopRateByUser($user_id,$value->shop_id),
        'addon'=>$dataaddon
        );
      endforeach;
      if($result){
        $data['result']="true";
        $data['msg']="Completed/Canceled Orders";
        $data['data']=$resultnew;
      }else{
        $data['result']="false";
        $data['msg']="Not any Completed";
      }
    }else{
      $data['result']="false";
      $data['msg']="parameter required delivery_boy_id";
    }
    echo json_encode($data);
  }

  public function seen_notification(){
    extract($_POST);
    if (isset($delivery_boy_id)) {
      // $this->db->where('app_seen_status',1);
      $where=array('reciever_id'=>$delivery_boy_id,'receiver_type'=>'Delivery Boy');
      $datas=array('app_seen_status'=>1);
      $app_seen_status=$this->Api_Model->updates('notifications',$datas,$where);
      if ($app_seen_status) {
        $json['result']="true";
        $json['msg']="seen all notification";
      }else{
        $json['result']="false";
        $json['msg']="sorry something went wrong";
      }
    }else{
      $json['result']="false";
      $json['msg']="parameter required delivery_boy_id";
    }
    echo json_encode($json);
  }


  public function shop_rating_review_list(){
    $shop_id=$this->input->post('shop_id');
    if (isset($shop_id)) {
      $result=$this->Api_Model->selectByshopId($shop_id);
      if ($result) {
        $data['result']="true";
        $data['msg']="Shop rating review list";
        $data['total_rating']=($this->Api_Model->Totalrating($shop_id))?round($this->Api_Model->Totalrating($shop_id),1):0;
        $data['avg_rating']=($this->Api_Model->ratingShopId($shop_id))?round($this->Api_Model->ratingShopId($shop_id),1):0;
        $data['rating5']=($this->Api_Model->s_avg_rating5($shop_id))?round($this->Api_Model->s_avg_rating5($shop_id),1):0;
        $data['rating4']=($this->Api_Model->s_avg_rating4($shop_id))?round($this->Api_Model->s_avg_rating4($shop_id),1):0;
        $data['rating3']=($this->Api_Model->s_avg_rating3($shop_id))?round($this->Api_Model->s_avg_rating3($shop_id),1):0;
        $data['rating2']=($this->Api_Model->s_avg_rating2($shop_id))?round($this->Api_Model->s_avg_rating2($shop_id),1):0;
        $data['rating1']=($this->Api_Model->s_avg_rating1($shop_id))?round($this->Api_Model->s_avg_rating1($shop_id),1):0;
        $data['data']=$result;
      }else{
        $data['result']="false";
        $data['msg']="Not any rating review";
      }
    }else{
      $data['result']="false";
      $data['msg']="parameter required shop_id";
    }
    echo json_encode($data);
  }

  public function product_ingridient(){
    extract($_POST);
    if (isset($product_id)) {
      $res=$this->Api_Model->ProductIngrident($product_id);
      if ($res) {
        $json['result']="true";
        $json['msg']="All ingrident in product";
        $json['data']=$res;
      }else{
        $json['result']="false";
      $json['msg']="sorry not found";
      }
    }else{
      $json['result']="false";
      $json['msg']="parameter required product_id";
    }
    echo json_encode($json);
  }






  public function search_restorent()
    {
      $lat  = $this->input->post('lat');
      $lang = $this->input->post('lang');


      if(isset($lat) && isset($lang))
      {
          $result = $this->Api_Model->search_restorent($lat,$lang);

          if($result)
          {
              $data['result'] = "true";
              $data['msg']    = "All Restaurant List!";
              $data['data']   = $result;
          }
          else
          {
              $data['result'] = "false";
              $data['msg']    = "no record found!";
          }
      }
      else
      {
           $data['result'] = "false";
           $data['msg'] = "parameter required lat,lang";
      }

      echo json_encode($data);

    }






   public function getcurrentversion()
   {
       $results = $this->Api_Model->getversion();



       if($results)
          {
              $data['result'] = "true";
              $data['msg']    = "Current Version!";
              $data['data']   = $results->current_version;
          }
          else
          {
              $data['result'] = "false";
              $data['msg']    = "no version found!";
          }

          echo json_encode($data);
   }





   public function cancel_table_booking()
   {
       $table_id  = $this->input->post('table_id');
       $user_id   = $this->input->post('user_id');


      if(isset($table_id) && isset($user_id))
      {


          $this->db->where("id", $table_id);
          $this->db->where("user_id", $user_id);
          $result = $this->db->delete("resto_table_book");



          if($this->db->affected_rows() > 0)
          {
              $data['result'] = "true";
              $data['msg']    = "Table Booking Cancel!";
          }
          else
          {
              $data['result'] = "false";
              $data['msg']    = "No booking table found!";
          }
      }
      else
      {
           $data['result'] = "false";
           $data['msg']    = "parameter required table_id,user_id";
      }

      echo json_encode($data);
   }






   public function get_payment_method()
   {
       $where = array(
           'payment_method.status' => 1
           );

       $result = $this->Api_Model->get_payment_method("payment_method",$where);

       if($result)
       {
           $json['result'] = "true";
           $json['msg']    = "All Payment method!";
           $json['path']   = base_url()."assets/payment/";
           $json['data']   = $result;
       }
       else
       {
           $json['result'] = "false";
           $json['msg']    = "No Payment method!";
       }

       echo json_encode($json);
   }








     public function get_transport_state()
     {
         $result = $this->Api_Model->get_List('transport_state');

         if($result)
         {
             $json['result'] = "true";
             $json['msg']    = "All state";
             $json['data']   = $result;
         }
         else
         {
             $json['result'] = "false";
             $json['msg']    = "No state!";
         }

         echo json_encode($json);
     }






     public function get_transport_area()
     {
         $state_id = $this->input->post('state_id');

         if(isset($state_id))
         {
             $result = $this->Api_Model->get_area_list('areas',$state_id);

             if($result)
             {
                 $json['result'] = "true";
                 $json['msg']    = "All areas";
                 $json['data']   = $result;
             }
             else
             {
                 $json['result'] = "false";
                 $json['msg']    = "No area found";
             }
         }
         else
         {
             $json['result'] = "false";
             $json['msg']    = "parameter required state_id";
         }

         echo json_encode($json);
     }









     public function get_transportation()
     {
         $area_id = $this->input->post('area_id');

         if(isset($area_id))
         {
             $result = $this->Api_Model->get_transportation_list('transportation',$area_id);

             if($result)
             {
                 $json['result'] = "true";
                 $json['msg']    = "All transportation";
                 $json['data']   = $result;
             }
             else
             {
                 $json['result'] = "false";
                 $json['msg']    = "No transportation found";
             }
         }
         else
         {
             $json['result'] = "false";
             $json['msg']    = "parameter required area_id";
         }

         echo json_encode($json);
     }
     
     
     
     
     
     public function delete_product_image()
     {
       
         $image_id   = $this->input->post('image_id');
         
         if(isset($image_id))
         {
             $where = array(
                 'id' => $image_id,
                 );
             
             $result = $this->Api_Model->deleteDatavv('product_images',$where); 
             
             if($result)
             {
                 $this->db->where("product_images.id",$image_id);
                 $this->db->select("product_images.image");
                 $this->db->from("product_images");
                 $rryy = $this->db->get()->row();
           
           
                 if($rryy)
                 {
                     
                    if($rryy->image && file_exists('assets/images/'.$rryy->image))
                    {
                        unlink('assets/images/'.$rryy->image);
               
               
                    }
   
             
                }
                 
                 
                 
                 $json['result'] = "true";
                 $json['msg']    = "Image deleted";
             }
             else
             {
                 $json['result'] = "false";
                 $json['msg']    = "image_id invalid";
             }
             
         }
         else
         {
             $json['result'] = "false";
             $json['msg']    = "parameter required image_id";
         }
         
          echo json_encode($json);
     }
     
     
     
    //  public function update_transportation_status()
    //  {
    //      $order_id           = $this->input->post('order_id');
    //      $transport_state_id = $this->input->post('transport_state_id');
    //      $transport_area_id  = $this->input->post('transport_area_id');
    //      $transportation_id  = $this->input->post('transportation_id');
    //      $transport_status   = $this->input->post('transport_status');
         
         
    //      if(isset($order_id) && isset($transport_state_id) && isset($transport_area_id) && isset($transportation_id) && isset($transport_status))
    //      {
    //          $wheredata = array(
    //             'id' => $user_id 
    //             );
                
            
    //         $data = array(
    //             'transport_state_id' => $transport_state_id,
    //             'transport_area_id' => $transport_area_id,
    //             'transportation_id' => $transportation_id,
    //             'transport_status' => $transport_status,
    //             );
             
             
    //          $result = $this->Api_Model->update($wheredata,"orders",$data);
             
             
    //          if($result)
    //          {
    //              $json['result'] = "true";
    //              $json['msg']    = "Order placed successfully";
    //          }
    //          else
    //          {
    //              $json['result'] = "false";
    //              $json['msg']    = "something went wrong";
    //          }
             
    //      }
    //      else
    //      {
    //          $json['result'] = "false";
    //          $json['msg']    = "parameter required order_id,transport_state_id,transport_area_id,transportation_id,transport_status(true or false)";
    //      }
         
         
    //      echo json_encode($json);
         
         
    //  }
     
     
     
     
     



}
