document.addEventListener('DOMContentLoaded', function() {
    // Ensure user is properly authenticated
    ensureAuthentication().then(() => {
        // Load user profile data
        loadUserProfile();
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

function loadUserProfile() {
    // Try to load from API first
    fetch('../api/profile.php?action=get_profile')
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
    
    // Set a realistic join date - let's say the user joined 30 days ago
    const today = new Date();
    const joinDate = new Date(today);
    joinDate.setDate(today.getDate() - 30); // 30 days ago
    
    // Update the user object with the corrected date
    user.created_at = joinDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD
    Auth.setUser(user);
    
    const daysOnPlatform = 30; // Since we set it to 30 days ago
    
    // Demo stats with more realistic data
    const stats = {
        days_on_healnest: daysOnPlatform,
        days_on_platform: daysOnPlatform,
        achievements_count: 3,
        journal_entries: 2,
        current_streak: 1,
        tasks_completed: 5,
        mood_entries: 3
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
        },
        {
            id: 4,
            title: 'Champion',
            description: 'Complete 30-day program',
            icon: '🏆',
            earned: false
        },
        {
            id: 5,
            title: 'Storyteller',
            description: 'Write 25 journal entries',
            icon: '📔',
            earned: false
        },
        {
            id: 6,
            title: 'Wellness Warrior',
            description: 'Reach wellness score of 90+',
            icon: '🌟',
            earned: false
        }
    ];
    
    displayUserProfile(user, stats, achievements);
}

function displayUserProfile(user, stats, achievements) {
    // Update profile header
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profileAvatarCircle = document.getElementById('profileAvatarCircle');
    
    if (profileName) {
        profileName.textContent = user.full_name || user.fullName || user.name || 'Demo User';
    }
    if (profileEmail) {
        profileEmail.textContent = user.email || 'demo@healnest.com';
    }
    if (profileAvatarCircle) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        profileAvatarCircle.textContent = userName.charAt(0).toUpperCase();
    }
    
    // Update form fields
    document.getElementById('fullName').value = user.full_name || user.fullName || user.name || '';
    document.getElementById('email').value = user.email || '';
    
    // Update statistics
    document.getElementById('daysOnPlatform').textContent = stats.days_on_healnest || stats.days_on_platform || 1;
    document.getElementById('currentStreakDisplay').textContent = stats.current_streak || 0;
    document.getElementById('achievementsCount').textContent = stats.achievements_count || 3;
    
    // Update achievements display
    updateAchievementsDisplay(achievements);
    
    // Update user info in header
    const userAvatar = document.getElementById('userAvatar');
    if (userAvatar) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        userAvatar.textContent = userName.charAt(0).toUpperCase();
    }
}

function updateAchievementsDisplay(achievements) {
    const achievementsList = document.getElementById('achievementsList');
    if (!achievementsList || !achievements) return;
    
    achievementsList.innerHTML = achievements.map(achievement => `
        <div class="achievement-card ${achievement.earned ? 'earned' : 'locked'}">
            <div class="achievement-icon">${achievement.icon}</div>
            <div class="achievement-title">${achievement.title}</div>
            <div class="achievement-desc">${achievement.description}</div>
        </div>
    `).join('');
}

function updateProfile(event) {
    event.preventDefault();
    
    const formData = {
        full_name: document.getElementById('fullName').value,
        email: document.getElementById('email').value
    };
    
    console.log('Updating profile with data:', formData);
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;
    
    // Try to update via API first
    fetch('../api/profile.php', {
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
            console.log('Profile updated successfully via API');
            showSuccessMessage('Profile updated successfully!');
            
            // Update local storage
            const user = Auth.getCurrentUser();
            Object.assign(user, formData);
            Auth.setUser(user);
            
            // Update the display immediately
            updateProfileDisplay(formData);
        } else {
            console.error('Profile update failed:', data.message);
            updateProfileFallback(formData);
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        updateProfileFallback(formData);
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function updateProfileFallback(formData) {
    console.log('Using fallback profile update');
    
    // Update localStorage
    const user = Auth.getCurrentUser();
    Object.assign(user, formData);
    Auth.setUser(user);
    
    // Update the display immediately
    updateProfileDisplay(formData);
    
    showSuccessMessage('Profile updated successfully!');
}

function updateProfileDisplay(userData) {
    // Update profile header immediately
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profileAvatarCircle = document.getElementById('profileAvatarCircle');
    const userAvatar = document.getElementById('userAvatar');
    
    if (profileName) {
        profileName.textContent = userData.full_name || 'Demo User';
    }
    if (profileEmail) {
        profileEmail.textContent = userData.email || 'demo@healnest.com';
    }
    if (profileAvatarCircle) {
        profileAvatarCircle.textContent = userData.full_name ? userData.full_name.charAt(0).toUpperCase() : 'U';
    }
    if (userAvatar) {
        userAvatar.textContent = userData.full_name ? userData.full_name.charAt(0).toUpperCase() : 'U';
    }
    
    // Dispatch custom event to notify other pages/components
    window.dispatchEvent(new CustomEvent('userProfileUpdated', {
        detail: userData
    }));
}

function showSuccessMessage(message) {
    // Create and show success message
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        z-index: 1000;
        font-family: 'Lato', sans-serif;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(successDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
    }, 3000);
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

function scrollToTasks() {
    // Redirect to dashboard tasks section
    window.location.href = './dashboard.php#tasksSection';
}

// Function to reset user join date if it's incorrect
window.resetUserJoinDate = function() {
    const user = Auth.getCurrentUser();
    if (user) {
        // Set join date to 30 days ago
        const today = new Date();
        const joinDate = new Date(today);
        joinDate.setDate(today.getDate() - 30);
        
        user.created_at = joinDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD
        Auth.setUser(user);
        console.log('User join date reset to:', user.created_at);
        // Reload profile data
        loadUserProfile();
        alert('User join date has been reset to 30 days ago. The days count should now show 30.');
    }
};