<?php
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
$ipslist = file_get_contents('zynexroot/inc/blacklist.dat');
if (strpos($ipslist, $ip) !== false) {
    $ipslist = file_get_contents("zynexroot/inc/logs/denied_visitors.txt");
    if (strpos($ipslist, $_SERVER['HTTP_CF_CONNECTING_IP']) !== true) {
       file_put_contents("zynexroot/inc/logs/denied_visitors.txt", $_SERVER['HTTP_CF_CONNECTING_IP'] . "\n", FILE_APPEND);
    }
    header('location:exit.php');
}
?>