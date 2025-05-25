<?php

include 'inc/config.php';
include 'inc/connect.php';
date_default_timezone_set('Europe/Dublin');
session_start();
if (!isset($_SESSION['loggedin'])) {
	header('Location: login.php');
	exit;
}
if ($_SESSION['role'] != "Admin") {
  die();
}
?>
<!DOCTYPE html>
<html>
  <head> 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>zynexroot - Handlers</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">
    
    <link rel='stylesheet' href='https://unpkg.com/webkul-micron@1.1.6/dist/css/micron.min.css'>
    
    <link rel="stylesheet" href="css/font.css">
    
    <link rel="stylesheet" href="css/toastr.min.css">
    
    <link rel="stylesheet" href="css/flag-icon.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">
    
    <link rel="stylesheet" href="css/style.red.css" id="theme-stylesheet">
    
    <link rel="stylesheet" href="css/custom.css">
    
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
      .blink_me {
      animation: blinker 1s linear infinite;
      }
    @keyframes blinker {  
      50% {
         background-color: rgba(134, 77, 217, 0.3);
      }
    }
    </style>
  </head>
  <body>
    <header class="header">   
      <nav class="navbar navbar-expand-lg">
          
        <div class="container-fluid d-flex align-items-center justify-content-between">
          <div class="navbar-header">
            <a href="index.php" class="navbar-brand">
<div style="
    font-family: 'Montserrat', sans-serif; 
    font-weight: 700; 
    color: #dcdcdc; 
    font-size: 1.2rem; 
    display: inline-flex; 
    align-items: center; 
    gap: 6px;">
            <span style="
        color: #ff3f3f; 
        letter-spacing: 0.5px;font-size: 12px;"> i  </span><span style="
        color: #e0e0e0; 
        text-transform: capitalize; 
        letter-spacing: 0.5px;font-size: 12px;">
Panel
    </span>
    <small style="
        font-size: 0.65rem; 
        color: #aaaaaa; 
        margin-left: auto; 
        text-transform: uppercase;">
        v7 pro
    </small>

    <!-- Button for Telegram -->
    <a href="https://t.me/thecoderlord" target="_blank" style="
        background-color: #4CAF50; /* Green */
        border: none;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        font-size: 0.6rem;
        margin-left: 5px;
        border-radius: 10px;
        transition: background-color 0.3s, transform 0.3s;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        Get Support
    </a>
</div>
            
            <button onclick="profile_avatar(this);" class="sidebar-toggle"><i class="fa fa-long-arrow-left"></i></button>
          </div>
          
            
            <div class="list-inline-item logout"><a id="logout" href="logout.php" class="nav-link"> <span class="d-none d-sm-inline"></span><i title="Logout" class="icon-logout"></i></a></div>
          </div>
        </div>
      </nav>
    </header>
    <div class="d-flex align-items-stretch">
      
      <nav id="sidebar">
        
        <div class="sidebar-header d-flex align-items-center">
          <div id="profile_avatar" class="avatar"><img src="img/avatar.jpg" class="img-fluid rounded-circle"></div>
          <div class="title">
            <h1 class="h5"><?php echo $_SESSION['username'];?></h1>
            <p><?php echo $_SESSION['role'];?></p>
          </div>
        </div>
        <ul class="list-unstyled">
          <li><a href="index.php"> <i class="icon-home"></i>Home </a></li>
          <?php if ($_SESSION['role'] == "Admin") {
            echo '<li class="active"><a href="handlers.php"> <i class="icon-user"></i>Handlers </a></li>';
          }
          if($handlers_access != "checked"){
            if ($_SESSION['role'] == "Admin") {
              echo '<li><a href="settings.php"> <i class="icon-settings"></i>Settings </a></li>';
            }
          }else{
            echo '<li><a href="settings.php"> <i class="icon-settings"></i>Settings </a></li>';
          }?>
      </nav>
      
      <div class="page-content">
        <div class="page-header">
          <div class="container-fluid">
          </div>
        </div>
        
        <section class="no-padding-bottom">
          <div class="col-lg-12">
            <div class="block">
              <div class="title"><strong class="d-block">Add Handlers</strong><span class="d-block">create new handler account.</span></div>
              <div class="block-body">
                <form method="POST" action="add.php" >
                  <div class="form-group">
                    <label class="form-control-label">Username</label>
                    <input autocomplete="off" type="text" name="huser" placeholder="Username" class="form-control">
                  </div>
                  <div class="form-group">       
                    <label class="form-control-label">Password</label>
                    <input autocomplete="off" type="text" name="hpass" placeholder="Password" class="form-control">
                  </div>
                  <div class="form-group">       
                    <input type="submit" value="Add" class="btn btn-primary">
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="block">
              <div class="title"><strong>Handlers List</strong></div>
              <div class="table-responsive"> 
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Username</th>
                      <th>Password</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php   $query = mysqli_query($conn, "SELECT * from handlers");
                    if($query){
                      if(mysqli_num_rows($query) >= 1){
                        $array = array_filter(mysqli_fetch_all($query,MYSQLI_ASSOC));
                      }
                    }
                    foreach($array as $value){
                      echo "
                      <tr>
                      <th style='vertical-align:middle;' scope='row'>{$value['id']}</th>
                      <td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;'>{$value['username']}</td>
                      <td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;'>{$value['password']}</td>
                      <td style='vertical-align:middle;'><button type='button' onclick='remove_handler(\"{$value['id']}\");' title='Remove' class='btn btn-outline-primary'><i class='icon-close'></i><span class='caret'></span></button></td>
                      </tr>
                      ";
                    }?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>
    
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/popper.js/umd/popper.min.js"> </script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="js/front.js"></script>
    <script src="js/toastr.min.js"></script>
    <script src="https://unpkg.com/webkul-micron@1.1.6/dist/script/micron.min.js"></script>
    <script src="js/other.js"></script>
    <script>
    function remove_handler(id){
    $.ajax({
			type : 'POST',
			url : 'inc/action.php?type=remove_handler',
			data : 'userid=' + id,
			success: function (data) {
				var parsed_data = JSON.parse(data);
				if(parsed_data.status == 'ok'){
          toastr["success"]("removed successfully");
          setTimeout(function () {
            window.location.reload();
          }, 2000);
				}else{
          toastr["error"]("failed to remove #" + id);
        }
			}
			})
  }
    </script>
    <?php if($_SESSION['add_handlers'] == 'success'){
      echo '<script>toastr["success"]("handler added successfully");</script>';
      $_SESSION['add_handlers'] = '';
    }elseif($_SESSION['add_handlers'] == 'error'){
      echo '<script>toastr["error"]("handler already exist")</script>';
      $_SESSION['add_handlers'] = '';
    } ?>
  </body>
</html>