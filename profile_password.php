<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login");
    exit;
}

include 'includes/header.php';
?>

<section class="py-5 bg-off-white" style="min-height: 80vh;">
    <div class="container px-4 mt-5 pt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="font-serif" style="font-size: 2.5rem;">My Account</h2>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-3 mb-5">
                <div class="list-group rounded-0 border-0">
                    <a href="profile" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Profile Details</a>
                    <a href="profile_orders" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Order History</a>
                    <a href="profile_password" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent fw-bold text-dark border-bottom">Change Password</a>
                    <a href="#" onclick="logoutUser()" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted">Sign Out</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-8 ps-md-5">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-4 text-dark" style="font-size: 0.9rem;">Change Password</h4>
                
                <div id="passwordAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="passwordForm" class="bg-white p-4 p-md-5 border border-light">
                    
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Current Password</label>
                        <input type="password" name="old_password" class="form-control rounded-0 border-dark p-3 font-sans" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">New Password</label>
                        <input type="password" name="new_password" class="form-control rounded-0 border-dark p-3 font-sans" required minlength="8">
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control rounded-0 border-dark p-3 font-sans" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide mt-3" id="passwordBtn">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('passwordBtn');
    const alertBox = document.getElementById('passwordAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Updating...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    if (data.new_password !== data.confirm_password) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'New passwords do not match';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Update Password';
        return;
    }

    try {
        const res = await fetch('api/customers/profile_password.php', {
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
            alertBox.innerText = result.message || 'Failed to update password';
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Update Password';
    }
});

async function logoutUser() {
    try {
        const res = await fetch('api/auth/customer_logout.php', { method: 'POST' });
        const result = await res.json();
        if (res.ok && result.status === 'success') {
            window.location.href = result.data.redirect;
        }
    } catch (e) {
        console.error(e);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
