// Simple Authentication Utility
const Auth = {
    // Check if user is logged in
    isLoggedIn() {
        return localStorage.getItem('healNestUser') !== null || 
               sessionStorage.getItem('healNestUser') !== null;
    },

    // Get current user data
    getCurrentUser() {
        const userData = localStorage.getItem('healNestUser') || 
                        sessionStorage.getItem('healNestUser');
        
        if (userData) {
            return JSON.parse(userData);
        }
        
        return null;
    },

    // Set user data
    setUser(userData) {
        localStorage.setItem('healNestUser', JSON.stringify(userData));
    },

    // Logout user
    logout() {
        localStorage.removeItem('healNestUser');
        sessionStorage.removeItem('healNestUser');
        localStorage.removeItem('healNestSession');
    },

    // Login user
    login(userData) {
        this.setUser(userData);
        sessionStorage.setItem('healNestSession', 'active');
    }
};