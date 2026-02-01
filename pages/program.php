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
    <title>My Program - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/program.css">
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
                <a href="program.php" class="nav-item active">
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
                            <h2>My Program</h2>
                            <p>Track your wellness journey</p>
                        </div>
                    </div>
                    <div class="user-profile" id="userAvatar">U</div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Program Header -->
                <div class="program-header-card">
                    <div class="program-header-content">
                        <div class="program-header-text">
                            <h1 id="programTitle">Anxiety Management & Coping</h1>
                            <p id="programDescription">Learn evidence-based techniques to manage anxiety and build confidence in daily situations.</p>
                            <div class="program-meta">
                                <span id="programDuration">30 Days</span>
                                <span class="meta-divider">•</span>
                                <span id="programReason">Personalized for your needs</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Overview -->
                <div class="progress-overview-card">
                    <div class="progress-header">
                        <h3>Your Progress</h3>
                        <p>Keep going, you're doing great</p>
                    </div>
                    
                    <div class="progress-stats-grid">
                        <div class="progress-stat">
                            <div class="stat-value" id="daysCompleted">0</div>
                            <div class="stat-label">Days Completed</div>
                        </div>
                        <div class="progress-stat">
                            <div class="stat-value" id="daysRemaining">30</div>
                            <div class="stat-label">Days Remaining</div>
                        </div>
                        <div class="progress-stat">
                            <div class="stat-value" id="currentStreak">0</div>
                            <div class="stat-label">Current Streak</div>
                        </div>
                        <div class="progress-stat">
                            <div class="stat-value" id="completionRate">0%</div>
                            <div class="stat-label">Completion</div>
                        </div>
                    </div>

                    <div class="progress-bar-section">
                        <div class="progress-bar-label">
                            <span>Overall Progress</span>
                            <span id="progressPercentage">0%</span>
                        </div>
                        <div class="progress-bar-track">
                            <div class="progress-bar-fill" id="programProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Program Details -->
                <div class="program-details-grid">
                    <!-- Why This Program -->
                    <div class="detail-card">
                        <h3>Why This Program?</h3>
                        <div class="detail-divider"></div>
                        <p id="programExplanation">Your assessment results indicate that you may benefit from anxiety management techniques. This program is designed to help you develop practical coping strategies for daily challenges.</p>
                    </div>

                    <!-- Daily Tasks Overview -->
                    <div class="detail-card">
                        <h3>Daily Tasks Overview</h3>
                        <div class="detail-divider"></div>
                        <div class="tasks-list" id="programTasksList">
                            <div class="task-item">
                                <div class="task-icon">🫁</div>
                                <div class="task-content">
                                    <h4>Anxiety Breathing</h4>
                                    <p>4-7-8 breathing technique for anxiety relief</p>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-icon">🧘</div>
                                <div class="task-content">
                                    <h4>Grounding Exercise</h4>
                                    <p>5-4-3-2-1 grounding technique when feeling anxious</p>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-icon">⏰</div>
                                <div class="task-content">
                                    <h4>Worry Time</h4>
                                    <p>Dedicated 10 minutes to process worries constructively</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="program-action">
                    <a href="tasks.php" class="btn-primary">View Today's Tasks</a>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/program.js"></script>
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