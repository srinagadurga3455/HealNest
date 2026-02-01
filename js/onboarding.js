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
        const user = Auth.getCurrentUser();
        if (!user) return false;
        
        // Check if this is a new user (recently registered)
        const hasSeenOnboarding = localStorage.getItem('healNestOnboardingComplete');
        const userRegistrationDate = user.created_at || user.registrationDate;
        
        // Show onboarding if:
        // 1. User hasn't seen onboarding before, OR
        // 2. User registered recently (within last 24 hours), OR  
        // 3. Manual trigger (help button)
        if (!hasSeenOnboarding) {
            return true;
        }
        
        // Check if user is new (registered within last 24 hours)
        if (userRegistrationDate) {
            const registrationTime = new Date(userRegistrationDate).getTime();
            const now = new Date().getTime();
            const hoursSinceRegistration = (now - registrationTime) / (1000 * 60 * 60);
            
            if (hoursSinceRegistration < 24) {
                return true;
            }
        }
        
        return false;
    }

    // Start the onboarding process
    start() {
        if (!this.shouldShowOnboarding()) {
            console.log('Onboarding not needed for this user');
            return;
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
                        text: 'This is your navigation sidebar. Use it to access different sections of your wellness journey.',
                        position: 'right'
                    },
                    {
                        target: '.header',
                        title: 'Your Dashboard',
                        text: 'This is your main dashboard where you can see your wellness overview and recent activities.',
                        position: 'bottom'
                    },
                    {
                        target: '.content-area',
                        title: 'Main Content Area',
                        text: 'Here you\'ll find your wellness information, quick actions, and recent activities.',
                        position: 'top'
                    },
                    {
                        target: '.user-profile',
                        title: 'Your Profile',
                        text: 'Click here to access your profile settings and personal information.',
                        position: 'left'
                    }
                ];
            
            case 'mood.php':
                return [
                    {
                        target: '.mood-selector-card',
                        title: 'Track Your Mood 😊',
                        text: 'Select how you\'re feeling today. This helps us understand your emotional patterns.',
                        position: 'bottom'
                    },
                    {
                        target: '.mood-note-section',
                        title: 'Add Context',
                        text: 'Optionally add notes about your mood. This can help you identify triggers and patterns.',
                        position: 'top'
                    },
                    {
                        target: '.mood-analytics-grid',
                        title: 'Mood Analytics',
                        text: 'View your mood history, statistics, and trends to understand your emotional patterns.',
                        position: 'top'
                    }
                ];
            
            case 'tasks.php':
                return [
                    {
                        target: '.tasks-progress-card',
                        title: 'Daily Progress 📊',
                        text: 'Track your daily task completion progress with this visual indicator.',
                        position: 'bottom'
                    },
                    {
                        target: '.tasks-section',
                        title: 'Your Daily Tasks',
                        text: 'Complete these wellness activities to improve your overall well-being.',
                        position: 'top'
                    },
                    {
                        target: '.wellness-tip-section',
                        title: 'Daily Wellness Tips',
                        text: 'Get helpful tips and advice to support your wellness journey.',
                        position: 'top'
                    }
                ];
            
            case 'journal.php':
                return [
                    {
                        target: '.journal-entry-form',
                        title: 'Express Yourself ✍️',
                        text: 'Write about your thoughts, feelings, and experiences. Journaling is a powerful tool for self-reflection.',
                        position: 'bottom'
                    },
                    {
                        target: '.mood-selection',
                        title: 'Mood with Entry',
                        text: 'Associate a mood with your journal entry to track emotional patterns.',
                        position: 'top'
                    },
                    {
                        target: '.journal-entries',
                        title: 'Your Journal History',
                        text: 'View and reflect on your previous journal entries here.',
                        position: 'top'
                    }
                ];
            
            default:
                return [];
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
        const message = document.createElement('div');
        message.className = 'onboarding-completion';
        message.innerHTML = `
            <div class="completion-content">
                <h3>🎉 Welcome to HealNest!</h3>
                <p>You're all set to begin your wellness journey. Remember, you can always access help from the menu.</p>
                <button onclick="this.parentElement.parentElement.remove()">Get Started</button>
            </div>
        `;
        document.body.appendChild(message);
        
        setTimeout(() => {
            if (message.parentElement) {
                message.remove();
            }
        }, 5000);
    }

    // Clean up onboarding elements
    cleanup() {
        if (this.overlay) {
            this.overlay.remove();
        }
        
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
    // Wait for page to fully load and user authentication to complete
    setTimeout(() => {
        // Only auto-start for new users
        if (onboardingGuide.shouldShowOnboarding()) {
            console.log('Starting onboarding for new user');
            onboardingGuide.start();
        } else {
            console.log('Onboarding not needed - user has already seen it or is not new');
        }
    }, 2000); // Increased delay to ensure everything is loaded
});