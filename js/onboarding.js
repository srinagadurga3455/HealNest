// User Onboarding Guide System
class OnboardingGuide {
    constructor() {
        this.currentStep = 0;
        this.isActive = false;
        this.overlay = null;
        this.tooltip = null;
    }

    // Check if user needs onboarding
    shouldShowOnboarding() {
        // Check if user has already completed onboarding
        const hasSeenOnboarding = localStorage.getItem('healNestOnboardingComplete');
        if (hasSeenOnboarding === 'true') {
            return false; // User has already seen the tour
        }
        
        // Check if user is authenticated
        const user = Auth.getCurrentUser();
        if (!user) {
            return false; // No user logged in
        }
        
        // Check if this is a new user session (first time on dashboard)
        const hasVisitedDashboard = localStorage.getItem('healNestDashboardVisited');
        if (!hasVisitedDashboard) {
            // Mark as visited and show onboarding
            localStorage.setItem('healNestDashboardVisited', 'true');
            return true;
        }
        
        // Check if user registered recently (within last 24 hours)
        const userRegistrationDate = user.created_at || user.registrationDate;
        if (userRegistrationDate) {
            const registrationTime = new Date(userRegistrationDate).getTime();
            const now = new Date().getTime();
            const hoursSinceRegistration = (now - registrationTime) / (1000 * 60 * 60);
            
            if (hoursSinceRegistration < 24 && !hasSeenOnboarding) {
                return true;
            }
        }
        
        // Special case for demo user who hasn't seen onboarding
        const isDemoUser = user.email === 'demo@healnest.com';
        if (isDemoUser && !hasSeenOnboarding) {
            return true;
        }
        
        return false;
    }

    // Start the onboarding process
    start() {
        if (!this.shouldShowOnboarding()) {
            console.log('Onboarding not needed for this user');
            return;
        }
        
        // Show welcome message first for new users
        this.showWelcomeMessage();
    }
    
    // Show initial welcome message
    showWelcomeMessage() {
        const welcomeMessage = document.createElement('div');
        welcomeMessage.className = 'onboarding-welcome';
        welcomeMessage.innerHTML = `
            <div class="welcome-backdrop"></div>
            <div class="welcome-content">
                <h2>🌟 Welcome to HealNest!</h2>
                <p>Your personal wellness companion designed to support your mental health journey.</p>
                <div class="welcome-features">
                    <div class="feature-item">
                        <span class="feature-icon">🎯</span>
                        <span>Personalized wellness programs</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">😊</span>
                        <span>Daily mood tracking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✅</span>
                        <span>Guided wellness tasks</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📔</span>
                        <span>Private journaling space</span>
                    </div>
                </div>
                <p style="margin-top: 20px; font-size: 0.95rem; color: #666;">
                    Let's take a quick tour to help you get started!
                </p>
                <div class="welcome-actions">
                    <button class="btn-skip-tour" onclick="onboardingGuide.skipWelcome()">Skip Tour</button>
                    <button class="btn-start-tour" onclick="onboardingGuide.startTour()">Start Tour</button>
                </div>
            </div>
        `;
        document.body.appendChild(welcomeMessage);
    }
    
    // Skip welcome and onboarding
    skipWelcome() {
        const welcomeElement = document.querySelector('.onboarding-welcome');
        if (welcomeElement) {
            welcomeElement.remove();
        }
        this.complete();
    }
    
    // Start the actual tour
    startTour() {
        const welcomeElement = document.querySelector('.onboarding-welcome');
        if (welcomeElement) {
            welcomeElement.remove();
        }
        this.isActive = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(0);
    }
    
    // Force start onboarding (for help button)
    forceStart() {
        this.isActive = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(0);
    }

    // Create overlay for highlighting elements
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'onboarding-overlay';
        this.overlay.innerHTML = `
            <div class="onboarding-backdrop"></div>
            <div class="onboarding-tooltip" id="onboardingTooltip">
                <div class="tooltip-content">
                    <h3 class="tooltip-title"></h3>
                    <p class="tooltip-text"></p>
                    <div class="tooltip-actions">
                        <button class="btn-skip" onclick="onboardingGuide.skip()">Skip Tour</button>
                        <div class="tooltip-navigation">
                            <button class="btn-prev" onclick="onboardingGuide.previousStep()" style="display: none;">Previous</button>
                            <button class="btn-next" onclick="onboardingGuide.nextStep()">Next</button>
                        </div>
                    </div>
                    <div class="step-indicator">
                        <span class="current-step">1</span> of <span class="total-steps">5</span>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.overlay);
        this.tooltip = document.getElementById('onboardingTooltip');
    }

    // Define onboarding steps for each page
    getStepsForPage() {
        const currentPage = window.location.pathname.split('/').pop();
        
        switch (currentPage) {
            case 'dashboard.php':
                return [
                    {
                        target: '.sidebar',
                        title: 'Welcome to HealNest! 🌟',
                        text: 'This is your navigation sidebar. Use it to access different sections of your wellness journey. Each section is designed to support your mental health.',
                        position: 'right'
                    },
                    {
                        target: '.header',
                        title: 'Your Personal Dashboard',
                        text: 'This is your main dashboard where you can see your wellness overview, progress, and recent activities at a glance.',
                        position: 'bottom'
                    },
                    {
                        target: '.content-area',
                        title: 'Your Wellness Hub',
                        text: 'Here you\'ll find your wellness information, quick actions, and recent activities. This is your central hub for tracking progress.',
                        position: 'top'
                    },
                    {
                        target: '.user-profile',
                        title: 'Your Profile',
                        text: 'Click here to access your profile settings, personal information, and account preferences.',
                        position: 'left'
                    },
                    {
                        target: '.nav-item[href="./program.php"]',
                        title: 'Your Wellness Program 🎯',
                        text: 'Visit "My Program" to see your personalized wellness plan based on your assessment results.',
                        position: 'right'
                    }
                ];
            
            case 'program.php':
                return [
                    {
                        target: '.program-header-card',
                        title: 'Your Personalized Program 🎯',
                        text: 'This program was created specifically for you based on your wellness assessment. It\'s tailored to your needs and goals.',
                        position: 'bottom'
                    },
                    {
                        target: '.progress-overview-card',
                        title: 'Track Your Progress 📊',
                        text: 'Monitor your daily progress, completion rates, and streaks. Consistency is key to building healthy habits.',
                        position: 'bottom'
                    },
                    {
                        target: '.detail-card',
                        title: 'Program Details',
                        text: 'Learn why this program was chosen for you and see an overview of your daily wellness tasks.',
                        position: 'top'
                    },
                    {
                        target: '.program-action',
                        title: 'Start Your Daily Tasks',
                        text: 'Click here to begin your daily wellness activities. Remember, small consistent steps lead to big changes!',
                        position: 'top'
                    }
                ];
            
            case 'mood.php':
                return [
                    {
                        target: '.mood-selector-card',
                        title: 'Track Your Mood 😊',
                        text: 'Select how you\'re feeling today. Regular mood tracking helps you understand your emotional patterns and triggers.',
                        position: 'bottom'
                    },
                    {
                        target: '.mood-note-section',
                        title: 'Add Context to Your Mood',
                        text: 'Optionally add notes about your mood. What happened today? What influenced your feelings? This context is valuable for self-reflection.',
                        position: 'top'
                    },
                    {
                        target: '.save-mood-btn',
                        title: 'Save Your Mood',
                        text: 'Don\'t forget to save your mood entry! Your data helps create meaningful insights over time.',
                        position: 'top'
                    },
                    {
                        target: '.mood-analytics-grid',
                        title: 'Mood Analytics & Insights 📈',
                        text: 'View your mood history, statistics, and trends. Look for patterns - they can reveal important insights about your mental health.',
                        position: 'top'
                    }
                ];
            
            case 'tasks.php':
                return [
                    {
                        target: '.tasks-progress-card',
                        title: 'Daily Progress Tracker 📊',
                        text: 'Track your daily task completion progress. This visual indicator helps you stay motivated and see your achievements.',
                        position: 'bottom'
                    },
                    {
                        target: '.tasks-section',
                        title: 'Your Personalized Daily Tasks ✅',
                        text: 'These wellness activities are specifically chosen for you. Complete them to improve your overall well-being and build healthy habits.',
                        position: 'top'
                    },
                    {
                        target: '.wellness-tip-section',
                        title: 'Daily Wellness Tips 💡',
                        text: 'Get helpful tips and advice to support your wellness journey. These insights can help you throughout your day.',
                        position: 'top'
                    }
                ];
            
            case 'journal.php':
                return [
                    {
                        target: '.journal-header-actions',
                        title: 'Your Personal Journal ✍️',
                        text: 'Welcome to your private journal space. Writing about your thoughts and feelings is a powerful tool for self-reflection and growth.',
                        position: 'bottom'
                    },
                    {
                        target: '.new-entry-form',
                        title: 'Create Journal Entries',
                        text: 'Click "New Entry" to write about your thoughts, experiences, and feelings. There\'s no right or wrong way to journal.',
                        position: 'bottom'
                    },
                    {
                        target: '.journal-sidebar',
                        title: 'Journal Tools & Stats',
                        text: 'Use the search and filter tools to find past entries. View your writing statistics and popular tags.',
                        position: 'left'
                    },
                    {
                        target: '.entries-list',
                        title: 'Your Journal History',
                        text: 'View and reflect on your previous journal entries here. Reading past entries can provide valuable insights into your growth.',
                        position: 'top'
                    }
                ];
            
            case 'profile.php':
                return [
                    {
                        target: '.profile-header',
                        title: 'Your Profile Settings 👤',
                        text: 'Manage your personal information, preferences, and account settings here.',
                        position: 'bottom'
                    },
                    {
                        target: '.profile-form',
                        title: 'Update Your Information',
                        text: 'Keep your profile information up to date. This helps us provide you with the most relevant wellness content.',
                        position: 'top'
                    }
                ];
            
            default:
                return [
                    {
                        target: '.sidebar',
                        title: 'Welcome to HealNest! 🌟',
                        text: 'Use the navigation sidebar to explore different sections of your wellness journey.',
                        position: 'right'
                    },
                    {
                        target: '.content-area',
                        title: 'Explore Your Wellness Tools',
                        text: 'Each page offers different tools to support your mental health and well-being.',
                        position: 'top'
                    }
                ];
        }
    }

    // Show specific step
    showStep(stepIndex) {
        const steps = this.getStepsForPage();
        if (stepIndex >= steps.length) {
            this.complete();
            return;
        }

        const step = steps[stepIndex];
        let targetElement = document.querySelector(step.target);
        
        // If target element doesn't exist, try alternative selectors or skip
        if (!targetElement) {
            console.log(`Target element not found: ${step.target}, trying alternatives...`);
            
            // Try alternative selectors based on the target
            const alternatives = {
                '.wellness-overview': '.content-area',
                '.quick-actions': '.content-area', 
                '.recent-activities': '.content-area',
                '.mood-options': '.mood-selector-card',
                '.mood-calendar': '.mood-analytics-grid',
                '.mood-stats': '.mood-analytics-grid',
                '.tasks-list': '.tasks-section',
                '.journal-entries': '.content-area'
            };
            
            if (alternatives[step.target]) {
                targetElement = document.querySelector(alternatives[step.target]);
            }
            
            // If still no element found, skip this step
            if (!targetElement) {
                console.log(`Skipping step ${stepIndex} - element not found`);
                this.nextStep();
                return;
            }
        }

        // Update tooltip content
        this.tooltip.querySelector('.tooltip-title').textContent = step.title;
        this.tooltip.querySelector('.tooltip-text').textContent = step.text;
        this.tooltip.querySelector('.current-step').textContent = stepIndex + 1;
        this.tooltip.querySelector('.total-steps').textContent = steps.length;

        // Show/hide navigation buttons
        const prevBtn = this.tooltip.querySelector('.btn-prev');
        const nextBtn = this.tooltip.querySelector('.btn-next');
        
        prevBtn.style.display = stepIndex > 0 ? 'inline-block' : 'none';
        nextBtn.textContent = stepIndex === steps.length - 1 ? 'Finish' : 'Next';

        // Position tooltip and highlight element
        this.positionTooltip(targetElement, step.position);
        this.highlightElement(targetElement);
    }

    // Position tooltip relative to target element
    positionTooltip(targetElement, position) {
        const rect = targetElement.getBoundingClientRect();
        const tooltip = this.tooltip;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Reset classes and styles
        tooltip.className = 'onboarding-tooltip';
        tooltip.style.left = '';
        tooltip.style.top = '';
        tooltip.style.right = '';
        tooltip.style.bottom = '';
        
        // Get tooltip dimensions
        const tooltipRect = tooltip.getBoundingClientRect();
        const tooltipWidth = tooltipRect.width || 320;
        const tooltipHeight = tooltipRect.height || 200;
        
        let left, top;
        
        switch (position) {
            case 'top':
                left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                top = rect.top - tooltipHeight - 20;
                tooltip.classList.add('tooltip-top');
                break;
            case 'bottom':
                left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                top = rect.bottom + 20;
                tooltip.classList.add('tooltip-bottom');
                break;
            case 'left':
                left = rect.left - tooltipWidth - 20;
                top = rect.top + (rect.height / 2) - (tooltipHeight / 2);
                tooltip.classList.add('tooltip-left');
                break;
            case 'right':
                left = rect.right + 20;
                top = rect.top + (rect.height / 2) - (tooltipHeight / 2);
                tooltip.classList.add('tooltip-right');
                break;
            default:
                left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                top = rect.bottom + 20;
                tooltip.classList.add('tooltip-bottom');
        }
        
        // Ensure tooltip stays within viewport
        if (left < 10) left = 10;
        if (left + tooltipWidth > viewportWidth - 10) left = viewportWidth - tooltipWidth - 10;
        if (top < 10) top = 10;
        if (top + tooltipHeight > viewportHeight - 10) top = viewportHeight - tooltipHeight - 10;
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        tooltip.style.position = 'fixed';
    }

    // Highlight target element
    highlightElement(element) {
        // Remove previous highlights
        document.querySelectorAll('.onboarding-highlight').forEach(el => {
            el.classList.remove('onboarding-highlight');
        });
        
        // Add highlight to current element
        element.classList.add('onboarding-highlight');
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Navigate to next step
    nextStep() {
        this.currentStep++;
        this.showStep(this.currentStep);
    }

    // Navigate to previous step
    previousStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showStep(this.currentStep);
        }
    }

    // Skip the entire tour
    skip() {
        this.complete();
    }

    // Complete the onboarding
    complete() {
        localStorage.setItem('healNestOnboardingComplete', 'true');
        this.cleanup();
        
        // Show completion message
        this.showCompletionMessage();
    }

    // Show completion message
    showCompletionMessage() {
        const currentPage = window.location.pathname.split('/').pop();
        let nextStepMessage = '';
        
        switch (currentPage) {
            case 'dashboard.php':
                nextStepMessage = 'Ready to start? Visit "My Program" to see your personalized wellness plan!';
                break;
            case 'program.php':
                nextStepMessage = 'Now head to "Today\'s Tasks" to begin your daily wellness activities!';
                break;
            case 'mood.php':
                nextStepMessage = 'Don\'t forget to track your mood daily for the best insights!';
                break;
            case 'tasks.php':
                nextStepMessage = 'Complete your tasks regularly to build healthy habits!';
                break;
            case 'journal.php':
                nextStepMessage = 'Start journaling today - even a few sentences can make a difference!';
                break;
            default:
                nextStepMessage = 'Explore all the features to get the most out of your wellness journey!';
        }
        
        const message = document.createElement('div');
        message.className = 'onboarding-completion';
        message.innerHTML = `
            <div class="completion-content">
                <h3>🎉 Welcome to HealNest!</h3>
                <p>You're all set to begin your wellness journey. ${nextStepMessage}</p>
                <p style="font-size: 0.9rem; color: #8b7355; margin-top: 10px;">
                    💡 Tip: You can always restart this tour by clicking the help button (?) in the bottom-right corner.
                </p>
                <button onclick="this.parentElement.parentElement.remove()">Get Started</button>
            </div>
        `;
        document.body.appendChild(message);
        
        setTimeout(() => {
            if (message.parentElement) {
                message.remove();
            }
        }, 8000); // Longer display time for more content
    }

    // Clean up onboarding elements
    cleanup() {
        if (this.overlay) {
            this.overlay.remove();
        }
        
        // Remove any stuck welcome messages
        const welcomeElements = document.querySelectorAll('.onboarding-welcome');
        welcomeElements.forEach(el => el.remove());
        
        // Remove any stuck completion messages
        const completionElements = document.querySelectorAll('.onboarding-completion');
        completionElements.forEach(el => el.remove());
        
        // Remove highlights
        document.querySelectorAll('.onboarding-highlight').forEach(el => {
            el.classList.remove('onboarding-highlight');
        });
        
        this.isActive = false;
    }

    // Reset onboarding (for testing)
    reset() {
        localStorage.removeItem('healNestOnboardingComplete');
        this.cleanup();
    }
}

// Global instance
const onboardingGuide = new OnboardingGuide();

// Auto-start onboarding when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Onboarding system loaded');
    
    // Wait for Auth system to be ready
    function checkAndStartOnboarding() {
        if (typeof Auth !== 'undefined' && Auth.getCurrentUser) {
            const user = Auth.getCurrentUser();
            console.log('User found:', user);
            
            if (onboardingGuide.shouldShowOnboarding()) {
                console.log('Starting onboarding for new user');
                
                // Start onboarding after a short delay to ensure page is fully loaded
                setTimeout(() => {
                    onboardingGuide.start();
                }, 2000);
            } else {
                console.log('Onboarding not needed - user has already seen it');
            }
        } else {
            console.log('Auth not ready, retrying...');
            // Retry after a short delay if Auth is not ready
            setTimeout(checkAndStartOnboarding, 500);
        }
    }
    
    // Start checking after initial delay
    setTimeout(checkAndStartOnboarding, 1000);
});

// Add method to manually trigger onboarding for testing
window.startOnboardingTour = function() {
    console.log('Manual onboarding start');
    onboardingGuide.forceStart();
};

// Add method to reset onboarding for testing
window.resetOnboarding = function() {
    localStorage.removeItem('healNestOnboardingComplete');
    localStorage.removeItem('healNestDashboardVisited');
    console.log('Onboarding reset. Refresh the page to see the tour again.');
};

// Add method to check onboarding status
window.checkOnboardingStatus = function() {
    const hasSeenOnboarding = localStorage.getItem('healNestOnboardingComplete');
    const hasVisitedDashboard = localStorage.getItem('healNestDashboardVisited');
    const user = Auth.getCurrentUser();
    
    console.log('=== ONBOARDING STATUS ===');
    console.log('Has seen onboarding:', hasSeenOnboarding);
    console.log('Has visited dashboard:', hasVisitedDashboard);
    console.log('Current user:', user);
    console.log('Should show onboarding:', onboardingGuide.shouldShowOnboarding());
};