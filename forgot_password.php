<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    header("Location: profile");
    exit;
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
    <h2>Locked out happens.</h2>
    <p>Enter the email tied to your account and we'll send a link to get you straight back in.</p>
  </div>

  <div class="auth-form-side">
    <div class="auth-box">

      <div id="requestState">
        <h3 style="font-size:1.4rem; margin-bottom:8px;">Forgot your password?</h3>
        <p style="color:rgba(26,22,32,0.6); font-size:0.92rem; margin-bottom:28px;">No worries — we'll send reset instructions to your email.</p>
        
        <div id="fpAlert" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div>
        <form id="forgotPasswordForm">
          <div class="field">
            <label for="fpEmail">Email address</label>
            <input id="fpEmail" name="email" type="email" placeholder="you@email.com" required>
          </div>
          <button class="form-submit btn-full" type="submit">Send reset link</button>
        </form>

        <p class="auth-foot"><a href="login.html">← Back to log in</a></p>
      </div>

      <div id="sentState" style="display:none; text-align:center;">
        <div class="status-icon-circle" style="margin:0 auto 24px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 6l8 7 8-7"/></svg>
        </div>
        <h3 style="font-size:1.3rem; margin-bottom:10px;">Check your email</h3>
        <p style="color:rgba(26,22,32,0.62); font-size:0.92rem; margin-bottom:26px;">We've sent password reset instructions to your email if an account exists with that address.</p>
        <a href="reset_password.html" class="btn btn-dark btn-full">I have my reset link</a>
        <p class="auth-foot">Didn't get it? <a href="resend_verification.html">Resend or check spam</a></p>
      </div>

    </div>
  </div>
</div>


<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('fpAlert');
    const requestState = document.getElementById('requestState');
    const sentState = document.getElementById('sentState');
    
    btn.disabled = true;
    btn.innerHTML = 'Sending...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.status === 'success') {
            requestState.style.display = 'none';
            sentState.style.display = 'block';
        } else {
            alertBox.style.display = 'block';
            alertBox.style.backgroundColor = '#fee';
            alertBox.style.borderLeft = '4px solid #c00';
            alertBox.style.color = '#c00';
            alertBox.innerText = result.message || 'Failed to send reset link.';
        }
    } catch (error) {
        alertBox.style.display = 'block';
        alertBox.style.backgroundColor = '#fee';
        alertBox.style.borderLeft = '4px solid #c00';
        alertBox.style.color = '#c00';
        alertBox.innerText = 'Network error. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Send reset link';
    }
});
</script>
<?php include 'includes/footer.php'; ?>