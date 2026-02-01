let selectedMood = null;
let currentDate = new Date();

// Mood emojis mapping
const moodEmojis = {
    excellent: '😄',
    good: '😊',
    neutral: '😐',
    challenging: '😔',
    difficult: '😢'
};

// Simple MoodTracker for localStorage fallback
const MoodTracker = {
    saveMood: function(mood, note) {
        const date = new Date().toISOString().split('T')[0];
        const moodData = this.getMoodData();
        moodData[date] = {
            mood: mood,
            note: note,
            mood_score: this.getMoodScore(mood)
        };
        localStorage.setItem('healNestMoodData', JSON.stringify(moodData));
    },
    
    getTodayMood: function() {
        const date = new Date().toISOString().split('T')[0];
        const moodData = this.getMoodData();
        return moodData[date] || null;
    },
    
    getMoodData: function() {
        const data = localStorage.getItem('healNestMoodData');
        return data ? JSON.parse(data) : {};
    },
    
    getMoodStats: function() {
        const moodData = this.getMoodData();
        const stats = {
            excellent: 0,
            good: 0,
            neutral: 0,
            challenging: 0,
            difficult: 0
        };
        
        let total = 0;
        Object.values(moodData).forEach(entry => {
            if (stats.hasOwnProperty(entry.mood)) {
                stats[entry.mood]++;
                total++;
            }
        });
        
        return {
            moods: stats,
            total: total
        };
    },
    
    getMoodScore: function(mood) {
        const scores = {
            excellent: 5,
            good: 4,
            neutral: 3,
            challenging: 2,
            difficult: 1
        };
        return scores[mood] || 3;
    }
};

// Initialize page
document.addEventListener('DOMContentLoaded', function () {
    // Ensure user is properly authenticated and update profile
    ensureAuthentication().then(() => {
        updateUserInfo();
        loadTodaysMood();
        generateCalendar();
        updateMoodStats();
        updateMoodTrend();
        loadRecentEntries();

        // Add click handlers for mood options
        document.querySelectorAll('.mood-option').forEach(option => {
            option.addEventListener('click', function () {
                // Remove selected class from all options
                document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
                // Add selected class to clicked option
                this.classList.add('selected');
                selectedMood = this.dataset.mood;
            });
        });
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

function updateUserInfo() {
    const user = Auth.getCurrentUser();
    const userAvatar = document.getElementById('userAvatar');
    
    if (userAvatar && user) {
        const userName = user.full_name || user.fullName || user.name || user.email;
        userAvatar.textContent = userName.charAt(0).toUpperCase();
    }
}

function loadTodaysMood() {
    fetch('api/mood.php?action=get_today_mood')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedMood = data.mood.mood;
                document.querySelector(`[data-mood="${data.mood.mood}"]`).classList.add('selected');
                document.getElementById('moodNote').value = data.mood.note || '';
            } else {
                // No mood for today, that's fine
                console.log('No mood entry for today');
            }
        })
        .catch(error => {
            console.error('Error loading today\'s mood:', error);
            // Fallback to localStorage
            loadTodaysMoodFallback();
        });
}

function loadTodaysMoodFallback() {
    const todayMood = MoodTracker.getTodayMood();
    if (todayMood) {
        selectedMood = todayMood.mood;
        document.querySelector(`[data-mood="${todayMood.mood}"]`).classList.add('selected');
        document.getElementById('moodNote').value = todayMood.note || '';
    }
}

function saveMood() {
    if (!selectedMood) {
        alert('Please select a mood first.');
        return;
    }

    const note = document.getElementById('moodNote').value.trim();
    const btn = document.querySelector('.save-mood-btn');
    const originalText = btn.textContent;

    // Disable button while saving
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const moodData = {
        mood: selectedMood,
        note: note,
        date: new Date().toISOString().split('T')[0]
    };

<<<<<<< HEAD
    console.log('Saving mood data:', moodData);

    fetch('../api/mood.php', {
=======
    fetch('api/mood.php?action=save_mood', {
>>>>>>> 99248c9af45447c50dfe8ddbc28b348bfd821d1f
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'save_mood',
            ...moodData
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed response:', data);
            
            if (data.success) {
                // Show success message
                btn.textContent = 'Mood Saved';
                btn.style.background = '#28a745';

                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                }, 2000);

                // Refresh analytics
                generateCalendar();
                updateMoodStats();
                updateMoodTrend();
                loadRecentEntries();
            } else {
                console.error('Failed to save mood:', data.message);
                btn.disabled = false;
                btn.textContent = originalText;
                alert('Failed to save mood: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            btn.disabled = false;
            btn.textContent = originalText;
            alert('Error saving mood. Please check the console for details.');
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        btn.disabled = false;
        btn.textContent = originalText;
        alert('Network error. Please try again.');
    });
}

function saveMoodFallback() {
    const note = document.getElementById('moodNote').value.trim();
    const btn = document.querySelector('.save-mood-btn');
    const originalText = btn.textContent;

    try {
        MoodTracker.saveMood(selectedMood, note);

        // Show success message
        btn.textContent = 'Mood Saved';
        btn.style.background = '#28a745';

        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '';
            btn.disabled = false;
        }, 2000);

        // Refresh analytics
        generateCalendar();
        updateMoodStats();
        updateMoodTrend();
        loadRecentEntries();

    } catch (error) {
        btn.disabled = false;
        btn.textContent = originalText;
        alert('Error saving mood. Please try again.');
    }
}

function generateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth() + 1;

    fetch(`api/mood.php?action=get_mood_calendar&year=${year}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCalendar(data.calendar);
            } else {
                console.error('Failed to load calendar data:', data.message);
                // Fallback to localStorage
                generateCalendarFallback();
            }
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            // Fallback to localStorage
            generateCalendarFallback();
        });
}

function displayCalendar(moodData) {
    const calendar = document.getElementById('moodCalendar');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];

    document.getElementById('currentMonth').textContent =
        `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    calendar.innerHTML = '';

    // Add day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        const header = document.createElement('div');
        header.textContent = day;
        header.style.fontWeight = 'bold';
        header.style.textAlign = 'center';
        header.style.padding = '0.5rem';
        header.style.color = '#6c757d';
        calendar.appendChild(header);
    });

    // Generate calendar days
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = date.getDate();

        const dateString = date.toISOString().split('T')[0];
        const dayMood = moodData[dateString];

        if (dayMood) {
            dayElement.classList.add('has-mood', `mood-${dayMood.mood}`);
            dayElement.title = `${dayMood.mood}${dayMood.note ? ': ' + dayMood.note : ''}`;
        }

        if (date.getMonth() !== currentDate.getMonth()) {
            dayElement.style.opacity = '0.3';
        }

        if (date.toDateString() === new Date().toDateString()) {
            dayElement.style.border = '2px solid #13DEB9';
        }

        calendar.appendChild(dayElement);
    }
}

function generateCalendarFallback() {
    const moodData = MoodTracker.getMoodData();
    displayCalendar(moodData);
}

function updateMoodStats() {
    fetch('api/mood.php?action=get_mood_stats&period=month')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMoodStats(data.stats);
            } else {
                console.error('Failed to load mood stats:', data.message);
                // Fallback to localStorage
                updateMoodStatsFallback();
            }
        })
        .catch(error => {
            console.error('Error loading mood stats:', error);
            // Fallback to localStorage
            updateMoodStatsFallback();
        });
}

function displayMoodStats(stats) {
    const statsContainer = document.getElementById('moodStats');
    if (!statsContainer) return;

    const moodLabels = {
        excellent: 'Excellent Days',
        good: 'Good Days',
        neutral: 'Neutral Days',
        challenging: 'Challenging Days',
        difficult: 'Difficult Days'
    };

    const moodColors = {
        excellent: '#28a745',
        good: '#20c997',
        neutral: '#6c757d',
        challenging: '#ffc107',
        difficult: '#dc3545'
    };

    statsContainer.innerHTML = Object.entries(stats.moods).map(([mood, count]) => `
        <div class="stat-item">
            <div class="stat-label">
                <span style="color: ${moodColors[mood]};">${moodEmojis[mood]}</span>
                ${moodLabels[mood]}
            </div>
            <div class="stat-value" style="color: ${moodColors[mood]};">${count}</div>
        </div>
    `).join('');

    // Add total entries
    statsContainer.innerHTML += `
        <div class="stat-item" style="border-top: 2px solid #e9ecef; margin-top: 1rem; padding-top: 1rem;">
            <div class="stat-label">Total Entries</div>
            <div class="stat-value" style="color: #13DEB9;">${stats.total}</div>
        </div>
    `;
}

function updateMoodStatsFallback() {
    const stats = MoodTracker.getMoodStats();
    displayMoodStats(stats);
}

function updateMoodTrend() {
    fetch('api/mood.php?action=get_mood_trend&days=7')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMoodTrend(data.trend);
            } else {
                console.error('Failed to load mood trend:', data.message);
                // Fallback to localStorage
                updateMoodTrendFallback();
            }
        })
        .catch(error => {
            console.error('Error loading mood trend:', error);
            // Fallback to localStorage
            updateMoodTrendFallback();
        });
}

function displayMoodTrend(trend) {
    const trendContainer = document.getElementById('moodTrend');
    if (!trendContainer) return;

    let trendIcon, trendText, trendColor;

    switch (trend.direction) {
        case 'improving':
            trendIcon = '📈';
            trendText = 'Your mood is trending upward!';
            trendColor = '#28a745';
            break;
        case 'declining':
            trendIcon = '📉';
            trendText = 'Your mood has been declining';
            trendColor = '#dc3545';
            break;
        default:
            trendIcon = '📊';
            trendText = 'Your mood is stable';
            trendColor = '#6c757d';
    }

    trendContainer.innerHTML = `
        <div class="trend-indicator">${trendIcon}</div>
        <div class="trend-text" style="color: ${trendColor};">${trendText}</div>
    `;
}

function updateMoodTrendFallback() {
    const moodData = MoodTracker.getMoodData();
    const trendContainer = document.getElementById('moodTrend');
    if (!trendContainer) return;

    // Get last 7 days
    const last7Days = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateString = date.toISOString().split('T')[0];
        last7Days.push(moodData[dateString]);
    }

    // Calculate trend
    const moodValues = {
        excellent: 5,
        good: 4,
        neutral: 3,
        challenging: 2,
        difficult: 1
    };

    const validDays = last7Days.filter(day => day);
    if (validDays.length < 2) {
        trendContainer.innerHTML = `
            <div class="trend-indicator">📊</div>
            <div class="trend-text">Not enough data for trend analysis</div>
        `;
        return;
    }

    const firstHalf = validDays.slice(0, Math.ceil(validDays.length / 2));
    const secondHalf = validDays.slice(Math.ceil(validDays.length / 2));

    const firstAvg = firstHalf.reduce((sum, day) => sum + moodValues[day.mood], 0) / firstHalf.length;
    const secondAvg = secondHalf.reduce((sum, day) => sum + moodValues[day.mood], 0) / secondHalf.length;

    let trendIcon, trendText, trendColor;

    if (secondAvg > firstAvg + 0.3) {
        trendIcon = '📈';
        trendText = 'Your mood is trending upward!';
        trendColor = '#28a745';
    } else if (secondAvg < firstAvg - 0.3) {
        trendIcon = '📉';
        trendText = 'Your mood has been declining';
        trendColor = '#dc3545';
    } else {
        trendIcon = '📊';
        trendText = 'Your mood is stable';
        trendColor = '#6c757d';
    }

    trendContainer.innerHTML = `
        <div class="trend-indicator">${trendIcon}</div>
        <div class="trend-text" style="color: ${trendColor};">${trendText}</div>
    `;
}

function loadRecentEntries() {
    fetch('api/mood.php?action=get_recent_entries&limit=10')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentEntries(data.entries);
            } else {
                console.error('Failed to load recent entries:', data.message);
                // Fallback to localStorage
                loadRecentEntriesFallback();
            }
        })
        .catch(error => {
            console.error('Error loading recent entries:', error);
            // Fallback to localStorage
            loadRecentEntriesFallback();
        });
}

function displayRecentEntries(entries) {
    const entriesContainer = document.getElementById('recentEntries');
    if (!entriesContainer) return;

    if (entries.length === 0) {
        entriesContainer.innerHTML = '<p class="text-muted text-center">No mood entries yet. Start tracking your mood today!</p>';
        return;
    }

    entriesContainer.innerHTML = entries.map(entry => `
        <div class="entry-item">
            <div class="entry-mood">${moodEmojis[entry.mood]}</div>
            <div class="entry-details">
                <div class="entry-date">${formatDate(entry.date)}</div>
                ${entry.note ? `<div class="entry-note">${entry.note}</div>` : ''}
            </div>
        </div>
    `).join('');
}

function loadRecentEntriesFallback() {
    const moodData = MoodTracker.getMoodData();

    // Convert to array and sort by date
    const entries = Object.entries(moodData)
        .map(([date, data]) => ({ date, ...data }))
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, 10);

    displayRecentEntries(entries);
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    }
}