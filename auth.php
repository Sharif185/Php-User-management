<?php
// auth.php

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect to dashboard if already logged in
function requireGuest() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>