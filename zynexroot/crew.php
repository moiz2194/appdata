<?php
include 'inc/config.php';
include 'inc/connect.php';
date_default_timezone_set('Europe/Dublin');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Redirect if not admin and handler access is not checked
if ($handlers_access != "checked" && $_SESSION['role'] != "Admin") {
    header('Location: login.php'); // Change the redirect location if needed
    exit;
}

// Check if checkbox state file exists
if (file_exists("offline.txt")) {
    // Read checkbox state from file
    $checkbox_state = file_get_contents("offline.txt");
    // Set session variable accordingly
    $_SESSION['checkbox_state'] = intval($checkbox_state);
} else {
    // If file doesn't exist, set checkbox state to unchecked
    $_SESSION['checkbox_state'] = 0;
}

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Zynex Root - Home</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="all,follow">
  <!-- Bootstrap CSS-->
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome CSS-->
  <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">
  <!-- Micron Animations CSS-->
  <link rel='stylesheet' href='https://unpkg.com/webkul-micron@1.1.6/dist/css/micron.min.css'>
  <!-- Custom Font Icons CSS-->
  <link rel="stylesheet" href="css/font.css">
  <!-- Toasrt Alerts CSS-->
  <link rel="stylesheet" href="css/toastr.min.css">
  <!-- Flags Icons CSS-->
  <link rel="stylesheet" href="css/flag-icon.css">
  <!-- Google fonts - Muli-->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">
  <!-- theme stylesheet-->
  <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
  <!-- Custom stylesheet - for your changes-->
  <link rel="stylesheet" href="css/custom.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Favicon-->
  <link rel="shortcut icon" href="img/favicon.ico">

  <style>
    .blink_me {
      animation: blinker 1s linear infinite;
    }

    @keyframes blinker {
      50% {
        background-color: rgba(32, 120, 102, 0.3);

      }
    }
  </style>


<?= $robots_script ?>
</head>

<body>
  <header class="header">
    <nav class="navbar navbar-expand-lg">

      <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="navbar-header">
          <!-- Navbar Header-->
<a href="index.php">
    <img src="logo.png"  style="width: 70px; height: 70px; cursor: pointer;">
</a>
          <!-- Sidebar Toggle Btn-->

        </div>
<!-- Title Section with Larger, White Icons -->
<div class="title" style="background-color: #222; padding: 15px; border-radius: 8px;">
    <i title="Reset Bans" onclick="reset_bans();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-recycle"></i>
    <i title="Reset Visits" onclick="reset_visits();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-globe"></i>
    <i title="Wipe Logs" onclick="wipe();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-trash"></i>
    <i title="Pause All Alerts" onclick="pause_all_alerts();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-bell-slash"></i>
</div>

<!-- Example JavaScript Functions -->

        <!-- Log out               -->
<div class="list-inline-item logout" style="padding: 10px; display: flex; align-items: center;">
<a href="index.php" class="nav-link" style="color: white; padding: 5px 10px; margin-right: 5px;">
    <i class="fa fa-home" style="font-size: 24px; margin-right: 5px;"></i>
    <span>Home</span>
</a>
<a href="crew.php" class="nav-link" style="color: white; padding: 5px 10px; margin-right: 5px;">
    <i class="fa fa-users" style="font-size: 24px; margin-right: 5px;"></i>
    <span>Crew</span>
</a>

    <a id="logout" href="logout.php" class="nav-link" style="color: white; padding: 5px 10px;">
        <span class="d-none d-sm-inline">Logout</span>
        <i title="Logout" class="fa fa-sign-out-alt" style="font-size: 24px; margin-left: 5px;"></i>
    </a>
</div>



		</div>
      </div>
    </nav>
  </header>
  <div class="d-flex align-items-stretch">
    <!-- Sidebar Navigation-->

<style>
/* General Layout */
.page-content {
    background-color: #0A0F1A;
    color: #f0f0f0;
    padding: 20px;
}

/* Panel Styling */
.block {
    background-color: #2b2b3d;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
}

.title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
}

/* Form Controls */
.form-control {
    background-color: #35354a;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px;
}

.form-control:focus {
    box-shadow: 0px 0px 5px #1a1a2e;
}

/* Checkboxes Styling */
.checkbox-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.i-checks {
    display: flex;
    align-items: center;
    background-color: #35354a;
    padding: 8px;
    border-radius: 4px;
}

.i-checks input[type="checkbox"] {
    margin-right: 10px;
}

.i-checks label {
    margin: 0;
}

/* Save Button */
.btn-primary {
    background-color: #3498db;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #217dbb;
}

</style>
<style>
.d-block {
  color: #ffffff;
}

.table {
  color: #787b82;

}

.table-striped tbody tr:nth-of-type(2n+1) {
  background-color: #35354a;
}
.btn-outline-primary {
  color: #217dbb;
}

.btn-outline-primary {
  color: #217dbb;
  background-color: transparent;
  background-image: none;
  border-color: #217dbb;
}

.table-striped tbody tr:nth-of-type(2n+1):hover {
  background-color: #4a4a67;
}


</style>
      
<div class="page-content">

        <section class="no-padding-bottom">
          <div class="col-lg-12">
            <div class="block">
              <div class="title"><strong class="d-block">Add Crews</strong><span class="d-block">create new Crew account.</span></div>
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
              <div class="title"><strong>Crews List</strong></div>
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
        <footer class="footer">
          <div class="footer__block block no-margin-bottom">
            <div class="container-fluid text-center">
                   
            </div>
          </div>
        </footer>
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