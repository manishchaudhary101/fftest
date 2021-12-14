<?php $logoUrl = asset('images/logo_full.png');
$bgUrl = asset('images/Login-03-01.jpg');
?>

<!DOCTYPE html>
<html>
<head>
<link href="<?php echo asset('css/toastr.css'); ?>" rel="stylesheet">
    <title>Account Activation</title>
    <style>
    body{
        background-image: url('<?php echo $bgUrl; ?>');
            background-size: cover;
            height: 100vh;
            width: 100%;
    }
</style>

</head>
<body style="text-align: center;">


      <script src="<?php echo asset('js/toastr.js'); ?>"></script>
      <script>
         
          toast.success('Something went wrong');
            window.setTimeout(function() {
    window.location.href = 'https://app.fourthfrontier.com/#/login';
}, 3000);
         
         
      </script>
</body>
</html>

<?php //die; ?>


