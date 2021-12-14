<?php $logoUrl = asset('images/logo_full.png');
   $bgUrl = asset('images/Login-03-01.jpg');
   ?>
<html lang="en">
   <head>
      <meta charset="UTF-8" />
      <title>Email</title>
      <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
      <style>
        .img_backgrond {
            background-color: #F5F5F5;
            
            height: 100vh;
            width: 100%;
            padding: 30px;
        }

         .login-content{
        
         padding-bottom: 20px;
         /* -webkit-box-shadow: 0 0 30px #ccc;
         box-shadow: 0 0 30px #ccc; */
         background-repeat: no-repeat;
         background-size: 100% 100%;
         
         margin: auto;
         background: white;
         }
         body {
         font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
         }
         p {
         font-size: 13px;
         font-weight: 400;
         }
         .user-name {
         color: #289cdc;
         font-weight: 500;
         }
         @media screen and (max-width: 530px) {
      
      .login-content {
        width: 100%;
      }
    }
    @media screen and (min-width: 531px) {
      .login-content {
        max-width: 450px !important;
      }
      .login-content {
        max-width: 450px !important;
      }
    }
      </style>
   </head>
   <body>
   <div class="img_backgrond">

      <div class="row">
         <div class="col-lg-4 offset-lg-4 col-sm-6 offset-sm-3 col-xs-10 offset-xs-1 login-content mt-5">
            <div class="row">
               <div class="col-sm-12 mt-4">
                  <h2 class="text-center" style="
                     text-align: center;
                     " >
                     <img src="<?php echo $logoUrl; ?>"  alt="Fourth Frontier Logo" style="width:245px; margin-top: 2px; padding: 2px 14px;   height: 31px;">
                  </h2>
               </div>
               <div class="col-sm-12 mt-4" style="
                  padding: 0px 10px;
                  ">
                  <h3 style="
                     color: #000000;
                     " class="text-center">Change Password Request</h3>
               </div>
            </div>
            <p style="
               padding: 0px 10px;
               color: #000000;
               ">
               Hi <span style="color: #000000;" class="user-name"><?php echo $user->name; ?>,</span><br><br>
               A password change was requested for your account. If you made this request, please click the button below to change your password.
            </p>
            <div
               style="margin-top:30px;width:60%;margin:40px auto;text-align:center">
               <a
                  href=<?php echo url("users/change-password?user_id="); ?><?php echo $user->id; ?>&token=<?php echo $resetTokenData->reset_token; ?>
                  style="background-color:#9B2423;color:#fff;border-radius:4px;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px"
                  target="_blank">Change Password</a>
            </div>
            <br />
            <p style="
               padding: 0px 10px; color: #000000;
               ">
               If you didn't mean to change your password, or it wasn't you,
               please ignore this email, your password will not change.
            </p>
            <br />
           
            <p style="padding: 0px 10px;  color: #000000; margin:0px auto">
             Please don’t hesitate to contact us at <a style=" color: #000000;"
                  href="mailto:support@fourthfrontier.com"
                  target="_blank"
                  >support@fourthfrontier.com </a
                  >if you have questions or suggestions.
              
            </p>
            <br />
            <br />
            <p style="padding: 0px 10px;margin:0px auto">
               <span>~Fourth Frontier Team</span>
            </p>
         </div>
      </div>
   </div>
   </body>
</html>
<?php //die; ?>