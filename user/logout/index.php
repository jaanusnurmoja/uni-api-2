<?php
// Initialize the session
session_start();
$comingFrom = $_SERVER['HTTP_REFERER'];
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: $comingFrom");
exit;
?>