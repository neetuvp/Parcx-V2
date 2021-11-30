<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <div class="brand-link">            
        <img src="<?= URL ?>dist/img/PARCX.png"> 
    </div>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= URL ?>dist/img/user.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info" class="d-block info" style="padding-left:8px;">
                <a href="" class="d-block" style="padding-left:8px;">
                    <div><?php echo $_SESSION['user']; ?></div></a>
                 <input type="hidden" id="login_user_name" value="<?php echo $_SESSION["user"] ?>">
                <input type="hidden" id="login_user_id" value="<?php echo $_SESSION["userId"] ?>">
                <input type="hidden" id="login_user_role" value="<?php echo $_SESSION["userRollName"] ?>">
                <input type="hidden" id="login_user_role_id" value="<?php echo $_SESSION["userRollId"] ?>">	
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" id="sidebar">

                <?php
                $data["user_id"] = $_SESSION['userId'];
                $data["user_role_id"] = $_SESSION['userRollId'];
                $data["url"] = URL;
                $data["task"] = 1;
                $data["lang"] = $_SESSION["language"];
                parcxV2UserManagement($data);
                ?>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>


<div class="modal fade" id="message-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content card-outline card-info">
            <div class="modal-header">
                <h5 class="modal-title" id="message-modal-heading"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="message-modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>                
            </div>
        </div>
    </div>
</div>
<script>

    function alertMessage(data)
    {
        $("#message-modal-body").text(data);
        $("#message-modal").modal('show');
    }
</script>