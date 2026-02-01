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

async function ensureAuthentication() {
    // Check if user is authenticated on server side
    try {
        const response = await fetch('../api/check_session.php');
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
        const response = await fetch('../api/login.php', {
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

async function loadProgramData() {
    console.log('Loading program data...');
    
    try {
        const response = await fetch('../api/dashboard.php?action=get_dashboard_data', {
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
        console.log('Program API response:', data);
        
        if (data.success) {
            if (data.user.program) {
                loadProgramInfo(data.user.program);
                loadProgramStats(data.stats);
                loadProgramTasks(data.tasks);
            } else {
                showNoProgramMessage();
            }
        } else {
            if (data.message === 'Not authenticated') {
                window.location.href = './login.php';
                return;
            }
            showErrorMessage('Failed to load program data: ' + data.message);
        }
        
    } catch (error) {
        console.error('Program data loading error:', error);
        showErrorMessage('Unable to load program data. Please refresh the page.');
    }
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
    console.log('Loading program stats:', statsData);
    
    // Update stats
    const daysCompleted = document.getElementById('daysCompleted');
    const daysRemaining = document.getElementById('daysRemaining');
    const currentStreak = document.getElementById('currentStreak');
    const completionRate = document.getElementById('completionRate');
    const progressPercentage = document.getElementById('progressPercentage');
    const programProgressBar = document.getElementById('programProgressBar');
    
    if (daysCompleted) {
        daysCompleted.textContent = statsData.program_days_completed || 0;
    }
    if (daysRemaining) {
        daysRemaining.textContent = statsData.program_days_remaining || 0;
    }
    if (currentStreak) {
        currentStreak.textContent = statsData.current_streak || 0;
    }
    
    // Calculate completion rate
    const totalDays = statsData.program_duration || 30;
    const completedDays = statsData.program_days_completed || 0;
    const rate = totalDays > 0 ? Math.round((completedDays / totalDays) * 100) : 0;
    
    if (completionRate) {
        completionRate.textContent = rate + '%';
    }
    if (progressPercentage) {
        progressPercentage.textContent = rate + '%';
    }
    if (programProgressBar) {
        programProgressBar.style.width = rate + '%';
    }
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