<?php 
$hideSidebar = true;
include 'includes/header.php'; 
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="ohemaa-card" style="width: 100%; max-width: 450px; padding: 48px 40px 36px;">
        <div class="text-center mb-4">
            <img src="<?php echo BASE_URL; ?>public/assets/img/logo.png" alt="Ohemaa Detergents" style="height: 40px; object-fit: contain; margin-bottom: 16px;">
            <h4 class="mb-2" style="font-family: 'Google Sans', sans-serif; font-weight: 400; color: var(--text-color); font-size: 24px;">Sign in</h4>
            <p class="mb-0" style="color: var(--text-color); font-size: 16px;">Use your Ohemaa Admin Account</p>
        </div>
        
        <form id="loginForm" class="mt-5">
            <div id="errorAlert" class="alert alert-danger d-none text-start" role="alert" style="border-radius: 8px; font-size: 14px;"></div>
            
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="name@example.com" required style="border-radius: 4px;">
                <label for="email">Email or phone</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" placeholder="Password" required style="border-radius: 4px;">
                <label for="password">Enter your password</label>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-5">
                <a href="#" style="color: var(--active-text); text-decoration: none; font-weight: 500; font-family: 'Google Sans', sans-serif; font-size: 14px;">Forgot password?</a>
                <button type="submit" class="btn-ohemaa">Sign in</button>
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
            setTimeout(() => window.location.href = `${BASE_URL}/admin/index', 1000);
        } else {
            const errorAlert = document.getElementById('errorAlert');
            errorAlert.innerText = data.message || 'Login failed';
            errorAlert.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        console.error(err);
        const errorAlert = document.getElementById('errorAlert');
        errorAlert.innerText = 'Connection error. Please try again.';
        errorAlert.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

document.getElementById('email').addEventListener('input', () => document.getElementById('errorAlert').classList.add('d-none'));
document.getElementById('password').addEventListener('input', () => document.getElementById('errorAlert').classList.add('d-none'));
</script>
<?php include 'includes/footer.php'; ?>
