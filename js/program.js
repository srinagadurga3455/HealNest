// Program page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Program page loaded');
    loadProgramData();
});

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