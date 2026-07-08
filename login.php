<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['customer_id'])) {
    header("Location: profile");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, password_hash, is_verified FROM customers WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    if ($user['is_verified'] == 0) {
                        $error = 'Please verify your email address before logging in.';
                    } else {
                        // Login successful
                        session_regenerate_id(true);
                        $_SESSION['customer_id'] = $user['id'];
                        
                        $redirect = $_SESSION['redirect_after_login'] ?? 'profile';
                        unset($_SESSION['redirect_after_login']);
                        
                        header("Location: " . $redirect);
                        exit;
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
            } catch (Exception $e) {
                $error = 'An error occurred. Please try again later.';
            }
        }
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
    <h2>Welcome back to Ohemaa.</h2>
    <p>Track orders, save delivery addresses, and reorder your regulars in a couple of taps.</p>
    <div class="hero-stats" style="margin-top:44px; padding-top:24px;">
      <div><span class="num">200+</span><span class="lbl">STOCKISTS NATIONWIDE</span></div>
      <div><span class="num">15+ yrs</span><span class="lbl">FORMULATING IN KUMASI</span></div>
    </div>
  </div>

  <div class="auth-form-side">
    <div class="auth-box">
      <div class="tabs">
        <button class="tab-btn active" data-tab="loginForm">Log in</button>
        <button class="tab-btn" data-tab="signupForm">Sign up</button>
      </div>

      <div id="loginForm" class="form-flip active">
        
        <?php if (!empty($error)): ?>
            <div style="padding: 15px; background: #fee; border-left: 4px solid #c00; margin-bottom: 20px; font-size: 0.9rem; color: #c00;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div style="padding: 15px; background: #efe; border-left: 4px solid #0c0; margin-bottom: 20px; font-size: 0.9rem; color: #0c0;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

          <div class="field">
            <label for="loginEmail">Email or phone</label>
            <input id="loginEmail" type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="you@email.com" required>
          </div>
          <div class="field">
            <label for="loginPass">Password</label>
            <input id="loginPass" type="password" name="password" placeholder="••••••••" required>
          </div>
          <div style="display:flex; justify-content:flex-end; margin-bottom:20px;">
            <a href="#" class="link-quiet">Forgot password?</a>
          </div>
          <button class="form-submit btn-full" type="submit">Log in</button>
        </form>
        <div class="divider-or">or</div>
        <div class="social-row">
          <button class="social-btn" type="button">Google</button>
          <button class="social-btn" type="button">Apple</button>
        </div>
        <p class="auth-foot">New to Ohemaa? <a href="#" onclick="document.querySelector('[data-tab=signupForm]').click(); return false;">Create an account</a></p>
      </div>

      <div id="signupForm" class="form-flip">
        
        <div id="registerAlert" class="alert d-none" style="padding: 15px; margin-bottom: 20px; font-size: 0.9rem; display: none;"></div>
        <form id="registerForm">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="field">
              <label>First Name</label>
              <input name="first_name" type="text" required>
            </div>
            <div class="field">
              <label>Last Name</label>
              <input name="last_name" type="text" required>
            </div>
          </div>
          <div class="field">
            <label>Email Address</label>
            <input name="email" type="email" placeholder="you@email.com" required>
          </div>
          <div class="field">
            <label>Phone Number</label>
            <input name="phone" type="tel" required>
          </div>
          <div class="field">
            <label>Delivery Address (Optional)</label>
            <input name="address" type="text">
          </div>
          <div class="field">
            <label>Password</label>
            <input name="password" type="password" required>
            <span style="font-size: 0.8rem; color: var(--grey); margin-top: 5px; display: block;">Minimum 8 characters</span>
          </div>
          <div class="field-check">
            <input type="checkbox" id="suTerms" required>
            <label for="suTerms" style="margin:0; font-weight:500;">I agree to the Terms of Service and Privacy Policy.</label>
          </div>
          <button class="form-submit btn-full" type="submit">Create account</button>
        </form>
        <p class="auth-foot">Already have an account? <a href="#" onclick="document.querySelector('[data-tab=loginForm]').click(); return false;">Log in</a></p>
      </div>

      <p class="auth-foot" style="margin-top:32px;">Applying as a shop or business? <a href="<?php echo BASE_URL; ?>become_stockist">Become a stockist</a> instead.</p>
    </div>
  </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/js/app.js"></script>
<script>
document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.form-submit');
    const alertBox = document.getElementById('registerAlert');
    
    btn.disabled = true;
    btn.innerHTML = 'Creating...';
    alertBox.style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/auth/register.php', {
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
            alertBox.innerHTML = result.message + " You can now log in.";
            this.reset();
            // Switch to login tab
            setTimeout(() => {
                document.querySelector('[data-tab=loginForm]').click();
            }, 2000);
        } else {
            alertBox.style.backgroundColor = '#fee';
            alertBox.style.borderLeft = '4px solid #c00';
            alertBox.style.color = '#c00';
            alertBox.innerText = result.message || 'Registration failed';
        }
    } catch (error) {
        alertBox.style.display = 'block';
        alertBox.style.backgroundColor = '#fee';
        alertBox.style.borderLeft = '4px solid #c00';
        alertBox.style.color = '#c00';
        alertBox.innerText = 'Network error. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Create account';
    }
});

// Tab logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.form-flip').forEach(f => f.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>
