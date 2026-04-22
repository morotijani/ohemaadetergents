<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<h2 class="mb-4 mt-2" style="font-weight: 400; font-size: 28px;">Welcome, <span id="welcomeName">Admin</span></h2>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="google-card h-100 p-0 overflow-hidden d-flex flex-column">
            <div class="p-4 border-bottom" style="border-color: var(--card-border) !important;">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background-color: var(--active-bg); color: var(--active-text);">
                        <span class="material-symbols-outlined" style="font-size: 28px;">inventory_2</span>
                    </div>
                    <h3 class="mb-0 fs-4">Product Management</h3>
                </div>
                <p class="text-muted mb-0" style="font-size: 14px;">View, add, edit, and organize your store's inventory and featured items to provide relevant results.</p>
            </div>
            
            <div class="flex-grow-1">
                <div class="google-list-item" onclick="window.location.href='/ohemaadetergents/admin/products/index'">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined me-3 text-muted">category</span>
                        <div>
                            <div class="fw-medium text-dark">Total Products</div>
                            <div class="text-muted" style="font-size: 13px;">Currently active in the storefront</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="fs-4 fw-bold me-3" id="productCount" style="color: var(--active-text);">0</div>
                        <span class="material-symbols-outlined text-muted" style="font-size: 20px;">chevron_right</span>
                    </div>
                </div>
            </div>
            
            <div class="p-3 border-top bg-light" style="border-color: var(--card-border) !important; background-color: var(--hover-bg) !important;">
                <a href="/ohemaadetergents/admin/products/index" class="text-decoration-none fw-medium" style="color: var(--active-text); font-size: 14px;">Manage your inventory</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
    if(user.name) {
        const firstName = user.name.split(' ')[0];
        document.getElementById('welcomeName').innerText = firstName;
    }
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await fetch(apiBase + '/products/read', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (response.ok && data.status === 'success') {
            document.getElementById('productCount').innerText = data.data.length;
        }
    } catch (err) {
        console.error('Error fetching dashboard data', err);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
