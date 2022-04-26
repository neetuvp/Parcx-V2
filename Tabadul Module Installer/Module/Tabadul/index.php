<?php
session_start();
$_SESSION['user']="";
if (isset($_GET['logout']))
  {  
  session_destroy();
  unset($_SESSION['user']);  
  }
?>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Parcx Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="plugins/jquery/jquery-3.3.1.min.js"></script>

  <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="plugins/font-awesome/css/ionicons.min.css">

  <link rel="stylesheet" href="dist/css/parcx.min.css">
  <link rel="stylesheet" href="dist/css/styles.css">
  <link rel="stylesheet" href="plugins/iCheck/square/blue.css">
  <!-- Google Font: Source Sans Pro -->
 <!-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">-->
</head>

<body class="hold-transition login-page">
  <div class="login-box">

    <div class="login-logo">
      <a href="#"><b>Parcx</b> Login</a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <form class="m-0">    
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <div class="input-group-text"><span class="fa fa-user"></span></div>
            </div>
            <input type="email" id="username" class="form-control" placeholder="Username">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <div class="input-group-text"><span class="fa fa-lock"></span></div>
            </div>
            <input type="password" id="password" class="form-control" placeholder="Password">
          </div>

          <div class="row">
            <div class="col-8">
              <div id="messagebox"></div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block btn-flat" id="signin">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- iCheck -->
  <script src="plugins/iCheck/icheck.min.js"></script>

  <script>
    $(function () {    

      //sign in
      $('#signin').on('click', function () {
          var username=$("#username").val();
          var password=$("#password").val();
          var data={username:username,password:password};
          var temp=JSON.stringify(data);
          //alert(temp);

          $.post("ajax/user.php?type=1",temp, function (data) {
            data=JSON.parse(data);           
          if(data["status"]==200)
            window.location = "home.php";
          else
            $("#messagebox").html(data["message"]);
          })
          .fail(function(){
            $("#messagebox").html("Cant connected to server");
          });

      });

       
    })
  </script>
</body>

</html>
