<?php
// pages/login.php
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
            <h2>Welcome Back</h2>
            <p style="color: var(--text-muted);">Log in to manage your trips.</p>
        </div>
        
        <form id="loginForm">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" class="form-control" required placeholder="john@example.com">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" class="form-control" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Log In</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
            Don't have an account? <a href="/signup">Sign up</a>
        </p>
    </div>
</div>

<?php 
$pageScripts = "
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const submitBtn = e.target.querySelector('button[type=\"submit\"]');
    
    try {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
        
        const response = await App.apiRequest('/api/auth?action=login', 'POST', {
            email, password
        });
        
        App.showToast('Login successful! Redirecting...', 'success');
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 1500);
        
    } catch (error) {
        App.showToast(error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Log In';
    }
});
";
include 'includes/components/footer.php'; 
?>
