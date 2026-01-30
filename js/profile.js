document.addEventListener('DOMContentLoaded', function() {
    // Load user profile data
    loadUserProfile();
});

function loadUserProfile() {
    // Try to load from API first
    fetch('api/profile.php?action=get_profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUserProfile(data.user, data.stats, data.achievements);
            } else {
                // Fallback to demo data
                loadUserProfileFallback();
            }
        })
        .catch(error => {
            console.log('API not available, using demo data:', error);
            loadUserProfileFallback();
        });
}

function loadUserProfileFallback() {
    const user = Auth.getCurrentUser();
    
    // Demo stats
    const stats = {
        days_on_healnest: 1,
        achievements_count: 3,
        journal_entries: 0,
        current_streak: 0
    };
    
    // Demo achievements
    const achievements = [
        {
            id: 1,
            title: 'First Steps',
            description: 'Completed your first assessment',
            icon: '☀️',
            earned: true
        },
        {
            id: 2,
            title: 'Streak Starter',
            description: 'Maintained a 3-day streak',
            icon: '🔥',
            earned: true
        },
        {
            id: 3,
            title: 'Task Master',
            description: 'Completed 10 daily tasks',
            icon: '📝',
            earned: true
        }
    ];
    
    displayUserProfile(user, stats, achievements);
}

function displayUserProfile(user, stats, achievements) {
    // Update form fields
    document.getElementById('fullName').value = user.full_name || user.fullName || user.name || 'demo';
    document.getElementById('email').value = user.email || 'demo@gmail.com';
    document.getElementById('age').value = user.age || '';
    document.getElementById('wellnessGoals').value = user.wellness_goals || '';
    
    // Update statistics
    document.getElementById('daysOnHealNest').textContent = stats.days_on_healnest || 1;
    document.getElementById('achievementsCount').textContent = stats.achievements_count || 3;
    
    // Update achievements list
    const achievementsList = document.getElementById('achievementsList');
    if (achievements && achievements.length > 0) {
        achievementsList.innerHTML = achievements.map(achievement => `
            <div class="achievement-item">
                <div class="achievement-icon">${achievement.icon}</div>
                <div class="achievement-content">
                    <div class="achievement-title">${achievement.title}</div>
                    <div class="achievement-description">${achievement.description}</div>
                </div>
            </div>
        `).join('');
    }
    
    // Update user info in header
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

function updateProfile(event) {
    event.preventDefault();
    
    const formData = {
        full_name: document.getElementById('fullName').value,
        email: document.getElementById('email').value,
        age: document.getElementById('age').value,
        wellness_goals: document.getElementById('wellnessGoals').value
    };
    
    // Try to update via API first
    fetch('api/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update_profile',
            ...formData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profile updated successfully!');
            // Update local storage
            const user = Auth.getCurrentUser();
            Object.assign(user, formData);
            Auth.setUser(user);
        } else {
            console.error('Profile update failed:', data.message);
            updateProfileFallback(formData);
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        updateProfileFallback(formData);
    });
}

function updateProfileFallback(formData) {
    // Update localStorage
    const user = Auth.getCurrentUser();
    Object.assign(user, formData);
    Auth.setUser(user);
    
    alert('Profile updated successfully!');
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

function scrollToTasks() {
    // Redirect to dashboard tasks section
    window.location.href = './dashboard.php#tasksSection';
}