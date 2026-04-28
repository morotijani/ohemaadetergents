<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Coupons & Discounts</h2>
    <button class="btn-ohemaa d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="openCreateModal()">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Create Coupon
    </button>
</div>

<div class="ohemaa-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Code</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Discount</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Usage</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Expiry</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Status</th>
                    <th class="border-top-0 text-muted text-end" style="font-weight: 500;">Actions</th>
                </tr>
            </thead>
            <tbody id="couponsTableBody">
                <!-- Coupons will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content ohemaa-card border-0 p-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="modalTitle" style="font-weight: 500;">Create Coupon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="couponForm">
            <input type="hidden" id="couponId">
            <div id="modalAlert" class="alert alert-danger d-none"></div>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="couponCode" placeholder="WELCOME10" required style="text-transform: uppercase;">
                <label>Coupon Code</label>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-select" id="couponType">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (GHS)</option>
                        </select>
                        <label>Discount Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control" id="couponValue" required>
                        <label>Value</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control" id="minOrderAmount" value="0">
                        <label>Min. Order Amount</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="maxUses" placeholder="Unlimited">
                        <label>Max Uses</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="expiryDate">
                        <label>Expiry Date</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-select" id="couponStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label>Status</label>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn-ohemaa-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-ohemaa" id="saveBtn" onclick="saveCoupon()">Save Coupon</button>
      </div>
    </div>
  </div>
</div>

<script>
let coupons = [];
let couponModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    couponModalInstance = new bootstrap.Modal(document.getElementById('couponModal'));
    loadCoupons();
});

async function loadCoupons() {
    try {
        const response = await fetch(apiBase + '/coupons/read', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (data.status === 'success') {
            coupons = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error fetching coupons', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('couponsTableBody');
    tbody.innerHTML = '';
    
    const filteredCoupons = coupons.filter(c => c.code.toLowerCase().includes(filter.toLowerCase()));
    
    if (filteredCoupons.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5"><span class="material-symbols-outlined fs-1 mb-2">sell</span><br>No coupons found.</td></tr>`;
        return;
    }

    filteredCoupons.forEach(c => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        
        const discountText = c.type === 'percentage' ? `${parseFloat(c.value)}%` : `GHS ${parseFloat(c.value).toFixed(2)}`;
        const usageText = c.max_uses ? `${c.used_count} / ${c.max_uses}` : `${c.used_count} / ∞`;
        const expiryText = c.expiry_date ? new Date(c.expiry_date).toLocaleDateString() : 'Never';
        const statusBadge = c.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';

        tr.innerHTML = `
            <td class="fw-bold text-primary" style="padding: 16px 24px; font-size: 15px; letter-spacing: 1px;">${c.code}</td>
            <td class="fw-medium">${discountText}</td>
            <td class="text-muted small">${usageText}</td>
            <td class="text-muted small">${expiryText}</td>
            <td><span class="badge ${statusBadge}" style="text-transform: capitalize;">${c.status}</span></td>
            <td class="text-end" style="padding-right: 24px;">
                <button class="icon-btn d-inline-flex" onclick="openEditModal(${c.id})" title="Edit">
                    <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                </button>
                <button class="icon-btn d-inline-flex text-danger" onclick="deleteCoupon(${c.id})" title="Delete">
                    <span class="material-symbols-outlined" style="font-size: 20px;">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function handleSearch(term) {
    renderTable(term);
}

function openCreateModal() {
    document.getElementById('couponForm').reset();
    document.getElementById('couponId').value = '';
    document.getElementById('modalTitle').innerText = 'Create Coupon';
    document.getElementById('modalAlert').classList.add('d-none');
}

function openEditModal(id) {
    const c = coupons.find(x => x.id == id);
    if (!c) return;
    
    document.getElementById('couponForm').reset();
    document.getElementById('modalAlert').classList.add('d-none');
    
    document.getElementById('couponId').value = c.id;
    document.getElementById('couponCode').value = c.code;
    document.getElementById('couponType').value = c.type;
    document.getElementById('couponValue').value = c.value;
    document.getElementById('minOrderAmount').value = c.min_order_amount;
    document.getElementById('maxUses').value = c.max_uses || '';
    document.getElementById('expiryDate').value = c.expiry_date || '';
    document.getElementById('couponStatus').value = c.status;
    
    document.getElementById('modalTitle').innerText = 'Edit Coupon';
    couponModalInstance.show();
}

async function saveCoupon() {
    const id = document.getElementById('couponId').value;
    const isEdit = !!id;
    const endpoint = isEdit ? '/coupons/update' : '/coupons/create';
    
    const body = {
        id: id,
        code: document.getElementById('couponCode').value,
        type: document.getElementById('couponType').value,
        value: document.getElementById('couponValue').value,
        min_order_amount: document.getElementById('minOrderAmount').value,
        max_uses: document.getElementById('maxUses').value,
        expiry_date: document.getElementById('expiryDate').value,
        status: document.getElementById('couponStatus').value
    };
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    try {
        const response = await fetch(apiBase + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            showToast(data.message);
            couponModalInstance.hide();
            loadCoupons();
        } else {
            showToast(data.message || 'Error saving coupon', 'error');
            const alert = document.getElementById('modalAlert');
            alert.innerText = data.message || 'Error saving coupon';
            alert.classList.remove('d-none');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

async function deleteCoupon(id) {
    if (!confirm('Are you sure you want to delete this coupon? This cannot be undone.')) return;
    
    try {
        const response = await fetch(apiBase + '/coupons/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            loadCoupons();
        } else {
            alert(data.message || 'Error deleting coupon');
        }
    } catch (err) {
        console.error(err);
        alert('Network error');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
