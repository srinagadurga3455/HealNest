<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HealNest - Mood Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/mood.css">
    <link rel="stylesheet" href="../css/mood.css"
</head>

<body>
    <button class="back-btn" onclick="window.location.href='./dashboard.php'">
        <i class="ti ti-arrow-left"></i>
    </button>

    <div class="mood-container">
        <!-- Mood Header -->
        <div class="mood-header">
            <h1 class="mb-3">Mood Tracker</h1>
            <p class="mb-0">Track your daily mood and emotions to better understand your mental wellness patterns.</p>
        </div>

        <!-- Today's Mood Selector -->
        <div class="mood-selector">
            <h3 class="mb-4">How are you feeling today?</h3>
            
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

            <div class="mood-note">
                <label for="moodNote" class="form-label">Add a note (optional)</label>
                <textarea id="moodNote" placeholder="What's on your mind? How are you feeling today?"></textarea>
            </div>

            <button class="save-mood-btn" onclick="saveMood()">Save Today's Mood</button>
        </div>

        <!-- Mood Analytics -->
        <div class="mood-analytics">
            <!-- Mood Calendar -->
            <div class="analytics-card">
                <h4 class="analytics-title">This Month</h4>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-sm btn-outline-primary" onclick="previousMonth()">‹</button>
                    <span id="currentMonth" class="fw-bold"></span>
                    <button class="btn btn-sm btn-outline-primary" onclick="nextMonth()">›</button>
                </div>
                <div class="mood-calendar" id="moodCalendar">
                    <!-- Calendar will be generated here -->
                </div>
            </div>

            <!-- Mood Statistics -->
            <div class="analytics-card">
                <h4 class="analytics-title">Mood Statistics</h4>
                <div class="mood-stats" id="moodStats">
                    <!-- Stats will be generated here -->
                </div>
            </div>

            <!-- Mood Trend -->
            <div class="analytics-card">
                <h4 class="analytics-title">Weekly Trend</h4>
                <div class="mood-trend" id="moodTrend">
                    <div class="trend-indicator">📈</div>
                    <div class="trend-text">Analyzing your mood patterns...</div>
                </div>
            </div>

            <!-- Recent Entries -->
            <div class="analytics-card">
                <h4 class="analytics-title">Recent Entries</h4>
                <div class="recent-entries" id="recentEntries">
                    <!-- Recent entries will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/mood.js"></script>
    
    <script src="../js/mood.js">
        
    </script>
</body>
</html>