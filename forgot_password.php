<?php
session_start();
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database Connection
include('include/db.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if email exists in the database
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Store token in the database
        $update_query = "UPDATE users SET password_reset_token = ?, token_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Send password reset email
        $reset_link = "http://localhost/user_management/reset_password.php?token=$token";
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sharifssentaayi1@gmail.com'; // Your full Gmail address
            $mail->Password = 'jeksdikvjtopdjzr'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = 587;
        
            $mail->SMTPDebug = 2; // Enable debugging
            $mail->Debugoutput = function($str, $level) {
                echo "Debug level $level; message: $str\n";
            };
        
            $mail->setFrom('your-email@gmail.com', 'Your Website');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "<p>Click the link below to reset your password:</p>
                           <p><a href='$reset_link'>$reset_link</a></p>
                           <p>This link will expire in 1 hour.</p>";
        
            $mail->send();
            $_SESSION["message"] = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION["error"] = "Mail error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION["error"] = "Email not found.";
    }

    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styling -->
    <style>
        body {
            background: linear-gradient(to right,rgb(48, 9, 90),rgb(7, 33, 77)); /* Beautiful gradient background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color:rgb(5, 38, 72);
            border: none;
        }
        .btn-primary:hover {
            background-color:rgb(108, 111, 13);
        }
        .form-control {
            border-radius: 5px;
        }
        .alert {
            margin-top: 20px;
        }
        h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 30px;
        }
        .form-group label {
            color: white;
        }
        .card-body {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="card" style="width: 100%; max-width: 400px;">
    <div class="card-body">
        <h2 class="text-center">Forgot Password</h2>
        
        <?php
        if (isset($_SESSION["message"])) {
            echo "<div class='alert alert-success'>" . $_SESSION["message"] . "</div>";
            unset($_SESSION["message"]);
        }
        if (isset($_SESSION["error"])) {
            echo "<div class='alert alert-danger'>" . $_SESSION["error"] . "</div>";
            unset($_SESSION["error"]);
        }
        ?>

        <form method="POST" id="forgotPasswordForm">
            <div class="form-group">
                <label for="email">Enter your Email:</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
                <small id="emailHelp" class="form-text text-muted">We'll send you a reset link.</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- JavaScript for form validation -->
<script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
        var email = document.getElementById('email').value;
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            event.preventDefault();
        }
    });
</script>

</body>
</html>
