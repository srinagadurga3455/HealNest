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
                <a href="pages/dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="pages/program.php" class="nav-item active">
                    <span class="nav-icon">🎯</span>
                    <span>My Program</span>
                </a>
                <a href="pages/mood.php" class="nav-item">
                    <span class="nav-icon">😊</span>
                    <span>Mood Tracker</span>
                </a>
                <a href="pages/tasks.php" class="nav-item">
                    <span class="nav-icon">✓</span>
                    <span>Today's Tasks</span>
                </a>
                <a href="pages/journal.php" class="nav-item">
                    <span class="nav-icon">📔</span>
                    <span>Journal</span>
                </a>
                <a href="pages/profile.php" class="nav-item">
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
                            <div style="text-align: center; padding: 20px; color: #999;">
                                Loading your personalized tasks...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="program-action">
                    <a href="pages/tasks.php" class="btn-primary">View Today's Tasks</a>
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
            window.location.href = 'pages/logout.php';
        }
        
        // Load program stats immediately when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Program page DOM loaded, loading stats...');
            // Wait a bit for DOM elements to be fully ready
            setTimeout(() => {
                loadProgramStatsImmediate();
            }, 100);
        });
        
        // Also try loading after window loads
        window.addEventListener('load', function() {
            console.log('Window fully loaded, loading stats as backup...');
            setTimeout(() => {
                loadProgramStatsImmediate();
            }, 200);
        });
        
        function loadProgramStatsImmediate() {
            console.log('Loading program stats immediately...');
            
            fetch('api/dashboard.php?action=get_dashboard_data', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                console.log('Immediate stats response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Immediate stats data:', data);
                
                if (data.success && data.stats) {
                    // Calculate values
                    const totalDays = data.stats.program_duration || 30;
                    const completedDays = data.stats.program_days_completed || 0;
                    const remainingDays = Math.max(0, totalDays - completedDays);
                    const rate = Math.round((completedDays / totalDays) * 100);
                    
                    console.log('Calculated stats:', {
                        totalDays,
                        completedDays,
                        remainingDays,
                        rate
                    });
                    
                    // Update DOM elements immediately
                    const elements = {
                        daysCompleted: document.getElementById('daysCompleted'),
                        daysRemaining: document.getElementById('daysRemaining'),
                        currentStreak: document.getElementById('currentStreak'),
                        completionRate: document.getElementById('completionRate'),
                        progressPercentage: document.getElementById('progressPercentage'),
                        programProgressBar: document.getElementById('programProgressBar')
                    };
                    
                    console.log('DOM elements found:', Object.keys(elements).reduce((acc, key) => {
                        acc[key] = !!elements[key];
                        return acc;
                    }, {}));
                    
                    if (elements.daysCompleted) {
                        elements.daysCompleted.textContent = completedDays;
                        console.log('Updated daysCompleted to:', completedDays);
                    }
                    
                    if (elements.daysRemaining) {
                        elements.daysRemaining.textContent = remainingDays;
                        console.log('Updated daysRemaining to:', remainingDays);
                    }
                    
                    if (elements.currentStreak) {
                        elements.currentStreak.textContent = data.stats.current_streak || 0;
                        console.log('Updated currentStreak to:', data.stats.current_streak || 0);
                    }
                    
                    if (elements.completionRate) {
                        elements.completionRate.textContent = rate + '%';
                        console.log('Updated completionRate to:', rate + '%');
                    }
                    
                    if (elements.progressPercentage) {
                        elements.progressPercentage.textContent = rate + '%';
                        console.log('Updated progressPercentage to:', rate + '%');
                    }
                    
                    if (elements.programProgressBar) {
                        elements.programProgressBar.style.width = rate + '%';
                        console.log('Updated programProgressBar to:', rate + '%');
                    }
                    
                    console.log('All program stats updated successfully');
                } else {
                    console.error('Failed to load stats:', data.message || 'No stats data');
                }
            })
            .catch(error => {
                console.error('Error loading immediate stats:', error);
            });
        }
        
        function testProgramData() {
            console.log('Testing program data...');
            
            fetch('api/dashboard.php?action=get_dashboard_data', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Program data test result:', data);
                if (data.success && data.stats) {
                    const totalDays = data.stats.program_duration || 30;
                    const completedDays = data.stats.program_days_completed || 0;
                    const remainingDays = Math.max(0, totalDays - completedDays);
                    const rate = Math.round((completedDays / totalDays) * 100);
                    
                    alert(`Program Data Test:\nTotal Days: ${totalDays}\nCompleted Days: ${completedDays}\nRemaining Days: ${remainingDays}\nCompletion: ${rate}%`);
                    
                    // Force update the display immediately
                    const daysCompletedEl = document.getElementById('daysCompleted');
                    const daysRemainingEl = document.getElementById('daysRemaining');
                    const completionRateEl = document.getElementById('completionRate');
                    const progressPercentageEl = document.getElementById('progressPercentage');
                    const programProgressBarEl = document.getElementById('programProgressBar');
                    const currentStreakEl = document.getElementById('currentStreak');
                    
                    if (daysCompletedEl) daysCompletedEl.textContent = completedDays;
                    if (daysRemainingEl) daysRemainingEl.textContent = remainingDays;
                    if (completionRateEl) completionRateEl.textContent = rate + '%';
                    if (progressPercentageEl) progressPercentageEl.textContent = rate + '%';
                    if (programProgressBarEl) programProgressBarEl.style.width = rate + '%';
                    if (currentStreakEl) currentStreakEl.textContent = data.stats.current_streak || 0;
                    
                    console.log('Manually updated all program stats');
                } else {
                    alert('Test failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Test error:', error);
                alert('Test error: ' + error.message);
            });
        }
    </script>
</body>
</html>