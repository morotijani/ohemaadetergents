<?php 
$hideSidebar = true;
include 'includes/header.php'; 
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="ohemaa-card text-center" style="width: 100%; max-width: 450px; padding: 48px 40px 36px; margin: 0 auto;">
        <h2 class="mb-2" style="font-weight: 500; color: var(--active-text);">Ohemaa</h2>
        <h4 class="mb-4" style="font-weight: 400; color: var(--text-color);">Sign in</h4>
        <p class="text-muted mb-4">Use your Admin Account</p>
        
        <form id="loginForm">
            <div id="errorAlert" class="alert alert-danger d-none text-start" role="alert"></div>
            
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                <label for="email">Email</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Enter your password</label>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn-google">Next</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function handleLogin(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    const originalText = btn.innerHTML;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing in...';
    
    try {
        const response = await fetch(apiBase + '/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (response.ok && data.status === 'success') {
            localStorage.setItem('admin_token', data.data.token);
            localStorage.setItem('admin_user', JSON.stringify(data.data.user));
            showToast('Login successful!');
            setTimeout(() => window.location.href = '/ohemaadetergents/admin/index', 1000);
        } else {
            showToast(data.message || 'Login failed', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
<?php include 'includes/footer.php'; ?>
