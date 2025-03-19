<?php
// Start session
session_start();

// Include authentication file
require 'auth.php';

// Redirect to dashboard if already logged in
requireGuest();

// Include database connection
include('include/db.php');

$error = ""; // Variable to store error messages

// Check if cookies exist for remember me
$stored_email = isset($_COOKIE['email']) ? $_COOKIE['email'] : '';
$stored_password = isset($_COOKIE['password']) ? $_COOKIE['password'] : '';

if (isset($_POST['login'])) {
    $email = strtolower(trim($_POST['email'])); // Convert email to lowercase
    $password = trim($_POST['password']); // Trim the password
    $remember_me = isset($_POST['remember']); // Check if "Remember Me" is checked

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Prepare and execute the SQL query
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->store_result();
        $stmt->bind_result($id, $username, $hashedPassword);

        // Check if the email exists and verify the password
        if ($stmt->fetch()) {
            if (password_verify($password, $hashedPassword)) {
                // Login successful
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;

                // If "Remember Me" is checked, set cookies for 30 days
                if ($remember_me) {
                    setcookie("email", $email, time() + (30 * 24 * 60 * 60), "/"); // 30 days
                    setcookie("password", $password, time() + (30 * 24 * 60 * 60), "/"); // 30 days
                } else {
                    // If "Remember Me" is NOT checked, clear any existing cookies
                    setcookie("email", "", time() - 3600, "/");
                    setcookie("password", "", time() - 3600, "/");
                }

                header("Location: dashboard.php"); // Redirect to the dashboard
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Email not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            <h2 class="text-center">Login</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST" id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($stored_email) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($stored_password) ?>" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember" <?= $stored_email ? 'checked' : '' ?>>
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-3">
                    <a href="forgot_password.php" class="text-white">Forgot Password?</a>
                </p>
            </form>
            <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-white">Register here</a></p>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            var email = $('input[name="email"]').val().trim();
            var password = $('input[name="password"]').val().trim();

            if (email === '' || password === '') {
                alert('All fields are required!');
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>
