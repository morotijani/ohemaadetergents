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
    // Assuming address and region are added to DB, but we fallback if not.
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
} catch (Exception $e) {
    $customer = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''];
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
    <div class="breadcrumb"><a href="index.html">Home</a><span>/</span><span>My Account</span></div>
    <h1 style="font-size:2rem; margin-top:14px;">Welcome back, <?php echo htmlspecialchars($customer['first_name']); ?>.</h1>
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
        <a href="profile" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>Profile</a>
        <a href="profile_orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/></svg>Orders</a>
        <a href="profile_password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>Settings</a>
        <a href="track_order"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="15" height="12" rx="1"/><path d="M16 10h4l3 3v5h-7z"/><circle cx="6" cy="20" r="2"/><circle cx="18" cy="20" r="2"/></svg>Track an order</a>
        <a href="#" onclick="if(window.logoutUser) logoutUser(); return false;" class="logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>Log out</a>
      </nav>
    </div>

    <div class="account-content reveal">
      <h2>Profile details</h2>
      <p class="sub">Update your personal information and delivery preferences.</p>

      <div class="settings-section">
        <h3>Personal information</h3>
        <p class="sub">This is used for order confirmations and delivery updates.</p>
        <div id="profileAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div><form id="profileForm">
          <div class="field-row">
            <div class="field">
              <label for="pFirst">First name</label>
              <input id="pFirst" name="first_name" type="text" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
            </div>
            <div class="field">
              <label for="pLast">Last name</label>
              <input id="pLast" name="last_name" type="text" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="pEmail">Email</label>
              <input id="pEmail" name="email" type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled style="background:#f5f5f5; color:#888;">
            </div>
            <div class="field">
              <label for="pPhone">Phone</label>
              <input id="pPhone" name="phone" type="tel" value="<?php echo htmlspecialchars($customer['phone']); ?>">
            </div>
          </div>
          <button class="form-submit" type="submit" style="width:auto; padding:13px 28px;">Save changes</button>
        </form>
      </div>

      <div class="settings-section">
        <h3>Delivery address</h3>
        <p class="sub">Used as your default address at checkout.</p>
        <div id="profileAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div><form id="profileForm">
          <div class="field">
            <label for="pAddr">Street address</label>
            <input id="pAddr" name="address" type="text" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
          </div>
          <div class="field-row">
            <div class="field">
              <label for="pCity">Town / area</label>
              <input id="pCity" type="text" value="Bantama, Kumasi">
            </div>
            <div class="field">
              <label for="pRegion">Region</label>
              <select id="pRegion" name="region">
                <option selected>Ashanti</option>
                <option>Greater Accra</option>
                <option>Eastern</option>
                <option>Central</option>
              </select>
            </div>
          </div>
          <button class="form-submit" type="submit" style="width:auto; padding:13px 28px;">Save address</button>
        </form>
      </div>

      <div class="settings-section">
        <h3>Account overview</h3>
        <div class="stat-cards" style="grid-template-columns:repeat(3,1fr);">
          <div class="stat-card"><span class="num">12</span><span class="lbl">Orders placed</span></div>
          <div class="stat-card"><span class="num">GH₵ 890</span><span class="lbl">Total spent</span></div>
          <div class="stat-card"><span class="num">Since 2024</span><span class="lbl">Member</span></div>
        </div>
      </div>

    </div>
  </div>
</section>


<script>
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('profileAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Saving...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/customers/profile_update.php', {
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
        btn.innerHTML = 'Save changes';
    }
});
</script>
<?php include 'includes/footer.php'; ?>