/**
 * HealNest - Main Application JavaScript
 * Handles authentication, user management, and shared functionality
 */

// ===== Authentication Functions =====
const Auth = {
  // Check if user is logged in
  isLoggedIn() {
    return localStorage.getItem('healNestUser') !== null;
  },

  // Get current user
  getCurrentUser() {
    const userData = localStorage.getItem('healNestUser');
    return userData ? JSON.parse(userData) : null;
  },

  // Login user
  login(email, password) {
    // Mock user data with program assignment
    const user = {
      email: email,
      name: email.split('@')[0],
      fullName: email.split('@')[0],
      loggedIn: true,
      loginTime: new Date().toISOString(),
      // Mock program assignment (in real app, this would come from database)
      assigned_program_id: Math.random() > 0.5 ? Math.floor(Math.random() * 5) + 1 : null,
      current_streak: Math.floor(Math.random() * 10) + 1,
      highest_streak: Math.floor(Math.random() * 20) + 5,
      total_program_days: Math.floor(Math.random() * 30) + 1,
      assessment_taken: localStorage.getItem('healNestAssessmentCompleted') === 'true',
      wellness_score: Math.floor(Math.random() * 40) + 60
    };
    localStorage.setItem('healNestUser', JSON.stringify(user));
    return user;
  },

  // Logout user
  logout() {
    localStorage.removeItem('healNestUser');
    localStorage.removeItem('healNestAssessment');
    localStorage.removeItem('healNestMoodData');
    localStorage.removeItem('healNestJournalEntries');
  },

  // Register new user
  register(name, email, password) {
    const user = {
      fullName: name,
      email: email,
      loggedIn: true,
      createdAt: new Date().toISOString()
    };
    localStorage.setItem('healNestUser', JSON.stringify(user));
    return user;
  },

  // Check authentication status and redirect if needed
  requireLogin() {
    if (!this.isLoggedIn() && !window.location.href.includes('login.html') && !window.location.href.includes('register.html') && !window.location.href.includes('landing.html') && !window.location.href.includes('index.php')) {
      window.location.href = './login.html';
    }
  }
};

// ===== Mood Tracker Functions =====
const MoodTracker = {
  // Save mood entry
  saveMood(mood, note = '') {
    const today = new Date().toISOString().split('T')[0];
    let moodData = this.getMoodData();
    
    moodData[today] = {
      mood: mood,
      note: note,
      timestamp: new Date().toISOString()
    };
    
    localStorage.setItem('healNestMoodData', JSON.stringify(moodData));
    return moodData[today];
  },

  // Get mood data
  getMoodData() {
    const data = localStorage.getItem('healNestMoodData');
    return data ? JSON.parse(data) : {};
  },

  // Get today's mood
  getTodayMood() {
    const today = new Date().toISOString().split('T')[0];
    const data = this.getMoodData();
    return data[today] || null;
  },

  // Get mood statistics
  getMoodStats() {
    const data = this.getMoodData();
    const stats = {
      total: Object.keys(data).length,
      moods: {
        excellent: 0,
        good: 0,
        neutral: 0,
        bad: 0,
        terrible: 0
      }
    };

    Object.values(data).forEach(entry => {
      if (stats.moods[entry.mood] !== undefined) {
        stats.moods[entry.mood]++;
      }
    });

    return stats;
  }
};

// ===== Journal Functions =====
const Journal = {
  // Save journal entry
  saveEntry(title, content, mood = 'neutral') {
    let entries = this.getEntries();
    const entry = {
      id: Date.now(),
      title: title,
      content: content,
      mood: mood,
      createdAt: new Date().toISOString(),
      tags: this.extractTags(content)
    };
    
    entries.push(entry);
    localStorage.setItem('healNestJournalEntries', JSON.stringify(entries));
    return entry;
  },

  // Get all entries
  getEntries() {
    const data = localStorage.getItem('healNestJournalEntries');
    return data ? JSON.parse(data) : [];
  },

  // Get entry by ID
  getEntry(id) {
    const entries = this.getEntries();
    return entries.find(e => e.id === id);
  },

  // Delete entry
  deleteEntry(id) {
    let entries = this.getEntries();
    entries = entries.filter(e => e.id !== id);
    localStorage.setItem('healNestJournalEntries', JSON.stringify(entries));
  },

  // Extract tags from content
  extractTags(content) {
    const tags = [];
    const tagPattern = /#(\w+)/g;
    let match;
    
    while ((match = tagPattern.exec(content)) !== null) {
      if (!tags.includes(match[1])) {
        tags.push(match[1]);
      }
    }
    
    return tags;
  },

  // Search entries
  search(query) {
    const entries = this.getEntries();
    return entries.filter(e => 
      e.title.toLowerCase().includes(query.toLowerCase()) ||
      e.content.toLowerCase().includes(query.toLowerCase()) ||
      e.tags.some(t => t.toLowerCase().includes(query.toLowerCase()))
    );
  }
};

// ===== Assessment Functions =====
const Assessment = {
  // Save assessment results
  saveResults(score, answers, recommendations) {
    const assessment = {
      score: score,
      answers: answers,
      recommendations: recommendations,
      completedAt: new Date().toISOString()
    };
    
    localStorage.setItem('healNestAssessment', JSON.stringify(assessment));
    localStorage.setItem('healNestAssessmentCompleted', 'true');
    return assessment;
  },

  // Get last assessment
  getLastAssessment() {
    const data = localStorage.getItem('healNestAssessment');
    return data ? JSON.parse(data) : null;
  },

  // Calculate wellness score
  calculateWellnessScore() {
    const assessment = this.getLastAssessment();
    if (!assessment) return 75;
    
    // Convert raw score to wellness percentage (higher is better)
    const maxPossibleScore = 60; // 20 questions * 3 max points each
    const wellnessScore = Math.round(((maxPossibleScore - assessment.score) / maxPossibleScore) * 100);
    return Math.max(0, Math.min(100, wellnessScore));
  },

  // Get assessment history
  getAssessmentHistory() {
    const history = localStorage.getItem('healNestAssessmentHistory');
    return history ? JSON.parse(history) : [];
  },

  // Save assessment to history
  saveToHistory(assessment) {
    const history = this.getAssessmentHistory();
    history.push(assessment);
    
    // Keep only last 10 assessments
    if (history.length > 10) {
      history.shift();
    }
    
    localStorage.setItem('healNestAssessmentHistory', JSON.stringify(history));
  }
};

// ===== Notification Functions =====
const Notification = {
  // Show success notification
  success(message, duration = 3000) {
    this.show(message, 'success', duration);
  },

  // Show error notification
  error(message, duration = 3000) {
    this.show(message, 'error', duration);
  },

  // Show warning notification
  warning(message, duration = 3000) {
    this.show(message, 'warning', duration);
  },

  // Show info notification
  info(message, duration = 3000) {
    this.show(message, 'info', duration);
  },

  // Generic show notification
  show(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed`;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('style', 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;');
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, duration);
  }
};

// ===== UI Functions =====
const UI = {
  // Format date
  formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  },

  // Format time
  formatTime(date) {
    return new Date(date).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    });
  },

  // Format datetime
  formatDateTime(date) {
    return this.formatDate(date) + ' ' + this.formatTime(date);
  },

  // Capitalize string
  capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  },

  // Get greeting based on time
  getGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 18) return 'Good Afternoon';
    return 'Good Evening';
  },

  // Update user display
  updateUserDisplay() {
    const user = Auth.getCurrentUser();
    if (!user) return;

    const userNameElements = document.querySelectorAll('[data-user-name]');
    userNameElements.forEach(el => {
      el.textContent = user.fullName || user.name || user.email;
    });

    const greetingElements = document.querySelectorAll('[data-greeting]');
    greetingElements.forEach(el => {
      el.textContent = this.getGreeting();
    });
  }
};

// ===== Validation Functions =====
const Validation = {
  // Validate email
  isValidEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
  },

  // Validate password strength
  getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    const strengths = ['Weak', 'Fair', 'Good', 'Strong'];
    return {
      level: strength,
      text: strengths[Math.max(0, strength - 1)] || 'Weak'
    };
  },

  // Validate form fields
  validateFormFields(fieldIds) {
    const errors = [];
    
    fieldIds.forEach(id => {
      const field = document.getElementById(id);
      if (!field || !field.value.trim()) {
        errors.push(`${field?.placeholder || field?.name || id} is required`);
      }
    });
    
    return {
      isValid: errors.length === 0,
      errors: errors
    };
  }
};

// ===== Initialize on page load =====
document.addEventListener('DOMContentLoaded', function() {
  // Check authentication
  Auth.requireLogin();
  
  // Update user display
  UI.updateUserDisplay();
  
  // Add logout functionality to logout buttons
  const logoutButtons = document.querySelectorAll('[data-logout]');
  logoutButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to logout?')) {
        Auth.logout();
        window.location.href = './landing.html';
      }
    });
  });

  // Add dark mode toggle if exists
  const darkModeToggle = document.getElementById('darkModeToggle');
  if (darkModeToggle) {
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
      document.body.classList.add('dark-mode');
    }

    darkModeToggle.addEventListener('change', function() {
      if (this.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'true');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'false');
      }
    });
  }
});

// Export for use in modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    Auth,
    MoodTracker,
    Journal,
    Assessment,
    Notification,
    UI,
    Validation
  };
}
