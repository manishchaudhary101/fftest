<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 27-05-2019
 * Time: 12:19
 */


$logoUrl = asset('images/logo_full.png');
$bgUrl = asset('images/Login-03-01.jpg'); ?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <header class="main-header">
    </header>
    <!--/ .main-header -->
    <title>Patient Reset Password</title>
    <style>

        .login-content {
            margin-top:200px;
        }
        @media (max-width: 768px){
            .login-content {
                margin-top:15rem;
            }

        }
        body { 
        background: url('<?php echo $bgUrl; ?>') no-repeat center center fixed; 
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
         }
        /* .img_backgrond {
            background-image: url('<?php echo $bgUrl; ?>');
            background-size:cover;
            background-repeat:no-repeat;
            width: 100%;
            padding: 0;
            min-height: 627px;
        } */

        .img_background{
            min-height:100vh;
        }

        .img_background > div.row{
            margin:0
        }
        label{
            font-size: 14px !important;
        }
        ::-webkit-input-placeholder {
            font-size:14px;
        }
        label{
            font-size: 1.4rem;
            color: #ededed;
            font-weight: 400;
            text-transform:uppercase;
        }
        ::-webkit-input-placeholder {
            font-size:14px;
        }
        input{
            background-color:#000;
            border:1px solid #000;
            border-radius:5px;
            color:#fff;
            font-size:1.5rem;
        }
        input:active,.form-control:focus{
            background-color:#ddd;

        }
        .btn-success{
            font-size: 1rem;
            letter-spacing: 0px;
            color: white;
            font-weight: 700;
            text-align: center;
            background-color:black;
            width:100%;
            border-color:#fff;
            text-transform:uppercase;
        }
        .btn-success:not([disabled]):not(.disabled):active, .btn-success:not([disabled]):not(.disabled).active, .show > .btn-success.dropdown-toggle{

            background-color: #ddd;
            border-color: #ddd;
            -webkit-box-shadow: 0 0 0 0.2rem rgba(253, 253, 253, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(253, 253, 253, 0.5);
        }
        .btn-success:hover {

            background-color: #ddd;
            border-color: #ddd;
            color:black;
        }

        .login-hd{
            font-size: 2rem;
            letter-spacing: 2px;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
        }
        .forget-link{
            width: 335px;
            height: 41px;
            font-size: 13px;
            letter-spacing: 0px;
            line-height: 18px;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
        }
        .login-form{

            font-family: "Barlow",'Arial',sans-serif;
        }
        .form-group{
            margin-bottom:1rem;
        }
        .user-message{
            font-size: 14px;
            color: #fff;
            margin: .5rem;
        }
        .mail-notification
        {
            position: absolute;
    
    left: 40%;
    right: 60%;
  
   
    top: 0%;
    background-color: #DDD;
            /* position: fixed; */
            /* right:10px; */
            /* top:10px; */
            z-index: 9999;
            width: 400px;
            
            -webkit-animation: seconds 1.0s forwards;
            -webkit-animation-iteration-count: 1;
            -webkit-animation-delay: 5s;
            animation: seconds 1.0s forwards;
            animation-iteration-count: 1;
            animation-delay: 5s;
            color: #fff;
            border-radius:5px;

            box-shadow: 5px 5px 10px rgba(0, 0, 0, .3);
        } @keyframes seconds {
              0%   {opacity: 1;}
              90%  {opacity: 1;}
              100% {opacity: 0;}
          }
        @-webkit-keyframes seconds {
            0%   {opacity: 1;}
            90%  {opacity: 1;}
            100% {opacity: 0;}
        }
        .mail-notification> h3
        {
            font-size: 15px !important;
            font-weight: bold;
            display: block;
            margin: 10px 10px 10px 10px;
            padding: 10px 0px 10px 0px;
            text-align: center;
        }
        .err-color
        {
            background: #960202;
        }
        .success-color
        {
            background: #00A79D;
        }
    </style>
    <link href="https://fonts.googleapis.com/css?family=Barlow&display=swap" rel="stylesheet">
</head>
<body class="skin-blue sidebar-collapse fixed login-page">


<div class="img_backgrond">
    <div class="row">
        <div class="col-lg-4 offset-lg-1 col-sm-6 offset-sm-2 col-xs-10 offset-xs-1 login-content">
            <div class="login-form">
            <div class="row">
                <!-- <div class="col-sm-12 mt-4">
                    <h2 class="text-center">
                        <img src="<?php echo $logoUrl; ?>" style="
    width: 400px;
"  alt="Fourth Frontier Logo">
                    </h2>
                </div> -->
                <div class="col-sm-12 mt-4">
                    <h2 class="login-hd text-center">Reset Password</h2>
                </div>
            </div>
            <form class="passwordForm" id="pwdform" onsubmit="return false">
                <input type="hidden" name="user_id" id="id" value= "<?php echo $_REQUEST['user_id'];?>" >
                <input type="hidden" name="token" id="token" value= "<?php echo $_REQUEST['token'];?>" >
                <div class="col-md-12">
                    <p class="user-message"> * The Password is case sensitive.</p>
                    <div class="form-group">
                        <input type="password" name="password" id="password" placeholder="new password" required class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                            <input type="password" name="retype_password" id="re_password" placeholder="confirm password" required  class="form-control">
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <div class="form-group">
                        <input type="submit" onclick="submitForm()" value="Reset Password" class="btn btn-success" />
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>






<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo asset('js/toastr.js'); ?>"></script>
<script>

    function submitForm(){
        var empty;
        $('form > input').each(function() {
            if ($(this).val() == '' ) {
                empty = true;
            }
        });
        if(empty)
        {
            var div = document.createElement('div');
            div.innerHTML = "<h3>Please fill up the required fields..</h3>";
            // better to use CSS though - just set class
           // div.setAttribute('class', 'mail-notification err-color');
           toast.success("Please fill up the required fields..");
        }else
        {
            if(!document.getElementById("password").value && document.getElementById("password").value=="")
            {
               
              toast.success("Please enter your password..");
            }else if(!document.getElementById("re_password").value && document.getElementById("re_password").value=="")
            {
               
                toast.success("Please enter Confirm Password..");
            }else if(document.getElementById("re_password").value!=document.getElementById("password").value)
            {
               
               toast.success("Password and Confirm Password should be same...");

            }else if(document.getElementById("password").value.length<8 || document.getElementById("password").value.length>50 )
            {
               
               toast.success("Password should be of 8 characters...");
            }else
            {
                var fd = new FormData();
                fd.append('password', document.getElementById("password").value);
                fd.append('retype_password',document.getElementById("re_password").value);
                fd.append('user_id',document.getElementById("id").value);
                fd.append('token',document.getElementById("token").value);
                $.ajax({
                    type: "POST",
                    url: "https://"+window.location.hostname+"/users/change-password-controller",
                    data:fd,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status == true) {
                            var url="/users/confirmation-message"+"?id=1";
                            window.location.replace(url);
                            document.getElementById("password").value=="";
                            document.getElementById("re_password").value=="";

                        }
                    },error: function(xhr){
                        var path=window.location.hostname;
                        var url="/users/confirmation-message"+"?id=0";
                        window.location.replace(url);
                    }
                });
            }

        }

    }

</script>
<!--/  -->
</body>
</html>

<?php //die; ?>


