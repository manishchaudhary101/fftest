
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <link rel="shortcut icon" href="images/favicon.png" />
      <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
      <link src=" https://fonts.googleapis.com/css?family=Roboto" />
      <title>Reset Password</title>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link href="<?php echo asset('css/toastr.css'); ?>" rel="stylesheet">
      <style>
         body {
         background-image: url('/images/Login-03-01.jpg');
         background-repeat: no-repeat;
         background-attachment: fixed; 
         background-size: 100% 100%;
         }
      </style>
      <?php $logoUrl = asset('images/logo_full_white.png');
         $bgUrl = asset('images/Login-03-01.jpg');
         ?>
   </head>
   <body >
      <script src="<?php echo asset('js/toastr.js'); ?>"></script>
      <script>
       
         function getParam( name )
         {
             name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
             var regexS = "[\\?&]"+name+"=([^&#]*)";
             var regex = new RegExp( regexS );
             var results = regex.exec( window.location.href );
             return results[1];
         }
         var param=getParam("id");
         if(param==1)
         {
            toast.alert('Password changed successfully');
         
            window.setTimeout(function() {
    window.location.href = 'https://app.fourthfrontier.com/#/login';
}, 3000);
         }else if(param==0)
         {
            
            toast.success('Something went wrong'); 
            window.setTimeout(function() {
    window.location.href = 'https://app.fourthfrontier.com/#/login';
}, 3000);
         }
         
      </script>
   </body>
</html>