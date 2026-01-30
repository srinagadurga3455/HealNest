<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Tasks - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/tasks.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">HealNest</h1>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="program.php" class="nav-item">
                    <span class="nav-icon">🎯</span>
                    <span>My Program</span>
                </a>
                <a href="mood.php" class="nav-item">
                    <span class="nav-icon">😊</span>
                    <span>Mood Tracker</span>
                </a>
                <a href="tasks.php" class="nav-item active">
                    <span class="nav-icon">✓</span>
                    <span>Today's Tasks</span>
                </a>
                <a href="journal.php" class="nav-item">
                    <span class="nav-icon">📔</span>
                    <span>Journal</span>
                </a>
                
                <a href="profile.php" class="nav-item">
                    <span class="nav-icon">👤</span>
                    <span>Profile</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <button class="logout-btn" onclick="logout()">Sign Out</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                        <div class="greeting">
                            <h2>Today's Tasks</h2>
                            <p>Complete your daily wellness activities</p>
                        </div>
                    </div>
                    <div class="user-profile" id="userAvatar">U</div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Progress Overview -->
                <div class="tasks-progress-card">
                    <div class="progress-content">
                        <div class="progress-text">
                            <h3>Today's Progress</h3>
                            <p id="tasksProgress">0 of 3 tasks completed</p>
                        </div>
                        <div class="progress-circle">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="54" class="progress-bg"></circle>
                                <circle cx="60" cy="60" r="54" class="progress-ring" id="progressRing"></circle>
                            </svg>
                            <div class="progress-value">
                                <span id="progressPercent">0%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Tasks -->
                <div class="tasks-section">
                    <h3 class="section-title">Daily Tasks</h3>
                    
                    <div class="tasks-list" id="dailyTasksList">
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading your tasks...</p>
                        </div>
                    </div>
                </div>

                <!-- Wellness Tip -->
                <div class="wellness-tip-section">
                    <h3 class="section-title">Today's Wellness Tip</h3>
                    
                    <div class="tip-card">
                        <div class="tip-icon">💡</div>
                        <div class="tip-content">
                            <p id="wellnessTipText">Take a moment to breathe deeply. Inhale peace, exhale stress. You're doing wonderfully.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/tasks.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        function logout() {
            localStorage.removeItem('healNestUser');
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>