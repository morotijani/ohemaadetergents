<?php include 'includes/header.php'; ?>

<header class="page-hero">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Become a Stockist</span></div>
    <span class="eyebrow">Sell Ohemaa</span>
    <h1>Put a queen's standard on your shelf.</h1>
    <p class="lede">200+ shops already carry the range. Apply below and our distribution team will follow up within two business days.</p>
  </div>
  <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section>
  <div class="wrap two-col">
    <div class="reveal">
      <span class="eyebrow">How it works</span>
      <h2 style="margin-top:18px; font-size:2rem;">From application to shelf in four steps.</h2>
      <div class="process-list mt-lg">
        <div class="process-item">
          <span class="step-num">01</span>
          <div><h3>Apply</h3><p>Tell us about your shop, location, and expected order volume using the form.</p></div>
        </div>
        <div class="process-item">
          <span class="step-num">02</span>
          <div><h3>Review</h3><p>Our distribution team checks regional coverage and confirms terms with you by phone.</p></div>
        </div>
        <div class="process-item">
          <span class="step-num">03</span>
          <div><h3>Sample delivery</h3><p>We send a starter case so you can see how the range moves before committing to a standing order.</p></div>
        </div>
        <div class="process-item">
          <span class="step-num">04</span>
          <div><h3>Ongoing supply</h3><p>Once approved, you're added to our delivery route with restock windows that match your sell-through.</p></div>
        </div>
      </div>

      <div class="value-card" style="margin-top:40px; background:var(--paper);">
        <h3 style="margin-bottom:10px;">What stockists get</h3>
        <p>Wholesale pricing, marketing material for in-shop display, a dedicated route rep, and first access to new product lines.</p>
      </div>
    </div>

    <div class="form-card reveal">
      <h3>Stockist application</h3>
      <p class="sub">Takes about two minutes. We'll never share your details.</p>
      <form onsubmit="event.preventDefault(); this.querySelector('.form-submit').textContent='Application sent ✓'; this.querySelector('.form-submit').disabled=true;">
        <div class="field-row">
          <div class="field">
            <label for="ownerName">Your name</label>
            <input id="ownerName" type="text" placeholder="e.g. Ama Yeboah" required>
          </div>
          <div class="field">
            <label for="ownerPhone">Phone number</label>
            <input id="ownerPhone" type="tel" placeholder="024 000 0000" required>
          </div>
        </div>
        <div class="field">
          <label for="shopName2">Shop or business name</label>
          <input id="shopName2" type="text" placeholder="e.g. Yeboah Provisions" required>
        </div>
        <div class="field">
          <label for="bizType">Business type</label>
          <select id="bizType">
            <option>Provision shop</option>
            <option>Supermarket</option>
            <option>Salon / spa</option>
            <option>Hotel / hospitality</option>
            <option>Cleaning service</option>
            <option>Wholesale distributor</option>
          </select>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="region2">Region</label>
            <select id="region2">
              <option>Ashanti</option>
              <option>Greater Accra</option>
              <option>Eastern</option>
              <option>Central</option>
              <option>Other</option>
            </select>
          </div>
          <div class="field">
            <label for="loc2">Town / area</label>
            <input id="loc2" type="text" placeholder="e.g. Bantama" required>
          </div>
        </div>
        <div class="field">
          <label for="vol2">Expected monthly volume</label>
          <select id="vol2">
            <option>Under 50 units</option>
            <option>50–200 units</option>
            <option>200–500 units</option>
            <option>500+ units</option>
          </select>
        </div>
        <div class="field">
          <label>Business registration (optional)</label>
          <div class="upload-box">Click to upload, or drag a file here — PDF or image</div>
        </div>
        <div class="field-check">
          <input type="checkbox" id="terms" required>
          <label for="terms" style="margin:0; font-weight:500;">I agree to be contacted by the Ohemaa distribution team about this application.</label>
        </div>
        <button class="form-submit btn-full" type="submit">Send application</button>
      </form>
      <p class="form-note">Already applied? <a href="<?php echo BASE_URL; ?>track_order" class="link-quiet">Track your onboarding status</a></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
