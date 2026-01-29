const registerForm = document.getElementById('registerForm');
const registerBtn = document.getElementById('registerBtn');
const alertDiv = document.getElementById('alertDiv');
const passwordInput = document.getElementById('password');
const passwordStrength = document.getElementById('passwordStrength');

// Password strength checker
passwordInput.addEventListener('input', function () {
    const password = this.value;
    const strength = checkPasswordStrength(password);

    passwordStrength.textContent = strength.text;
    passwordStrength.className = `password-strength strength-${strength.level}`;
});

function checkPasswordStrength(password) {
    let score = 0;

    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;

    const levels = ['weak', 'fair', 'good', 'strong'];
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];

    return {
        level: levels[Math.max(0, score - 1)] || 'weak',
        text: `Password strength: ${texts[Math.max(0, score - 1)] || 'Weak'}`
    };
}

registerForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validation
    if (!fullName || !email || !password || !confirmPassword) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'error');
        return;
    }

    if (password.length < 6) {
        showAlert('Password must be at least 6 characters long', 'error');
        return;
    }

    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }

    // Disable button and show loading
    registerBtn.disabled = true;
    registerBtn.textContent = 'Creating Account...';

    // Make API call to register user
    fetch('../api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            fullName: fullName,
            email: email,
            password: password,
            confirmPassword: confirmPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Store session token if provided
            if (data.session_token) {
                localStorage.setItem('healNestSessionToken', data.session_token);
            }
            
            setTimeout(() => {
                // Redirect based on API response
                window.location.href = data.redirect || './assessment.php';
            }, 1500);
        } else {
            showAlert(data.message, 'error');
            resetButton();
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showAlert('Registration failed. Please try again.', 'error');
        resetButton();
    });
});

function showAlert(message, type) {
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.classList.remove('hidden');
}

function resetButton() {
    registerBtn.disabled = false;
    registerBtn.textContent = 'Create Account';
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}