// Program page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Program page loaded');
    
    // Ensure user is properly authenticated and update profile
    ensureAuthentication().then(() => {
        updateUserInfo();
        loadProgramData();
    }).catch(error => {
        console.error('Authentication error:', error);
        // Still try to update user info from localStorage
        updateUserInfo();
        loadProgramData();
    });
    
    // Also load program data immediately as fallback
    setTimeout(() => {
        console.log('Fallback program data load...');
        loadProgramDataDirect();
    }, 1000);
    
    // Also update user info immediately in case Auth is already available
    setTimeout(() => {
        updateUserInfo();
    }, 100);
    
    // Listen for profile updates
    window.addEventListener('storage', function(e) {
        if (e.key === 'healNestUser') {
            console.log('Storage change detected, updating user info');
            updateUserInfo();
        }
    });
    
    window.addEventListener('userProfileUpdated', function() {
        console.log('Profile updated event received');
        updateUserInfo();
    });
});

// Direct program data loading function
async function loadProgramDataDirect() {
    console.log('=== DIRECT PROGRAM DATA LOAD ===');
    
    try {
        const response = await fetch('api/dashboard.php?action=get_dashboard_data', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Direct API response:', data);
        
        if (data.success && data.stats) {
            console.log('Direct loading - updating stats...');
            
            // Calculate values directly
            const totalDays = data.stats.program_duration || 30;
            const completedDays = data.stats.program_days_completed || 0;
            const remainingDays = Math.max(0, totalDays - completedDays);
            const rate = Math.round((completedDays / totalDays) * 100);
            
            // Update DOM elements directly
            const daysCompletedEl = document.getElementById('daysCompleted');
            const daysRemainingEl = document.getElementById('daysRemaining');
            const completionRateEl = document.getElementById('completionRate');
            const progressPercentageEl = document.getElementById('progressPercentage');
            const programProgressBarEl = document.getElementById('programProgressBar');
            const currentStreakEl = document.getElementById('currentStreak');
            
            if (daysCompletedEl) {
                daysCompletedEl.textContent = completedDays;
                console.log('Set daysCompleted to:', completedDays);
            }
            if (daysRemainingEl) {
                daysRemainingEl.textContent = remainingDays;
                console.log('Set daysRemaining to:', remainingDays);
            }
            if (completionRateEl) {
                completionRateEl.textContent = rate + '%';
                console.log('Set completionRate to:', rate + '%');
            }
            if (progressPercentageEl) {
                progressPercentageEl.textContent = rate + '%';
                console.log('Set progressPercentage to:', rate + '%');
            }
            if (programProgressBarEl) {
                programProgressBarEl.style.width = rate + '%';
                console.log('Set programProgressBar to:', rate + '%');
            }
            if (currentStreakEl) {
                currentStreakEl.textContent = data.stats.current_streak || 0;
                console.log('Set currentStreak to:', data.stats.current_streak || 0);
            }
            
            console.log('Direct program stats update complete');
        }
        
    } catch (error) {
        console.error('Direct program data loading error:', error);
    }
}

async function ensureAuthentication() {
    // Check if user is authenticated on server side
    try {
        const response = await fetch('api/check_session.php');
        const data = await response.json();
        
        if (!data.logged_in) {
            // User not authenticated on server, try to auto-login demo user
            const user = Auth.getCurrentUser();
            if (user && user.email === 'demo@healnest.com') {
                await autoLoginDemoUser();
            }
        }
    } catch (error) {
        console.log('Session check failed, continuing with fallback');
    }
}

async function autoLoginDemoUser() {
    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: 'demo@healnest.com',
                password: 'demo123'
            })
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Demo user auto-login successful');
        }
    } catch (error) {
        console.log('Auto-login failed, using fallback mode');
    }
}

function updateUserInfo() {
    console.log('Updating user info in program page...');
    
    // Check if Auth object exists
    if (typeof Auth === 'undefined') {
        console.error('Auth object not available');
        return;
    }
    
    const user = Auth.getCurrentUser();
    console.log('Current user:', user);
    const userAvatar = document.getElementById('userAvatar');
    console.log('User avatar element:', userAvatar);
    
    if (userAvatar) {
        if (user) {
            const userName = user.full_name || user.fullName || user.name || user.email;
            console.log('User name for avatar:', userName);
            const avatarLetter = userName.charAt(0).toUpperCase();
            console.log('Setting avatar to:', avatarLetter);
            userAvatar.textContent = avatarLetter;
        } else {
            console.log('No user data found, setting default avatar');
            userAvatar.textContent = 'U';
        }
    } else {
        console.error('userAvatar element not found');
    }
}

// Manual trigger function for testing
window.testProfileUpdate = function() {
    console.log('Manual profile update test');
    updateUserInfo();
};

async function loadPersonalizedTasks() {
    console.log('=== LOADING PERSONALIZED TASKS FOR PROGRAM ===');
    
    try {
        console.log('Making API call to: api/tasks.php');
        const response = await fetch('api/tasks.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_personalized_tasks'
            })
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Raw response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text was:', responseText);
            throw new Error('Invalid JSON response');
        }
        
        console.log('Parsed personalized tasks response:', data);
        
        if (data.success && data.tasks && data.tasks.length > 0) {
            console.log('Found', data.tasks.length, 'personalized tasks');
            displayProgramTasks(data.tasks);
        } else {
            console.log('No personalized tasks found, using fallback. Message:', data.message);
            displayDefaultTasks();
        }
        
    } catch (error) {
        console.error('Error loading personalized tasks:', error);
        console.log('Using default tasks due to error');
        displayDefaultTasks();
    }
}

async function loadProgramData() {
    console.log('=== LOADING PROGRAM DATA ===');
    
    try {
        // Load personalized tasks first
        await loadPersonalizedTasks();
        
        console.log('Making API call to dashboard...');
        const response = await fetch('api/dashboard.php?action=get_dashboard_data', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        console.log('Dashboard API response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        console.log('=== FULL DASHBOARD API RESPONSE ===');
        console.log(data);
        
        if (data.success) {
            console.log('API call successful, processing data...');
            
            if (data.user && data.user.program) {
                console.log('Loading program info...');
                loadProgramInfo(data.user.program);
            } else {
                console.log('No program data found');
                showNoProgramMessage();
            }
            
            if (data.stats) {
                console.log('Loading program stats...');
                console.log('Stats data being passed:', data.stats);
                loadProgramStats(data.stats);
                
                // Also do direct update as backup
                const totalDays = data.stats.program_duration || 30;
                const completedDays = data.stats.program_days_completed || 0;
                const remainingDays = Math.max(0, totalDays - completedDays);
                const rate = Math.round((completedDays / totalDays) * 100);
                
                // Ensure DOM elements are updated
                setTimeout(() => {
                    const daysRemainingEl = document.getElementById('daysRemaining');
                    if (daysRemainingEl && daysRemainingEl.textContent === '0') {
                        console.log('Backup update - setting daysRemaining to:', remainingDays);
                        daysRemainingEl.textContent = remainingDays;
                    }
                }, 500);
            } else {
                console.log('No stats data found');
            }
        } else {
            console.error('API returned error:', data.message);
            if (data.message === 'Not authenticated') {
                window.location.href = './login.php';
                return;
            }
            showErrorMessage('Failed to load program data: ' + data.message);
        }
        
    } catch (error) {
        console.error('Program data loading error:', error);
        showErrorMessage('Unable to load program data. Please refresh the page.');
        // Still try to load tasks even if program data fails
        await loadPersonalizedTasks();
    }
    
    console.log('=== PROGRAM DATA LOADING COMPLETE ===');
}

function displayProgramTasks(tasks) {
    console.log('=== DISPLAYING PROGRAM TASKS ===');
    console.log('Tasks to display:', tasks);
    console.log('Number of tasks:', tasks.length);
    
    const tasksList = document.getElementById('programTasksList');
    console.log('Tasks list element:', tasksList);
    
    if (!tasksList) {
        console.error('Program tasks list element not found');
        return;
    }
    
    // Clear existing tasks
    console.log('Clearing existing tasks...');
    tasksList.innerHTML = '';
    
    // Map task categories to appropriate icons
    const categoryIcons = {
        'Emotional Wellbeing': '💝',
        'Anxiety & Stress': '🫁',
        'Sleep & Rest': '😴',
        'Social Connection': '🤝',
        'Self-Care': '🌱',
        'General Wellness': '🧘',
        'Mindfulness': '🧘',
        'Reflection': '🤔'
    };
    
    // Add tasks to the list (limit to first 4 for overview)
    const displayTasks = tasks.slice(0, 4);
    console.log('Tasks to display after slice:', displayTasks);
    
    displayTasks.forEach((task, index) => {
        console.log(`Creating task ${index + 1}:`, task);
        
        const taskItem = document.createElement('div');
        taskItem.className = 'task-item';
        
        const icon = categoryIcons[task.category] || '✓';
        console.log(`Task ${index + 1} icon:`, icon, 'for category:', task.category);
        
        taskItem.innerHTML = `
            <div class="task-icon">${icon}</div>
            <div class="task-content">
                <h4>${task.title}</h4>
                <p>${task.description}</p>
            </div>
        `;
        
        console.log(`Adding task ${index + 1} to DOM`);
        tasksList.appendChild(taskItem);
    });
    
    console.log(`Successfully displayed ${displayTasks.length} personalized tasks in program overview`);
    console.log('Final tasks list HTML:', tasksList.innerHTML);
}

function displayDefaultTasks() {
    console.log('Displaying default tasks');
    
    const tasksList = document.getElementById('programTasksList');
    if (!tasksList) return;
    
    // Clear existing tasks
    tasksList.innerHTML = '';
    
    const defaultTasks = [
        {
            title: 'Morning Meditation',
            description: '10 minutes of guided mindfulness meditation',
            icon: '🧘'
        },
        {
            title: 'Breathing Exercise',
            description: '5-minute deep breathing for stress relief',
            icon: '🫁'
        },
        {
            title: 'Gratitude Practice',
            description: 'Write down 3 things you\'re grateful for today',
            icon: '💝'
        }
    ];
    
    defaultTasks.forEach(task => {
        const taskItem = document.createElement('div');
        taskItem.className = 'task-item';
        
        taskItem.innerHTML = `
            <div class="task-icon">${task.icon}</div>
            <div class="task-content">
                <h4>${task.title}</h4>
                <p>${task.description}</p>
            </div>
        `;
        
        tasksList.appendChild(taskItem);
    });
}

function loadProgramInfo(programData) {
    console.log('Loading program info:', programData);
    
    // Update program header
    const programTitle = document.getElementById('programTitle');
    const programDescription = document.getElementById('programDescription');
    const programReason = document.getElementById('programReason');
    const programExplanation = document.getElementById('programExplanation');
    
    if (programTitle) {
        programTitle.textContent = `${programData.icon || '🎯'} ${programData.name}`;
    }
    if (programDescription) {
        programDescription.textContent = programData.description;
    }
    if (programReason) {
        programReason.textContent = 'Based on your assessment results';
    }
    if (programExplanation) {
        programExplanation.textContent = programData.description;
    }
}

function loadProgramStats(statsData) {
    console.log('=== LOADING PROGRAM STATS ===');
    console.log('Raw stats data received:', statsData);
    
    // Update stats
    const daysCompleted = document.getElementById('daysCompleted');
    const daysRemaining = document.getElementById('daysRemaining');
    const currentStreak = document.getElementById('currentStreak');
    const completionRate = document.getElementById('completionRate');
    const progressPercentage = document.getElementById('progressPercentage');
    const programProgressBar = document.getElementById('programProgressBar');
    
    // Calculate values
    const totalDays = statsData.program_duration || 30;
    const completedDays = statsData.program_days_completed || 0;
    const remainingDays = Math.max(0, totalDays - completedDays);
    const rate = totalDays > 0 ? Math.round((completedDays / totalDays) * 100) : 0;
    
    console.log('Calculated values:', {
        totalDays,
        completedDays,
        remainingDays,
        rate
    });
    
    console.log('DOM elements found:', {
        daysCompleted: !!daysCompleted,
        daysRemaining: !!daysRemaining,
        currentStreak: !!currentStreak,
        completionRate: !!completionRate,
        progressPercentage: !!progressPercentage,
        programProgressBar: !!programProgressBar
    });
    
    if (daysCompleted) {
        daysCompleted.textContent = completedDays;
        console.log('Set daysCompleted to:', completedDays);
    }
    if (daysRemaining) {
        daysRemaining.textContent = remainingDays;
        console.log('Set daysRemaining to:', remainingDays);
    }
    if (currentStreak) {
        currentStreak.textContent = statsData.current_streak || 0;
        console.log('Set currentStreak to:', statsData.current_streak || 0);
    }
    if (completionRate) {
        completionRate.textContent = rate + '%';
        console.log('Set completionRate to:', rate + '%');
    }
    if (progressPercentage) {
        progressPercentage.textContent = rate + '%';
        console.log('Set progressPercentage to:', rate + '%');
    }
    if (programProgressBar) {
        programProgressBar.style.width = rate + '%';
        console.log('Set programProgressBar width to:', rate + '%');
    }
    
    console.log('=== PROGRAM STATS LOADING COMPLETE ===');
}

function loadProgramTasks(tasksData) {
    console.log('Loading program tasks:', tasksData);
    
    const tasksList = document.getElementById('programTasksList');
    if (!tasksList || !tasksData || tasksData.length === 0) {
        return;
    }
    
    // Clear existing tasks
    tasksList.innerHTML = '';
    
    // Add tasks to the list
    tasksData.forEach(task => {
        const listItem = document.createElement('li');
        listItem.innerHTML = `
            <strong>${task.title}:</strong> ${task.description}
            <span class="badge ${task.completed ? 'bg-success' : 'bg-secondary'} ms-2">
                ${task.completed ? 'Completed' : 'Pending'}
            </span>
        `;
        tasksList.appendChild(listItem);
    });
}

function showNoProgramMessage() {
    const programTitle = document.getElementById('programTitle');
    const programDescription = document.getElementById('programDescription');
    
    if (programTitle) {
        programTitle.textContent = '🎯 No Program Assigned';
    }
    if (programDescription) {
        programDescription.innerHTML = 'You haven\'t been assigned a wellness program yet. <a href="./assessment.php">Take the assessment</a> to get started.';
    }
}

function showErrorMessage(message) {
    // Create or update error message display
    let errorDiv = document.getElementById('program-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'program-error';
        errorDiv.className = 'alert alert-danger';
        errorDiv.style.cssText = 'margin: 20px 0;';
        
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
        }
    }
    errorDiv.textContent = message;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear client-side storage first
        Auth.logout();
        
        // Call server-side logout
        fetch('../logout.php', {
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