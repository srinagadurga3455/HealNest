document.addEventListener('DOMContentLoaded', function() {
    // Test API connection first
    testAPIConnection();
    
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

function testAPIConnection() {
    console.log('Testing API connection...');
    fetch('api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'test'
        })
    })
    .then(response => {
        console.log('API test response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('API test raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('API test parsed response:', data);
        } catch (e) {
            console.error('API test - failed to parse JSON:', e);
        }
    })
    .catch(error => {
        console.error('API test - network error:', error);
    });
}

async function ensureAuthentication() {
    console.log('Checking authentication...');
    
    // Check if user is authenticated on server side
    try {
        const response = await fetch('api/check_session.php');
        const data = await response.json();
        
        console.log('Session check result:', data);
        
        if (!data.logged_in) {
            console.log('User not authenticated on server, attempting auto-login...');
            // User not authenticated on server, try to auto-login demo user
            const user = Auth.getCurrentUser();
            if (user && user.email === 'demo@healnest.com') {
                await autoLoginDemoUser();
            } else {
                // Try to login demo user anyway
                console.log('No local user found, trying demo login...');
                await autoLoginDemoUser();
            }
        } else {
            console.log('User is authenticated:', data.user);
        }
    } catch (error) {
        console.log('Session check failed:', error);
        console.log('Attempting demo user auto-login...');
        await autoLoginDemoUser();
    }
}

async function autoLoginDemoUser() {
    console.log('Attempting auto-login for demo user...');
    
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
        
        const text = await response.text();
        console.log('Auto-login raw response:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('Auto-login parsed response:', data);
            
            if (data.success) {
                console.log('Demo user auto-login successful');
                // Store user data locally
                if (data.user) {
                    localStorage.setItem('healNestUser', JSON.stringify(data.user));
                }
                return true;
            } else {
                console.error('Auto-login failed:', data.message);
                return false;
            }
        } catch (e) {
            console.error('Failed to parse auto-login JSON:', e);
            return false;
        }
    } catch (error) {
        console.log('Auto-login network error:', error);
        return false;
    }
}

function loadDailyTasks() {
    console.log('Loading daily tasks...');
    
    // Load personalized tasks based on assessment
    fetch('api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_personalized_tasks'
        })
    })
    .then(response => {
        console.log('Tasks API response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Tasks API raw response:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse tasks JSON:', e);
            throw new Error('Invalid JSON response: ' + text);
        }
    })
    .then(data => {
        console.log('Personalized tasks response:', data);
        if (data.success) {
            if (data.tasks && data.tasks.length > 0) {
                displayDailyTasks(data.tasks);
                updateTaskProgress();
            } else {
                displayNoTasks(data.message || 'No tasks assigned yet. Complete your assessment to get personalized tasks.');
            }
        } else {
            console.error('Failed to load personalized tasks:', data.message);
            // Show error message to user
            displayErrorMessage('Unable to connect to server. Using demo data.');
            loadDailyTasksFallback();
        }
    })
    .catch(error => {
        console.log('Personalized tasks API error:', error);
        // Show error message to user
        displayErrorMessage('Unable to connect to server. Using demo data.');
        loadDailyTasksFallback();
    });
}

function displayErrorMessage(message) {
    // Create or update error message display
    let errorDiv = document.querySelector('.api-error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'api-error-message';
        errorDiv.style.cssText = `
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        `;
        
        // Insert at the top of content area
        const contentArea = document.querySelector('.content-area');
        if (contentArea) {
            contentArea.insertBefore(errorDiv, contentArea.firstChild);
        }
    }
    
    errorDiv.textContent = message;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (errorDiv && errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
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
    console.log('=== UPDATING TASK PROGRESS ===');
    
    // Get current progress from the API
    fetch('api/tasks.php', {
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
        console.log('Task progress API response:', data);
        if (data.success) {
            console.log(`Updating progress: ${data.completed_tasks} of ${data.total_tasks} tasks completed`);
            updateProgressDisplay(data.completed_tasks, data.total_tasks);
        } else {
            console.error('Failed to get task progress:', data.message);
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
    console.log(`=== UPDATING PROGRESS DISPLAY ===`);
    console.log(`Completed: ${completedCount}, Total: ${totalCount}`);
    
    const progressElement = document.getElementById('tasksProgress');
    const progressPercent = document.getElementById('progressPercent');
    const progressRing = document.getElementById('progressRing');
    
    if (progressElement) {
        const progressText = `${completedCount} of ${totalCount} tasks completed`;
        progressElement.textContent = progressText;
        console.log('Updated progress text:', progressText);
    }
    
    if (progressPercent) {
        const percentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
        progressPercent.textContent = `${percentage}%`;
        console.log('Updated progress percentage:', percentage + '%');
        
        // Update progress ring
        if (progressRing) {
            const circumference = 339.292; // 2 * π * 54
            const offset = circumference - (percentage / 100) * circumference;
            progressRing.style.strokeDashoffset = offset;
            console.log('Updated progress ring offset:', offset);
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
    console.log(`=== TOGGLING TASK ${taskId} ===`);
    const checkbox = document.querySelector(`[data-task-id="${taskId}"]`);
    const taskItem = checkbox.closest('.task-item');
    
    console.log(`Checkbox checked state: ${checkbox.checked}`);
    console.log(`Task item:`, taskItem);
    
    // Update UI immediately for better user experience
    updateTaskUI(taskItem, null, checkbox.checked);
    updateTaskProgress();
    
    // First check if user is authenticated
    fetch('api/check_session.php')
    .then(response => response.json())
    .then(sessionData => {
        console.log('Session check:', sessionData);
        
        if (!sessionData.logged_in) {
            console.log('User not logged in, attempting auto-login...');
            return autoLoginDemoUser().then(() => {
                // After auto-login, proceed with task completion
                return completeTaskAPI(taskId, checkbox.checked);
            });
        } else {
            // User is logged in, proceed with task completion
            return completeTaskAPI(taskId, checkbox.checked);
        }
    })
    .then(data => {
        console.log('Task completion result:', data);
        if (data && data.success) {
            console.log('✅ Task updated successfully via API');
            updateTaskProgress();
            
            // Notify dashboard to update progress if it's open in another tab/window
            notifyDashboardUpdate();
            
            // If we're on the dashboard page, update it directly
            if (window.location.pathname.includes('dashboard.php')) {
                updateDashboardProgress();
            }
        } else {
            console.error('❌ Failed to update task via API:', data?.message);
            // Revert UI changes if API call failed
            checkbox.checked = !checkbox.checked;
            updateTaskUI(taskItem, null, checkbox.checked);
            updateTaskProgress();
            alert('Failed to save task completion: ' + (data?.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Error in task completion flow:', error);
        // Revert UI changes if API call failed
        checkbox.checked = !checkbox.checked;
        updateTaskUI(taskItem, null, checkbox.checked);
        updateTaskProgress();
        
        // Use localStorage fallback
        console.log('Using localStorage fallback...');
        toggleDailyTaskFallback(taskId, checkbox.checked, taskItem, null);
    });
}

async function completeTaskAPI(taskId, completed) {
    console.log('Making API call to complete task...');
    
    const response = await fetch('api/tasks.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'complete_task',
            task_id: taskId,
            completed: completed
        })
    });
    
    console.log('API response status:', response.status);
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const text = await response.text();
    console.log('Raw API response:', text);
    
    try {
        return JSON.parse(text);
    } catch (e) {
        console.error('Failed to parse JSON:', e);
        throw new Error('Invalid JSON response: ' + text);
    }
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
        fetch('logout.php', {
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

// Function to notify dashboard about task completion updates
function notifyDashboardUpdate() {
    // Use localStorage to communicate between tabs/windows
    const updateEvent = {
        type: 'task_progress_update',
        timestamp: Date.now()
    };
    
    localStorage.setItem('healNestTaskUpdate', JSON.stringify(updateEvent));
    
    // Remove the item immediately to trigger storage event
    setTimeout(() => {
        localStorage.removeItem('healNestTaskUpdate');
    }, 100);
    
    console.log('Notified dashboard about task progress update');
}

// Function to update dashboard progress directly (if on dashboard page)
function updateDashboardProgress() {
    console.log('Updating dashboard progress directly...');
    
    fetch('api/dashboard.php?action=get_dashboard_data', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.stats) {
            // Update dashboard progress elements if they exist
            const welcomeStreak = document.getElementById('welcomeStreak');
            const todayPerformance = document.getElementById('todayPerformance');
            const currentStreak = document.getElementById('currentStreak');
            
            if (welcomeStreak) {
                welcomeStreak.textContent = (data.stats.current_streak || 0) + ' days';
            }
            if (todayPerformance) {
                todayPerformance.textContent = (data.stats.completion_percentage || 0) + '%';
            }
            if (currentStreak) {
                currentStreak.textContent = data.stats.current_streak || 0;
            }
            
            console.log('Dashboard progress updated:', data.stats.completion_percentage + '%');
        }
    })
    .catch(error => {
        console.error('Error updating dashboard progress:', error);
    });
}