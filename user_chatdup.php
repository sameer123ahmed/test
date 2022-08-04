
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.7.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.7.1/firebase-database.js"></script>



<!-- TODO: Add SDKs for Firebase products that you want to use
     https://firebase.google.com/docs/web/setup#available-libraries -->
<script src="https://www.gstatic.com/firebasejs/8.7.1/firebase-analytics.js"></script>





<input type="hidden" id="user_id" value="<?php echo $datas->user_id; ?>" >
<input type="hidden" id="fcm_id" value="<?php echo $datas->fcm_id; ?>" >
<input type="hidden" id="admin_id" value="<?php echo $this->session->userdata('login'); ?>" >
<input type="hidden" id="user_image" value="<?php echo $datas->user_image; ?>" >
<input type="hidden" id="date_time" value="<?php echo date("H:i", time()); ?>" >









<script>
  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  var firebaseConfig = {
    apiKey: "AIzaSyAS59ufUYMeBPXRcvJb-WG3o-udQJIuBdI",
    authDomain: "dog-training-3f34a.firebaseapp.com",
    databaseURL: "https://dog-training-3f34a-default-rtdb.firebaseio.com",
    projectId: "dog-training-3f34a",
    storageBucket: "dog-training-3f34a.appspot.com",
    messagingSenderId: "266199684108",
    appId: "1:266199684108:web:eb05b4d750c477f9b18a29",
    measurementId: "G-4EXQ8FMKN9"
  };
  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  firebase.analytics();



 // send message //

 
  var fcm_id        = document.getElementById("fcm_id").value;
  var userID        = document.getElementById("user_id").value;
  var adminID       = document.getElementById("admin_id").value;
  var time          = document.getElementById("date_time").value;
  var user_image    = document.getElementById("user_image").value;
  

  var username = "Sami";
  var image = "";
  var recording = "";
  var status = "";
  var username = "";
  var video = "";


   var imagebase64 = "";
   var videobase64 = "";
   
  


function imageSend(element)
{
    
        var oFReader = new FileReader();
        
        
        oFReader.readAsDataURL(document.getElementById("imgupload").files[0]);

        oFReader.onload = function (oFREvent) {
            document.getElementById("imagesss").src = oFREvent.target.result;
            $('#c_type').val('image');
            
            $("#messaage").css("display", "none");
            $("#videosss").css("display", "none");
            $("#imagesss").css("display", "block");
            //$("#id").css("display", "block");
        };
        
        
        
         
        
        var file = element.files[0];
        
        var reader = new FileReader(); 
        
        
        reader.onload = function (oFREvent) {
        
               $('#imagesss').attr('src', oFREvent.target.result);

        };
    
    reader.onloadend = function() {  
        imagebase64 = reader.result;
       
    //      var nameArr = imagebase64.split(',');
    //   console.log(nameArr[1]);
    }  
    reader.readAsDataURL(file);
        
        
 
 
   
}



var video_file = "";




function videoSend(element)
{
    
        ///Find Send Button and Disable it
         $(".send_btn").attr("disabled", true);
//check karo
        
        var formData = new FormData();
          
          formData.append('video', document.getElementById("videoupload").files[0]);
          
          
          
          $.ajax({
              type: "POST",
              url: "<?php echo base_url() ?>Api/chat_video",
              data: formData,
              processData: false,
              contentType: false,
              success: function(response) {
                  
                  
                  console.log(response);
                var obj = JSON.parse(response);
                
                console.log(obj);
                
                if (obj.result == true) {
                    
                      

                       video_file = obj.video_url+obj.datas.video_file;
                       $(".send_btn").attr("disabled", false);
                      
                      
                  }
                  else if(obj.result == false){
                    // in case video failed then you have to  set content type text
                    // c_type = text kar kar dena
                    $(".send_btn").attr("disabled", true);

                    
                
                  }

 
              },
              error: function(errResponse) {
                  console.log(errResponse);
                  
              }
          });
    
    
  
    
        var oFReader = new FileReader();
        

       oFReader.readAsDataURL(document.getElementById("videoupload").files[0]);
        
        oFReader.onload = function (oFREvent) {
        document.getElementById("videosss").src = oFREvent.target.result;
         
         
         

            $('#c_type').val('vid');
            
            $("#messaage").css("display", "none");
            $("#imagesss").css("display", "none");
            $("#videosss").css("display", "block");
            //$("#id").css("display", "block");
        };
        
        
        
         
        
        var file = element.files[0];
        
        var reader = new FileReader(); 
        
        
        reader.onload = function (oFREvent) {
        
               $('#videosss').attr('src', oFREvent.target.result);

        };
        
        
        
        //// reader. ...... esme on error find karna kese hoga then usme 
        
        // in case video failed then you have to  set content type text
                    // c_type = text kar kar dena
                 //   $(".send_btn").attr("disabled", true);
    
    reader.onloadend = function() {  
         videobase64 = reader.result;
        
        // abc = reader.result;
        
        
        
        
        // var videobase64 = abc.replace("-","");
      
        // console.log(videobase64);
        
   
    }  
    reader.readAsDataURL(file);
        
        
 
 
   
}



















function sendMessagess()
{
 
    
    
  var c_type = "text";
  var c_type = document.getElementById("c_type").value;
  
  if(c_type == "image")
  {
      
      var c_type = "image";
  }

  if(c_type == "vid")
  {
      
      var c_type = "vid";
  }
  
  
  
  
  var message = document.getElementById("messaage").value;
  
 
 
      if(c_type == "text")
      {
       
          if(message != "")
          {
    
         firebase.database().ref("chat").push().set({
          "receiveerID": userID,
           "senderID": adminID,
          "message": message,
          "command_id": 'Help',
          "image": '',
          "plan_order_id": 'Help',
          "recording": recording,
          "status": status,
          "time": time,
          "username": username,
          "video": video,
        //   "c_type": c_type,
  }).then(function() {
     $('input[name=messaa]').val("");
     
    
  }); 
  
          }
          
      }
      
      if(c_type == "image")
      {
          firebase.database().ref("chat").push().set({
           "receiveerID": userID,
           "senderID": adminID,
           "message": 'Image',
           "command_id": 'Help',
           "image": imagebase64,
           "plan_order_id": 'Help',
           "recording": recording,
           "status": status,
           "time": time,
           "username": username,
           "video": '',
           //"c_type": 'image',
  }).then(function() {
     $('input[name=messaa]').val("");
  });
  
  
      }
      
  
  
  
           
            
            
            
            
            
    
    if(c_type == "vid")
      {
         
          firebase.database().ref("chat").push().set({
           "receiveerID": userID,
           "senderID": adminID,
           "message": 'Vid',
           "command_id": 'Help',
           "image": '',
           "plan_order_id": 'Help',
           "recording": recording,
           "status": status,
           "time": time,
           "username": username,
           "video": video_file,
           //"c_type": 'image',
  }).then(function() {
     $('input[name=messaa]').val("");
  });
  
  
  
 // console.log(videobase64);
  
      }
      
      
            video_file = "";
            $('#c_type').val('text');
            $("#imagesss").css("display", "none");
            $("#videosss").css("display", "none");
            $("#messaage").css("display", "block");

  
  

  
  
    // $.ajax({
    //             url: '<?php echo base_url();?>admin/send_notification',
    //             type: 'post',
    //             // data: {fcm_id:fcm_id,message:message},
    //             data: {fcm_id:fcm_id,course_name:course_name},
    //             success: function (result)
    //             {
                 
                  
    //             }
                
    // });

  return false;
}



  
  
  
  // show for user and admin messages show //
firebase.database().ref("chat").on("child_added", function(snapshot) {
  var html = "";
  var img = "";
  
  
         var image = snapshot.val().image;
         var video = snapshot.val().video;
         
    
        
         user_img = '<img src="<?php echo base_url() ?>uploads/userprofile/'+user_image+'" class="rounded-circle user_img_msg">';
         
  
         
     
     if(snapshot.val().message == "Image")
     {
        img = '<img style="height:150px"; src="data:image/jpeg;base64,'+image+'">';
     }
     
     else if(snapshot.val().message == "Vid")
     {
         img   = '<video width="280" height="220" controls>';
         img  += '<source src="'+video+'" type="video/mp4">';
         img  += '</video>';
     }
     
     else
     {
         img =snapshot.val().message;
     }
  
  
  
  if(snapshot.val().senderID == userID && snapshot.val().command_id == 'Help')
  {
      
      
        html += "<div class='d-flex justify-content-end mb-4'>";
        html += "<div class='msg_cotainer_send'>";
		html += img; 
		html += "<span class='msg_time_send'>";
		html += snapshot.val().time;
		html += "</span>";
	    html += "</div>";
	  	html += "<div class='img_cont_msg'>";
		html += user_img
		html += "</div>";
		html += "</div>";
    


                          
  }
  
  
  
 
  document.getElementById("messages").innerHTML += html;
});





// for admin message show //

firebase.database().ref("chat").on("child_added", function(snapshot) {
  var html = "";
  
  if(snapshot.val().senderID == adminID && snapshot.val().receiveerID == userID && snapshot.val().command_id == 'Help' && snapshot.val().plan_order_id == 'Help')
  {
      
      
      
      var html = "";
      var img = "";
  
  
         var image = snapshot.val().image;
         var video = snapshot.val().video;

      
        //  console.log(video);
         
     
     if(snapshot.val().message == "Image")
     {
        img = '<img style="height:150px"; src="'+image+'">';
     }
     
     else if(snapshot.val().message == "Vid")
     {
        // img = '<img style="height:150px"; src="'+video+'">';
         img   = '<video width="280" height="220" controls>';
         img  += '<source src="'+video+'">';
         img  += '</video>';
     }
     
     else
     {
         img =snapshot.val().message;
     }
      
      
      
     
    html += "<div class='d-flex justify-content-start mb-4'>";
	html += "<div class='img_cont_msg'>";
	html += "<img src='<?php echo base_url();?>assets/images/avatars/avatar-5.png' class='rounded-circle user_img_msg'>";
    html += "</div>";
    html += "<div class='msg_cotainer'>";
    html += img;
	html += "<span class='msg_time'>";
	html += snapshot.val().time;
	html += "</span>";
	html += "</div>";
	html += "</div>";
	
	

     
     
  }

  

  document.getElementById("messages").innerHTML += html;
});





    
    
</script>








<div class="html">
	<div class="body">
		


<div class="clearfix"></div>

    

  <div class="content-wrapper">

    <div class="container-fluid">

      <!-- Breadcrumb-->

     <div class="row pt-2 pb-2">

        <div class="col-sm-9">
            
        <div class="row">
            <!--<h4 class="page-title">User Name : <?php echo $datas->fullname; ?> (<?=$datas->userId;?>),</h4>-->
            
            <!--<h4 class=" page-title">Course Name : <?=$datas->course_name;?></h4>-->
            </div>

         <!--   <ol class="breadcrumb">-->

         <!--   <li class="breadcrumb-item"><a href="<?php echo base_url('Admin-Dashboard')?>">Dashboard</a></li>-->

         <!--   <li class="breadcrumb-item"><a href="<?php echo base_url('Admin-Chat-List')?>">Chat User List</a></li>-->

         <!--   <li class="breadcrumb-item active" aria-current="page">Chat User Message</li>-->
            
            
            
         <!--</ol>-->

       </div>

      

     </div>

    <!-- End Breadcrumb-->









<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"></script>


		

		<div class="container-fluid h-100">
			<div class="row justify-content-center h-100">
				
				<div class="col-md-12 col-xl-12 chat">
					<div class="card">
					    
					    
					    
					    <div class="card-header msg_head">
							<div class="d-flex bd-highlight">
								<div class="img_cont">
									<img src="<?php echo base_url() ?>uploads/userprofile/<?php echo $datas->user_image; ?>" class="rounded-circle user_img">
									<span class="online_icon"></span>
								</div>
								<div class="user_info">
									<span>Chat with <?php echo $datas->fullname; ?> (<?=$datas->userId;?>),</span><br>
									
								
									<!--<p>1767 Messages</p>-->
								</div>
								<!--<div class="video_cam">-->
								<!--	<span><i class="fas fa-video"></i></span>-->
								<!--	<span><i class="fas fa-phone"></i></span>-->
								<!--</div>-->
							</div>
							<!--<span id="action_menu_btn"><i class="fas fa-ellipsis-v"></i></span>-->
							<!--<div class="action_menu">-->
							<!--	<ul>-->
							<!--		<li><i class="fas fa-user-circle"></i> View profile</li>-->
							<!--		<li><i class="fas fa-users"></i> Add to close friends</li>-->
							<!--		<li><i class="fas fa-plus"></i> Add to group</li>-->
							<!--		<li><i class="fas fa-ban"></i> Block</li>-->
							<!--	</ul>-->
							<!--</div>-->
						</div>
					    
					    
					    
					    
						
						<div class="card-body msg_card_body">
						    
						    
						   
		
						    
						    
						    
						    
							
							<div id="messages"></div>
								
							<div id="messages"></div>
						</div>
						
						
				
						
						<form onsubmit="return sendMessagess();">
						    
						    <div class="card-footer">
						    
						    
						    <div class="input-group"> 
						    
							  <div class="input-group-append">
								<span class="input-group-text attach_btn"><i class="fas fa-paperclip mr-3" id="OpenImgUpload"></i>
							  
							<i class="fas fa-video-camera" id="OpenVideoUpload" ></i></span>
							  </div>
							  
							    
							    
			                    <div id="msg_content">
			                         <input id="c_type" style="width:60px" type="hidden" name="type" value="text" />
			                        <img style="display:none;width:150px;background-color:#7F7FD5"  id="imagesss"  >
			                        <video controls style="display:none;width:150px;background-color:#7F7FD5"  id="videosss"  ></video>
			                        
			                    </div>
			                    <input type="text" data-type="text" id="messaage" name="messaa" class="form-control type_msg" placeholder="Type your message..." autocomplete="off" />
								
							  <div class="input-group-append">
								<button type="submit" class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></button>
							  </div>
							  
								</div>
								
								
							</div>
						
								
						
						
						</form>
						
						<input type="file" id="imgupload" onChange="imageSend(this)"; style="display:none"/>
						<input type="file" id="videoupload" onChange="videoSend(this)"; style="display:none"/>

								
					
						 
						
					</div>  
					
					   
					
					
			
			    </div>
		    </div>
	    </div>


	

<style>
		.body,.html{
			height: 100%;
			margin: 0;
			background: #7F7FD5;
	       background: -webkit-linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5);
	        background: linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5);
		}

		.chat{
			margin-top: auto;
			margin-bottom: auto;
			
		}
		.card{
			height: 530px;
			border-radius: 15px !important;
			background-color: rgba(0,0,0,0.4) !important;
		
			
		
		}
		.contacts_body{
			padding:  0.75rem 0 !important;
			overflow-y: auto;
			white-space: nowrap;
		}
		.msg_card_body{
			overflow-y: auto;
			
			
		
		}
		.card-header{
			border-radius: 15px 15px 0 0 !important;
			border-bottom: 0 !important;
		}
	 .card-footer{
		border-radius: 0 0 15px 15px !important;
			border-top: 0 !important;
		margin-left: 20px;
		margin-right: 20px;
			
	}
		.container{
			align-content: center;
			overflow-y: hidden;
			
		}
		.search{
			border-radius: 15px 0 0 15px !important;
			background-color: rgba(0,0,0,0.3) !important;
			border:0 !important;
			color:white !important;
		}
		.search:focus{
		     box-shadow:none !important;
           outline:0px !important;
		}
		.type_msg{
			background-color: rgba(0,0,0,0.3) !important;
			border:0 !important;
			color:white !important;
			height: 60px !important;
			overflow-y: auto;
			
		   
		}
			.type_msg:focus{
		     box-shadow:none !important;
           outline:0px !important;
		}
		.attach_btn{
	border-radius: 15px 0 0 15px !important;
	background-color: rgba(0,0,0,0.3) !important;
			border:0 !important;
			color: white !important;
			cursor: pointer;
		}
		.send_btn{
	border-radius: 0 15px 15px 0 !important;
	background-color: rgba(0,0,0,0.3) !important;
			border:0 !important;
			color: white !important;
			cursor: pointer;
		}
		.search_btn{
			border-radius: 0 15px 15px 0 !important;
			background-color: rgba(0,0,0,0.3) !important;
			border:0 !important;
			color: white !important;
			cursor: pointer;
		}
		.contacts{
			list-style: none;
			padding: 0;
		}
		.contacts li{
			width: 100% !important;
			padding: 5px 10px;
			margin-bottom: 15px !important;
		}
	.active{
			background-color: rgba(0,0,0,0.3);
	}
		.user_img{
			height: 70px;
			width: 70px;
			border:1.5px solid #f5f6fa;
		
		}
		.user_img_msg{
			height: 40px;
			width: 40px;
			border:1.5px solid #f5f6fa;
		
		}
	.img_cont{
			position: relative;
			height: 70px;
			width: 70px;
	}
	.img_cont_msg{
			height: 40px;
			width: 40px;
	}
	.online_icon{
		position: absolute;
		height: 15px;
		width:15px;
		background-color: #4cd137;
		border-radius: 50%;
		bottom: 0.2em;
		right: 0.4em;
		border:1.5px solid white;
	}
	.offline{
		background-color: #c23616 !important;
	}
	.user_info{
		margin-top: auto;
		margin-bottom: auto;
		margin-left: 15px;
	}
	.user_info span{
		font-size: 20px;
		color: white;
	}
	.user_info p{
	font-size: 10px;
	color: rgba(255,255,255,0.6);
	}
	.video_cam{
		margin-left: 50px;
		margin-top: 5px;
	}
	.video_cam span{
		color: white;
		font-size: 20px;
		cursor: pointer;
		margin-right: 20px;
	}
	.msg_cotainer{
		margin-top: auto;
		margin-bottom: auto;
		margin-left: 10px;
		border-radius: 25px;
		background-color: #82ccdd;
		padding: 10px;
		position: relative;
	}
	.msg_cotainer_send{
		margin-top: auto;
		margin-bottom: auto;
		margin-right: 10px;
		border-radius: 25px;
		background-color: #78e08f;
		padding: 10px;
		position: relative;
	}
	.msg_time{
		position: absolute;
		left: 0;
		bottom: -15px;
		/*color: rgba(255,255,255,0.5);*/
		color: #fff;
		font-size: 10px;
	}
	.msg_time_send{
		position: absolute;
		right:0;
		bottom: -15px;
		/*color: rgba(255,255,255,0.5);*/
		color: #fff;
		font-size: 10px;
	}
	.msg_head{
		position: relative;
	}
	#action_menu_btn{
		position: absolute;
		right: 10px;
		top: 10px;
		color: white;
		cursor: pointer;
		font-size: 20px;
	}
	.action_menu{
		z-index: 1;
		position: absolute;
		padding: 15px 0;
		background-color: rgba(0,0,0,0.5);
		color: white;
		border-radius: 15px;
		top: 30px;
		right: 15px;
		display: none;
	}
	.action_menu ul{
		list-style: none;
		padding: 0;
	margin: 0;
	}
	.action_menu ul li{
		width: 100%;
		padding: 10px 15px;
		margin-bottom: 5px;
	}
	.action_menu ul li i{
		padding-right: 10px;
	
	}
	.action_menu ul li:hover{
		cursor: pointer;
		background-color: rgba(0,0,0,0.2);
	}
	@media(max-width: 576px){
	.contacts_card{
		margin-bottom: 15px !important;
	}
	}
	

</style>



<script>
		$(document).ready(function(){
$('#action_menu_btn').click(function(){
	$('.action_menu').toggle();
});
	});
</script>

<script>
    $('#OpenImgUpload').click(function(){ $('#imgupload').trigger('click'); });

</script>

<script>
    $('#OpenVideoUpload').click(function(){ $('#videoupload').trigger('click'); });

</script>











   </div>

    <!-- End container-fluid-->

    

    </div>







    <style type="text/css">.container{max-width:1170px; margin:auto;}

      img{ max-width:100%;}

      .inbox_people {

        background: #f8f8f8 none repeat scroll 0 0;

        float: left;

        overflow: hidden;

        width: 40%; border-right:1px solid #c4c4c4;

      }

      .inbox_msg {

        border: 1px solid #c4c4c4;

        clear: both;

        overflow: hidden;

      }

      .top_spac{ margin: 20px 0 0;}





      .recent_heading {float: left; width:40%;}

      .srch_bar {

        display: inline-block;

        text-align: right;

        width: 60%; padding:

      }

      .headind_srch{ padding:10px 29px 10px 20px; overflow:hidden; border-bottom:1px solid #c4c4c4;}



      .recent_heading h4 {

        color: #05728f;

        font-size: 21px;

        margin: auto;

      }

      .srch_bar input{ border:1px solid #cdcdcd; border-width:0 0 1px 0; width:80%; padding:2px 0 4px 6px; background:none;}

      .srch_bar .input-group-addon button {

        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;

        border: medium none;

        padding: 0;

        color: #707070;

        font-size: 18px;

      }

      .srch_bar .input-group-addon { margin: 0 0 0 -27px;}



      .chat_ib h5{ font-size:15px; color:#464646; margin:0 0 8px 0;}

      .chat_ib h5 span{ font-size:13px; float:right;}

      .chat_ib p{ font-size:14px; color:#989898; margin:auto}

      .chat_img {

        float: left;

        width: 11%;

      }

      .chat_ib {

        float: left;

        padding: 0 0 0 15px;

        width: 88%;

      }



      .chat_people{ overflow:hidden; clear:both;}

      .chat_list {

        border-bottom: 1px solid #c4c4c4;

        margin: 0;

        padding: 18px 16px 10px;

      }

      .inbox_chat { height: 550px; overflow-y: scroll;}



      .active_chat{ background:#ebebeb;}



      .incoming_msg_img {

        display: inline-block;

        width: 6%;

      }

      .received_msg {

        display: inline-block;

        padding: 0 0 0 10px;

        vertical-align: top;

        width: 92%;

       }

       .received_withd_msg p {

        /*background: #ebebeb none repeat scroll 0 0;*/

        background: #15101024 none repeat scroll 0 0;

        border-radius: 3px;

        color: #646464;

        font-size: 14px;

        margin: 0;

        padding: 5px 10px 5px 12px;

        width: 100%;

      }

      .time_date {

        color: #747474;

        display: block;

        font-size: 12px;

        margin: 8px 0 0;

      }

      .received_withd_msg { width: 57%;}

      .mesgs {

        float: left;

        padding: 30px 15px 0 25px;

        /*width: 60%;*/

        width: 100%;

      }



       .sent_msg p {

        /*background: #05728f none repeat scroll 0 0;*/

        background: #042954bf none repeat scroll 0 0;

        border-radius: 3px;

        font-size: 14px;

        margin: 0; color:#fff;

        padding: 5px 10px 5px 12px;

        width:100%;

      }

      .outgoing_msg{ overflow:hidden; margin:26px 0 26px;}

      .sent_msg {

        float: right;

        /*width: 46%;*/

        width: 50%;

      }

      .input_msg_write input {

       /* background: rgba(0, 0, 0, 0) none repeat scroll 0 0;*/

        background: rgba(0, 0, 0, 0.32) none repeat scroll 0 0;

        border: medium none;

        color: #4c4c4c;

        font-size: 15px;

        min-height: 48px;

        width: 100%;

      }



      .type_msg {border-top: 1px solid #c4c4c4;position: relative;}

      .msg_send_btn {

        background: #05728f none repeat scroll 0 0;

        border: medium none;

        border-radius: 50%;

        color: #fff;

        cursor: pointer;

        font-size: 17px;

        height: 33px;

        position: absolute;

        right: 0;

        top: 11px;

        width: 33px;

      }

      .messaging { padding: 0 0 50px 0;}

      .msg_history {

        /*height: 516px;*/

        /*height: 350px;*/

        overflow-y: auto;

      }

  </style>

















</div>

</div>







