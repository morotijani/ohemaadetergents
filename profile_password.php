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
    
    // Fetch customer details for sidebar
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();

} catch (Exception $e) {
    $customer = ['first_name' => '', 'last_name' => '', 'email' => ''];
}

include 'includes/header.php';
?>


<header class="page-hero" style="padding:48px 0 40px;">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="index.html">Home</a><span>/</span><span>My Account</span><span>/</span><span>Settings</span></div>
    <h1 style="font-size:2rem; margin-top:14px;">Account settings</h1>
  </div>
</header>

<section style="padding-top:50px;">
  <div class="wrap account-shell">

    <div class="account-sidebar reveal">
      <div class="account-avatar"><?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?></div>
      <div class="account-who">
        <span class="name"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
        <span class="email"><?php echo htmlspecialchars($customer['email']); ?></span>
      </div>
      <nav class="account-nav">
        <a href="profile"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>Profile</a>
        <a href="profile_orders"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/></svg>Orders</a>
        <a href="profile_password" class="active"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>Settings</a>
        <a href="track_order"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="15" height="12" rx="1"/><path d="M16 10h4l3 3v5h-7z"/><circle cx="6" cy="20" r="2"/><circle cx="18" cy="20" r="2"/></svg>Track an order</a>
        <a href="logout.php" class="logout"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>Log out</a>
      </nav>
    </div>

    <div class="account-content reveal">
      <h2>Settings</h2>
      <p class="sub">Manage notifications, your password, and your account.</p>

      <div class="settings-section">
        <h3>Notification preferences</h3>
        <p class="sub">Choose what you hear from us.</p>
        <div class="toggle-row">
          <div><div class="label">Order updates</div><div class="desc">SMS and email when your order status changes</div></div>
          <label class="switch"><input type="checkbox" checked><span class="switch-track"></span></label>
        </div>
        <div class="toggle-row">
          <div><div class="label">New product launches</div><div class="desc">Be first to know when we add to the range</div></div>
          <label class="switch"><input type="checkbox" checked><span class="switch-track"></span></label>
        </div>
        <div class="toggle-row">
          <div><div class="label">Promotions & discounts</div><div class="desc">Occasional offers on your favourite products</div></div>
          <label class="switch"><input type="checkbox"><span class="switch-track"></span></label>
        </div>
      </div>

      <div class="settings-section" id="change-password">
        <h3>Change password</h3>
        <p class="sub">Use at least 8 characters, with a mix of letters and numbers.</p>
        <div id="passwordAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div><form id="passwordForm">
          <div class="field pw-field">
            <label for="curPass">Current password</label>
            <input id="curPass" name="current_password" type="password" placeholder="••••••••" required>
            <span class="pw-toggle">Show</span>
          </div>
          <div class="field-row">
            <div class="field pw-field">
              <label for="newPass">New password</label>
              <input id="newPass" name="new_password" type="password" placeholder="••••••••" required>
              <span class="pw-toggle">Show</span>
            </div>
            <div class="field pw-field">
              <label for="confirmPass">Confirm new password</label>
              <input id="confirmPass" name="confirm_password" type="password" placeholder="••••••••" required>
              <span class="pw-toggle">Show</span>
            </div>
          </div>
          <button class="form-submit" type="submit" style="width:auto; padding:13px 28px;">Update password</button>
        </form>
      </div>

      <div class="settings-section">
        <h3>Language & region</h3>
        <div class="field-row">
          <div class="field">
            <label for="lang">Language</label>
            <select id="lang"><option>English</option><option>Twi</option></select>
          </div>
          <div class="field">
            <label for="curr">Currency</label>
            <select id="curr"><option>GH₵ — Ghana Cedi</option></select>
          </div>
        </div>
      </div>

      <div class="settings-section">
        <div class="danger-zone">
          <h3>Delete account</h3>
          <p class="sub">This permanently removes your profile, saved addresses, and order history. This can't be undone.</p>
          <button class="btn" style="border:1.5px solid var(--crimson); color:var(--crimson);" type="button" onclick="this.textContent='Contact support to confirm'; this.disabled=true;">Delete my account</button>
        </div>
      </div>

    </div>
  </div>
</section>


<script>
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('passwordAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Updating...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/customers/password_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        alertBox.style.display = 'block';
        if (res.ok && result.status === 'success') {
            alertBox.style.backgroundColor = '#efe';
            alertBox.style.borderLeft = '4px solid #0c0';
            alertBox.style.color = '#0c0';
            alertBox.innerHTML = result.message;
            this.reset();
        } else {
            alertBox.style.backgroundColor = '#fee';
            alertBox.style.borderLeft = '4px solid #c00';
            alertBox.style.color = '#c00';
            alertBox.innerText = result.message || 'Update failed';
        }
    } catch (error) {
        alertBox.style.display = 'block';
        alertBox.style.backgroundColor = '#fee';
        alertBox.style.borderLeft = '4px solid #c00';
        alertBox.style.color = '#c00';
        alertBox.innerText = 'Network error. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Update password';
    }
});

document.querySelectorAll('.pw-toggle').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const input = this.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = 'Hide';
        } else {
            input.type = 'password';
            this.textContent = 'Show';
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>