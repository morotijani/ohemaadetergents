<?php include 'includes/header.php'; ?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4 bg-white p-5 border border-light">
                <div class="text-center mb-4">
                    <h2 class="font-serif" style="font-size: 2rem;">Sign In</h2>
                    <p class="font-sans text-muted" style="font-size: 0.9rem;">Access your signature collection account</p>
                </div>

                <div id="loginAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="loginForm">
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-0 border-dark p-3 font-sans" required>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted mb-0" style="font-size: 0.75rem;">Password</label>
                            <a href="forgot_password" class="text-dark text-decoration-none font-sans" style="font-size: 0.75rem;">Forgot Password?</a>
                        </div>
                        <input type="password" name="password" class="form-control rounded-0 border-dark p-3 font-sans mt-2" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-0 py-3 font-sans text-uppercase letter-spacing-wide mb-4" id="loginBtn">
                        Sign In
                    </button>
                    <div class="text-center">
                        <p class="font-sans text-muted mb-0" style="font-size: 0.85rem;">
                            Don't have an account? <a href="register" class="text-dark fw-bold text-decoration-none">Register here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const alertBox = document.getElementById('loginAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Signing In...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/customer_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.status === 'success') {
            alertBox.classList.add('alert-success');
            alertBox.innerText = result.message;
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                window.location.href = result.data.redirect;
            }, 1000);
        } else {
            alertBox.classList.add('alert-danger');
            if (result.data && result.data.action === 'verify') {
                alertBox.innerText = result.message + ' Redirecting...';
                alertBox.classList.remove('d-none');
                setTimeout(() => {
                    window.location.href = 'resend_verification?email=' + encodeURIComponent(result.data.email);
                }, 1500);
            } else {
                alertBox.innerText = result.message || 'Login failed';
                alertBox.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Sign In';
            }
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Sign In';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
