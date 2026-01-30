document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard DOM loaded, starting data load...');
    
    // Add error handler for uncaught errors
    window.addEventListener('error', function(e) {
        console.error('JavaScript error in dashboard:', e.error);
        showErrorMessage('JavaScript error: ' + e.message);
    });
    
    // Add error handler for unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection in dashboard:', e.reason);
        showErrorMessage('Promise error: ' + e.reason);
    });
    
    // Test if DOM elements exist
    const programName = document.getElementById('programName');
    const programDescription = document.getElementById('programDescription');
    console.log('DOM elements check:', {
        programName: !!programName,
        programDescription: !!programDescription,
        programNameText: programName ? programName.textContent : 'N/A',
        programDescText: programDescription ? programDescription.textContent : 'N/A'
    });
    
    // Initialize interactive elements
    setupMoodTracker();
    
    // Load dashboard data directly - let the API handle authentication
    loadDashboardData();
});

function loadDashboardData() {
    console.log('Starting dashboard data load...');
    // Try to load from API first
    fetch('api/dashboard.php?action=get_dashboard_data', {
        method: 'GET',
        credentials: 'same-origin', // Include cookies/session
        headers: {
            'Content-Type': 'application/json',
        }
    })
        .then(response => {
            console.log('Dashboard API response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Dashboard API response data:', data);
            if (data.success) {
                console.log('Dashboard data loaded successfully:', data);
                console.log('Program data received:', data.user.program);
                // Load all dashboard components with API data
                loadUserInfo(data.user);
                loadStats(data.stats);
                loadTodaysMood(data.todays_mood);
                loadJournalEntries(data.journal_entries);
                loadTodaysTasks(data.tasks);
                loadProgramInfo(data.user.program);
            } else {
                console.log('API returned error:', data.message);
                // If not authenticated, redirect to login
                if (data.message === 'Not authenticated') {
                    window.location.href = './login.php';
                    return;
                }
                // For other errors, show error message
                showErrorMessage('Failed to load dashboard data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('API error:', error);
            showErrorMessage('Unable to connect to server. Please refresh the page.');
        });
}

function showErrorMessage(message) {
    // Create or update error message display
    let errorDiv = document.getElementById('dashboard-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'dashboard-error';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

function loadDashboardFallback() {
    // Remove fallback - force API usage only
    showErrorMessage('Unable to load dashboard data. Please try logging in again.');
}

function loadUserInfo(userData = null) {
    const user = userData || Auth.getCurrentUser();
    const greeting = getGreeting();
    
    // Update greeting elements
    const greetingEl = document.getElementById('greeting');
    if (greetingEl) greetingEl.textContent = greeting;
    
    // Update user name elements
    const userNameEl = document.getElementById('userName');
    const headerUserNameEl = document.getElementById('headerUserName');
    const welcomeTextEl = document.getElementById('welcomeText');
    const userEmailEl = document.getElementById('userEmail');
    
    const displayName = user.full_name || user.fullName || user.name || user.email.split('@')[0];
    
    if (userNameEl) userNameEl.textContent = displayName;
    if (headerUserNameEl) headerUserNameEl.textContent = displayName;
    if (welcomeTextEl) welcomeTextEl.textContent = `Welcome back, ${displayName}!`;
    if (userEmailEl) userEmailEl.textContent = user.email;
    
    // Set user avatar
    const userAvatar = document.getElementById('userAvatar');
    if (userAvatar) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        userAvatar.textContent = userName.charAt(0).toUpperCase();
    }
}

function loadUserInfoFallback() {
    // Removed - dashboard now uses API data only
    showErrorMessage('Unable to load user information.');
}

function getGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
}

function loadStats(statsData = null) {
    if (statsData) {
        // Use API data
        const currentStreakEl = document.getElementById('currentStreak');
        const maxStreakEl = document.getElementById('maxStreak');
        const programDaysEl = document.getElementById('programDaysCompleted');
        const journalCountEl = document.getElementById('journalCount');
        const wellnessScoreEl = document.getElementById('wellnessScore');
        const streakDetailsEl = document.getElementById('streakDetails');
        
        // Welcome section stats
        const welcomeStreakEl = document.getElementById('welcomeStreak');
        const todayPerformanceEl = document.getElementById('todayPerformance');
        
        if (currentStreakEl) {
            currentStreakEl.textContent = statsData.current_streak;
            
            // Add animation for streak
            if (statsData.current_streak > 0) {
                currentStreakEl.style.animation = 'pulse 2s infinite';
            }
        }
        
        if (maxStreakEl) maxStreakEl.textContent = statsData.highest_streak;
        
        if (programDaysEl) {
            // Show "X of Y days" format
            const completedText = statsData.program_days_completed + ' of ' + statsData.program_duration;
            programDaysEl.textContent = completedText;
        }
        
        if (journalCountEl) journalCountEl.textContent = statsData.journal_count;
        if (wellnessScoreEl) wellnessScoreEl.textContent = statsData.wellness_score + '%';
        
        // Update streak details
        if (streakDetailsEl) {
            const streakText = statsData.current_streak === 0 ? 
                'Start your streak today!' : 
                statsData.current_streak === 1 ? 
                'Great start! Keep going!' :
                `${statsData.current_streak} consecutive active days`;
            streakDetailsEl.textContent = streakText;
        }
        
        // Update welcome section
        if (welcomeStreakEl) {
            welcomeStreakEl.textContent = statsData.current_streak + ' days';
        }
        if (todayPerformanceEl) {
            // Calculate today's performance based on tasks completed
            const tasksToday = statsData.tasks_completed_today || 0;
            const expectedTasks = 3; // Assume 3 tasks per day
            const performance = expectedTasks > 0 ? Math.round((tasksToday / expectedTasks) * 100) : 0;
            todayPerformanceEl.textContent = performance + '%';
        }
        
        console.log('Stats updated:', {
            streak: statsData.current_streak,
            program_days: statsData.program_days_completed + '/' + statsData.program_duration,
            wellness: statsData.wellness_score
        });
    } else {
        loadStatsFallback();
    }
}

function loadStatsFallback() {
    // Removed - dashboard now uses API data only
    showErrorMessage('Unable to load statistics.');
}

// Removed static calculation functions - now using API data only

function loadTodaysMood(moodData = null) {
    if (moodData) {
        const moodElement = document.querySelector(`[data-mood="${moodData.mood}"]`);
        if (moodElement) {
            moodElement.classList.add('selected');
        }
    } else {
        loadTodaysMoodFallback();
    }
}

function loadTodaysMoodFallback() {
    // Removed - dashboard now uses API data only
}

function setupMoodTracker() {
    document.querySelectorAll('.mood-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
            // Add selected class to clicked option
            this.classList.add('selected');
            
            const mood = this.dataset.mood;
            const moodScore = getMoodScore(mood);
            
            // Save mood to API
            fetch('api/dashboard.php?action=save_mood', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mood: mood,
                    mood_score: moodScore,
                    note: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show feedback
                    const feedback = document.getElementById('moodFeedback') || document.getElementById('mood-feedback');
                    if (feedback) {
                        feedback.style.display = 'block';
                        setTimeout(() => {
                            feedback.style.display = 'none';
                        }, 3000);
                    }
                    
                    // Refresh stats
                    loadDashboardData();
                } else {
                    console.error('Failed to save mood:', data.message);
                    // Fallback to localStorage
                    MoodTracker.saveMood(mood, '');
                }
            })
            .catch(error => {
                console.error('Error saving mood:', error);
                // Fallback to localStorage
                MoodTracker.saveMood(mood, '');
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

function loadJournalEntries(entriesData = null) {
    const container = document.getElementById('journalEntries') || document.getElementById('journal-entries');
    if (!container) return;
    
    const entries = entriesData || [];
    
    if (entries.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="ti ti-book" style="font-size: 2rem; color: #6c757d;"></i>
                <p class="text-muted mt-2 mb-0">No journal entries yet</p>
                <a href="./journal.php" class="btn btn-sm btn-primary mt-2">Write Your First Entry</a>
            </div>
        `;
        return;
    }
    
    container.innerHTML = entries.map(entry => `
        <div class="border-bottom py-2 cursor-pointer" onclick="window.location.href='./journal.php'">
            <h6 class="mb-1">${entry.title}</h6>
            <p class="text-muted small mb-1">${truncateText(entry.content, 60)}</p>
            <small class="text-muted">${formatRelativeTime(entry.created_at)}</small>
        </div>
    `).join('');
}

function loadJournalEntriesFallback() {
    // Removed - dashboard now uses API data only
}

function loadTodaysTasks(tasksData = null) {
    const container = document.getElementById('todaysTasks') || document.getElementById('tasks-list');
    if (!container) return;
    
    const tasks = tasksData || [];
    
    if (tasks.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="ti ti-clipboard-check" style="font-size: 2rem; color: #6c757d;"></i>
                <p class="text-muted mt-2 mb-0">Complete your assessment to get personalized tasks</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = tasks.map(task => `
        <div class="task-item">
            <input type="checkbox" class="task-checkbox" ${task.completed_today ? 'checked' : ''} 
                   data-task-id="${task.id}" onchange="toggleTask(${task.id}, this.checked)">
            <div class="flex-grow-1">
                <div class="fw-semibold">${task.title}</div>
                <small class="text-muted">${task.description}</small>
            </div>
            <small class="${task.completed_today ? 'text-success' : 'text-warning'}">${task.completed_today ? 'Completed' : 'Pending'}</small>
        </div>
    `).join('');
}

function loadTodaysTasksFallback() {
    // Removed - dashboard now uses API data only
}

function toggleTask(taskId, completed) {
    fetch('api/dashboard.php?action=complete_task', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            task_id: taskId,
            completed: completed,
            notes: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh dashboard data to update stats
            loadDashboardData();
        } else {
            console.error('Failed to update task:', data.message);
            // Revert checkbox state
            const checkbox = document.querySelector(`[data-task-id="${taskId}"]`);
            if (checkbox) {
                checkbox.checked = !completed;
            }
        }
    })
    .catch(error => {
        console.error('Error updating task:', error);
        // Revert checkbox state
        const checkbox = document.querySelector(`[data-task-id="${taskId}"]`);
        if (checkbox) {
            checkbox.checked = !completed;
        }
    });
}

// Removed localStorage task toggle - now using API only

function loadProgramInfo(programData = null) {
    console.log('Loading program info:', programData);
    
    if (programData) {
        // Use API data
        const programCard = document.getElementById('programInfoCard');
        if (programCard) {
            programCard.innerHTML = `
                <div class="program-content">
                    <h4 class="mb-2">${programData.icon || '🧘'} ${programData.name}</h4>
                    <p class="mb-2">${programData.description}</p>
                </div>
            `;
        }
        
        // Update program elements if they exist
        const programName = document.getElementById('programName');
        const programDescription = document.getElementById('programDescription');
        const programReason = document.getElementById('programReason');
        
        console.log('Program elements found:', {
            programName: !!programName,
            programDescription: !!programDescription,
            programReason: !!programReason
        });
        
        if (programName) {
            programName.textContent = `${programData.icon || '🎯'} ${programData.name}`;
            console.log('Updated program name:', programData.name);
        }
        if (programDescription) {
            programDescription.textContent = programData.description;
            console.log('Updated program description:', programData.description);
        }
        if (programReason) {
            programReason.textContent = 'Based on your assessment results';
        }
    } else {
        console.log('No program data, loading fallback');
        loadProgramInfoFallback();
    }
}

function loadProgramInfoFallback() {
    console.log('Loading program info fallback...');
    
    // Show a more helpful message instead of just leaving static text
    const programName = document.getElementById('programName');
    const programDescription = document.getElementById('programDescription');
    const programReason = document.getElementById('programReason');
    
    if (programName) {
        programName.textContent = '⚠️ Unable to load program data';
        programName.style.color = '#dc3545';
    }
    if (programDescription) {
        programDescription.textContent = 'There was an issue loading your program information. Please refresh the page or contact support.';
        programDescription.style.color = '#dc3545';
    }
    if (programReason) {
        programReason.textContent = 'API connection issue';
        programReason.style.color = '#dc3545';
    }
    
    // Try to load from localStorage as last resort
    const userData = localStorage.getItem('healNestUser');
    if (userData) {
        try {
            const user = JSON.parse(userData);
            console.log('Found user data in localStorage:', user);
            if (user.has_program) {
                if (programName) {
                    programName.textContent = '🎯 Your Wellness Program (Cached)';
                    programName.style.color = '#5D87FF';
                }
                if (programDescription) {
                    programDescription.textContent = 'You have a wellness program assigned. Please refresh the page to load the latest details.';
                    programDescription.style.color = '#6c757d';
                }
            }
        } catch (e) {
            console.error('Error parsing localStorage user data:', e);
        }
    }
}

function updateStreak() {
    // Update progress via API
    fetch('api/dashboard.php?action=update_progress', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh dashboard data
            loadDashboardData();
        } else {
            console.error('Failed to update progress:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating progress:', error);
        // Fallback to just refreshing stats
        loadStatsFallback();
    });
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear client-side storage first
        Auth.logout();
        
        // Call server-side logout
        fetch('pages/logout.php', {
            method: 'POST',
            credentials: 'same-origin'
        }).then(() => {
            window.location.href = 'index.html';
        }).catch(() => {
            // Fallback - redirect anyway
            window.location.href = 'index.html';
        });
    }
}

// Utility functions
function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    
    return date.toLocaleDateString();
}

function getMoodEmoji(mood) {
    const emojis = {
        excellent: '😄',
        good: '😊',
        neutral: '😐',
        challenging: '😔',
        difficult: '😢'
    };
    return emojis[mood] || '😐';
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    
    if (sidebar && mobileBtn && window.innerWidth <= 768 && !sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// Initialize interactive elements when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeInteractiveElements();
});

function initializeInteractiveElements() {
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('headerCollapse');
    const sidebar = document.querySelector('.left-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    // Initialize mood tracker
    setupMoodTracker();
}