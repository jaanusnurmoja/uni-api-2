<?php
/**
 * Väljalogimine. Id-kaardi puhul käiakse korraks ära ka autentimisteenuses
 */
// Initialize the session
session_start();
$comingFrom = $_SERVER['HTTP_REFERER'];
$thisDir = dirname($_SERVER['SCRIPT_NAME']);
$thisDir = urlencode($thisDir);
if (isset($_SESSION['idCardData'])) {
    unset($_SESSION['idCardData']);
    header("location: $_SESSION[idCardAuthService]" . "?cb=$thisDir&sessend");
}
// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: $comingFrom");
exit;