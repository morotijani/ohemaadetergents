<?php
$pageTitle = 'Become a Stockist — Ohemaa Detergents';
include 'includes/header.php';
?>

<header class="page-hero">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1" />
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1" />
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766" />
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Become a Stockist</span>
    </div>
    <span class="eyebrow">Sell Ohemaa</span>
    <h1>Put a queen's standard on your shelf.</h1>
    <p class="lede">200+ shops already carry the range. Apply below and our distribution team will follow up within two
      business days.</p>
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
          <div>
            <h3>Apply</h3>
            <p>Tell us about your shop, location, and expected order volume using the form.</p>
          </div>
        </div>
        <div class="process-item">
          <span class="step-num">02</span>
          <div>
            <h3>Review</h3>
            <p>Our distribution team checks regional coverage and confirms terms with you by phone.</p>
          </div>
        </div>
        <div class="process-item">
          <span class="step-num">03</span>
          <div>
            <h3>Sample delivery</h3>
            <p>We send a starter case so you can see how the range moves before committing to a standing order.</p>
          </div>
        </div>
        <div class="process-item">
          <span class="step-num">04</span>
          <div>
            <h3>Ongoing supply</h3>
            <p>Once approved, you're added to our delivery route with restock windows that match your sell-through.</p>
          </div>
        </div>
      </div>

      <div class="value-card" style="margin-top:40px; background:var(--paper);">
        <h3 style="margin-bottom:10px;">What stockists get</h3>
        <p>Wholesale pricing, marketing material for in-shop display, a dedicated route rep, and first access to new
          product lines.</p>
      </div>
    </div>

    <div class="form-card reveal">
      <h3>Stockist application</h3>
      <p class="sub">Takes about two minutes. We'll never share your details.</p>

      <div id="successMessage" style="display: none; padding: 15px; background: #eefee4; border-left: 4px solid #2e7d32; margin-bottom: 20px; font-size: 0.95rem; color: #1b5e20;">
        <strong>Application submitted successfully!</strong><br>Our distribution team will contact you within two business days.
      </div>

      <div id="errorMessage" style="display: none; padding: 15px; background: #fee; border-left: 4px solid #c00; margin-bottom: 20px; font-size: 0.9rem; color: #c00;">
      </div>

      <form id="stockistForm">
          <div class="field-row">
            <div class="field">
              <label for="ownerName">Your name</label>
              <input id="ownerName" name="owner_name" type="text" placeholder="e.g. Ama Yeboah" required>
            </div>
            <div class="field">
              <label for="ownerPhone">Phone number</label>
              <input id="ownerPhone" name="phone" type="tel" placeholder="024 000 0000" required>
            </div>
          </div>
          <div class="field">
            <label for="shopName2">Shop or business name</label>
            <input id="shopName2" name="shop_name" type="text" placeholder="e.g. Yeboah Provisions" required>
          </div>
          <div class="field">
            <label for="bizType">Business type</label>
            <select id="bizType" name="biz_type">
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
              <select id="region2" name="region">
                <option>Ahafo</option>
                <option>Ashanti</option>
                <option>Bono</option>
                <option>Bono East</option>
                <option>Central</option>
                <option>Eastern</option>
                <option>Greater Accra</option>
                <option>North East</option>
                <option>Northern</option>
                <option>Oti</option>
                <option>Savannah</option>
                <option>Upper East</option>
                <option>Upper West</option>
                <option>Volta</option>
                <option>Western</option>
                <option>Western North</option>
              </select>
            </div>
            <div class="field">
              <label for="loc2">Town / area</label>
              <input id="loc2" name="town_area" type="text" placeholder="e.g. Bantama" required>
            </div>
          </div>
          <div class="field">
            <label for="vol2">Expected monthly volume</label>
            <select id="vol2" name="monthly_volume">
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
            <label for="terms" style="margin:0; font-weight:500;">I agree to be contacted by the Ohemaa distribution team
              about this application.</label>
          </div>
          <button class="form-submit btn-full" type="submit">Send application</button>
        </form>

      <script>
        document.getElementById('stockistForm').addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const submitBtn = this.querySelector('.form-submit');
          const originalText = submitBtn.textContent;
          submitBtn.textContent = 'Submitting...';
          submitBtn.disabled = true;
          
          const formData = new FormData(this);
          const data = Object.fromEntries(formData.entries());
          
          document.getElementById('errorMessage').style.display = 'none';

          try {
            const res = await fetch(`${BASE_URL}/api/stockists/create.php`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            });
            
            const result = await res.json();
            
            if (result.status === 'success') {
              document.getElementById('stockistForm').style.display = 'none';
              document.getElementById('successMessage').style.display = 'block';
            } else {
              document.getElementById('errorMessage').textContent = result.message || 'An error occurred';
              document.getElementById('errorMessage').style.display = 'block';
              submitBtn.textContent = originalText;
              submitBtn.disabled = false;
            }
          } catch (error) {
            document.getElementById('errorMessage').textContent = 'Network error. Please try again.';
            document.getElementById('errorMessage').style.display = 'block';
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
          }
        });
      </script>
      <p class="form-note">Already applied? <a href="<?php echo BASE_URL; ?>stockists" class="link-quiet">Track your
          onboarding status</a></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>