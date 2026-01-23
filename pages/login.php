<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ./dashboard.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - HealNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <a href="./landing.html" class="back-link">← Back to Home</a>
    
    <div class="auth-container">
        <div class="logo">
            <h1>🌟 HealNest</h1>
            <p>Welcome back! Sign in to continue your wellness journey</p>
        </div>

        <div id="alertDiv" class="alert hidden"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot your password?</a>
            </div>

            <button type="submit" id="loginBtn" class="btn">Sign In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="./register.html">Sign up here</a></p>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>








