<?php
session_start();
if (isset($_SESSION['customer_id'])) {
  header("Location: profile");
  exit;
}

require_once __DIR__ . '/src/Database.php';
$token = $_GET['token'] ?? '';
if (empty($token)) {
  header("Location: forgot_password");
  exit;
}

try {
  $db = App\Database::getInstance()->getConnection();
  $stmt = $db->prepare("SELECT email FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
  $stmt->execute([$token]);
  $customer = $stmt->fetch();
  if (!$customer) {
    die("Invalid or expired reset link. Please <a href='forgot_password.php'>request a new one</a>.");
  }
} catch (Exception $e) {
  die("Database error.");
}

include 'includes/header.php';
?>


<div class="auth-shell">
  <div class="auth-visual">
    <svg class="seal" width="56" height="56" viewBox="0 0 60 60" fill="none">
      <circle cx="30" cy="30" r="29" fill="none" stroke="#C9A227" stroke-width="1.5" />
      <circle cx="30" cy="30" r="22" fill="none" stroke="#C9A227" stroke-width="1" />
      <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#C9A227" />
      <circle cx="30" cy="30" r="4" fill="#2B1B4D" />
    </svg>
    <h2>Choose a new password.</h2>
    <p>Make it something strong you haven't used on Ohemaa before — at least 8 characters, mixing letters and numbers.
    </p>
  </div>

  <div class="auth-form-side">
    <div class="auth-box">

      <div id="resetState">
        <h3 style="font-size:1.4rem; margin-bottom:8px;">Set a new password</h3>
        <p style="color:rgba(26,22,32,0.6); font-size:0.92rem; margin-bottom:28px;">Resetting password for <strong
            style="color:var(--ink);"><?php echo htmlspecialchars($customer['email']); ?></strong></p>

        <div id="rpAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div>
        <form id="resetPasswordForm">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
          <div class="field pw-field">
            <label for="rpNew">New password</label>
            <input id="rpNew" name="new_password" type="password" placeholder="At least 8 characters" required
              minlength="8">
            <span class="pw-toggle">Show</span>
          </div>
          <div class="field pw-field">
            <label for="rpConfirm">Confirm new password</label>
            <input id="rpConfirm" name="confirm_password" type="password" placeholder="Re-enter password" required
              minlength="8">
            <span class="pw-toggle">Show</span>
          </div>
          <ul style="font-size:0.8rem; color:rgba(26,22,32,0.55); margin:4px 0 22px 18px; line-height:1.8;">
            <li>At least 8 characters</li>
            <li>A mix of letters and numbers</li>
          </ul>
          <button class="form-submit btn-full" type="submit">Reset password</button>
        </form>

        <p class="auth-foot"><a href="login">← Back to log in</a></p>
      </div>

      <div id="doneState" style="display:none; text-align:center;">
        <div class="status-icon-circle" style="margin:0 auto 24px; background:rgba(30,110,99,0.15);">
          <svg viewBox="0 0 24 24" fill="none" stroke="#1E6E63" stroke-width="2">
            <path d="M9 12l2 2 4-4" />
            <circle cx="12" cy="12" r="9" />
          </svg>
        </div>
        <h3 style="font-size:1.3rem; margin-bottom:10px;">Password updated</h3>
        <p style="color:rgba(26,22,32,0.62); font-size:0.92rem; margin-bottom:26px;">Your password has been reset. Use
          your new password to log in.</p>
        <a href="login" class="btn btn-dark btn-full">Continue to log in</a>
      </div>

    </div>
  </div>
</div>


<script>
  document.getElementById('resetPasswordForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('rpAlert');
    const requestState = document.getElementById('resetState');
    const doneState = document.getElementById('doneState');

    btn.disabled = true;
    btn.innerHTML = 'Resetting...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch('api/auth/reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const result = await res.json();

      if (res.ok && result.status === 'success') {
        requestState.style.display = 'none';
        doneState.style.display = 'block';
      } else {
        alertBox.style.display = 'block';
        alertBox.style.backgroundColor = '#fee';
        alertBox.style.borderLeft = '4px solid #c00';
        alertBox.style.color = '#c00';
        alertBox.innerText = result.message || 'Failed to reset password.';
      }
    } catch (error) {
      alertBox.style.display = 'block';
      alertBox.style.backgroundColor = '#fee';
      alertBox.style.borderLeft = '4px solid #c00';
      alertBox.style.color = '#c00';
      alertBox.innerText = 'Network error. Please try again.';
    } finally {
      btn.disabled = false;
      btn.innerHTML = 'Reset password';
    }
  });

  document.querySelectorAll('.pw-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
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