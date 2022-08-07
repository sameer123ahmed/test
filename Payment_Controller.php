 public function checkouttt()
   {
       
       
       
        $price             = $this->input->post('price');
        $course_trainig_id = $this->input->post('id');
        $command_name      = $this->input->post('command_name');
        $user_id           = $this->session->userdata('id');
        $payment_method    = $this->input->post('payment_method');
        $training_cert     = $this->input->post('training_cert');
        
        
        $course_id         = implode(',',$command_name);
        
        
        
        if($training_cert == "on")
        {
            $cert = 1;
        }
        else
        {
            $cert = "";
        }
       
        
        
        
        
        
        if($payment_method == "cash")
        {
                    $data = array(
                
                'course_trainig_id' => $course_trainig_id,
                'course_id' => $course_id,
                'user_id' => $user_id,
                'payment_method' => $payment_method,
                'total_amount' => $price,
                'certificate_status' => $cert,
                'date_time' => date('Y-m-d H:i:s',time()),
                );
                
            
            $this->Common->insertData('course_trining_order',$data);
            
            
            $this->data['page']    = 'payment_success';
            $this->data['content'] = 'pages/home/payment_success';
            $this->load->view('website/tamplate', $this->data);
        }
        else
        {
            $api = new Api('rzp_test_mgV7Z91dyUfpuG', 'aQGPZU9CBfZ2K1k44DhkMUaa');
    /**
     * You can calculate payment amount as per your logic
     * Always set the amount from backend for security reasons
     */
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
   
    
    
    
    //  $datas['user_detail']=$this->db->query('select * from user where id=1')->row(); ;
    //  $PASS['datas'] = $datas['user_detail'];
     
     
        $PASS['price'] = $price;
        $PASS['course_trainig_id'] = $course_trainig_id;
        $PASS['command_name'] = $command_name;
        $PASS['user_id'] = $user_id;
        $PASS['payment_method'] = $payment_method;
        $PASS['training_cert'] = $training_cert;
        $PASS['course_id'] = $course_id;
        $PASS['cert'] = $cert;

    
    
        $this->load->view('website/pages/home/razorpay_checkout_new',$PASS);
        }
       
       
       
       
    
  }
    
    