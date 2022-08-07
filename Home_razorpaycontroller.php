<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'views/admin/razorpay/Razorpay.php';
use Razorpay\Api\Api;

class Home extends CI_Controller {


   public function __construct()
   {
   	  parent::__construct();
   	  $this->load->model('Home_Model');
   } 



   public function index()
   {
      $data['products'] = $this->Home_Model->get_products();

   	  $this->load->view('admin/home',$data);
   }




   public function checkout()
   {
   	  $id = $this->input->post('id');

      $this->db->where("products.id",$id);
      $this->db->select("*");
      $this->db->from("products");
      $rows = $this->db->get()->row();

      

      $price = $rows->price;
      $user_id = 50;
      $payment_method = "Online";
      


   	  $api = new Api('rzp_test_dDUJgMezW06TcB', '9dewjyp0AXhvDCs1EGPRwOCV');

     $_SESSION['payable_amount'] = $price;
     $razorpayOrder = $api->order->create(array(
      'receipt'         => rand(),
      'amount'          => $_SESSION['payable_amount'] * 100, // 2000 rupees in paise
      'currency'        => 'INR',
      'payment_capture' => 1 // auto capture
    ));
    $amount = $razorpayOrder['amount'];
    $razorpayOrderId = $razorpayOrder['id'];
    $_SESSION['razorpay_order_id'] = $razorpayOrderId;
    $data = $this->prepareData($amount,$razorpayOrderId,$user_id);
    
    
    
    
 
    
    
   
    
    
        $PASS['data'] =  $data;
        $PASS['price'] = $price;
        $PASS['user_id'] = $user_id;
        $PASS['payment_method'] = $payment_method;

        $PASS['product_id'] = $id;
       

    
    
        $this->load->view('admin/razorpay_checkout_new',$PASS);
   }









    public function prepareData($amount,$razorpayOrderId,$user_id)
  {
      
      

            $user_id = $user_id;
            $username = "sameer ahmed";
            $email    = "sameer@gmail.com";
            $mobile   = "9340018290";
      
      
      
    $data = array(
      "key" => 'rzp_test_dDUJgMezW06TcB',
      "amount" => $amount,
      "name" => "Codiant Website",
      "description" => "Codiant Website Payment",
      "image" => "https://sssdt.com/DogsTraining/main-css/img/logo/logo.jpg",
      "prefill" => array(
        "name"  => $username,
        "email"  => $email,
        "contact" => $mobile,
      ),
      "notes"  => array(
        "address"  => "Hello World",
        "merchant_order_id" => rand(),
      ),
      "theme"  => array(
        "color"  => "#F37254"
      ),
      "order_id" => $razorpayOrderId,
    );
    return $data;
  }










     public function verify()
  {
      
       $price             = $this->input->post('price');
       $user_id           = $this->input->post('user_id');
       $payment_method    = $this->input->post('payment_method');

       $product_id    = $this->input->post('product_id');
       
       
       

    
      
    $success = true;
    $error = "payment_failed";
    if (empty($_POST['razorpay_payment_id']) === false) {
      $api = new Api('rzp_test_dDUJgMezW06TcB', '9dewjyp0AXhvDCs1EGPRwOCV');
    try {
        $attributes = array(
          'razorpay_order_id' => $_SESSION['razorpay_order_id'],
          'razorpay_payment_id' => $_POST['razorpay_payment_id'],
          'razorpay_signature' => $_POST['razorpay_signature']
        );
        $api->utility->verifyPaymentSignature($attributes);
      } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay_Error : ' . $e->getMessage();
      }
    }
    if ($success === true) {
        
      
      
      
      
               $data1122 = array(
           
                'user_id' => $user_id,
                'product_id' => $product_id,
                'payment_method' => $payment_method,
                'total_amount' => $price,
                'date_time' => date('Y-m-d H:i:s',time()),
                );
                
            
            $this->db->insert('orders',$data1122);

      
      redirect(base_url().'Home');
    }
    else {
      echo "payment failed";
    }
  }
    
    
    
   
  


}
















//// views razorpay_checkout_new ////


<div>Do not press back button.Payment is processing...</div>

<a href="<?php echo base_url('Home'); ?>">Cancel Payment</a>



<button id="rzp-button1" style="display:none;">Pay with Razorpay</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.slim.min.js"></script>
<form name='razorpayform' action="<?php echo base_url().'Home/verify';?>" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >

    <input type="hidden" name="product_id" value="<?php echo $product_id ?>">
    <input type="hidden" name="price" value="<?php echo $price ?>">
    <input type="hidden" name="command_name" value="<?php echo $price ?>">
    <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
    <input type="hidden" name="payment_method" value="<?php echo $payment_method ?>">    
</form>
<script>
// Checkout details as a json
var options = <?php echo json_encode($data);?>;
/**
 * The entire list of Checkout fields is available at
 * https://docs.razorpay.com/docs/checkout-form#checkout-fields
 */
options.handler = function (response){
    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
    document.getElementById('razorpay_signature').value = response.razorpay_signature;
    document.razorpayform.submit();
};
// Boolean whether to show image inside a white frame. (default: true)
options.theme.image_padding = false;
options.modal = {
    ondismiss: function() {
        console.log("This code runs when the popup is closed");
    },
    // Boolean indicating whether pressing escape key 
    // should close the checkout form. (default: true)
    escape: true,
    // Boolean indicating whether clicking translucent blank
    // space outside checkout form should close the form. (default: false)
    backdropclose: false
};
var rzp = new Razorpay(options);
$(document).ready(function(){
  $("#rzp-button1").click();
   rzp.open();
    e.preventDefault();
});
</script>

//// end views ////




?>