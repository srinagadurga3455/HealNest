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
    <title>Journal - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/journal.css">
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
                <a href="mood.php" class="nav-item">
                    <span class="nav-icon">😊</span>
                    <span>Mood Tracker</span>
                </a>
                <a href="tasks.php" class="nav-item">
                    <span class="nav-icon">✓</span>
                    <span>Today's Tasks</span>
                </a>
                <a href="journal.php" class="nav-item active">
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
                            <h2>Journal</h2>
                            <p>Express your thoughts and reflect</p>
                        </div>
                    </div>
                    <div class="user-profile" id="userAvatar">U</div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <div class="journal-layout">
                    <!-- Main Journal Area -->
                    <div class="journal-main">
                        <!-- Default View -->
                        <div id="defaultView">
                            <div class="journal-header-actions">
                                <h3>My Entries</h3>
                                <button class="btn-new-entry" onclick="showNewEntryForm()">New Entry</button>
                            </div>
                            
                            <div class="entries-list" id="entriesList">
                                <!-- Entries will be loaded here -->
                            </div>
                        </div>

                        <!-- New Entry Form -->
                        <div id="newEntryForm" class="new-entry-form">
                            <div class="form-header">
                                <h3>New Entry</h3>
                                <button class="btn-cancel" onclick="hideNewEntryForm()">Cancel</button>
                            </div>
                            
                            <form onsubmit="saveEntry(event)" class="entry-form">
                                <div class="form-group">
                                    <label for="entryTitle">Title</label>
                                    <input type="text" id="entryTitle" class="form-control" placeholder="Give your entry a title..." required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entryContent">Your Thoughts</label>
                                    <textarea id="entryContent" class="form-control journal-textarea" placeholder="What's on your mind today? Share your thoughts, feelings, experiences..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>How are you feeling?</label>
                                    <div class="mood-selector">
                                        <button type="button" class="mood-option" data-mood="excellent">
                                            <span class="mood-emoji">😄</span>
                                            <span class="mood-label">Excellent</span>
                                        </button>
                                        <button type="button" class="mood-option" data-mood="good">
                                            <span class="mood-emoji">😊</span>
                                            <span class="mood-label">Good</span>
                                        </button>
                                        <button type="button" class="mood-option" data-mood="neutral">
                                            <span class="mood-emoji">😐</span>
                                            <span class="mood-label">Neutral</span>
                                        </button>
                                        <button type="button" class="mood-option" data-mood="challenging">
                                            <span class="mood-emoji">😔</span>
                                            <span class="mood-label">Challenging</span>
                                        </button>
                                        <button type="button" class="mood-option" data-mood="difficult">
                                            <span class="mood-emoji">😢</span>
                                            <span class="mood-label">Difficult</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn-save">Save Entry</button>
                            </form>
                        </div>

                        <!-- Entry View -->
                        <div id="entryView" class="entry-view">
                            <div class="entry-view-header">
                                <button class="btn-back" onclick="showDefaultView()">← Back to Entries</button>
                                <div class="entry-actions">
                                    <button class="btn-action" onclick="editEntry()">Edit</button>
                                    <button class="btn-action btn-delete" onclick="deleteEntry()">Delete</button>
                                </div>
                            </div>
                            
                            <div id="entryViewContent">
                                <!-- Entry content will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="journal-sidebar">
                        <!-- Search & Filter -->
                        <div class="sidebar-card">
                            <h4>Search & Filter</h4>
                            
                            <div class="search-box">
                                <input type="text" class="search-input" id="searchInput" placeholder="Search entries..." onkeyup="searchEntries()">
                            </div>
                            
                            <div class="filter-moods">
                                <button class="filter-mood active" data-filter="all" onclick="filterEntries('all')">All</button>
                                <button class="filter-mood" data-filter="excellent" onclick="filterEntries('excellent')">😄</button>
                                <button class="filter-mood" data-filter="good" onclick="filterEntries('good')">😊</button>
                                <button class="filter-mood" data-filter="neutral" onclick="filterEntries('neutral')">😐</button>
                                <button class="filter-mood" data-filter="challenging" onclick="filterEntries('challenging')">😔</button>
                                <button class="filter-mood" data-filter="difficult" onclick="filterEntries('difficult')">😢</button>
                            </div>
                        </div>

                        <!-- Journal Stats -->
                        <div class="sidebar-card">
                            <h4>Statistics</h4>
                            <div class="stats-grid" id="journalStats">
                                <!-- Stats will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/journal-utils.js"></script>
    <script src="js/journal.js"></script>
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