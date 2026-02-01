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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - HealNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/onboarding.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">HealNest</h1>
            </div>
            
            <nav class="sidebar-nav">
                <a href="./dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="./program.php" class="nav-item">
                    <span class="nav-icon">🎯</span>
                    <span class="nav-text">My Program</span>
                </a>
                <a href="./mood.php" class="nav-item">
                    <span class="nav-icon">😊</span>
                    <span class="nav-text">Mood Tracker</span>
                </a>
                <a href="./tasks.php" class="nav-item">
                    <span class="nav-icon">✓</span>
                    <span class="nav-text">Today's Tasks</span>
                </a>
                <a href="./journal.php" class="nav-item">
                    <span class="nav-icon">📔</span>
                    <span class="nav-text">Journal</span>
                </a>
                <a href="./profile.php" class="nav-item">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">Profile</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <button class="logout-btn" onclick="logout()">
                    <span>Sign Out</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()">
                            <span>☰</span>
                        </button>
                        <div class="greeting">
                            <h2 id="greeting">Good evening</h2>
                            <p id="userName">Welcome back</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="user-profile" id="userAvatar">
                            <span>U</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Wellness Score Card -->
                <div class="wellness-card">
                    <div class="wellness-content">
                        <div class="wellness-left">
                            <h3>Your Wellness Journey</h3>
                            <p>Continue building healthy habits and tracking your progress toward inner peace.</p>
                            <div class="wellness-stats">
                                <div class="wellness-stat">
                                    <span class="stat-label">Current Streak</span>
                                    <span class="stat-value" id="welcomeStreak">0 days</span>
                                </div>
                                <div class="wellness-stat">
                                    <span class="stat-label">Today's Progress</span>
                                    <span class="stat-value" id="todayPerformance">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="wellness-right">
                            <div class="wellness-score">
                                <div class="score-circle">
                                    <svg viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" class="score-bg"></circle>
                                        <circle cx="50" cy="50" r="45" class="score-fill" id="scoreCircle"></circle>
                                    </svg>
                                    <div class="score-text">
                                        <span class="score-number" id="wellnessScore">75</span>
                                        <span class="score-label">score</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Card -->
                <div class="program-card" id="programAssignmentSection">
                    <div class="program-content">
                        <div class="program-header">
                            <h4 id="programName">Your Wellness Program</h4>
                            <p id="programDescription">Loading your personalized program...</p>
                        </div>
                        <div class="program-action">
                            <a href="./tasks.php" class="btn-primary">Start Today's Tasks</a>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🔥</div>
                        <div class="stat-info">
                            <span class="stat-number" id="currentStreak">0</span>
                            <span class="stat-label">Day Streak</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🏆</div>
                        <div class="stat-info">
                            <span class="stat-number" id="maxStreak">0</span>
                            <span class="stat-label">Best Streak</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <span class="stat-number" id="programDaysCompleted">0</span>
                            <span class="stat-label">Days Active</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-info">
                            <span class="stat-number" id="journalCount">0</span>
                            <span class="stat-label">Journal Entries</span>
                        </div>
                    </div>
                </div>

                <!-- Mood Tracker -->
                <div class="section-card">
                    <h5 class="section-title">How are you feeling today?</h5>
                    <div class="mood-grid">
                        <button class="mood-btn" data-mood="excellent" onclick="selectMood('excellent')">
                            <span class="mood-emoji">😊</span>
                            <span class="mood-label">Excellent</span>
                        </button>
                        <button class="mood-btn" data-mood="good" onclick="selectMood('good')">
                            <span class="mood-emoji">🙂</span>
                            <span class="mood-label">Good</span>
                        </button>
                        <button class="mood-btn" data-mood="neutral" onclick="selectMood('neutral')">
                            <span class="mood-emoji">😐</span>
                            <span class="mood-label">Neutral</span>
                        </button>
                        <button class="mood-btn" data-mood="challenging" onclick="selectMood('challenging')">
                            <span class="mood-emoji">😔</span>
                            <span class="mood-label">Challenging</span>
                        </button>
                    </div>
                    <div id="mood-feedback" class="mood-feedback hidden">
                        <p>Thank you for sharing. Your mood has been recorded.</p>
                    </div>
                </div>

                <!-- Journal Entries -->
                <div class="section-card">
                    <div class="section-header">
                        <h5 class="section-title">Recent Reflections</h5>
                        <a href="./journal.php" class="section-link">View All</a>
                    </div>
                    <div id="journal-entries" class="journal-entries">
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <a href="./journal.php" class="btn-outline">Write Your First Entry</a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="section-card">
                    <h5 class="section-title">Quick Actions</h5>
                    <div class="quick-actions">
                        <a href="./tasks.php" class="action-btn">
                            <span class="action-icon">✓</span>
                            <span class="action-text">Complete Tasks</span>
                        </a>
                        <a href="./program.php" class="action-btn">
                            <span class="action-icon">🎯</span>
                            <span class="action-text">View Program</span>
                        </a>
                        <a href="./profile.php" class="action-btn">
                            <span class="action-icon">👤</span>
                            <span class="action-text">Update Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Help button for onboarding -->
    <button class="help-button" onclick="onboardingGuide.forceStart();" title="Show guided tour">
        ?
    </button>

    <script src="../js/auth.js"></script>
    <script src="../js/onboarding.js?v=<?php echo time(); ?>"></script>
    <script src="../js/journal-utils.js"></script>
    <script src="../js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        // Mood selection
        let selectedMood = null;
        function selectMood(mood) {
            // Remove previous selection
            document.querySelectorAll('.mood-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selection to clicked mood
            event.target.closest('.mood-btn').classList.add('selected');
            selectedMood = mood;
            
            // Save mood (implement API call)
            saveMood(mood);
            
            // Show feedback
            document.getElementById('mood-feedback').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('mood-feedback').classList.add('hidden');
            }, 3000);
        }

        function saveMood(mood) {
            // Implement mood saving logic
            console.log('Saving mood:', mood);
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to sign out?')) {
                // Clear session
                fetch('../api/logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    window.location.href = '../index.html';
                });
            }
        }

        // Update greeting based on time
        function updateGreeting() {
            const hour = new Date().getHours();
            const greetingEl = document.getElementById('greeting');
            
            if (hour < 12) {
                greetingEl.textContent = 'Good morning';
            } else if (hour < 18) {
                greetingEl.textContent = 'Good afternoon';
            } else {
                greetingEl.textContent = 'Good evening';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateGreeting();
        });
    </script>
</body>
</html>