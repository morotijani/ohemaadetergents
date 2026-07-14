// ---------- Mobile nav ----------
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', isOpen);
    });
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));
}

// ---------- Scroll reveal ----------
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ---------- Toast ----------
function showToast(msg) {
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => toast.classList.remove('show'), 2400);
}

async function addToCart(productId, qty = 1, sizeId = null, btn = null) {
    if (btn) {
        btn.disabled = true;
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Adding...';
    }
    
    try {
        const res = await fetch(`${BASE_URL}/api/cart/action.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId, qty: qty, size_id: sizeId })
        });
        const data = await res.json();
        if (data.status === 'success') {
            document.querySelectorAll('.js-cart-badge').forEach(b => {
                b.textContent = data.data.count;
                b.style.display = data.data.count > 0 ? 'flex' : 'none';
            });
            showToast('Added to cart');
            if (btn) {
                btn.textContent = 'Added ✓';
                btn.classList.add('added');
                setTimeout(() => { 
                    btn.textContent = btn.dataset.originalText; 
                    btn.classList.remove('added'); 
                    btn.disabled = false; 
                }, 1400);
            }
        } else {
            showToast(data.message || 'Could not add to cart');
            if (btn) {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText;
            }
        }
    } catch(e) {
        console.error(e);
        showToast('Network error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = btn.dataset.originalText;
        }
    }
}

// ---------- Product detail tabs ----------
document.querySelectorAll('.tab-nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const nav = btn.closest('.tab-nav');
        nav.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const wrapper = nav.closest('.pd-tabs');
        wrapper.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        const target = wrapper.querySelector('#' + btn.dataset.tabTarget);
        if (target) target.classList.add('active');
    });
});

// ---------- Product detail option chips (size) ----------
document.querySelectorAll('.option-row').forEach(row => {
    row.querySelectorAll('.option-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            row.querySelectorAll('.option-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
        });
    });
});

// ---------- Product gallery thumbnails ----------
document.querySelectorAll('.pd-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
        thumb.parentElement.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    });
});

// ---------- Stockist directory search ----------
const filterChips = document.querySelectorAll('.filter-chip');
const directorySearch = document.getElementById('directorySearch');
if (directorySearch) {
    directorySearch.addEventListener('input', () => {
        const q = directorySearch.value.trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('.stockist-card').forEach(card => {
            const activeChip = document.querySelector('.filter-chip.active');
            const cat = activeChip ? activeChip.dataset.filter : 'all';
            const matchesRegion = cat === 'all' || card.dataset.category === cat;
            const matchesSearch = !q || card.textContent.toLowerCase().includes(q);
            const show = matchesRegion && matchesSearch;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        const noResults = document.querySelector('.no-results');
        if (noResults) noResults.classList.toggle('show', visibleCount === 0);
    });
}
if (filterChips.length && document.querySelector('.stockist-card')) {
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            if (directorySearch) directorySearch.dispatchEvent(new Event('input'));
        });
    });
}

// ---------- Accordion ----------
document.querySelectorAll('.faq-accordion-trigger').forEach(trigger => {
    trigger.addEventListener('click', () => {
        const item = trigger.closest('.faq-accordion-item');
        const panel = item.querySelector('.faq-accordion-panel');
        const isOpen = item.classList.contains('open');
        item.parentElement.querySelectorAll('.faq-accordion-item.open').forEach(openItem => {
            if (openItem !== item) {
                openItem.classList.remove('open');
                openItem.querySelector('.faq-accordion-panel').style.maxHeight = null;
            }
        });
        if (isOpen) {
            item.classList.remove('open');
            panel.style.maxHeight = null;
        } else {
            item.classList.add('open');
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }
    });
});

// ---------- Auth tabs (login page) ----------
document.querySelectorAll('.tab-btn').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        tab.parentElement.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.form-flip').forEach(f => f.classList.remove('active'));
        const targetForm = document.getElementById(target);
        if (targetForm) targetForm.classList.add('active');
    });
});

// ---------- Cart quantity steppers (cart page) ----------
document.querySelectorAll('.qty-stepper').forEach(stepper => {
    const valEl = stepper.querySelector('.qty-val');
    const minus = stepper.querySelector('.qty-minus');
    const plus = stepper.querySelector('.qty-plus');
    if (!valEl || !minus || !plus) return;
    const updateLineTotal = () => {
        const row = stepper.closest('.cart-row');
        if (!row) return;
        const unit = parseFloat(row.dataset.unitPrice || '0');
        const qty = parseInt(valEl.textContent, 10);
        const lineEl = row.querySelector('.cart-line-total');
        if (lineEl) lineEl.textContent = 'GH₵ ' + (unit * qty).toFixed(2);
        recalcCartSummary();
    };
    minus.addEventListener('click', () => {
        let v = parseInt(valEl.textContent, 10);
        if (v > 1) { valEl.textContent = v - 1; updateLineTotal(); }
    });
    plus.addEventListener('click', () => {
        let v = parseInt(valEl.textContent, 10);
        valEl.textContent = v + 1; updateLineTotal();
    });
});

function recalcCartSummary() {
    const rows = document.querySelectorAll('.cart-row[data-unit-price]');
    if (!rows.length) return;
    let subtotal = 0;
    rows.forEach(row => {
        const unit = parseFloat(row.dataset.unitPrice || '0');
        const qty = parseInt(row.querySelector('.qty-val').textContent, 10);
        subtotal += unit * qty;
    });
    const subtotalEl = document.querySelector('.js-subtotal');
    const totalEl = document.querySelector('.js-total');
    if (subtotalEl) subtotalEl.textContent = 'GH₵ ' + subtotal.toFixed(2);
    if (totalEl) totalEl.textContent = 'GH₵ ' + subtotal.toFixed(2);
}
recalcCartSummary();

// cart-remove click is handled per-row via inline onclick="removeItem()" in cart.php
// No global listener needed here to avoid double-firing

// ---------- Product filter chips ----------
if (filterChips.length) {
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            filterChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const cat = chip.dataset.filter;
            let visibleCount = 0;
            document.querySelectorAll('[data-category]').forEach(card => {
                const show = cat === 'all' || card.dataset.category === cat;
                card.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            const noResults = document.querySelector('.no-results');
            if (noResults && !document.getElementById('directorySearch')) noResults.classList.toggle('show', visibleCount === 0);
        });
    });
}

// ---------- Password visibility toggle ----------
document.querySelectorAll('.pw-toggle').forEach(toggle => {
    toggle.addEventListener('click', () => {
        const field = toggle.closest('.pw-field').querySelector('input');
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        toggle.textContent = showing ? 'Show' : 'Hide';
    });
});

// ---------- Checkout payment options ----------
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', () => {
        opt.parentElement.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        const radio = opt.querySelector('input[type=radio]');
        if (radio) radio.checked = true;
    });
});

// ---------- Resend verification cooldown ----------
document.querySelectorAll('.js-resend-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        let seconds = 30;
        btn.disabled = true;
        const original = btn.textContent;
        const timerEl = document.querySelector('.resend-timer');
        showToast('Verification email sent');
        const interval = setInterval(() => {
            if (timerEl) timerEl.textContent = `You can resend in ${seconds}s`;
            seconds--;
            if (seconds < 0) {
                clearInterval(interval);
                btn.disabled = false;
                btn.textContent = original;
                if (timerEl) timerEl.textContent = '';
            }
        }, 1000);
    });
});

// ---------- Wholesale quote builder ----------
const wholesaleProducts = window.wholesaleProductsDb || [];

function tierPriceFor(product, cartons) {
    const tier = product.tiers.find(t => cartons >= t.min && cartons <= t.max) || product.tiers[0];
    return tier.price;
}

const quoteBuilder = document.getElementById('quoteBuilder');
if (quoteBuilder) {
    function recalcQuoteBuilder() {
        let subtotal = 0;
        document.querySelectorAll('.quote-row[data-product-id]').forEach(row => {
            const product = wholesaleProducts.find(p => p.id === row.dataset.productId);
            const input = row.querySelector('.carton-input');
            const cartons = Math.max(0, parseInt(input.value, 10) || 0);
            const unitPrice = tierPriceFor(product, cartons || 1);
            const lineTotal = cartons * product.carton * unitPrice;
            row.querySelector('.quote-unit-price').textContent = cartons > 0 ? `GH₵ ${unitPrice.toFixed(2)} / unit` : '—';
            row.querySelector('.quote-line-total').textContent = `GH₵ ${lineTotal.toFixed(2)}`;
            subtotal += lineTotal;
        });
        const vat = subtotal * 0.15;
        const total = subtotal + vat;
        document.querySelector('.js-wh-subtotal').textContent = `GH₵ ${subtotal.toFixed(2)}`;
        document.querySelector('.js-wh-vat').textContent = `GH₵ ${vat.toFixed(2)}`;
        document.querySelector('.js-wh-total').textContent = `GH₵ ${total.toFixed(2)}`;
        return { subtotal, vat, total };
    }
    document.querySelectorAll('.carton-input').forEach(input => {
        input.addEventListener('input', recalcQuoteBuilder);
    });
    recalcQuoteBuilder();
    window.__recalcQuoteBuilder = recalcQuoteBuilder;
}

function generateInvoice() {
    const totals = window.__recalcQuoteBuilder ? window.__recalcQuoteBuilder() : null;
    if (!totals || totals.subtotal <= 0) {
        showToast('Add at least one product quantity first');
        return;
    }
    const company = document.getElementById('whCompany').value || 'Your Company Name';
    const contact = document.getElementById('whContact').value || 'Contact Person';
    const email = document.getElementById('whEmail').value || 'you@company.com';
    const phone = document.getElementById('whPhone').value || '—';
    const address = document.getElementById('whAddress').value || 'Delivery address';

    const today = new Date();
    const validUntil = new Date(today.getTime() + 14 * 24 * 60 * 60 * 1000);
    const fmt = d => d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const piNumber = 'PI-' + today.getFullYear() + '-' + String(Math.floor(1000 + Math.random() * 9000));

    let rows = '';
    document.querySelectorAll('.quote-row[data-product-id]').forEach(row => {
        const product = wholesaleProducts.find(p => p.id === row.dataset.productId);
        const cartons = parseInt(row.querySelector('.carton-input').value, 10) || 0;
        if (cartons <= 0) return;
        const unitPrice = tierPriceFor(product, cartons);
        const units = cartons * product.carton;
        const lineTotal = units * unitPrice;
        rows += `<tr>
      <td>${product.name}<br><span style="font-size:0.76rem; color:rgba(26,22,32,0.5);">${cartons} carton${cartons > 1 ? 's' : ''} × ${product.carton} units</span></td>
      <td class="num">${units}</td>
      <td class="num">GH₵ ${unitPrice.toFixed(2)}</td>
      <td class="num">GH₵ ${lineTotal.toFixed(2)}</td>
    </tr>`;
    });

    document.getElementById('invoiceDoc').innerHTML = `
    <div class="invoice-head">
        <div>
            <div class="invoice-brand">
                <img src="${BASE_URL}/public/assets/img/logo.jpeg" alt="Ohemaa Detergents" style="width: 100px; height: auto;">
                OHEMAA DETERGENTS
            </div>
            <p style="font-size:0.82rem; color:rgba(26,22,32,0.55); margin-top:6px;">Accra, Greater Accra Region — Nii Tempon Street, SDA Junction, Adenta, GH<br>info@ohemaa-detergents.com · +233 24 515 5966 / +233 55 964 5525</p>
        </div>
        <div class="invoice-meta">
            <span class="invoice-title">Pro-Forma Invoice</span>
            <div><strong>${piNumber}</strong></div>
            <div>Issued: ${fmt(today)}</div>
            <div>Valid until: ${fmt(validUntil)}</div>
        </div>
        </div>

    <div class="invoice-parties">
      <div>
        <h4>Bill To</h4>
        <p><strong>${company}</strong><br>${contact}<br>${email}<br>${phone}</p>
      </div>
      <div>
        <h4>Ship To</h4>
        <p>${address}</p>
      </div>
    </div>

    <table class="invoice-table">
      <thead><tr><th>Item</th><th class="num">Units</th><th class="num">Unit Price</th><th class="num">Line Total</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>

    <div class="invoice-totals">
      <div class="row"><span>Subtotal</span><span>GH₵ ${totals.subtotal.toFixed(2)}</span></div>
      <div class="row"><span>Estimated VAT (15%)</span><span>GH₵ ${totals.vat.toFixed(2)}</span></div>
      <div class="row"><span>Delivery / logistics</span><span>Quoted separately</span></div>
      <div class="row grand"><span>Total (excl. delivery)</span><span>GH₵ ${totals.total.toFixed(2)}</span></div>
    </div>

    <div class="invoice-terms">
      <strong>Payment terms:</strong> 50% deposit to confirm order, balance due before dispatch. Payable via Mobile Money or bank transfer — details sent on request.<br>
      <strong>Validity:</strong> Prices quoted are valid for 14 days from the issue date above.<br>
      <strong>Note:</strong> This is a pro-forma invoice for quotation purposes only and is not a tax invoice or a demand for payment.
    </div>
  `;

    document.getElementById('invoiceSection').style.display = 'block';
    document.getElementById('invoiceSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    showToast('Pro-forma invoice generated');
}

// ---------- Track order demo ----------
const trackForm = document.getElementById('trackForm');
if (trackForm) {
    trackForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const result = document.getElementById('trackResult');
        if (result) {
            result.style.display = 'block';
            result.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}
