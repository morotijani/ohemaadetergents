<?php include 'includes/header.php'; ?>

<section class="page-header py-5 mt-5">
    <div class="container text-center py-5">
        <h1 class="fw-900 display-4 mb-3">GET IN TOUCH</h1>
        <p class="text-muted lead mx-auto" style="max-width: 600px;">Have a question about our premium detergents? We're here to help you achieve the perfect clean.</p>
    </div>
</section>

<section class="contact-section pb-5 mb-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="contact-info-card p-5 h-100 rounded-5 shadow-sm border bg-white">
                    <h3 class="fw-bold mb-4">Contact Info</h3>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-gold-soft p-3 rounded-circle me-3">
                            <i class="bi bi-geo-alt text-gold fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Visit Us</h6>
                            <p class="text-muted small mb-0">Accra, Ghana<br>Spintex Road, Suite 402</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-gold-soft p-3 rounded-circle me-3">
                            <i class="bi bi-telephone text-gold fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Call Us</h6>
                            <p class="text-muted small mb-0">+233 24 000 0000<br>+233 50 000 0000</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="bg-gold-soft p-3 rounded-circle me-3">
                            <i class="bi bi-envelope text-gold fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Us</h6>
                            <p class="text-muted small mb-0">hello@ohemaadetergents.com<br>support@ohemaadetergents.com</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h6 class="fw-bold mb-3">Follow Our Journey</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-gold fs-5"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-gold fs-5"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-gold fs-5"><i class="bi bi-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-card p-5 rounded-5 shadow-sm border bg-white">
                    <h3 class="fw-bold mb-2">Send us a Message</h3>
                    <p class="text-muted mb-4">We'll respond to your inquiry within 24 hours.</p>

                    <form id="contactForm">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0 bg-light rounded-4" id="name" name="name" placeholder="Name" required>
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control border-0 bg-light rounded-4" id="email" name="email" placeholder="Email" required>
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0 bg-light rounded-4" id="subject" name="subject" placeholder="Subject">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control border-0 bg-light rounded-4" id="message" name="message" placeholder="Message" style="height: 150px" required></textarea>
                                    <label for="message">How can we help?</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-4 shadow-sm" id="submitBtn">
                                    SEND MESSAGE
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="formResponse" class="mt-4 d-none">
                        <div class="alert rounded-4 py-3 border-0 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <span id="responseText"></span>
                        </div>
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
    const alertBox = responseDiv.querySelector('.alert');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> SENDING...';

    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value
    };

    try {
        const res = await fetch('/ohemaadetergents/api/contact/submit', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();

        responseDiv.classList.remove('d-none');
        responseText.innerText = data.message;
        
        if (data.status === 'success') {
            alertBox.className = 'alert alert-success rounded-4 py-3 border-0 d-flex align-items-center';
            document.getElementById('contactForm').reset();
        } else {
            alertBox.className = 'alert alert-danger rounded-4 py-3 border-0 d-flex align-items-center';
        }
    } catch (e) {
        responseDiv.classList.remove('d-none');
        alertBox.className = 'alert alert-danger rounded-4 py-3 border-0 d-flex align-items-center';
        responseText.innerText = 'Network error. Please try again later.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<style>
.bg-gold-soft { background-color: rgba(212, 175, 55, 0.1); }
.text-gold { color: #D4AF37; }
.btn-primary { background-color: #000; border: none; }
.btn-primary:hover { background-color: #333; }
.form-control:focus { box-shadow: none; border: 1px solid #D4AF37 !important; }
.rounded-5 { border-radius: 2rem !important; }
.fw-900 { font-weight: 900; letter-spacing: -2px; }
</style>

<?php include 'includes/footer.php'; ?>
