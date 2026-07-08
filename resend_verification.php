<?php
session_start();
// Pre-fill email if they just registered or are logged in but not verified
$email = $_GET['email'] ?? '';
if (empty($email) && isset($_SESSION['customer_id'])) {
    require_once __DIR__ . '/src/Database.php';
    try {
        $db = App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT email, is_verified FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $customer = $stmt->fetch();
        if ($customer) {
            if ($customer['is_verified']) {
                header("Location: profile");
                exit;
            }
            $email = $customer['email'];
        }
    } catch (Exception $e) {
        // Ignore
    }
}
include 'includes/header.php';
?>


<div class="auth-shell">
  <div class="auth-visual">
    <svg class="seal" width="56" height="56" viewBox="0 0 60 60" fill="none">
      <circle cx="30" cy="30" r="29" fill="none" stroke="#C9A227" stroke-width="1.5"/>
      <circle cx="30" cy="30" r="22" fill="none" stroke="#C9A227" stroke-width="1"/>
      <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#C9A227"/>
      <circle cx="30" cy="30" r="4" fill="#2B1B4D"/>
    </svg>
    <h2>Almost there.</h2>
    <p>Verifying your email keeps your order history and delivery details secure to your account only.</p>
  </div>

  <div class="auth-form-side">
    <div class="auth-box" style="text-align:center;">

      <div class="status-icon-circle" style="margin:0 auto 24px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 6l8 7 8-7"/></svg>
      </div>

      <h3 style="font-size:1.4rem; margin-bottom:10px;">Verify your email</h3>
      <p style="color:rgba(26,22,32,0.62); font-size:0.92rem; margin-bottom:8px;">We sent a verification link to</p>
      <p style="font-weight:700; margin-bottom:26px;" id="displayEmail"><?php echo htmlspecialchars($email); ?></p>

      
        <div id="resendAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none; text-align: left;"></div>
        <form id="resendForm">
            <div class="field" style="text-align:left;">
              <label for="verifyEmail">Wrong email? Update it below</label>
              <input id="verifyEmail" name="email" type="email" placeholder="you@email.com" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <button class="form-submit btn-full js-resend-btn" type="submit">Resend verification email</button>
        </form>

      <p class="resend-timer"></p>

      <p class="auth-foot" style="margin-top:28px;">Already verified? <a href="login.html">Log in</a></p>
      <p class="auth-foot">Wrong account? <a href="login.html">Sign out and switch</a></p>
    </div>
  </div>
</div>


<script>
document.getElementById('resendForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('resendAlert');
    const emailInput = document.getElementById('verifyEmail').value;
    const displayEmail = document.getElementById('displayEmail');
    
    btn.disabled = true;
    btn.innerHTML = 'Sending...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/resend_verification.php', {
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
            if (displayEmail) displayEmail.textContent = emailInput;
        } else {
            alertBox.style.backgroundColor = '#fee';
            alertBox.style.borderLeft = '4px solid #c00';
            alertBox.style.color = '#c00';
            alertBox.innerText = result.message || 'Failed to resend.';
        }
    } catch (error) {
        alertBox.style.display = 'block';
        alertBox.style.backgroundColor = '#fee';
        alertBox.style.borderLeft = '4px solid #c00';
        alertBox.style.color = '#c00';
        alertBox.innerText = 'Network error. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Resend verification email';
    }
});
</script>
<?php include 'includes/footer.php'; ?>