<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="brand">
          <svg class="seal" viewBox="0 0 60 60" fill="none">
            <circle cx="30" cy="30" r="29" fill="#2B1B4D" stroke="#C9A227" stroke-width="1.5"/>
            <circle cx="30" cy="30" r="22" fill="none" stroke="#C9A227" stroke-width="1"/>
            <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#C9A227"/>
            <circle cx="30" cy="30" r="4" fill="#2B1B4D"/>
          </svg>
          <div style="display: flex; flex-direction: column; justify-content: center; line-height: 1.1;">
              <span style="font-weight: 800; font-size: 1em;">OHEMAA</span>
              <span style="font-size: 0.4em; letter-spacing: 0.15em; opacity: 0.85; font-weight: 600;">DETERGENTS</span>
          </div>
        </div>
        <p>Formulated and bottled in Kumasi, Ashanti Region. Cleanliness fit for a queen, since day one.</p>
      </div>
      <div class="foot-col">
        <h4>Explore</h4>
        <a href="<?php echo BASE_URL; ?>about">Heritage</a>
        <a href="<?php echo BASE_URL; ?>shop">Products</a>
        <a href="<?php echo BASE_URL; ?>process">Process</a>
        <a href="<?php echo BASE_URL; ?>stockists">Stockists</a>
        <a href="#sustainability">Sustainability</a>
      
        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="<?php echo BASE_URL; ?>profile">My Account</a>
            <a href="#" onclick="if(window.logoutUser) logoutUser();">Logout</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>login">Login</a>
            <a href="<?php echo BASE_URL; ?>register">Register</a>
        <?php endif; ?></div>
      <div class="foot-col">
        <h4>Business</h4>
        <a href="<?php echo BASE_URL; ?>become_stockist">Become a stockist</a>
        <a href="<?php echo BASE_URL; ?>shop">Private label</a>
        <a href="<?php echo BASE_URL; ?>contact">Wholesale pricing</a>
      </div>
      <div class="foot-col">
        <h4>Account</h4>
        <a href="<?php echo BASE_URL; ?>track_order">Track an order</a>
        <a href="cart.html">Your cart</a>
        <a href="login.html">Log in / Sign up</a>
        <a href="<?php echo BASE_URL; ?>contact">Contact us</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© 2026 Ohemaa Cleaning Agents. All rights reserved.</span>
      <div class="kente-strip"></div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/app.js"></script>
</body>
</html>