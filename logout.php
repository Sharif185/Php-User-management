<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear "Remember Me" cookies
if (isset($_COOKIE['email'])) {
    setcookie("email", "", time() - 3600, "/"); // Expire email cookie
}
if (isset($_COOKIE['password'])) {
    setcookie("password", "", time() - 3600, "/"); // Expire password cookie
}

// Redirect to the login page
header("Location: login.php");
exit();
?>
