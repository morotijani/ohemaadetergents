<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 style="font-weight: 400; font-size: 28px;">Dashboard Overview</h2>
    <div class="text-muted small" id="lastUpdated">Last updated: Just now</div>
</div>

<!-- Financial Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">TOTAL REVENUE</div>
                    <h3 class="mb-0 fw-bold" id="totalRevenue">GHS 0.00</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(129, 201, 149, 0.15); color: #81c995;">
                    <span class="material-symbols-outlined">payments</span>
                </div>
            </div>
            <div class="mt-3 text-success small d-flex align-items-center">
                <span class="material-symbols-outlined fs-6 me-1">trending_up</span>
                <span>All time earnings</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">TOTAL EXPENDITURE</div>
                    <h3 class="mb-0 fw-bold" id="totalExpenditure">GHS 0.00</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(242, 139, 130, 0.15); color: #f28b82;">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3 text-danger small d-flex align-items-center">
                <span class="material-symbols-outlined fs-6 me-1">trending_down</span>
                <span>Total operational costs</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100 border-primary" style="border-width: 2px !important;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">NET PROFIT</div>
                    <h3 class="mb-0 fw-bold text-primary" id="netProfit">GHS 0.00</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(26, 115, 232, 0.15); color: #1a73e8;">
                    <span class="material-symbols-outlined">savings</span>
                </div>
            </div>
            <div class="mt-3 text-primary small d-flex align-items-center">
                <span class="material-symbols-outlined fs-6 me-1">equalizer</span>
                <span>Actual business profit</span>
            </div>
        </div>
    </div>
</div>

<!-- Operational Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">ORDERS</div>
                    <h3 class="mb-0 fw-bold" id="totalOrders">0</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(26, 115, 232, 0.15); color: #1a73e8;">
                    <span class="material-symbols-outlined">shopping_cart</span>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <span id="pendingOrders">0</span> pending orders
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">CUSTOMERS</div>
                    <h3 class="mb-0 fw-bold" id="totalCustomers">0</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(242, 139, 130, 0.15); color: #f28b82;">
                    <span class="material-symbols-outlined">group</span>
                </div>
            </div>
            <div class="mt-3 text-muted small">Total registered users</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">INVENTORY</div>
                    <h3 class="mb-0 fw-bold" id="totalProducts">0</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(251, 188, 4, 0.15); color: #fbbc04;">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
            </div>
            <div class="mt-3 text-warning small" id="lowStockAlert">0 items low in stock</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart Section -->
    <div class="col-md-12">
        <div class="ohemaa-card p-4">
            <h4 class="ohemaa-card-header mb-4" style="font-size: 18px;">Sales Performance (<?php echo date('Y'); ?>)</h4>
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Performers -->
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <h4 class="ohemaa-card-header mb-4" style="font-size: 18px;">Top Performers</h4>
            <div id="topPerformersList">
                <!-- Top products will be loaded here -->
            </div>
            <div class="mt-auto pt-3">
                <a href="/ohemaadetergents/admin/products/index" class="btn-ohemaa-outline w-100 text-center text-decoration-none">Review Catalog</a>
            </div>
        </div>
    </div>
    
    <!-- Low Stock -->
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <h4 class="ohemaa-card-header mb-4" style="font-size: 18px;">Inventory Alerts</h4>
            <div id="lowStockList">
                <!-- Low stock items will be loaded here -->
            </div>
            <div class="mt-auto pt-3">
                <a href="/ohemaadetergents/admin/products/index" class="btn-ohemaa-outline w-100 text-center text-decoration-none">View Inventory</a>
            </div>
        </div>
    </div>

    <!-- Recent Expenditure -->
    <div class="col-md-4">
        <div class="ohemaa-card p-4 h-100">
            <h4 class="ohemaa-card-header mb-4" style="font-size: 18px;">Recent Expenditure</h4>
            <div id="recentExpenditureList">
                <!-- Expenditures will be loaded here -->
            </div>
            <div class="mt-auto pt-3">
                <a href="/ohemaadetergents/admin/expenditure" class="btn-ohemaa-outline w-100 text-center text-decoration-none">Manage Finances</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-12">
        <div class="ohemaa-card p-0 overflow-hidden">
            <div class="p-4 d-flex justify-content-between align-items-center border-bottom" style="border-color: var(--card-border) !important;">
                <h4 class="mb-0" style="font-size: 18px; font-weight: 400;">Recent Orders</h4>
                <a href="/ohemaadetergents/admin/orders/index" class="btn-ohemaa-outline text-decoration-none" style="padding: 4px 12px; font-size: 13px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted" style="font-weight: 500; font-size: 13px;">ORDER ID</th>
                            <th class="text-muted" style="font-weight: 500; font-size: 13px;">CUSTOMER</th>
                            <th class="text-muted" style="font-weight: 500; font-size: 13px;">DATE</th>
                            <th class="text-muted" style="font-weight: 500; font-size: 13px;">TOTAL</th>
                            <th class="text-muted" style="font-weight: 500; font-size: 13px;">STATUS</th>
                            <th class="pe-4 text-end text-muted" style="font-weight: 500; font-size: 13px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersBody">
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Loading recent orders...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let salesChart = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await fetch(apiBase + '/dashboard/stats', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (response.ok && data.status === 'success') {
            const stats = data.data;
            
            // Update Stats
            document.getElementById('totalRevenue').innerText = 'GHS ' + stats.revenue.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('totalExpenditure').innerText = 'GHS ' + stats.total_expenditure.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('netProfit').innerText = 'GHS ' + stats.net_profit.toLocaleString(undefined, {minimumFractionDigits: 2});
            
            document.getElementById('totalOrders').innerText = stats.total_orders;
            document.getElementById('pendingOrders').innerText = stats.pending_orders;
            document.getElementById('totalCustomers').innerText = stats.total_customers;
            document.getElementById('totalProducts').innerText = stats.total_products;
            
            // Low Stock
            const lowStockList = document.getElementById('lowStockList');
            if (stats.low_stock.length > 0) {
                document.getElementById('lowStockAlert').innerText = stats.low_stock.length + ' items low in stock';
                lowStockList.innerHTML = stats.low_stock.map(item => `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-medium" style="font-size: 14px;">${item.name}</div>
                            <div class="text-muted small">${item.stock} units left</div>
                        </div>
                        <span class="badge rounded-pill bg-danger" style="font-size: 10px;">LOW</span>
                    </div>
                `).join('');
            } else {
                document.getElementById('lowStockAlert').innerText = 'All stock levels healthy';
                lowStockList.innerHTML = '<div class="text-center py-3 text-muted small"><span class="material-symbols-outlined fs-2 text-success">check_circle</span><p class="mt-1">Stock levels healthy</p></div>';
            }

            // Recent Expenditure
            const expList = document.getElementById('recentExpenditureList');
            if (stats.recent_expenditures.length > 0) {
                expList.innerHTML = stats.recent_expenditures.map(exp => `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div style="max-width: 70%;">
                            <div class="fw-medium text-truncate" style="font-size: 14px;" title="${exp.description}">${exp.category}</div>
                            <div class="text-muted small">${new Date(exp.date).toLocaleDateString()}</div>
                        </div>
                        <div class="text-danger fw-bold" style="font-size: 14px;">- GHS ${parseFloat(exp.amount).toFixed(2)}</div>
                    </div>
                `).join('');
            } else {
                expList.innerHTML = '<div class="text-center py-3 text-muted small"><p>No recent expenditures.</p></div>';
            }

            // Top Performers
            const topList = document.getElementById('topPerformersList');
            if (stats.top_performers.length > 0) {
                topList.innerHTML = stats.top_performers.map(p => {
                    const imgUrl = p.image_url ? `/ohemaadetergents/${p.image_url}` : 'https://via.placeholder.com/40';
                    return `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center" style="max-width: 70%;">
                                <img src="${imgUrl}" alt="${p.name}" class="rounded object-fit-cover me-3" style="width: 32px; height: 32px;">
                                <div class="text-truncate">
                                    <div class="fw-medium" style="font-size: 14px;">${p.name}</div>
                                    <div class="text-muted small">${p.total_sold} units sold</div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gold" style="font-size: 20px;">workspace_premium</span>
                        </div>
                    `;
                }).join('');
            } else {
                topList.innerHTML = '<div class="text-center py-5 text-muted small"><p>No sales data yet.</p></div>';
            }

            // Recent Orders
            const ordersBody = document.getElementById('recentOrdersBody');
            if (stats.recent_orders.length > 0) {
                ordersBody.innerHTML = stats.recent_orders.map(o => {
                    const date = new Date(o.created_at).toLocaleDateString('en-GB', {day: 'numeric', month: 'short'});
                    let statusClass = 'bg-secondary';
                    if (o.status === 'completed') statusClass = 'bg-success';
                    if (o.status === 'pending') statusClass = 'bg-warning text-dark';
                    if (o.status === 'cancelled') statusClass = 'bg-danger';

                    return `
                        <tr>
                            <td class="ps-4 fw-medium">#${o.tracking_number}</td>
                            <td>${o.customer_name || 'Guest'}</td>
                            <td class="text-muted">${date}</td>
                            <td>GHS ${parseFloat(o.total_amount).toFixed(2)}</td>
                            <td><span class="badge ${statusClass}" style="text-transform: capitalize;">${o.status}</span></td>
                            <td class="pe-4 text-end">
                                <a href="/ohemaadetergents/admin/orders/index?id=${o.id}" class="icon-btn d-inline-flex">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">visibility</span>
                                </a>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                ordersBody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No recent orders.</td></tr>';
            }

            // Chart
            renderChart(stats.monthly_sales);
            
            document.getElementById('lastUpdated').innerText = 'Last updated: ' + new Date().toLocaleTimeString();

        }
    } catch (err) {
        console.error('Error fetching dashboard data', err);
    }
}

function renderChart(data) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    if (salesChart) salesChart.destroy();
    
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#9aa0a6' : '#5f6368';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue (GHS)',
                data: data,
                borderColor: '#1a73e8',
                backgroundColor: 'rgba(26, 115, 232, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#1a73e8'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { family: 'Roboto' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { family: 'Roboto' } }
                }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
