<?php
include('includes/common.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo URL; ?>dist/img/icon/favicon.gif" type="image/x-icon">
        <title>PARCX Parking Management System V 02.01.20211015</title>


        <link href="dist/fonts/noto-sans.css" rel="stylesheet">
        <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link rel="stylesheet" href="dist/css/adminlte.min.css">
    </head>
    <body class="login-page bg-white">


        <div class="container bg-white">
            <div class="row">
                <div class="col">
                    <img src="dist/img/PXCentralServer.jpg" >
                </div>
                <div class="col">


                    <div class="login-box">
                        <!-- /.login-logo -->
                        <div class="card card-outline mt-3">
                            <div class="card-header text-center">
                                <img src="dist/img/parcx_logo_web_small_dark.png" >
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light" role="alert" id="messagebox">
                                    Sign in to start your session
                                </div>

                                <form  method="post" id="login-form">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" required placeholder="Username" id="username" name="Username">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" required placeholder="Password" id="password" name="Password">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8">

                                        </div>
                                        <!-- /.col -->
                                        <div class="col-4">
                                            <button type="submit" class="btn btn-primary btn-block" id="login">Sign In</button>
                                        </div>
                                        <!-- /.col -->
                                    </div>
                                </form>       

                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.login-box -->

                </div> <!-- col-->


            </div> <!-- Row -->

            <!-- Footer . Login page -->
            <div class="row mt-5">
                <div class="col text-center">

                    PARCX Parking Management System V 02.01.20211015| Copyright 2021 | All Rights Reserved | AFME , Dubai  

                </div> <!-- End . Col -->


            </div> <!-- End.Row-->     




        </div> <!-- Container-->


        <!-- jQuery -->
        <script src="plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App -->
        <script src="dist/js/adminlte.min.js"></script>        
        <script src="<?=URL?>dist/js/jquery.validate.js"></script>
        <script src="dist/js/index.js"></script>
    </body>
</html>
