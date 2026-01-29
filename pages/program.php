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
    <title>HealNest - My Program</title>
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
                            <a class="sidebar-link" href="./dashboard.php">
                                <i>🏠</i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="./program.php">
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
                                    <a href="index.html" class="dropdown-item" onclick="logout()">
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
                <!-- Program Header -->
                <div class="program-header-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2" id="programTitle">Anxiety Management & Coping</h2>
                            <p class="mb-2" id="programDescription">Learn evidence-based techniques to manage anxiety and build confidence in daily situations.</p>
                            <p class="mb-0"><small id="programReason">Your responses suggest anxiety symptoms. This program provides practical coping tools.</small></p>
                        </div>
                    </div>
                </div>

                <!-- Program Progress -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    📊 Program Progress
                                </h5>
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="stats-card">
                                            <div class="stat-icon">📅</div>
                                            <div class="stat-number" id="daysCompleted">0</div>
                                            <div class="stat-label">Days Completed</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="stats-card">
                                            <div class="stat-icon">⏳</div>
                                            <div class="stat-number" id="daysRemaining">30</div>
                                            <div class="stat-label">Days Remaining</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="stats-card">
                                            <div class="stat-icon">🔥</div>
                                            <div class="stat-number" id="currentStreak">0</div>
                                            <div class="stat-label">Current Streak</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="stats-card">
                                            <div class="stat-icon">📈</div>
                                            <div class="stat-number" id="completionRate">0%</div>
                                            <div class="stat-label">Completion Rate</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Program Progress</span>
                                        <span class="text-muted" id="progressPercentage">0%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" id="programProgressBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Details -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    🎯 Program Details
                                </h5>
                                
                                <div class="program-detail-section mb-4">
                                    <h6 class="text-primary mb-3">Why This Program?</h6>
                                    <p class="text-muted" id="programExplanation">Your responses suggest anxiety symptoms. This program provides practical coping tools.</p>
                                </div>

                                <div class="program-detail-section" id="dailyTasksSection">
                                    <h6 class="text-primary mb-3">Daily Tasks Overview</h6>
                                    <ul class="program-tasks-list" id="programTasksList">
                                        <li><strong>Anxiety Breathing:</strong> 4-7-8 breathing technique for anxiety relief</li>
                                        <li><strong>Grounding Exercise:</strong> 5-4-3-2-1 grounding technique when feeling anxious</li>
                                        <li><strong>Worry Time:</strong> Dedicated 10 minutes to process worries constructively</li>
                                    </ul>
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
    <script src="../js/program.js"></script>
</body>
</html>