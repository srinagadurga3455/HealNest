<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HealNest - Profile</title>
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
                            <a class="sidebar-link" href="./tasks.php">
                                <i>✅</i>
                                <span>Today's Tasks</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="./profile.php">
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
                                    <a href="./landing  .html" class="dropdown-item" onclick="logout()">
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
                <!-- Profile Header -->
                <div class="profile-header-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Your Profile 👤</h2>
                            <p class="mb-0">Manage your account settings and view your wellness journey statistics.</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row">
                    <!-- Left Column - Personal Information -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    👤 Personal Information
                                </h5>
                                
                                <form id="profileForm" onsubmit="updateProfile(event)">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="fullName" class="form-label">Full Name</label>
                                            <input type="text" id="fullName" class="form-control" value="demo" required>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" id="email" class="form-control" value="demo@gmail.com" required>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="age" class="form-label">Age</label>
                                            <input type="number" id="age" class="form-control" placeholder="Enter your age">
                                        </div>
                                        
                                        <div class="col-12 mb-4">
                                            <label for="wellnessGoals" class="form-label">Wellness Goals</label>
                                            <textarea id="wellnessGoals" class="form-control" rows="4" placeholder="What are your wellness goals?"></textarea>
                                        </div>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                Update Profile
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Statistics & Achievements -->
                    <div class="col-lg-4">
                        <!-- Your Statistics -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    📊 Your Statistics
                                </h5>
                                
                                <div class="profile-stats">
                                    <div class="profile-stat-item text-center mb-4">
                                        <div class="stat-icon">📅</div>
                                        <div class="stat-number" id="daysOnHealNest">1</div>
                                        <div class="stat-label">Days on HealNest</div>
                                    </div>
                                    
                                    <div class="profile-stat-item text-center">
                                        <div class="stat-icon">🏆</div>
                                        <div class="stat-number" id="achievementsCount">3</div>
                                        <div class="stat-label">Achievements</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Achievements -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    🏆 Achievements
                                </h5>
                                
                                <div class="achievements-list" id="achievementsList">
                                    <div class="achievement-item">
                                        <div class="achievement-icon">☀️</div>
                                        <div class="achievement-content">
                                            <div class="achievement-title">First Steps</div>
                                            <div class="achievement-description">Completed your first assessment</div>
                                        </div>
                                    </div>
                                    
                                    <div class="achievement-item">
                                        <div class="achievement-icon">🔥</div>
                                        <div class="achievement-content">
                                            <div class="achievement-title">Streak Starter</div>
                                            <div class="achievement-description">Maintained a 3-day streak</div>
                                        </div>
                                    </div>
                                    
                                    <div class="achievement-item">
                                        <div class="achievement-icon">📝</div>
                                        <div class="achievement-content">
                                            <div class="achievement-title">Task Master</div>
                                            <div class="achievement-description">Completed 10 daily tasks</div>
                                        </div>
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
    <script src="../js/profile.js"></script>
</body>
</html>