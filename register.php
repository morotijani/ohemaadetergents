<?php include 'includes/header.php'; ?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4 mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 bg-white p-4 p-md-5 border border-light">
                <div class="text-center mb-4">
                    <h2 class="font-serif" style="font-size: 2rem;">Create Account</h2>
                    <p class="font-sans text-muted" style="font-size: 0.9rem;">Join our signature collection</p>
                </div>

                <div id="registerAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">First Name</label>
                            <input type="text" name="first_name" class="form-control rounded-0 border-dark p-3 font-sans" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Last Name</label>
                            <input type="text" name="last_name" class="form-control rounded-0 border-dark p-3 font-sans" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-0 border-dark p-3 font-sans" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-0 border-dark p-3 font-sans">
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Password</label>
                        <input type="password" name="password" class="form-control rounded-0 border-dark p-3 font-sans" required minlength="8">
                        <div class="form-text font-sans text-muted" style="font-size: 0.7rem;">Minimum 8 characters</div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 rounded-0 py-3 font-sans text-uppercase letter-spacing-wide mb-4" id="registerBtn">
                        Register
                    </button>
                    <div class="text-center">
                        <p class="font-sans text-muted mb-0" style="font-size: 0.85rem;">
                            Already have an account? <a href="login" class="text-dark fw-bold text-decoration-none">Sign In</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('registerBtn');
    const alertBox = document.getElementById('registerAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Registering...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/register.php', {
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
            alertBox.innerText = result.message || 'Registration failed';
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Register';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
