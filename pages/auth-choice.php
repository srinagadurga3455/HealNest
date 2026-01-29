<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - HealNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        .auth-choice-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        .auth-choice-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/></svg>');
            pointer-events: none;
        }

        .auth-choice-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
            margin: 2rem;
            text-align: center;
        }

        .auth-logo {
            margin-bottom: 2rem;
        }

        .auth-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #5D87FF;
            margin-bottom: 0.5rem;
        }

        .auth-logo p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .welcome-text {
            margin-bottom: 2.5rem;
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2A3547;
            margin-bottom: 1rem;
        }

        .welcome-text p {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.6;
        }

        .auth-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(93, 135, 255, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #5D87FF;
            border: 2px solid #5D87FF;
        }

        .btn-outline:hover {
            background: #5D87FF;
            color: white;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            padding: 0 1rem;
        }

        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .features-preview {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .features-preview h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2A3547;
            margin-bottom: 1rem;
        }

        .features-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .feature-icon {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .auth-choice-container {
                margin: 1rem;
                padding: 2rem;
            }

            .auth-logo h1 {
                font-size: 2rem;
            }

            .welcome-text h2 {
                font-size: 1.5rem;
            }

            .features-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-choice-page">
        <a href="../pages/landing.html" class="back-link">← Back to Home</a>
        
        <div class="auth-choice-container">
            <div class="auth-logo">
                <h1>🌟 HealNest</h1>
                <p>Your Mental Wellness Journey Starts Here</p>
            </div>

            <div class="welcome-text">
                <h2>Welcome to HealNest!</h2>
                <p>Join thousands of users who have improved their mental wellness with personalized programs, mood tracking, and daily wellness tasks.</p>
            </div>

            <div class="auth-options">
                <a href="register.php" class="btn btn-primary">
                    <span>🚀</span> Create New Account
                </a>
                
                <div class="divider">
                    <span>Already have an account?</span>
                </div>
                
                <a href="login.php" class="btn btn-outline">
                    <span>👋</span> Sign In
                </a>
            </div>

            <div class="features-preview">
                <h3>What you'll get with HealNest:</h3>
                <div class="features-list">
                    <div class="feature-item">
                        <span class="feature-icon">🎯</span>
                        <span>Personalized Programs</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">😊</span>
                        <span>Mood Tracking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✅</span>
                        <span>Daily Wellness Tasks</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📔</span>
                        <span>Digital Journal</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🔥</span>
                        <span>Streak Tracking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📊</span>
                        <span>Progress Analytics</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>