<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HealNest - Digital Journal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/journal.css">
    <link rel="stylesheet" href="../css/journal.css">
</head>

<body>
    <button class="back-btn" onclick="window.location.href='./dashboard.php'">
        <i class="ti ti-arrow-left"></i>
    </button>

    <div class="journal-container">
        <!-- Journal Header -->
        <div class="journal-header">
            <h1 class="mb-3">Digital Journal</h1>
            <p class="mb-0">Express your thoughts, track your emotions, and reflect on your journey.</p>
        </div>

        <div class="journal-layout">
            <!-- Main Content Area -->
            <div class="journal-main">
                <!-- Default View -->
                <div id="defaultView">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>My Journal Entries</h3>
                        <button class="btn-primary" onclick="showNewEntryForm()">
                            <i class="ti ti-plus me-2"></i>New Entry
                        </button>
                    </div>
                    
                    <div class="entries-list" id="entriesList">
                        <!-- Entries will be loaded here -->
                    </div>
                </div>

                <!-- New Entry Form -->
                <div id="newEntryForm" class="new-entry-form">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>New Journal Entry</h3>
                        <button class="btn-secondary" onclick="hideNewEntryForm()">Cancel</button>
                    </div>
                    
                    <form onsubmit="saveEntry(event)">
                        <div class="form-group">
                            <label for="entryTitle" class="form-label">Title</label>
                            <input type="text" id="entryTitle" class="form-control" placeholder="Give your entry a title..." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="entryContent" class="form-label">Content</label>
                            <textarea id="entryContent" class="form-control journal-textarea" placeholder="What's on your mind today? Share your thoughts, feelings, experiences..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">How are you feeling?</label>
                            <div class="mood-selector-inline">
                                <button type="button" class="mood-btn" data-mood="excellent">😄 Excellent</button>
                                <button type="button" class="mood-btn" data-mood="good">😊 Good</button>
                                <button type="button" class="mood-btn" data-mood="neutral">😐 Neutral</button>
                                <button type="button" class="mood-btn" data-mood="challenging">😔 Challenging</button>
                                <button type="button" class="mood-btn" data-mood="difficult">😢 Difficult</button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Save Entry
                        </button>
                    </form>
                </div>

                <!-- Entry View -->
                <div id="entryView" class="entry-view">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button class="btn-secondary" onclick="showDefaultView()">
                            <i class="ti ti-arrow-left me-2"></i>Back to Entries
                        </button>
                        <div>
                            <button class="btn-secondary me-2" onclick="editEntry()">
                                <i class="ti ti-edit"></i> Edit
                            </button>
                            <button class="btn-secondary" onclick="deleteEntry()" style="background: #dc3545;">
                                <i class="ti ti-trash"></i> Delete
                            </button>
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
                    <h5 class="mb-3">Search & Filter</h5>
                    
                    <div class="search-box">
                        <i class="ti ti-search search-icon"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search entries..." onkeyup="searchEntries()">
                    </div>
                    
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all" onclick="filterEntries('all')">All</button>
                        <button class="filter-btn" data-filter="excellent" onclick="filterEntries('excellent')">😄</button>
                        <button class="filter-btn" data-filter="good" onclick="filterEntries('good')">😊</button>
                        <button class="filter-btn" data-filter="neutral" onclick="filterEntries('neutral')">😐</button>
                        <button class="filter-btn" data-filter="challenging" onclick="filterEntries('challenging')">😔</button>
                        <button class="filter-btn" data-filter="difficult" onclick="filterEntries('difficult')">😢</button>
                    </div>
                </div>

                <!-- Journal Stats -->
                <div class="sidebar-card">
                    <h5 class="mb-3">Journal Statistics</h5>
                    <div class="stats-grid" id="journalStats">
                        <!-- Stats will be loaded here -->
                    </div>
                </div>

                <!-- Popular Tags -->
                <div class="sidebar-card">
                    <h5 class="mb-3">Popular Tags</h5>
                    <div id="popularTags">
                        <!-- Tags will be loaded here -->
                    </div>
                </div>

                <!-- Writing Prompts -->
                <div class="sidebar-card">
                    <h5 class="mb-3">Writing Prompts</h5>
                    <div id="writingPrompts">
                        <p class="text-muted mb-2">Need inspiration? Try these prompts:</p>
                        <div id="promptsList">
                            <!-- Prompts will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/journal-utils.js"></script>
    <script src="../js/journal.js"></script>
    
    <script src="../js/journal.js">
        
    </script>
</body>
</html>