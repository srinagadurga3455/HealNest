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
    <title>HealNest - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div>
                <div class="brand-logo">
                    <h1>☀️ HealNest</h1>
                </div>
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="./dashboard.php">
                                <i>🏠</i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./program.php">
                                <i>🎯</i>
                                <span>My Program</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./tasks.php">
                                <i>✅</i>
                                <span>Today's Tasks</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./profile.php">
                                <i>👤</i>
                                <span>Profile</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                            <li class="nav-item">
                                <span class="text-muted me-3" id="welcomeText">Welcome back!</span>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div id="userAvatar">U</div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="drop2">
                                    <a href="./profile.php" class="dropdown-item">
                                        <i class="ti ti-user"></i>
                                        <span>My Profile</span>
                                    </a>
                                    <a href="./journal.php" class="dropdown-item">
                                        <i class="ti ti-edit"></i>
                                        <span>Journal</span>
                                    </a>
                                    <a href="javascript:void(0)" class="dropdown-item" onclick="logout()">
                                        <i class="ti ti-logout"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!--  Header End -->
            <div class="container-fluid">
                <!-- Welcome Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="welcome-section">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="welcome-content">
                                        <h2 class="welcome-title mb-2">
                                            <span id="greeting">Good evening</span>, <span id="userName">demo</span>! 
                                            <span class="welcome-emoji">🌟</span>
                                        </h2>
                                        <p class="welcome-subtitle mb-2">Welcome to your wellness dashboard. Track your progress and continue your mental health journey.</p>
                                        <div class="welcome-stats">
                                            <span class="welcome-stat">
                                                <i class="ti ti-target"></i>
                                                Today's Performance: <strong id="todayPerformance">0%</strong>
                                            </span>
                                            <span class="welcome-stat">
                                                <i class="ti ti-flame"></i>
                                                Streak: <strong id="welcomeStreak">0 days</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="wellness-score-container">
                                        <div class="progress-circle">
                                            <div class="progress-text" id="wellnessScore">75%</div>
                                        </div>
                                        <p class="wellness-label mt-2 mb-0">Wellness Score</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Assignment Card -->
                <div class="row mb-4" id="programAssignmentSection">
                    <div class="col-12">
                        <div class="program-assignment-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="program-header">
                                        <h4 class="program-title mb-2" id="programName">🎯 Your Wellness Program</h4>
                                        <p class="program-description mb-3" id="programDescription">Loading your personalized program...</p>
                                        <div class="program-meta">
                                            <span class="program-badge" id="programReason">Based on your assessment results</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="program-actions">
                                        <a href="./tasks.php" class="btn btn-primary btn-lg">
                                            <i class="ti ti-checklist"></i>
                                            Complete Today's Tasks
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-card streak-card">
                            <div class="stat-icon">🔥</div>
                            <div class="stat-number" id="currentStreak">0</div>
                            <div class="stat-label">Current Streak</div>
                            <div class="streak-info">
                                <small id="streakDetails">Consecutive active days</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">🏆</div>
                            <div class="stat-number" id="maxStreak">0</div>
                            <div class="stat-label">Best Streak</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-number" id="programDaysCompleted">0</div>
                            <div class="stat-label">Program Days</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">📝</div>
                            <div class="stat-number" id="journalCount">0</div>
                            <div class="stat-label">Journal Entries</div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Mood Tracker -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    😊 How are you feeling today?
                                </h5>
                                <div class="mood-tracker">
                                    <div class="mood-option" data-mood="excellent">
                                        <div class="mood-emoji">😄</div>
                                        <div class="mood-label">Excellent</div>
                                    </div>
                                    <div class="mood-option" data-mood="good">
                                        <div class="mood-emoji">😊</div>
                                        <div class="mood-label">Good</div>
                                    </div>
                                    <div class="mood-option" data-mood="neutral">
                                        <div class="mood-emoji">😐</div>
                                        <div class="mood-label">Neutral</div>
                                    </div>
                                    <div class="mood-option" data-mood="challenging">
                                        <div class="mood-emoji">😔</div>
                                        <div class="mood-label">Challenging</div>
                                    </div>
                                </div>
                                <div id="mood-feedback" class="mt-3 text-center" style="display: none;">
                                    <p class="mb-0 text-success">Thanks for sharing! Your mood has been recorded.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Journal Entries -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    📝 Recent Journal Entries
                                </h5>
                                <div id="journal-entries" class="text-center py-4">
                                    <div class="empty-state">
                                        <div class="empty-icon">📝</div>
                                        <p class="text-muted mb-3">No journal entries yet</p>
                                        <a href="./journal.php" class="btn btn-primary">Write Your First Entry</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    🎯 Quick Actions
                                </h5>
                                <div class="quick-actions">
                                    <a href="./tasks.php" class="btn btn-primary btn-block">
                                        ✅ Complete Today's Tasks
                                    </a>
                                    <a href="./program.php" class="btn btn-outline-primary btn-block">
                                        🎯 View My Program
                                    </a>
                                    <a href="./profile.php" class="btn btn-outline-primary btn-block">
                                        👤 Update Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                <!-- Tasks Section (Hidden by default, shown when scrolled to) -->
                <div class="row mt-4" id="tasksSection" style="display: none;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    ✅ Today's Tasks
                                </h5>
                                <div id="tasks-list">
                                    <!-- Tasks will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/journal-utils.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>