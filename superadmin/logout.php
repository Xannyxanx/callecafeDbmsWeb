<?php
session_start();
$_SESSION = array(); 
session_destroy(); 


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


header("Location: 0.login.html");
exit();
?>
