document.addEventListener('DOMContentLoaded', function() {
    // Ensure user is properly authenticated
    ensureAuthentication().then(() => {
        // Update user info
        updateUserInfo();
        // Load user's daily tasks
        loadDailyTasks();
        loadWellnessTip();
    });
    
    // Listen for profile updates
    window.addEventListener('storage', function(e) {
        if (e.key === 'healNestUser') {
            updateUserInfo();
        }
    });
    
    window.addEventListener('userProfileUpdated', function() {
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

function loadDailyTasks() {
    // Load tasks from the new tasks API
    fetch('../api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_user_tasks'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (data.tasks && data.tasks.length > 0) {
                displayDailyTasks(data.tasks);
                updateTaskProgress();
            } else {
                displayNoTasks(data.message || 'No tasks assigned yet. Complete your assessment to get personalized tasks.');
            }
        } else {
            console.error('Failed to load tasks:', data.message);
            loadDailyTasksFallback();
        }
    })
    .catch(error => {
        console.log('API not available, using demo data:', error);
        loadDailyTasksFallback();
    });
}

function displayNoTasks(message) {
    const tasksList = document.getElementById('dailyTasksList');
    
    tasksList.innerHTML = `
        <div class="no-tasks-message">
            <div class="no-tasks-icon">📋</div>
            <h3>No Tasks Yet</h3>
            <p>${message}</p>
            <a href="assessment.php" class="btn-primary" style="margin-top: 1rem; display: inline-block; text-decoration: none;">
                Take Assessment
            </a>
        </div>
    `;
    
    // Update progress to 0
    updateProgressDisplay(0, 0);
}

function updateTaskProgress() {
    // Get current progress from the API
    fetch('../api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_task_progress'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProgressDisplay(data.completed_tasks, data.total_tasks);
        }
    })
    .catch(error => {
        console.error('Error getting task progress:', error);
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
    updateProgressDisplay(completedCount, totalCount);
    
    // Update tasks list with clean, minimal design
    tasksList.innerHTML = tasks.map(task => {
        const isCompleted = task.completed_today || task.completed;
        
        return `
            <div class="task-item ${isCompleted ? 'completed' : ''}">
                <div class="task-checkbox">
                    <input type="checkbox" id="task${task.id}" 
                           data-task-id="${task.id}" ${isCompleted ? 'checked' : ''}>
                    <div class="checkbox-custom"></div>
                </div>
                <div class="task-content">
                    <div class="task-header">
                        <div class="task-info">
                            <h4 class="task-title">${task.title}</h4>
                        </div>
                    </div>
                    <p class="task-description">${task.description}</p>
                </div>
            </div>
        `;
    }).join('');
    
    // Add event listeners to checkboxes after creating the HTML
    tasks.forEach(task => {
        const checkbox = document.getElementById(`task${task.id}`);
        const checkboxContainer = checkbox?.closest('.task-checkbox');
        
        if (checkbox) {
            // Add change event listener to checkbox
            checkbox.addEventListener('change', function(e) {
                console.log(`Task ${task.id} checkbox changed to:`, this.checked);
                toggleDailyTask(task.id);
            });
            
            // Add click event listener to checkbox container for better UX
            if (checkboxContainer) {
                checkboxContainer.addEventListener('click', function(e) {
                    // Prevent double-triggering if clicking directly on checkbox
                    if (e.target === checkbox) return;
                    
                    console.log(`Task ${task.id} checkbox container clicked`);
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            }
        }
    });
    
    // Update user info
    updateUserInfo();
}

function updateProgressDisplay(completedCount, totalCount) {
    const progressElement = document.getElementById('tasksProgress');
    const progressPercent = document.getElementById('progressPercent');
    const progressRing = document.getElementById('progressRing');
    
    if (progressElement) {
        progressElement.textContent = `${completedCount} of ${totalCount} tasks completed`;
    }
    
    if (progressPercent) {
        const percentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
        progressPercent.textContent = `${percentage}%`;
        
        // Update progress ring
        if (progressRing) {
            const circumference = 339.292; // 2 * π * 54
            const offset = circumference - (percentage / 100) * circumference;
            progressRing.style.strokeDashoffset = offset;
        }
    }
}

function updateUserInfo() {
    const user = Auth.getCurrentUser();
    const userAvatar = document.getElementById('userAvatar');
    
    if (userAvatar && user) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        userAvatar.textContent = userName.charAt(0).toUpperCase();
    }
}

function toggleDailyTask(taskId) {
    console.log(`Toggling task ${taskId}`);
    const checkbox = document.querySelector(`[data-task-id="${taskId}"]`);
    const taskItem = checkbox.closest('.task-item');
    
    console.log(`Checkbox checked state: ${checkbox.checked}`);
    
    // Update UI immediately for better user experience
    updateTaskUI(taskItem, null, checkbox.checked);
    updateTaskProgress();
    
    // Update via the new tasks API
    fetch('../api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'complete_task',
            task_id: taskId,
            completed: checkbox.checked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Task updated successfully via API');
            // Update progress after successful API call
            updateTaskProgress();
        } else {
            console.error('Failed to update task via API:', data.message);
            // Revert UI changes if API call failed
            checkbox.checked = !checkbox.checked;
            updateTaskUI(taskItem, null, checkbox.checked);
            updateTaskProgress();
        }
    })
    .catch(error => {
        console.error('Error updating task via API:', error);
        // Fallback to localStorage
        toggleDailyTaskFallback(taskId, checkbox.checked, taskItem, null);
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
    updateTaskUI(taskItem, null, completed);
    updateProgress();
}

function updateTaskUI(taskItem, statusBadge, completed) {
    console.log(`Updating task UI - completed: ${completed}`);
    
    if (completed) {
        taskItem.classList.add('completed');
        console.log('Added completed class');
    } else {
        taskItem.classList.remove('completed');
        console.log('Removed completed class');
    }
    
    // Ensure checkbox state is correct
    const checkbox = taskItem.querySelector('input[type="checkbox"]');
    if (checkbox) {
        checkbox.checked = completed;
        console.log(`Set checkbox checked to: ${completed}`);
    }
}

function updateProgress() {
    const checkboxes = document.querySelectorAll('.task-checkbox input[type="checkbox"]');
    const completedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const totalCount = checkboxes.length;
    
    updateProgressDisplay(completedCount, totalCount);
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