<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - HealNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            line-height: 1.8;
            color: #2c2c2c;
            background: #ffffff;
        }

        /* Auth Pages */
        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
        }

        .auth-visual {
            background: #fafafa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            position: relative;
        }

        .auth-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=1200&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
        }

        .visual-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 500px;
        }

        .visual-content h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3.5rem;
            font-weight: 300;
            color: #2c2c2c;
            margin-bottom: 2rem;
            line-height: 1.2;
        }

        .visual-content p {
            font-size: 1.1rem;
            color: #666;
            font-weight: 300;
            line-height: 1.8;
        }

        .auth-form-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            background: #ffffff;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
        }

        .auth-logo {
            margin-bottom: 3rem;
        }

        .auth-logo h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 500;
            color: #2c2c2c;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        .auth-logo p {
            color: #666;
            font-size: 0.95rem;
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 400;
            color: #2c2c2c;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid #e0e0e0;
            border-radius: 0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #ffffff;
            font-family: 'Lato', sans-serif;
            color: #2c2c2c;
        }

        .form-control:focus {
            outline: none;
            border-color: #2c2c2c;
            background: #fafafa;
        }

        .form-control::placeholder {
            color: #999;
            font-weight: 300;
        }

        .btn {
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: 0;
            font-weight: 400;
            transition: all 0.4s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            background: #2c2c2c;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }

        .btn:hover {
            background: #8b7355;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn:disabled:hover {
            background: #2c2c2c;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 400;
            border-left: 3px solid;
        }

        .alert-success {
            background: #f8fdf8;
            color: #2d5016;
            border-left-color: #8b7355;
        }

        .alert-error {
            background: #fef8f8;
            color: #5d1a1a;
            border-left-color: #d32f2f;
        }

        .hidden {
            display: none;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 1px solid #f0f0f0;
        }

        .auth-footer p {
            color: #666;
            font-size: 0.95rem;
            font-weight: 300;
        }

        .auth-footer a {
            color: #2c2c2c;
            text-decoration: none;
            font-weight: 400;
            border-bottom: 1px solid #2c2c2c;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            color: #8b7355;
            border-bottom-color: #8b7355;
        }

        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: #2c2c2c;
            text-decoration: none;
            font-weight: 300;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            z-index: 10;
        }

        .back-link:hover {
            color: #8b7355;
        }

        .loading {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        .loading:not(.hidden) {
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .auth-page {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                display: none;
            }

            .auth-form-section {
                padding: 3rem 2rem;
            }
        }

        @media (max-width: 768px) {
            .auth-form-section {
                padding: 2rem 1.5rem;
            }

            .visual-content h2 {
                font-size: 2.5rem;
            }
        }

        /* Form field focus animation */
        .form-control {
            position: relative;
        }

        .form-group {
            position: relative;
        }

        .form-group::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #8b7355;
            transition: width 0.3s ease;
        }

        .form-group:focus-within::after {
            width: 100%;
        }

        .forgot-password {
            text-align: right;
            margin-top: 0.5rem;
        }

        .forgot-password a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 300;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #8b7355;
        }

        /* Password toggle styles */
        .password-input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            color: #666;
            font-size: 1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #2c2c2c;
        }

        .password-toggle:focus {
            outline: none;
            color: #8b7355;
        }

        .password-input-wrapper .form-control {
            padding-right: 3rem;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <a href="../index.html" class="back-link">← Back to Home</a>
        
        <div class="auth-visual">
            <div class="visual-content">
                <h2>Welcome Back to Calm</h2>
                <p>Continue your journey towards mindfulness, balance, and inner peace.</p>
            </div>
        </div>

        <div class="auth-form-section">
            <div class="auth-container">
                <div class="auth-logo">
                    <h1>HealNest</h1>
                    <p>Sign in to your account</p>
                </div>

                <div id="alertDiv" class="alert hidden"></div>

                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <span id="eyeIcon">👁</span>
                            </button>
                        </div>
                        <div class="forgot-password">
                            <a href="#" onclick="alert('Password reset feature coming soon!'); return false;">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" id="loginBtn" class="btn">
                        <span id="btnText">Sign In</span>
                        <span id="btnLoading" class="loading hidden"></span>
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Create one here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            // Basic validation
            if (!email || !password) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading state
            setLoading(true);
            
            try {
                const response = await fetch('../api/login.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Store user data in localStorage for client-side auth checks
                    if (data.user) {
                        const userData = {
                            id: data.user.id,
                            name: data.user.name,
                            email: email,
                            assessment_taken: data.user.assessment_taken,
                            has_program: data.user.has_program
                        };
                        localStorage.setItem('healNestUser', JSON.stringify(userData));
                    }
                    
                    showAlert('Welcome back! Redirecting...', 'success');
                    setTimeout(() => {
                        // Check if user has taken assessment
                        if (data.user && data.user.assessment_taken) {
                            window.location.href = 'dashboard.php';
                        } else {
                            window.location.href = 'assessment.php';
                        }
                    }, 1500);
                } else {
                    showAlert(data.message || 'Invalid email or password. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Network error. Please check your connection and try again.', 'error');
            } finally {
                setLoading(false);
            }
        });
        
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertDiv');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.classList.remove('hidden');
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.classList.add('hidden');
                }, 3000);
            }
        }
        
        function setLoading(loading) {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            if (loading) {
                btn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
            } else {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁';
            }
        }
    </script>
</body>
</html>