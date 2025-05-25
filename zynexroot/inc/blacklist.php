<?php
$ip = $_SERVER['REMOTE_ADDR'];
$ipslist = file_get_contents('zynexroot/inc/blacklist.dat');
if (strpos($ipslist, $ip) !== false) {
    $ipslist = file_get_contents("zynexroot/inc/logs/denied_visitors.txt");
    if (strpos($ipslist, $_SERVER['REMOTE_ADDR']) !== true) {
       file_put_contents("zynexroot/inc/logs/denied_visitors.txt", $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);
    }
    header('location:exit.php');
}
?>