<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include('include/db.php');
$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$query = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = htmlspecialchars($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png"];

        if (in_array($imageFileType, $allowed_types) && $_FILES["profile_picture"]["size"] <= 5 * 1024 * 1024) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Delete the old profile picture if it exists
                if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                $update_query = "UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("sssi", $username, $email, $target_file, $user_id);
            } else {
                echo "<div class='alert alert-danger'>Error uploading file.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid file type or size exceeds 5MB.</div>";
        }
    } else {
        $update_query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    // Execute the update query
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Profile updated successfully. Redirecting...</div>";
        header("Refresh: 2; URL=edit_profile.php");
    } else {
        echo "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    // Delete the user's profile picture if it exists
    if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
        unlink($user['profile_picture']);
    }
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);

    // Execute the delete query
    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting account.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .profile-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            animation: fadeIn 0.8s ease-in-out;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #007bff;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .profile-pic:hover {
            transform: scale(1.1);
            border-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-primary, .btn-danger {
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004080;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
        }
        .loading-spinner {
            display: none;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2 class="text-center mb-4">Edit Profile</h2>
        <div class="text-center">
            <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'default-profile.png'); ?>" alt="Profile Picture" class="profile-pic">
        </div>
        <form method="POST" enctype="multipart/form-data" action="edit_profile.php" class="mt-4" id="profileForm">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Change Profile Picture</label>
                <input type="file" class="form-control" name="profile_picture" accept="image/jpeg, image/png">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary w-100" id="updateButton">
                <span id="buttonText">Update Profile</span>
                <div class="loading-spinner" id="loadingSpinner"></div>
            </button>
        </form>
        <div class="text-center mt-4">
            <button class="btn btn-danger w-100" onclick="confirmDelete()">Delete My Account</button>
        </div>
    </div>
    <script>
        document.getElementById('profileForm').addEventListener('submit', function () {
            document.getElementById('updateButton').disabled = true;
            document.getElementById('buttonText').style.display = 'none';
            document.getElementById('loadingSpinner').style.display = 'block';
        });

        function confirmDelete() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                document.body.innerHTML += '<form id="deleteForm" method="POST"><input type="hidden" name="delete_account"></form>';
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
