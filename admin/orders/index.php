<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Orders</h2>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Tracking Number</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Customer</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Total</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Status</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Date</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">Action</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <!-- Orders will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetails">
                    <!-- Details will be loaded here -->
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label fw-medium">Update Status</label>
                    <select id="orderStatusSelect" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ohemaa-outline" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-ohemaa" id="saveStatusBtn" onclick="updateOrderStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
let orders = [];
let selectedOrderId = null;
let orderModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    orderModalInstance = new bootstrap.Modal(document.getElementById('orderModal'));
    loadOrders();
});

async function loadOrders() {
    try {
        const response = await fetch(apiBase + '/orders/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            orders = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error loading orders', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '';
    
    const filteredOrders = orders.filter(o => 
        o.tracking_number.toLowerCase().includes(filter.toLowerCase()) || 
        o.customer_name.toLowerCase().includes(filter.toLowerCase())
    );
    
    if (filteredOrders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5">No orders found.</td></tr>`;
        return;
    }

    filteredOrders.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString();
        const statusClass = getStatusClass(o.status);
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        tr.innerHTML = `
            <td class="px-4 py-3 fw-medium">${o.tracking_number}</td>
            <td class="px-4 py-3">${o.customer_name}</td>
            <td class="px-4 py-3">GHS ${parseFloat(o.total_amount).toFixed(2)}</td>
            <td class="px-4 py-3"><span class="badge ${statusClass}">${o.status}</span></td>
            <td class="px-4 py-3 text-muted">${date}</td>
            <td class="px-4 py-3 text-end" style="padding-right: 24px;">
                <button class="icon-btn d-inline-flex" onclick="viewOrder(${o.id})" title="View Details">
                    <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'bg-warning-subtle text-warning';
        case 'processing': return 'bg-info-subtle text-info';
        case 'completed': return 'bg-success-subtle text-success';
        case 'cancelled': return 'bg-danger-subtle text-danger';
        default: return 'bg-secondary-subtle text-secondary';
    }
}

function handleSearch(term) {
    renderTable(term);
}

function viewOrder(id) {
    const o = orders.find(x => x.id == id);
    if (!o) return;
    
    selectedOrderId = id;
    const detailsDiv = document.getElementById('orderDetails');
    
    let itemsHtml = '<table class="table table-sm mt-3"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
    o.items.forEach(item => {
        itemsHtml += `<tr>
            <td>${item.product_name}</td>
            <td>${item.quantity}</td>
            <td>GHS ${parseFloat(item.unit_price).toFixed(2)}</td>
            <td>GHS ${parseFloat(item.subtotal).toFixed(2)}</td>
        </tr>`;
    });
    itemsHtml += '</tbody></table>';

    detailsDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Tracking Number</p>
                <p class="fw-bold">${o.tracking_number}</p>
                <p class="mb-1 text-muted small">Customer</p>
                <p>${o.customer_name} (${o.email})</p>
                <p class="mb-1 text-muted small">Phone</p>
                <p>${o.phone || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Shipping Address</p>
                <p>${o.shipping_address}</p>
                <p class="mb-1 text-muted small">Total Amount</p>
                <p class="fw-bold text-primary">GHS ${parseFloat(o.total_amount).toFixed(2)}</p>
            </div>
        </div>
        ${itemsHtml}
    `;
    
    document.getElementById('orderStatusSelect').value = o.status;
    orderModalInstance.show();
}

async function updateOrderStatus() {
    const status = document.getElementById('orderStatusSelect').value;
    const saveBtn = document.getElementById('saveStatusBtn');
    const originalText = saveBtn.innerHTML;
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
    
    try {
        const response = await fetch(apiBase + '/orders/update_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id: selectedOrderId, status })
        });
        
        const data = await response.json();
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            orderModalInstance.hide();
            loadOrders();
        }
    } catch (err) {
        console.error(err);
        showToast('Error updating status', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
