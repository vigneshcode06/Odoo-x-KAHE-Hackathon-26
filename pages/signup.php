<?php
// pages/signup.php
if(isset($_SESSION['user_id'])) {
    header("Location: /dashboard");
    exit;
}

$pageStyles = "
    .auth-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 160px);
        padding: 2rem;
    }
    .auth-card {
        width: 100%;
        max-width: 400px;
    }
    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .auth-header h2 {
        margin-bottom: 0.5rem;
    }
";

include 'includes/components/header.php';
?>

<div class="auth-container">
    <div class="glass-card auth-card">
        <div class="auth-header">
            <h2>Create an Account</h2>
            <p style="color: var(--text-muted);">Join Traveloop to start planning.</p>
        </div>
        
        <form id="signupForm">
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" class="form-control" required placeholder="John Doe">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" class="form-control" required placeholder="john@example.com">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" class="form-control" required placeholder="••••••••">
                <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 6px; display: block;">
                    Min 8 chars · uppercase · lowercase · number · symbol (!@#$%)
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Sign Up</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
            Already have an account? <a href="/login">Log in</a>
        </p>
    </div>
</div>

<?php 
$pageScripts = "
document.getElementById('signupForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const submitBtn = e.target.querySelector('button[type=\"submit\"]');

    // Strong password validation
    const strongPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%^&*()_+\-=\[\]{};':\"\\\\|,.<>\/?]).{8,}$/;

    if (!strongPassword.test(password)) {
        App.showToast('Password must be at least 8 characters and include uppercase, lowercase, number, and symbol (e.g. Test@123)', 'error');
        return;
    }
    
    try {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
        
        const response = await App.apiRequest('/api/auth?action=register', 'POST', {
            name, email, password
        });
        
        App.showToast('Registration successful! Redirecting...', 'success');
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 1500);
        
    } catch (error) {
        App.showToast(error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign Up';
    }
});
";
include 'includes/components/footer.php'; 
?>
