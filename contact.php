<?php include 'includes/header.php'; ?>

<header class="page-hero">
    <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
        <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1" />
        <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1" />
        <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766" />
    </svg>
    <div class="wrap">
        <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Contact</span></div>
        <span class="eyebrow">We'd love to hear from you</span>
        <h1>Get in touch with the Ohemaa team.</h1>
        <p class="lede">Questions about an order, a stockist application, or a wholesale enquiry — reach us directly
            below.</p>
    </div>
    <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section>
    <div class="wrap">
        <div class="contact-grid reveal">
            <div class="contact-card">
                <div class="value-icon" style="background:var(--indigo);"><svg viewBox="0 0 24 24" fill="none"
                        stroke="#E7C766" stroke-width="2">
                        <path
                            d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z" />
                    </svg></div>
                <h3>Call us</h3>
                <p><a href="tel:+233240000000">+233 24 515 5966 / +233 55 964 5525</a></p>
                <p>Mon–Sat, 8am–6pm GMT</p>
            </div>
            <div class="contact-card">
                <div class="value-icon" style="background:var(--indigo);"><svg viewBox="0 0 24 24" fill="none"
                        stroke="#E7C766" stroke-width="2">
                        <path d="M4 4h16v16H4z" />
                        <path d="M4 6l8 7 8-7" />
                    </svg></div>
                <h3>Email us</h3>
                <p><a href="mailto:hello@ohemaaclean.com">hello@ohemaaclean.com</a></p>
                <p>We reply within one business day</p>
            </div>
            <div class="contact-card">
                <div class="value-icon" style="background:var(--indigo);"><svg viewBox="0 0 24 24" fill="none"
                        stroke="#E7C766" stroke-width="2">
                        <path d="M12 21s7-6.5 7-11.5A7 7 0 105 9.5C5 14.5 12 21 12 21z" />
                        <circle cx="12" cy="9.5" r="2.5" />
                    </svg></div>
                <h3>Visit us</h3>
                <p>Accra, Greater Accra Region — Nii Tempon Street, SDA Junction, Adenta</p>
                <p>Warehouse pickups by appointment</p>
            </div>
        </div>

        <div class="two-col mt-lg">
            <div class="reveal">
                <div class="map-card">
                    <svg viewBox="0 0 500 260" xmlns="http://www.w3.org/2000/svg">
                        <rect width="500" height="260" fill="#2B1B4D" />
                        <path d="M0 180 Q120 140 240 175 T500 160" stroke="#E7C766" stroke-width="2" fill="none"
                            opacity="0.35" />
                        <path d="M0 90 Q140 60 260 95 T500 70" stroke="#1E6E63" stroke-width="2" fill="none"
                            opacity="0.4" />
                        <circle cx="250" cy="130" r="8" fill="#C9A227" />
                        <circle cx="250" cy="130" r="16" fill="none" stroke="#C9A227" stroke-width="2" opacity="0.5" />
                        <path
                            d="M250 100 L256 118 L275 118 L260 129 L266 147 L250 136 L234 147 L240 129 L225 118 L244 118 Z"
                            fill="#E7C766" opacity="0.8" />
                    </svg>
                    <div class="map-pin-label">📍 Accra, Greater Accra Region — Nii Tempon Street, SDA Junction, Adenta
                    </div>
                </div>
            </div>
            <div class="form-card on-paper reveal">
                <h3>Send us a message</h3>
                <p class="sub">General enquiries, order issues, or feedback — this goes straight to our support team.
                </p>
                <form id="contactForm">
                    <div class="field-row">
                        <div class="field">
                            <label for="cName">Name</label>
                            <input id="cName" type="text" placeholder="Your name" required>
                        </div>
                        <div class="field">
                            <label for="cEmail">Email</label>
                            <input id="cEmail" type="email" placeholder="you@email.com" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="cSubject">Subject</label>
                        <select id="cSubject">
                            <option>Order support</option>
                            <option>Wholesale enquiry</option>
                            <option>Stockist application</option>
                            <option>Product feedback</option>
                            <option>Something else</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="cMessage">Message</label>
                        <textarea id="cMessage" placeholder="How can we help?" required></textarea>
                    </div>
                    <button class="form-submit btn-full" type="submit">Send message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="heritage">
    <div class="wrap">
        <div class="center-head reveal">
            <span class="eyebrow">Before you write in</span>
            <h2>Quick answers</h2>
        </div>
        <div class="wrap-narrow" style="padding:0; margin:0 auto;">
            <div class="accordion">
                <div class="accordion-item">
                    <button class="accordion-trigger">Where's my order? <span class="plus">+</span></button>
                    <div class="accordion-panel">
                        <div class="accordion-panel-inner">Use our <a href="<?php echo BASE_URL; ?>track_order"
                                style="color:var(--indigo); font-weight:700;">order tracking page</a> with your order
                            number for the fastest answer.</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <button class="accordion-trigger">I want to sell Ohemaa in my shop. <span
                            class="plus">+</span></button>
                    <div class="accordion-panel">
                        <div class="accordion-panel-inner">Head to our <a href="<?php echo BASE_URL; ?>become_stockist"
                                style="color:var(--indigo); font-weight:700;">Become a Stockist</a> page and fill out
                            the short application form.</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <button class="accordion-trigger">Do you deliver outside the Ashanti Region? <span
                            class="plus">+</span></button>
                    <div class="accordion-panel">
                        <div class="accordion-panel-inner">We're actively expanding delivery coverage — Greater Accra is
                            now live, with more regions being added. Message us to check your area.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.getElementById('contactForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const submitBtn = this.querySelector('.form-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(BASE_URL + '/api/contact/submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: document.getElementById('cName').value,
                    email: document.getElementById('cEmail').value,
                    subject: document.getElementById('cSubject').value,
                    message: document.getElementById('cMessage').value
                })
            });

            const result = await response.json();

            if (response.ok) {
                submitBtn.textContent = 'Message sent ✓';
                submitBtn.style.background = 'var(--teal)';
                submitBtn.style.color = 'var(--ivory)';
                submitBtn.style.borderColor = 'var(--teal)';
                this.reset();
            } else {
                alert(result.error || 'Failed to send message.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        } catch (err) {
            alert('A network error occurred. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>