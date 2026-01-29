document.addEventListener('DOMContentLoaded', function() {
    // Ensure user is properly authenticated
    ensureAuthentication().then(() => {
        // Load user's daily tasks
        loadDailyTasks();
        loadWellnessTip();
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

function loadDailyTasks() {
    // Try to load from API first
    fetch('../api/dashboard.php?action=get_todays_tasks')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.tasks && data.tasks.length > 0) {
                displayDailyTasks(data.tasks);
            } else {
                // Fallback to demo data
                loadDailyTasksFallback();
            }
        })
        .catch(error => {
            console.log('API not available, using demo data:', error);
            loadDailyTasksFallback();
        });
}

function loadDailyTasksFallback() {
    const user = Auth.getCurrentUser();
    
    // Get program-specific tasks based on user's assigned program
    const programTasks = {
        1: [ // Mindfulness & Stress Relief (matches database)
            {
                id: 1,
                title: 'Morning Meditation',
                description: '10 minutes of guided mindfulness meditation',
                completed: false
            },
            {
                id: 2,
                title: 'Breathing Exercise',
                description: '5-minute deep breathing for stress relief',
                completed: false
            },
            {
                id: 3,
                title: 'Stress Level Check',
                description: 'Rate and reflect on your stress level (1-10)',
                completed: false
            }
        ],
        2: [ // Alternative program
            {
                id: 1,
                title: 'Anxiety Breathing',
                description: '4-7-8 breathing technique for anxiety relief',
                completed: false
            },
            {
                id: 2,
                title: 'Grounding Exercise',
                description: '5-4-3-2-1 grounding technique when feeling anxious',
                completed: false
            },
            {
                id: 3,
                title: 'Worry Time',
                description: 'Dedicated 10 minutes to process worries constructively',
                completed: false
            }
        ]
    };
    
    const programId = user.assigned_program_id || 1;
    const tasks = programTasks[programId] || programTasks[1];
    
    // Check completed tasks from localStorage
    const completedTasks = JSON.parse(localStorage.getItem('healNestCompletedTasks') || '[]');
    const today = new Date().toISOString().split('T')[0];
    const todayCompleted = completedTasks.filter(task => task.date === today);
    
    // Mark tasks as completed if they were completed today
    tasks.forEach(task => {
        task.completed_today = todayCompleted.some(completed => completed.taskId === task.id);
        // Keep backward compatibility
        task.completed = task.completed_today;
    });
    
    displayDailyTasks(tasks);
}

function displayDailyTasks(tasks) {
    const tasksList = document.getElementById('dailyTasksList');
    
    // Handle both API response format (completed_today) and fallback format (completed)
    const completedCount = tasks.filter(task => task.completed_today || task.completed).length;
    const totalCount = tasks.length;
    
    // Update progress
    const progressElement = document.getElementById('tasksProgress');
    if (progressElement) {
        progressElement.textContent = `Today's Progress: ${completedCount}/${totalCount} completed`;
    }
    
    // Update tasks list
    tasksList.innerHTML = tasks.map(task => {
        const isCompleted = task.completed_today || task.completed;
        let completionInfo = '';
        
        // Show completion details if available
        if (isCompleted && task.completion_details) {
            const completedTime = new Date(task.completion_details.completed_at).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
            completionInfo = `<div class="completion-info text-muted small">Completed at ${completedTime}</div>`;
        }
        
        return `
            <div class="daily-task-item ${isCompleted ? 'completed' : ''}">
                <div class="task-checkbox-wrapper">
                    <input type="checkbox" class="task-checkbox" id="task${task.id}" 
                           data-task-id="${task.id}" ${isCompleted ? 'checked' : ''} 
                           onchange="toggleDailyTask(${task.id})">
                </div>
                <div class="task-content">
                    <div class="task-title">${task.title}</div>
                    <div class="task-description">${task.description}</div>
                    ${completionInfo}
                </div>
                <div class="task-status">
                    <span class="status-badge ${isCompleted ? 'completed' : 'pending'}">
                        ${isCompleted ? 'Completed' : 'Pending'}
                    </span>
                </div>
            </div>
        `;
    }).join('');
    
    // Update user info
    const user = Auth.getCurrentUser();
    const userAvatar = document.getElementById('userAvatar');
    if (userAvatar) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        userAvatar.textContent = userName.charAt(0).toUpperCase();
    }
    
    const welcomeText = document.getElementById('welcomeText');
    if (welcomeText) {
        const displayName = user.full_name || user.fullName || user.name || user.email.split('@')[0];
        welcomeText.textContent = `Welcome back, ${displayName}!`;
    }
}

function toggleDailyTask(taskId) {
    const checkbox = document.querySelector(`[data-task-id="${taskId}"]`);
    const taskItem = checkbox.closest('.daily-task-item');
    const statusBadge = taskItem.querySelector('.status-badge');
    
    // Try to update via API first
    fetch('../api/dashboard.php?action=complete_task', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            task_id: taskId,
            completed: checkbox.checked,
            notes: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTaskUI(taskItem, statusBadge, checkbox.checked);
            updateProgress();
            
            // Reload tasks to ensure we have the latest state from database
            setTimeout(() => {
                loadDailyTasks();
            }, 500);
        } else {
            console.error('Failed to update task:', data.message);
            toggleDailyTaskFallback(taskId, checkbox.checked, taskItem, statusBadge);
        }
    })
    .catch(error => {
        console.error('Error updating task:', error);
        toggleDailyTaskFallback(taskId, checkbox.checked, taskItem, statusBadge);
    });
}

function toggleDailyTaskFallback(taskId, completed, taskItem, statusBadge) {
    const today = new Date().toISOString().split('T')[0];
    let completedTasks = JSON.parse(localStorage.getItem('healNestCompletedTasks') || '[]');
    
    // Check if task is already completed today
    const existingIndex = completedTasks.findIndex(task => 
        task.taskId === taskId && task.date === today
    );
    
    if (completed) {
        if (existingIndex < 0) {
            // Add completion
            completedTasks.push({
                taskId: taskId,
                date: today,
                completedAt: new Date().toISOString()
            });
        }
    } else {
        if (existingIndex >= 0) {
            // Remove completion
            completedTasks.splice(existingIndex, 1);
        }
    }
    
    localStorage.setItem('healNestCompletedTasks', JSON.stringify(completedTasks));
    updateTaskUI(taskItem, statusBadge, completed);
    updateProgress();
}

function updateTaskUI(taskItem, statusBadge, completed) {
    if (completed) {
        taskItem.classList.add('completed');
        statusBadge.textContent = 'Completed';
        statusBadge.className = 'status-badge completed';
    } else {
        taskItem.classList.remove('completed');
        statusBadge.textContent = 'Pending';
        statusBadge.className = 'status-badge pending';
    }
}

function updateProgress() {
    const checkboxes = document.querySelectorAll('.task-checkbox');
    const completedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const totalCount = checkboxes.length;
    
    const progressElement = document.getElementById('tasksProgress');
    if (progressElement) {
        progressElement.textContent = `Today's Progress: ${completedCount}/${totalCount} completed`;
    }
}

function loadWellnessTip() {
    // Array of wellness tips
    const wellnessTips = [
        "Celebrate your wins, no matter how small. You're doing great!",
        "Take a moment to breathe deeply. Your mental health matters.",
        "Progress isn't always linear. Be patient with yourself.",
        "Small steps every day lead to big changes over time.",
        "Remember: it's okay to have difficult days. Tomorrow is a new opportunity.",
        "Your feelings are valid. Take time to acknowledge them.",
        "Self-care isn't selfish. You deserve to prioritize your wellbeing.",
        "Focus on what you can control today, and let go of what you can't.",
        "Every step forward, no matter how small, is still progress.",
        "You are stronger than you think and more resilient than you know."
    ];
    
    // Get tip of the day (based on date)
    const today = new Date();
    const dayOfYear = Math.floor((today - new Date(today.getFullYear(), 0, 0)) / 1000 / 60 / 60 / 24);
    const tipIndex = dayOfYear % wellnessTips.length;
    
    const tipElement = document.getElementById('wellnessTipText');
    if (tipElement) {
        tipElement.textContent = wellnessTips[tipIndex];
    }
}

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