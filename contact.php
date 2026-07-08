<?php include 'includes/header.php'; ?>

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Client Services</h1>
            <p class="font-sans text-muted letter-spacing-wide text-uppercase mx-auto" style="font-size: 0.75rem; max-width: 400px; line-height: 1.8;">
                For inquiries regarding products, orders, or our heritage, our advisors are available to assist you.
            </p>
        </div>
    </div>
</div>

<section class="contact-section pb-5 mb-5">
    <div class="container-fluid px-4 px-lg-5">
        <div class="row g-0">
            
            <!-- Contact Info -->
            <div class="col-lg-4 pe-lg-5 mb-5 mb-lg-0 border-end border-light">
                <div class="mb-5">
                    <h6 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Boutique</h6>
                    <p class="font-sans text-muted mb-0" style="font-size: 0.85rem; line-height: 1.8;">
                        Accra, Ghana<br>
                        Spintex Road, Suite 402
                    </p>
                </div>

                <div class="mb-5">
                    <h6 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Direct Line</h6>
                    <p class="font-sans text-muted mb-0" style="font-size: 0.85rem; line-height: 1.8;">
                        +233 24 000 0000<br>
                        +233 50 000 0000
                    </p>
                </div>

                <div class="mb-5">
                    <h6 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Digital</h6>
                    <p class="font-sans text-muted mb-0" style="font-size: 0.85rem; line-height: 1.8;">
                        hello@ohemaadetergents.com<br>
                        support@ohemaadetergents.com
                    </p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8 ps-lg-5">
                <h4 class="font-sans text-uppercase letter-spacing-widest text-black mb-5 fw-600" style="font-size: 0.75rem;">Send an Inquiry</h4>
                
                <form id="contactForm">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Name *</label>
                            <input type="text" id="name" name="name" class="form-control rounded-0 border-black p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control rounded-0 border-black p-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control rounded-0 border-black p-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Message *</label>
                            <textarea id="message" name="message" class="form-control rounded-0 border-black p-3" style="height: 150px" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-black w-100 py-3" id="submitBtn">
                        Submit Inquiry
                    </button>
                </form>
                
                <div id="formResponse" class="mt-4 d-none">
                    <div class="alert rounded-0 border border-black p-3 font-sans text-black" style="font-size: 0.85rem;">
                        <span id="responseText"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    const responseDiv = document.getElementById('formResponse');
    const responseText = document.getElementById('responseText');

    btn.disabled = true;
    btn.innerHTML = 'Submitting...';

    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value
    };

    try {
        const res = await fetch(`${BASE_URL}/api/contact/submit`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();

        responseDiv.classList.remove('d-none');
        responseText.innerText = data.message;
        
        if (data.status === 'success') {
            document.getElementById('contactForm').reset();
        }
    } catch (e) {
        responseDiv.classList.remove('d-none');
        responseText.innerText = 'System error. Please try again later.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
