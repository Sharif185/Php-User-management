<?php
session_start();
include('include/db.php');

if (!isset($_GET["token"])) {
    die("Invalid request.");
}

$token = $_GET["token"];

// Check if token exists and is valid
$query = "SELECT id FROM users WHERE password_reset_token = ? AND token_expiry > NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Invalid or expired token.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    // Update password and clear the reset token
    $update_query = "UPDATE users SET password = ?, password_reset_token = NULL, token_expiry = NULL WHERE password_reset_token = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ss", $new_password, $token);
    if ($stmt->execute()) {
        $_SESSION["message"] = "Password reset successful. <a href='login.php'>Login</a>";
    } else {
        $_SESSION["error"] = "Failed to reset password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styling -->
    <style>
        body {
            background-image: url('https://www.w3schools.com/w3images/woods.jpg');
            background-size: cover;
            font-family: 'Arial', sans-serif;
            padding: 0;
            margin: 0;
            color: #fff;
        }

        .container {
            max-width: 600px;
            margin-top: 80px;
        }

        .card {
            border-radius: 15px;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
        }

        .btn-primary {
            background-color: #28a745;
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
        }

        .alert {
            margin-top: 20px;
        }

        h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .card-body {
            animation: fadeIn 2s ease-in-out;
        }

        /* Animation for fade-in effect */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="text-center">Reset Password</h2>

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

            <form method="POST" id="resetPasswordForm">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" class="form-control" id="new_password" required>
                    <small id="passwordHelp" class="form-text text-muted">Password should be at least 8 characters long.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- JavaScript for form validation -->
<script>
    document.getElementById('resetPasswordForm').addEventListener('submit', function(event) {
        var password = document.getElementById('new_password').value;
        if (password.length < 8) {
            alert("Password must be at least 8 characters long.");
            event.preventDefault();
        }
    });
</script>

</body>
</html>
