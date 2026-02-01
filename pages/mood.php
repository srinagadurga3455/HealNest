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
                <a href="pages/dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="pages/program.php" class="nav-item">
                    <span class="nav-icon">🎯</span>
                    <span>My Program</span>
                </a>
                <a href="pages/mood.php" class="nav-item active">
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

                    <button class="save-mood-btn" id="saveMoodBtn">Save Today's Mood</button>
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
            window.location.href = 'pages/logout.php';
        }
        
        function testMoodAPI() {
            // Test different URL patterns
            const testUrls = [
                'api/mood.php',
                '/HealNest/api/mood.php',
                '../api/mood.php',
                'http://localhost/HealNest/api/mood.php'
            ];
            
            console.log('Current location:', window.location.href);
            console.log('Base href:', document.querySelector('base')?.href || 'none');
            
            testUrls.forEach((testUrl, index) => {
                console.log(`\n=== Testing URL ${index + 1}: ${testUrl} ===`);
                
                fetch(testUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'test'
                    })
                })
                .then(response => {
                    console.log(`URL ${index + 1} Response status:`, response.status);
                    console.log(`URL ${index + 1} Response URL:`, response.url);
                    return response.text();
                })
                .then(text => {
                    console.log(`URL ${index + 1} Raw response:`, text.substring(0, 200));
                    try {
                        const data = JSON.parse(text);
                        console.log(`✅ URL ${index + 1} SUCCESS:`, data);
                        if (index === 0) { // Only alert for the first successful one
                            alert(`SUCCESS with URL: ${testUrl}\n` + JSON.stringify(data, null, 2));
                        }
                    } catch (e) {
                        console.log(`❌ URL ${index + 1} JSON parse error:`, e.message);
                    }
                })
                .catch(error => {
                    console.log(`❌ URL ${index + 1} Network error:`, error.message);
                });
            });
        }
    </script>
</body>
</html>