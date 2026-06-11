<?php
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: login");
    exit;
}
include 'includes/header.php';
?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4 mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4 bg-white p-5 border border-light">
                <div class="text-center mb-4">
                    <h2 class="font-serif" style="font-size: 2rem;">New Password</h2>
                    <p class="font-sans text-muted" style="font-size: 0.9rem;">Set a new password for your account</p>
                </div>

                <div id="resetAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">New Password</label>
                        <input type="password" name="password" class="form-control rounded-0 border-dark p-3 font-sans" required minlength="8">
                        <div class="form-text font-sans text-muted" style="font-size: 0.7rem;">Minimum 8 characters</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control rounded-0 border-dark p-3 font-sans" required minlength="8">
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 rounded-0 py-3 font-sans text-uppercase letter-spacing-wide mb-4" id="resetBtn">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('resetForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('resetBtn');
    const alertBox = document.getElementById('resetAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Resetting...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    if (data.password !== data.confirm_password) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Passwords do not match';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Reset Password';
        return;
    }

    try {
        const res = await fetch('api/auth/customer_reset_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.status === 'success') {
            alertBox.classList.add('alert-success');
            alertBox.innerHTML = result.message;
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                window.location.href = result.data.redirect;
            }, 2000);
        } else {
            alertBox.classList.add('alert-danger');
            alertBox.innerText = result.message || 'Failed to reset password';
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = 'Reset Password';
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Reset Password';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
