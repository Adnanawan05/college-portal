let currentRole = '';
let isLoginForm = true;

function selectRole(role) {
    currentRole = role;
    document.getElementById('roleSelector').style.display = 'none';
    document.getElementById('authForm').style.display = 'block';
    
    const roleIcons = {
        'admin': 'fa-user-shield',
        'teacher': 'fa-chalkboard-teacher',
        'student': 'fa-user-graduate',
        'parent': 'fa-users'
    };
    
    const roleTitles = {
        'admin': 'Admin Login',
        'teacher': 'Teacher Login',
        'student': 'Student Login',
        'parent': 'Parent Login'
    };
    
    document.getElementById('roleIcon').className = `fas ${roleIcons[role]} role-icon`;
    document.getElementById('formTitle').textContent = roleTitles[role];
    
    // Admin can only login, no signup
    if (role === 'admin') {
        document.getElementById('formFooter').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('signupForm').style.display = 'none';
        isLoginForm = true;
    } else {
        document.getElementById('formFooter').style.display = 'block';
    }
    
    // Clear any previous errors
    document.getElementById('alertBox').style.display = 'none';
}

function backToRoleSelection() {
    document.getElementById('roleSelector').style.display = 'block';
    document.getElementById('authForm').style.display = 'none';
    document.getElementById('alertBox').style.display = 'none';
    currentRole = '';
    isLoginForm = true;
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('signupForm').style.display = 'none';
    // Reset forms
    document.getElementById('loginForm').reset();
    document.getElementById('signupForm').reset();
}

function toggleForm() {
    isLoginForm = !isLoginForm;
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const formTitle = document.getElementById('formTitle');
    const formSubtitle = document.getElementById('formSubtitle');
    const formFooter = document.getElementById('formFooter');
    
    if (isLoginForm) {
        loginForm.style.display = 'block';
        signupForm.style.display = 'none';
        formTitle.textContent = currentRole.charAt(0).toUpperCase() + currentRole.slice(1) + ' Login';
        formSubtitle.textContent = 'Enter your credentials to continue';
        formFooter.innerHTML = '<p>Don\'t have an account? <a href="#" onclick="toggleForm(); return false;">Sign Up</a></p>';
    } else {
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
        formTitle.textContent = currentRole.charAt(0).toUpperCase() + currentRole.slice(1) + ' Sign Up';
        formSubtitle.textContent = 'Create your account to get started';
        formFooter.innerHTML = '<p>Already have an account? <a href="#" onclick="toggleForm(); return false;">Login</a></p>';
    }
    
    document.getElementById('alertBox').style.display = 'none';
}

function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    alertBox.textContent = message;
    alertBox.className = `alert ${type}`;
    alertBox.style.display = 'block';
    
    // Auto hide success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 3000);
    }
}

// Handle login form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[name="email"]').value;
        const password = this.querySelector('input[name="password"]').value;
        
        if (!email || !password) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        if (!currentRole) {
            showAlert('Please select a role first', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', email);
        formData.append('password', password);
        formData.append('role', currentRole);
        
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            let data;
            
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                showAlert('Server error. Please check your setup.', 'error');
                return;
            }
            
            if (data.success) {
                showAlert('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = `dashboard/${data.role}.html`;
                }, 1000);
            } else {
                showAlert(data.message || 'Login failed', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('Connection error. Please make sure Apache and MySQL are running.', 'error');
        }
    });
    
    // Handle signup form submission
    document.getElementById('signupForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const name = this.querySelector('input[name="name"]').value;
        const email = this.querySelector('input[name="email"]').value;
        const phone = this.querySelector('input[name="phone"]').value;
        const password = this.querySelector('input[name="password"]').value;
        
        if (!name || !email || !phone || !password) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'signup');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('password', password);
        formData.append('role', currentRole);
        
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            let data;
            
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                showAlert('Server error. Please check your setup.', 'error');
                return;
            }
            
            if (data.success) {
                showAlert('Registration successful! Please login.', 'success');
                setTimeout(() => {
                    toggleForm();
                    document.getElementById('signupForm').reset();
                }, 1500);
            } else {
                showAlert(data.message || 'Registration failed', 'error');
            }
        } catch (error) {
            console.error('Signup error:', error);
            showAlert('Connection error. Please try again.', 'error');
        }
    });
});
