<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Orders</h2>
    <div class="d-flex gap-3">
        <select id="statusFilter" class="form-select border-light rounded-0 font-sans" onchange="renderTable()" style="width: auto;">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px; width: 5%;">#</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Tracking Number</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Customer</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Total</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Status</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Date</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">
                        Action</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <!-- Orders will be loaded here -->
            </tbody>
        </table>
    </div>
    <div class="p-4 d-flex justify-content-between align-items-center border-top border-light bg-white">
        <div class="text-muted font-sans" style="font-size: 0.85rem;" id="paginationInfo">Showing 0 to 0 of 0 entries</div>
        <div class="d-flex gap-2" id="paginationControls">
            <!-- Pagination buttons go here -->
        </div>
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
                <a id="invoiceBtn" href="#" target="_blank"
                    class="btn-ohemaa-outline text-decoration-none d-flex align-items-center">
                    <span class="material-symbols-outlined me-2" style="font-size: 18px;">receipt_long</span> Invoice
                </a>
                <button type="button" class="btn-ohemaa" onclick="confirmStatusChange()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Status Modal -->
<div class="modal fade" id="confirmStatusModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-5">Confirm Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="mb-0" id="confirmStatusText">Are you sure you want to change this order's status?</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn-ohemaa-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-ohemaa btn-sm" id="confirmStatusBtn"
                    onclick="updateOrderStatus()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    let orders = [];
    let selectedOrderId = null;
    let orderModalInstance = null;
    let confirmModalInstance = null;
    let currentSearchTerm = '';
    
    let currentPage = 1;
    const itemsPerPage = 10;

    document.addEventListener('DOMContentLoaded', () => {
        orderModalInstance = new bootstrap.Modal(document.getElementById('orderModal'));
        confirmModalInstance = new bootstrap.Modal(document.getElementById('confirmStatusModal'));
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');

        loadOrders().then(() => {
            if (orderId) {
                viewOrder(orderId);
            }
        });
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

    function changePage(page) {
        currentPage = page;
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('ordersTableBody');
        tbody.innerHTML = '';
        
        const statusFilter = document.getElementById('statusFilter').value;

        const filteredOrders = orders.filter(o => {
            const matchesSearch = o.tracking_number.toLowerCase().includes(currentSearchTerm.toLowerCase()) ||
                                  o.customer_name.toLowerCase().includes(currentSearchTerm.toLowerCase());
            const matchesStatus = statusFilter === 'all' || o.status === statusFilter;
            return matchesSearch && matchesStatus;
        });

        // Pagination Logic
        const totalItems = filteredOrders.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const currentOrders = filteredOrders.slice(startIndex, endIndex);

        document.getElementById('paginationInfo').innerText = totalItems > 0 
            ? `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries` 
            : 'Showing 0 to 0 of 0 entries';

        renderPaginationControls(totalPages);

        if (currentOrders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5">No orders found.</td></tr>`;
            return;
        }

        currentOrders.forEach((o, index) => {
            const actualIndex = startIndex + index + 1;
            const date = new Date(o.created_at).toLocaleDateString();
            const statusClass = getStatusClass(o.status);
            const tr = document.createElement('tr');
            tr.className = 'ohemaa-list-item';
            tr.style.display = 'table-row';
            tr.innerHTML = `
            <td class="px-4 py-3 fw-bold text-muted">${actualIndex}</td>
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

    function renderPaginationControls(totalPages) {
        const controls = document.getElementById('paginationControls');
        controls.innerHTML = '';
        
        if (totalPages <= 1) return;

        // Prev Button
        const prevBtn = document.createElement('button');
        prevBtn.className = `btn btn-sm btn-outline-dark rounded-0 font-sans ${currentPage === 1 ? 'disabled' : ''}`;
        prevBtn.innerText = 'Prev';
        if (currentPage > 1) prevBtn.onclick = () => changePage(currentPage - 1);
        controls.appendChild(prevBtn);

        // Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            // Simple truncation logic for many pages
            if (totalPages > 7 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                if (i === 2 || i === totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.className = 'px-2 text-muted align-self-end';
                    dots.innerText = '...';
                    controls.appendChild(dots);
                }
                continue;
            }

            const pageBtn = document.createElement('button');
            pageBtn.className = `btn btn-sm ${i === currentPage ? 'btn-dark' : 'btn-outline-dark'} rounded-0 font-sans`;
            pageBtn.innerText = i;
            if (i !== currentPage) pageBtn.onclick = () => changePage(i);
            controls.appendChild(pageBtn);
        }

        // Next Button
        const nextBtn = document.createElement('button');
        nextBtn.className = `btn btn-sm btn-outline-dark rounded-0 font-sans ${currentPage === totalPages ? 'disabled' : ''}`;
        nextBtn.innerText = 'Next';
        if (currentPage < totalPages) nextBtn.onclick = () => changePage(currentPage + 1);
        controls.appendChild(nextBtn);
    }

    function getStatusClass(status) {
        switch (status) {
            case 'pending': return 'bg-warning-subtle text-warning';
            case 'processing': return 'bg-info-subtle text-info';
            case 'completed': return 'bg-success-subtle text-success';
            case 'cancelled': return 'bg-danger-subtle text-danger';
            default: return 'bg-secondary-subtle text-secondary';
        }
    }

    function handleSearch(term) {
        currentSearchTerm = term;
        renderTable();
    }

    function viewOrder(id) {
        const o = orders.find(x => x.id == id);
        if (!o) return;

        selectedOrderId = id;
        const token = localStorage.getItem('admin_token');
        document.getElementById('invoiceBtn').href = `${BASE_URL}/admin/orders/invoice?id=${id}&token=${token}`;

        const detailsDiv = document.getElementById('orderDetails');

        let itemsHtml = `
    <div class="ohemaa-card p-0 overflow-hidden mt-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr style="background-color: var(--hover-bg);">
                        <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Product</th>
                        <th class="border-0 px-4 py-3 text-muted fw-medium text-center" style="font-size: 14px;">Qty</th>
                        <th class="border-0 px-4 py-3 text-muted fw-medium text-end" style="font-size: 14px;">Price</th>
                        <th class="border-0 px-4 py-3 text-muted fw-medium text-end" style="font-size: 14px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
        o.items.forEach(item => {
            itemsHtml += `
            <tr class="ohemaa-list-item" style="display: table-row;">
                <td class="px-4 py-3 fw-medium">${item.product_name}</td>
                <td class="px-4 py-3 text-center">${item.quantity}</td>
                <td class="px-4 py-3 text-end">GHS ${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="px-4 py-3 text-end fw-medium">GHS ${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `;
        });
        itemsHtml += `
                </tbody>
            </table>
        </div>
    </div>
    `;

        const orderDate = new Date(o.created_at).toLocaleString();

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
                <p class="mb-1 text-muted small">Order Date</p>
                <p>${orderDate}</p>
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

    function confirmStatusChange() {
        const status = document.getElementById('orderStatusSelect').value;
        const o = orders.find(x => x.id == selectedOrderId);
        if (status === o.status) {
            showToast('Status is already ' + status, 'info');
            return;
        }

        let text = `Are you sure you want to change order <strong>${o.tracking_number}</strong> to <strong>${status}</strong>?`;
        if (status === 'completed' || status === 'cancelled') {
            text += `<br><small class="text-muted mt-2 d-block">An email notification will be sent to the customer.</small>`;
        }

        document.getElementById('confirmStatusText').innerHTML = text;
        confirmModalInstance.show();
    }

    async function updateOrderStatus() {
        const status = document.getElementById('orderStatusSelect').value;
        const saveBtn = document.getElementById('confirmStatusBtn');
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
                confirmModalInstance.hide();
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