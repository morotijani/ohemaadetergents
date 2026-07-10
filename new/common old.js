// ---------- Mobile nav ----------
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if(navToggle && navLinks){
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', isOpen);
  });
  navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));
}

// ---------- Scroll reveal ----------
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if(entry.isIntersecting){
      entry.target.classList.add('in');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ---------- Toast ----------
function showToast(msg){
  let toast = document.querySelector('.toast');
  if(!toast){
    toast = document.createElement('div');
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => toast.classList.remove('show'), 2400);
}

// ---------- In-memory cart badge demo (no backend / no storage) ----------
window.__cartCount = window.__cartCount || 0;
function bumpCartBadge(delta){
  window.__cartCount = Math.max(0, window.__cartCount + delta);
  document.querySelectorAll('.js-cart-badge').forEach(b => {
    b.textContent = window.__cartCount;
    b.style.display = window.__cartCount > 0 ? 'flex' : 'none';
  });
}
document.querySelectorAll('.add-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const box = btn.closest('.pd-buybox');
    const qtyEl = box ? box.querySelector('.qty-val') : null;
    const delta = qtyEl ? parseInt(qtyEl.textContent, 10) : 1;
    bumpCartBadge(delta);
    const original = btn.textContent;
    btn.textContent = 'Added ✓';
    btn.classList.add('added');
    showToast(btn.dataset.product ? `${btn.dataset.product} added to cart` : 'Added to cart');
    setTimeout(() => { btn.textContent = original; btn.classList.remove('added'); }, 1400);
  });
});

// ---------- Product detail tabs ----------
document.querySelectorAll('.tab-nav-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const nav = btn.closest('.tab-nav');
    nav.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const wrapper = nav.closest('.pd-tabs');
    wrapper.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    const target = wrapper.querySelector('#' + btn.dataset.tabTarget);
    if(target) target.classList.add('active');
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
const directorySearch = document.getElementById('directorySearch');
if(directorySearch){
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
      if(show) visibleCount++;
    });
    const noResults = document.querySelector('.no-results');
    if(noResults) noResults.classList.toggle('show', visibleCount === 0);
  });
}
if(filterChips.length && document.querySelector('.stockist-card')){
  filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
      if(directorySearch) directorySearch.dispatchEvent(new Event('input'));
    });
  });
}

// ---------- Accordion ----------
document.querySelectorAll('.accordion-trigger').forEach(trigger => {
  trigger.addEventListener('click', () => {
    const item = trigger.closest('.accordion-item');
    const panel = item.querySelector('.accordion-panel');
    const isOpen = item.classList.contains('open');
    item.parentElement.querySelectorAll('.accordion-item.open').forEach(openItem => {
      if(openItem !== item){
        openItem.classList.remove('open');
        openItem.querySelector('.accordion-panel').style.maxHeight = null;
      }
    });
    if(isOpen){
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
    if(targetForm) targetForm.classList.add('active');
  });
});

// ---------- Cart quantity steppers (cart page) ----------
document.querySelectorAll('.qty-stepper').forEach(stepper => {
  const valEl = stepper.querySelector('.qty-val');
  const minus = stepper.querySelector('.qty-minus');
  const plus = stepper.querySelector('.qty-plus');
  if(!valEl || !minus || !plus) return;
  const updateLineTotal = () => {
    const row = stepper.closest('.cart-row');
    if(!row) return;
    const unit = parseFloat(row.dataset.unitPrice || '0');
    const qty = parseInt(valEl.textContent, 10);
    const lineEl = row.querySelector('.cart-line-total');
    if(lineEl) lineEl.textContent = 'GH₵ ' + (unit * qty).toFixed(2);
    recalcCartSummary();
  };
  minus.addEventListener('click', () => {
    let v = parseInt(valEl.textContent, 10);
    if(v > 1){ valEl.textContent = v - 1; updateLineTotal(); }
  });
  plus.addEventListener('click', () => {
    let v = parseInt(valEl.textContent, 10);
    valEl.textContent = v + 1; updateLineTotal();
  });
});

function recalcCartSummary(){
  const rows = document.querySelectorAll('.cart-row[data-unit-price]');
  if(!rows.length) return;
  let subtotal = 0;
  rows.forEach(row => {
    const unit = parseFloat(row.dataset.unitPrice || '0');
    const qty = parseInt(row.querySelector('.qty-val').textContent, 10);
    subtotal += unit * qty;
  });
  const subtotalEl = document.querySelector('.js-subtotal');
  const totalEl = document.querySelector('.js-total');
  const shippingEl = document.querySelector('.js-shipping');
  const shipping = subtotal > 0 ? 15 : 0;
  if(subtotalEl) subtotalEl.textContent = 'GH₵ ' + subtotal.toFixed(2);
  if(shippingEl) shippingEl.textContent = shipping ? 'GH₵ ' + shipping.toFixed(2) : '—';
  if(totalEl) totalEl.textContent = 'GH₵ ' + (subtotal + shipping).toFixed(2);
}
recalcCartSummary();

document.querySelectorAll('.cart-remove').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const row = btn.closest('.cart-row');
    if(row){
      row.remove();
      recalcCartSummary();
      const remaining = document.querySelectorAll('.cart-row').length;
      const listEl = document.querySelector('.js-cart-list');
      const emptyEl = document.querySelector('.js-cart-empty');
      if(remaining === 0 && listEl && emptyEl){
        listEl.style.display = 'none';
        emptyEl.style.display = 'block';
      }
    }
  });
});

// ---------- Product filter chips ----------
const filterChips = document.querySelectorAll('.filter-chip');
if(filterChips.length){
  filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
      filterChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const cat = chip.dataset.filter;
      let visibleCount = 0;
      document.querySelectorAll('[data-category]').forEach(card => {
        const show = cat === 'all' || card.dataset.category === cat;
        card.style.display = show ? '' : 'none';
        if(show) visibleCount++;
      });
      const noResults = document.querySelector('.no-results');
      if(noResults && !document.getElementById('directorySearch')) noResults.classList.toggle('show', visibleCount === 0);
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
    if(radio) radio.checked = true;
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
      if(timerEl) timerEl.textContent = `You can resend in ${seconds}s`;
      seconds--;
      if(seconds < 0){
        clearInterval(interval);
        btn.disabled = false;
        btn.textContent = original;
        if(timerEl) timerEl.textContent = '';
      }
    }, 1000);
  });
});

// ---------- Track order demo ----------
const trackForm = document.getElementById('trackForm');
if(trackForm){
  trackForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const result = document.getElementById('trackResult');
    if(result){
      result.style.display = 'block';
      result.scrollIntoView({ behavior:'smooth', block:'start' });
    }
  });
}
