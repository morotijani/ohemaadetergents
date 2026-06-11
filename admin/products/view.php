<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div id="overviewContent" class="d-none">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-4">
        <div class="d-flex align-items-center">
            <a href="/ohemaadetergents/admin/products/index" class="icon-btn me-3">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="mb-0 fw-light" id="viewProductName">Product Details</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/ohemaadetergents/admin/products/index" class="text-decoration-none">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Overview</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-ohemaa-outline d-flex align-items-center" onclick="editProduct()">
                <span class="material-symbols-outlined me-2">edit</span> Edit Product
            </button>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="ohemaa-card p-4 text-center">
                <div class="rounded-circle bg-primary-subtle p-3 d-inline-flex mb-3">
                    <span class="material-symbols-outlined text-primary">shopping_bag</span>
                </div>
                <h3 class="mb-1" id="metricTotalSold">0</h3>
                <p class="text-muted small mb-0">Total Units Sold</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ohemaa-card p-4 text-center">
                <div class="rounded-circle bg-success-subtle p-3 d-inline-flex mb-3">
                    <span class="material-symbols-outlined text-success">payments</span>
                </div>
                <h3 class="mb-1" id="metricRevenue">GHS 0.00</h3>
                <p class="text-muted small mb-0">Total Revenue</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ohemaa-card p-4 text-center">
                <div class="rounded-circle bg-warning-subtle p-3 d-inline-flex mb-3">
                    <span class="material-symbols-outlined text-warning">inventory_2</span>
                </div>
                <h3 class="mb-1" id="viewStock">0</h3>
                <p class="text-muted small mb-0">Current Stock</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ohemaa-card p-4 text-center">
                <div class="rounded-circle bg-info-subtle p-3 d-inline-flex mb-3">
                    <span class="material-symbols-outlined text-info">star</span>
                </div>
                <h3 class="mb-1" id="metricReviews">0</h3>
                <p class="text-muted small mb-0">Customer Reviews</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Product Info & Media -->
        <div class="col-md-4">
            <div class="ohemaa-card mb-4">
                <div class="p-4 border-bottom">
                    <h6 class="mb-0 fw-bold">Product Information</h6>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Price</label>
                        <span class="fw-bold fs-5 text-primary" id="viewPrice">GHS 0.00</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Category</label>
                        <span class="badge bg-light text-dark border" id="viewCategory">Uncategorized</span>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block">Description</label>
                        <p class="small text-muted mb-0" id="viewDescription"></p>
                    </div>
                </div>
            </div>

            <div class="ohemaa-card">
                <div class="p-4 border-bottom">
                    <h6 class="mb-0 fw-bold">Product Media</h6>
                </div>
                <div class="p-4">
                    <div class="row g-2" id="viewImageGrid">
                        <!-- Images will load here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Orders & Reviews Tabs -->
        <div class="col-md-8">
            <div class="ohemaa-card">
                <div class="p-0">
                    <ul class="nav nav-tabs border-bottom px-4 pt-2" id="overviewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-3 border-0" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab">
                                Order History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 border-0" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button" role="tab">
                                Reviews
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-4" id="overviewTabContent">
                        <!-- Orders Pane -->
                        <div class="tab-pane fade show active" id="orders-pane" role="tabpanel" tabindex="0">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th class="border-0 text-muted small">Order #</th>
                                            <th class="border-0 text-muted small">Date</th>
                                            <th class="border-0 text-muted small">Qty</th>
                                            <th class="border-0 text-muted small">Total</th>
                                            <th class="border-0 text-muted small">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderHistoryBody">
                                        <!-- Orders will load here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Reviews Pane -->
                        <div class="tab-pane fade" id="reviews-pane" role="tabpanel" tabindex="0">
                            <div id="reviewHistoryBody">
                                <!-- Reviews will load here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loader" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
    <p class="text-muted mt-2">Loading product overview...</p>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (!productId) {
        window.location.href = '/ohemaadetergents/admin/products/index';
    }

    function editProduct() {
        window.location.href = '/ohemaadetergents/admin/products/index?edit_id=' + productId + '&source=view';
    }

    document.addEventListener('DOMContentLoaded', loadProductOverview);

    async function loadProductOverview() {
        try {
            const res = await fetch(apiBase + '/products/details?id=' + productId, {
                headers: getAuthHeaders()
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                renderOverview(data.data);
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Network error', 'error');
        }
    }

    function renderOverview(data) {
        const p = data.product;
        const m = data.metrics;

        // Populate Metrics
        document.getElementById('viewProductName').innerText = p.name;
        document.getElementById('metricTotalSold').innerText = m.total_sold;
        document.getElementById('metricRevenue').innerText = 'GHS ' + m.total_revenue.toFixed(2);
        document.getElementById('viewStock').innerText = p.stock;
        document.getElementById('metricReviews').innerText = data.reviews.length;

        // Populate Info
        document.getElementById('viewPrice').innerText = 'GHS ' + parseFloat(p.price).toFixed(2);
        document.getElementById('viewCategory').innerText = p.category_name || 'Uncategorized';
        document.getElementById('viewDescription').innerText = p.description || 'No description provided.';

        // Populate Media
        const imageGrid = document.getElementById('viewImageGrid');
        imageGrid.innerHTML = '';
        const allImages = [];
        if (p.image_url) allImages.push(p.image_url);
        if (p.extra_images) allImages.push(...p.extra_images);

        allImages.forEach(img => {
            const col = document.createElement('div');
            col.className = 'col-6';
            col.innerHTML = `
                <div class="ratio ratio-1x1 rounded-3 overflow-hidden border shadow-sm">
                    <img src="/ohemaadetergents/${img}" class="object-fit-cover">
                </div>
            `;
            imageGrid.appendChild(col);
        });

        // Populate Orders
        const orderBody = document.getElementById('orderHistoryBody');
        orderBody.innerHTML = '';
        if (data.orders.length === 0) {
            orderBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No orders yet.</td></tr>';
        } else {
            data.orders.forEach(o => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold text-primary">#${o.tracking_number}</td>
                    <td class="small text-muted">${new Date(o.created_at).toLocaleDateString()}</td>
                    <td>${o.quantity}</td>
                    <td class="fw-medium">GHS ${parseFloat(o.total_amount).toFixed(2)}</td>
                    <td><span class="badge rounded-pill bg-${getStatusColor(o.status)} small">${o.status}</span></td>
                `;
                orderBody.appendChild(tr);
            });
        }

        // Populate Reviews
        const reviewBody = document.getElementById('reviewHistoryBody');
        reviewBody.innerHTML = '';
        if (data.reviews.length === 0) {
            reviewBody.innerHTML = '<div class="text-center py-4 text-muted">No reviews yet.</div>';
        } else {
            data.reviews.forEach(r => {
                const div = document.createElement('div');
                div.className = 'p-3 rounded-4 bg-light mb-3 border';
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-warning small">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
                        <span class="small text-muted">${new Date(r.created_at).toLocaleDateString()}</span>
                    </div>
                    <p class="mb-2 small">"${r.comment}"</p>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:24px; height:24px; font-size:10px;">${r.customer_name.charAt(0)}</div>
                        <span class="small fw-bold">${r.customer_name}</span>
                        <span class="badge bg-${r.status === 'approved' ? 'success' : 'warning'} p-1 px-2 rounded-pill" style="font-size:8px;">${r.status}</span>
                    </div>
                `;
                reviewBody.appendChild(div);
            });
        }

        document.getElementById('loader').classList.add('d-none');
        document.getElementById('overviewContent').classList.remove('d-none');
    }

    function getStatusColor(status) {
        switch(status.toLowerCase()) {
            case 'completed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
</script>

<style>
    .nav-tabs .nav-link { color: #6c757d; font-weight: 500; }
    .nav-tabs .nav-link.active { color: var(--primary); border-bottom: 2px solid var(--primary) !important; background: transparent; }
</style>

<?php include '../includes/footer.php'; ?>
