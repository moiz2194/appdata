<?php
include 'zynexroot/inc/config.php';
if($enable_gateway != "checked"){
    header('HTTP/1.0 404 Not Found');
    die("<h1>404 Not Found</h1>The page that you have requested could not be found.");
}
?>