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
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/onboarding.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">HealNest</h1>
            </div>
            
            <nav class="sidebar-nav">
                <a href="pages/dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="pages/program.php" class="nav-item">
                    <span class="nav-icon">🎯</span>
                    <span class="nav-text">My Program</span>
                </a>
                <a href="pages/mood.php" class="nav-item">
                    <span class="nav-icon">😊</span>
                    <span class="nav-text">Mood Tracker</span>
                </a>
                <a href="pages/tasks.php" class="nav-item">
                    <span class="nav-icon">✓</span>
                    <span class="nav-text">Today's Tasks</span>
                </a>
                <a href="pages/journal.php" class="nav-item">
                    <span class="nav-icon">📔</span>
                    <span class="nav-text">Journal</span>
                </a>
                <a href="pages/profile.php" class="nav-item">
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
                            <a href="pages/tasks.php" class="btn-primary">Start Today's Tasks</a>
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
                        <button class="mood-btn mood-option" data-mood="excellent">
                            <span class="mood-emoji">😊</span>
                            <span class="mood-label">Excellent</span>
                        </button>
                        <button class="mood-btn mood-option" data-mood="good">
                            <span class="mood-emoji">🙂</span>
                            <span class="mood-label">Good</span>
                        </button>
                        <button class="mood-btn mood-option" data-mood="neutral">
                            <span class="mood-emoji">😐</span>
                            <span class="mood-label">Neutral</span>
                        </button>
                        <button class="mood-btn mood-option" data-mood="challenging">
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
                        <a href="pages/journal.php" class="section-link">View All</a>
                    </div>
                    <div id="journal-entries" class="journal-entries">
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <a href="pages/journal.php" class="btn-outline">Write Your First Entry</a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="section-card">
                    <h5 class="section-title">Quick Actions</h5>
                    <div class="quick-actions">
                        <a href="pages/tasks.php" class="action-btn">
                            <span class="action-icon">✓</span>
                            <span class="action-text">Complete Tasks</span>
                        </a>
                        <a href="pages/program.php" class="action-btn">
                            <span class="action-icon">🎯</span>
                            <span class="action-text">View Program</span>
                        </a>
                        <a href="pages/profile.php" class="action-btn">
                            <span class="action-icon">👤</span>
                            <span class="action-text">Update Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/onboarding.js?v=<?php echo time(); ?>"></script>
    <script src="js/journal-utils.js"></script>
    <script src="js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to sign out?')) {
                // Clear session
                fetch('api/logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    localStorage.removeItem('healNestUser');
                    window.location.href = 'index.html';
                }).catch(() => {
                    // Fallback - clear localStorage and redirect anyway
                    localStorage.removeItem('healNestUser');
                    window.location.href = 'index.html';
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
            
            // Load dashboard data when page loads
            console.log('Dashboard page loaded, initializing...');
            
            // Check if dashboard.js is loaded
            if (typeof loadDashboardData === 'function') {
                console.log('Dashboard.js loaded successfully');
                // Dashboard.js will handle data loading
            } else {
                console.log('Dashboard.js not loaded, loading data manually...');
                loadDashboardDataFallback();
            }
            
            // Listen for task completion updates from other tabs/windows
            window.addEventListener('storage', function(e) {
                if (e.key === 'healNestTaskUpdate') {
                    console.log('Received task update notification from another tab');
                    // Reload dashboard data to get updated progress
                    setTimeout(() => {
                        loadDashboardDataFallback();
                    }, 500); // Small delay to ensure database is updated
                }
            });
        });
        
        // Fallback function to load dashboard data if dashboard.js fails
        function loadDashboardDataFallback() {
            fetch('api/dashboard.php?action=get_dashboard_data', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Dashboard data loaded:', data);
                if (data.success) {
                    // Update stats
                    if (data.stats) {
                        document.getElementById('currentStreak').textContent = data.stats.current_streak || 0;
                        document.getElementById('maxStreak').textContent = data.stats.highest_streak || 0;
                        document.getElementById('programDaysCompleted').textContent = data.stats.program_days_completed || 0;
                        document.getElementById('journalCount').textContent = data.stats.journal_count || 0;
                        document.getElementById('wellnessScore').textContent = data.stats.wellness_score || 75;
                        document.getElementById('welcomeStreak').textContent = (data.stats.current_streak || 0) + ' days';
                        document.getElementById('todayPerformance').textContent = (data.stats.completion_percentage || 0) + '%';
                    }
                    
                    // Update user info
                    if (data.user) {
                        document.getElementById('userName').textContent = data.user.full_name || 'Welcome back';
                        const userAvatar = document.getElementById('userAvatar');
                        if (userAvatar && data.user.full_name) {
                            userAvatar.textContent = data.user.full_name.charAt(0).toUpperCase();
                        }
                        
                        // Update program info
                        if (data.user.program) {
                            document.getElementById('programName').textContent = data.user.program.icon + ' ' + data.user.program.name;
                            document.getElementById('programDescription').textContent = data.user.program.description;
                        }
                    }
                    
                    // Update mood if available
                    if (data.todays_mood) {
                        const moodElement = document.querySelector(`[data-mood="${data.todays_mood.mood}"]`);
                        if (moodElement) {
                            moodElement.classList.add('selected');
                        }
                    }
                    
                    // Setup mood selection functionality
                    setupMoodSelection();
                } else {
                    console.error('Failed to load dashboard data:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading dashboard data:', error);
            });
        }
        
        // Setup mood selection functionality
        function setupMoodSelection() {
            document.querySelectorAll('.mood-option').forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    const mood = this.dataset.mood;
                    const moodScore = getMoodScore(mood);
                    
                    // Save mood to API
                    fetch('api/mood.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'save',
                            mood: mood,
                            mood_score: moodScore,
                            note: ''
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show feedback
                            const feedback = document.getElementById('mood-feedback');
                            if (feedback) {
                                feedback.classList.remove('hidden');
                                setTimeout(() => {
                                    feedback.classList.add('hidden');
                                }, 3000);
                            }
                            
                            // Refresh dashboard data
                            setTimeout(() => {
                                loadDashboardDataFallback();
                            }, 500);
                        } else {
                            console.error('Failed to save mood:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving mood:', error);
                    });
                });
            });
        }
        
        function getMoodScore(mood) {
            const moodScores = {
                'excellent': 5,
                'good': 4,
                'neutral': 3,
                'challenging': 2,
                'difficult': 1
            };
            return moodScores[mood] || 3;
        }
    </script>
</body>
</html>