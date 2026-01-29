<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HealNest - Today's Tasks</title>
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
                            <a class="sidebar-link" href="./program.php">
                                <i>🎯</i>
                                <span>My Program</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="./tasks.php">
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
                                    <a href="./landing.html" class="dropdown-item" onclick="logout()">
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
                <!-- Tasks Header -->
                <div class="tasks-header-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Today's Tasks 📝</h2>
                            <p class="mb-2">Complete your daily wellness tasks to maintain your streak and improve your mental health.</p>
                            <p class="mb-0"><small id="tasksProgress">Today's Progress: 0/3 completed</small></p>
                        </div>
                    </div>
                </div>

                <!-- Daily Tasks Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    ✅ Daily Tasks
                                </h5>
                                
                                <div class="daily-tasks-list" id="dailyTasksList">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading your tasks...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Wellness Tip -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    💡 Today's Wellness Tip
                                </h5>
                                
                                <div class="wellness-tip-card">
                                    <div class="tip-icon">☀️</div>
                                    <div class="tip-content">
                                        <p class="tip-text" id="wellnessTipText">Celebrate your wins, no matter how small. You're doing great!</p>
                                    </div>
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
    <script src="../js/tasks.js"></script>
</body>
</html>