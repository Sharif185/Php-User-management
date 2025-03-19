<?php
// Include the authentication file
require 'auth.php';

// Ensure the user is logged in
requireLogin();

// Include database connection
include('include/db.php');

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Animate.css for Animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar-header, .sidebar-profile {
            text-align: center;
        }
        .sidebar-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #fff;
        }
        .sidebar-links {
            list-style-type: none;
            padding-left: 0;
        }
        .sidebar-links li {
            padding: 10px;
            text-align: center;
        }
        .sidebar-links li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        .sidebar-links li:hover {
            background-color:rgb(117, 19, 197);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>Welcome, <?= htmlspecialchars($user['username']); ?></h4>
    </div>
    <div class="sidebar-profile">
        <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
        <?php else: ?>
            <img src="default-profile.png" alt="Profile Picture">
        <?php endif; ?>
        <p><?= htmlspecialchars($user['username']); ?></p>
    </div>

    <!-- Sidebar Links -->
    <ul class="sidebar-links">
        <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
        <li>
            <a href="delete_account.php" class="text-danger" onclick="return confirm('Are you sure you want to delete your account?')">
                <i class="fas fa-trash"></i> Delete Account
            </a>
        </li>
    </ul>

    <!-- Logout Button -->
    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-danger btn-block">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Dashboard</h2>
    <p>Welcome to your dashboard. Here you can manage your account.</p>

    <!-- Include Edit Profile -->
    <div class="mt-4">
        <h3>Edit Profile</h3>
        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture" class="form-control-file">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<!-- Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
