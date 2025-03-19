<?php
session_start();
include('include/db.php'); // Include database connection

$error = ""; // Variable to store error messages

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = strtolower(trim($_POST['email'])); // Convert email to lowercase
    $password = trim($_POST['password']); // Trim the password
    $profilePicture = null;

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            // File Upload Handling
            if (!empty($_FILES['profile_picture']['name'])) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true); // Create the directory if it doesn't exist
                }

                $fileName = basename($_FILES["profile_picture"]["name"]);
                $fileSize = $_FILES["profile_picture"]["size"];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($fileSize > 5242880) { // 5MB max
                    $error = "File is too large. Max size is 5MB.";
                } elseif (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
                    $error = "Invalid file type. Only JPG, JPEG, and PNG allowed.";
                } else {
                    $profilePicture = $targetDir . uniqid() . "." . $fileType;
                    if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profilePicture)) {
                        $error = "Failed to upload file.";
                    }
                }
            }

            if (empty($error)) {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert User
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashedPassword, $profilePicture);

                if ($stmt->execute()) {
                    echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .btn-primary {
            background-color: #ff6600;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e65c00;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <h2 class="text-center">Register</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST" enctype="multipart/form-data" id="registerForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Profile Picture </label>
                    <input type="file" class="form-control" name="profile_picture" id="profile_picture" accept="image/jpeg, image/png">
                </div>
                <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-white">Login here</a></p>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#registerForm').on('submit', function(e) {
            var file = $('#profile_picture')[0].files[0];
            if (file) {
                var fileSize = file.size / 1024 / 1024; // Convert to MB
                var fileType = file.type;
                if (fileSize > 5) {
                    alert('File size must be less than 5MB');
                    e.preventDefault();
                }
                if (!['image/jpeg', 'image/png'].includes(fileType)) {
                    alert('Only JPG, JPEG, and PNG formats are allowed');
                    e.preventDefault();
                }
            }
        });
    });
</script>
</body>
</html>