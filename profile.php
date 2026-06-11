<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login");
    exit;
}

require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
} catch (Exception $e) {
    // Handle error gracefully
    $customer = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''];
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
                    <a href="profile" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent fw-bold border-bottom text-dark">Profile Details</a>
                    <a href="profile_orders" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Order History</a>
                    <a href="profile_password" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Change Password</a>
                    <a href="#" onclick="logoutUser()" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted">Sign Out</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-8 ps-md-5">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-4 text-dark" style="font-size: 0.9rem;">Profile Details</h4>
                
                <div id="profileAlert" class="alert d-none font-sans" style="font-size: 0.85rem; border-radius: 0;"></div>

                <form id="profileForm" class="bg-white p-4 p-md-5 border border-light">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">First Name</label>
                            <input type="text" name="first_name" class="form-control rounded-0 border-dark p-3 font-sans" required value="<?php echo htmlspecialchars($customer['first_name']); ?>">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Last Name</label>
                            <input type="text" name="last_name" class="form-control rounded-0 border-dark p-3 font-sans" required value="<?php echo htmlspecialchars($customer['last_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Email Address</label>
                        <input type="email" class="form-control rounded-0 border-dark p-3 font-sans bg-light" disabled value="<?php echo htmlspecialchars($customer['email']); ?>">
                        <div class="form-text font-sans text-muted" style="font-size: 0.7rem;">Email cannot be changed</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.75rem;">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-0 border-dark p-3 font-sans" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                    </div>

                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide mt-3" id="profileBtn">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('profileBtn');
    const alertBox = document.getElementById('profileAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Saving...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/customers/profile_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.status === 'success') {
            alertBox.classList.add('alert-success');
            alertBox.innerHTML = result.message;
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('alert-danger');
            alertBox.innerText = result.message || 'Failed to update profile';
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save Changes';
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
