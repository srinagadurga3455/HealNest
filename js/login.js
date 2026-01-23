const loginForm = document.getElementById('loginForm');
const loginBtn = document.getElementById('loginBtn');
const alertDiv = document.getElementById('alertDiv');

loginForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }

    // Disable button and show loading
    loginBtn.disabled = true;
    loginBtn.textContent = 'Signing In...';

    try {
        // Make API call to PHP backend
        const response = await fetch('../api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const data = await response.json();

        if (data.success) {
            // Store session token in localStorage
            localStorage.setItem('healNestSessionToken', data.session_token);
            localStorage.setItem('healNestUser', JSON.stringify(data.user));
            
            showAlert('Login successful!', 'success');
            
            setTimeout(() => {
                // Redirect based on backend response
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.message || 'Invalid email or password', 'error');
            resetButton();
        }
    } catch (error) {
        console.error('Login error:', error);
        showAlert('An error occurred. Please try again.', 'error');
        resetButton();
    }
    
    function resetButton() {
        loginBtn.disabled = false;
        loginBtn.textContent = 'Sign In';
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showAlert(message, type) {
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.classList.remove('hidden');
}
