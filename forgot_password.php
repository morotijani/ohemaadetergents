<?php include 'includes/header.php'; ?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4 mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4 bg-white p-5 border border-light">
                <div class="text-center mb-4">
                    <h2 class="font-serif" style="font-size: 2rem;">Forgot Password</h2>
                    <p class="font-sans text-muted" style="font-size: 0.9rem;">Enter your email to receive a reset link</p>
                </div>

                <div id="forgotAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="forgotForm">
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-0 border-dark p-3 font-sans" required>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 rounded-0 py-3 font-sans text-uppercase letter-spacing-wide mb-4" id="forgotBtn">
                        Send Reset Link
                    </button>
                    <div class="text-center">
                        <p class="font-sans text-muted mb-0" style="font-size: 0.85rem;">
                            Remembered your password? <a href="login" class="text-dark fw-bold text-decoration-none">Sign In</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('forgotForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('forgotBtn');
    const alertBox = document.getElementById('forgotAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Sending...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/customer_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.status === 'success') {
            alertBox.classList.add('alert-success');
            alertBox.innerHTML = result.message;
            alertBox.classList.remove('d-none');
            this.reset();
        } else {
            alertBox.classList.add('alert-danger');
            alertBox.innerText = result.message || 'Failed to send link';
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Send Reset Link';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
