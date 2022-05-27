</ul>

<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="nav-item d-none" id="full-screen">
       <a class="nav-link" data-widget="fullscreen"  href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    <li class="nav-item">
    <select id="language">
            <option>English</option>
            <option>Arabic</option>
            <option>Spanish</option>
        </select>
    </li>
    <li class="nav-item">       
        <a href="<?=URL;?>index.php?logout='1'" class="btn btn-info logout-button">
            <i class="fas fa-sign-out-alt mr-1"></i>
            <span id="logout"><?= isset($json["logout"])?$json["logout"]:"Logout";?></span>
        </a>
    </li>
</ul>

</nav>
<!-- /.navbar -->

<script>
    $("#full-screen").click(function(){
        $('[data-widget="pushmenu"]').click();
    });
</script>