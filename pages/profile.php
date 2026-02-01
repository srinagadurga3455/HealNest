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
    <title>Profile - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/profile.css">
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
                <a href="tasks.php" class="nav-item">
                    <span class="nav-icon">✓</span>
                    <span>Today's Tasks</span>
                </a>
                <a href="journal.php" class="nav-item">
                    <span class="nav-icon">📔</span>
                    <span>Journal</span>
                </a>
                <a href="profile.php" class="nav-item active">
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
                            <h2>Profile</h2>
                            <p>Manage your account and wellness journey</p>
                        </div>
                    </div>
                    <div class="user-profile" id="userAvatar">U</div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Profile Header Card -->
                <div class="profile-header-card">
                    <div class="profile-avatar">
                        <div class="avatar-circle" id="profileAvatarCircle">U</div>
                    </div>
                    <div class="profile-info">
                        <h1 id="profileName">User Name</h1>
                        <p id="profileEmail">user@email.com</p>
                    </div>
                    <div class="profile-stats-grid">
                        <div class="stat-box">
                            <div class="stat-value" id="daysOnPlatform">0</div>
                            <div class="stat-label">Days on HealNest</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="currentStreakDisplay">0</div>
                            <div class="stat-label">Current Streak</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="achievementsCount">0</div>
                            <div class="stat-label">Achievements</div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content Grid -->
                <div class="profile-content-grid">
                    <!-- Personal Information -->
                    <div class="profile-section">
                        <h3 class="section-title">Personal Information</h3>
                        
                        <form id="profileForm" onsubmit="updateProfile(event)" class="profile-form">
                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" id="fullName" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            
                            <button type="submit" class="btn-save">Update Profile</button>
                        </form>
                    </div>

                    <!-- Achievements -->
                    <div class="profile-section">
                        <h3 class="section-title">Achievements</h3>
                        
                        <div class="achievements-grid" id="achievementsList">
                            <div class="achievement-card earned">
                                <div class="achievement-icon">☀️</div>
                                <div class="achievement-title">First Steps</div>
                                <div class="achievement-desc">Completed your first assessment</div>
                            </div>
                            
                            <div class="achievement-card earned">
                                <div class="achievement-icon">🔥</div>
                                <div class="achievement-title">Streak Starter</div>
                                <div class="achievement-desc">Maintained a 3-day streak</div>
                            </div>
                            
                            <div class="achievement-card earned">
                                <div class="achievement-icon">📝</div>
                                <div class="achievement-title">Task Master</div>
                                <div class="achievement-desc">Completed 10 daily tasks</div>
                            </div>
                            
                            <div class="achievement-card locked">
                                <div class="achievement-icon">🏆</div>
                                <div class="achievement-title">Champion</div>
                                <div class="achievement-desc">Complete 30-day program</div>
                            </div>
                            
                            <div class="achievement-card locked">
                                <div class="achievement-icon">📔</div>
                                <div class="achievement-title">Storyteller</div>
                                <div class="achievement-desc">Write 25 journal entries</div>
                            </div>
                            
                            <div class="achievement-card locked">
                                <div class="achievement-icon">🌟</div>
                                <div class="achievement-title">Wellness Warrior</div>
                                <div class="achievement-desc">Reach wellness score of 90+</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/profile.js"></script>
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