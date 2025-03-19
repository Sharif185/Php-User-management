<?php
// Start session
session_start();

// Include database connection
include('include/db.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID
$user_id = $_SESSION['user_id'];

// Fetch the profile picture path (if exists)
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Delete profile picture file if it exists
if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
    unlink($user['profile_picture']); // Remove file from server
}

// Delete the user account from the database
$deleteQuery = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Destroy session and log out the user
session_destroy();

// Redirect to login page with a success message
header("Location: login.php?message=Account deleted successfully");
exit();
?>
