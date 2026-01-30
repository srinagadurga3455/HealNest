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
    <title>Mood Tracker - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/mood.css">
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
                <a href="mood.php" class="nav-item active">
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
                            <h2>Mood Tracker</h2>
                            <p>Track your emotions and understand your patterns</p>
                        </div>
                    </div>
                    <div class="user-profile" id="userAvatar">U</div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Today's Mood Selector -->
                <div class="mood-selector-card">
                    <h3 class="section-title">How are you feeling today?</h3>
                    
                    <div class="mood-options">
                        <div class="mood-option" data-mood="excellent">
                            <span class="mood-emoji">😄</span>
                            <div class="mood-label">Excellent</div>
                        </div>
                        <div class="mood-option" data-mood="good">
                            <span class="mood-emoji">😊</span>
                            <div class="mood-label">Good</div>
                        </div>
                        <div class="mood-option" data-mood="neutral">
                            <span class="mood-emoji">😐</span>
                            <div class="mood-label">Neutral</div>
                        </div>
                        <div class="mood-option" data-mood="challenging">
                            <span class="mood-emoji">😔</span>
                            <div class="mood-label">Challenging</div>
                        </div>
                        <div class="mood-option" data-mood="difficult">
                            <span class="mood-emoji">😢</span>
                            <div class="mood-label">Difficult</div>
                        </div>
                    </div>

                    <div class="mood-note-section">
                        <label for="moodNote">Add a note (optional)</label>
                        <textarea id="moodNote" placeholder="What's on your mind? How are you feeling today?"></textarea>
                    </div>

                    <button class="save-mood-btn" onclick="saveMood()">Save Today's Mood</button>
                </div>

                <!-- Mood Analytics Grid -->
                <div class="mood-analytics-grid">
                    <!-- Mood Calendar -->
                    <div class="analytics-card">
                        <h4 class="card-title">This Month</h4>
                        <div class="calendar-controls">
                            <button class="nav-btn" onclick="previousMonth()">‹</button>
                            <span id="currentMonth" class="current-month"></span>
                            <button class="nav-btn" onclick="nextMonth()">›</button>
                        </div>
                        <div class="mood-calendar" id="moodCalendar">
                            <!-- Calendar will be generated here -->
                        </div>
                    </div>

                    <!-- Mood Statistics -->
                    <div class="analytics-card">
                        <h4 class="card-title">Mood Statistics</h4>
                        <div class="mood-stats" id="moodStats">
                            <!-- Stats will be generated here -->
                        </div>
                    </div>

                    <!-- Mood Trend -->
                    <div class="analytics-card">
                        <h4 class="card-title">Weekly Trend</h4>
                        <div class="mood-trend" id="moodTrend">
                            <div class="trend-indicator">📈</div>
                            <div class="trend-text">Analyzing your mood patterns...</div>
                        </div>
                    </div>

                    <!-- Recent Entries -->
                    <div class="analytics-card">
                        <h4 class="card-title">Recent Entries</h4>
                        <div class="recent-entries" id="recentEntries">
                            <!-- Recent entries will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/mood.js"></script>
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