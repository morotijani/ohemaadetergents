<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Help & Documentation</h2>
    <div class="text-muted small">System guides and business formulas</div>
</div>

<div class="row">
    <!-- Quick Navigation -->
    <div class="col-md-3">
        <div class="ohemaa-card p-0 overflow-hidden sticky-top" style="top: 88px;">
            <div class="p-4 border-bottom" style="background-color: var(--hover-bg); border-color: var(--card-border) !important;">
                <h6 class="mb-0 fw-bold">Jump to Section</h6>
            </div>
            <div class="list-group list-group-flush" id="helpNav">
                <a href="#financials" class="list-group-item list-group-item-action border-0 py-3 px-4">Financial Formulas</a>
                <a href="#orders" class="list-group-item list-group-item-action border-0 py-3 px-4">Order Management</a>
                <a href="#inventory" class="list-group-item list-group-item-action border-0 py-3 px-4">Inventory & Bulk Edits</a>
                <a href="#coupons" class="list-group-item list-group-item-action border-0 py-3 px-4">Coupon Logic</a>
                <a href="#reviews" class="list-group-item list-group-item-action border-0 py-3 px-4">Review Moderation</a>
                <a href="#security" class="list-group-item list-group-item-action border-0 py-3 px-4">Security & Logs</a>
            </div>
        </div>
    </div>

    <!-- Documentation Content -->
    <div class="col-md-9">
        <!-- Financials -->
        <div id="financials" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-primary" style="font-size: 32px;">calculate</span>
                Financial Intelligence Formulas
            </h3>
            <p class="text-muted mb-5">The dashboard uses real-time data to calculate your business health. Here is how the numbers are generated:</p>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 rounded-lg bg-light border">
                        <h6 class="fw-bold mb-2">Total Revenue</h6>
                        <p class="small text-muted mb-3">Sum of all successfully placed orders that have not been cancelled.</p>
                        <code class="d-block p-2 bg-dark text-white rounded">Σ (Order Total Amount) WHERE status != 'cancelled'</code>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 rounded-lg bg-light border">
                        <h6 class="fw-bold mb-2">Total Expenditure</h6>
                        <p class="small text-muted mb-3">Total amount spent on business operations (salaries, supplies, logistics).</p>
                        <code class="d-block p-2 bg-dark text-white rounded">Σ (Expenditure Amount)</code>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-4 rounded-lg bg-primary-subtle border border-primary-subtle">
                        <h6 class="fw-bold mb-2 text-primary">Net Profit</h6>
                        <p class="small text-muted mb-3">Your actual earnings after all costs are deducted. This is the most important metric.</p>
                        <code class="d-block p-2 bg-primary text-white rounded">Total Revenue - Total Expenditure</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div id="orders" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-success" style="font-size: 32px;">local_shipping</span>
                Order & Invoice Management
            </h3>
            <div class="mb-4">
                <h6 class="fw-bold">Generating Invoices</h6>
                <p class="text-muted">Navigate to **Orders**, click the view icon on any order, and use the **Generate Invoice** button. Invoices are print-optimized for physical delivery or PDF saving.</p>
            </div>
            <div>
                <h6 class="fw-bold">Order Statuses</h6>
                <ul class="text-muted small">
                    <li><strong>Pending:</strong> Order placed but not yet processed or paid.</li>
                    <li><strong>Completed:</strong> Goods delivered and payment confirmed.</li>
                    <li><strong>Cancelled:</strong> Order voided; revenue is automatically deducted from totals.</li>
                </ul>
            </div>
        </div>

        <!-- Inventory -->
        <div id="inventory" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-warning" style="font-size: 32px;">inventory_2</span>
                Inventory & Bulk Editing
            </h3>
            <p class="text-muted">Managing large inventories is simplified through the **Bulk Edit** tool.</p>
            <div class="alert alert-info border-0 shadow-none py-3">
                <h6 class="fw-bold small mb-1">Formula: Percentage Update</h6>
                <p class="small mb-0">New Price = Current Price + (Current Price * (Percentage / 100))</p>
            </div>
            <p class="small text-muted mt-3">Use "Set Exact Value" to reset stock levels after a new shipment arrives.</p>
        </div>

        <!-- Coupons -->
        <div id="coupons" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-info" style="font-size: 32px;">sell</span>
                Coupon Logic & Limits
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold">Percentage vs Fixed</h6>
                    <p class="small text-muted">A 10% coupon reduces the cart by a ratio, while a GHS 10 coupon reduces it by a flat amount.</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Minimum Requirement</h6>
                    <p class="small text-muted">The coupon will only activate if the cart total exceeds the set minimum amount.</p>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div id="reviews" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-danger" style="font-size: 32px;">reviews</span>
                Review Moderation Workflow
            </h3>
            <p class="text-muted">To maintain brand reputation, all reviews are **Pending** by default.</p>
            <ol class="text-muted small">
                <li>A customer submits a review on the product page.</li>
                <li>Admin receives a "Pending" alert in the **Reviews** module.</li>
                <li>Admin approves the review (it goes live) or rejects it (it is hidden).</li>
            </ol>
        </div>

        <!-- Security -->
        <div id="security" class="ohemaa-card p-5 mb-4">
            <h3 class="mb-4 d-flex align-items-center">
                <span class="material-symbols-outlined me-3 text-dark" style="font-size: 32px;">security</span>
                Security & Audit Logs
            </h3>
            <p class="text-muted">Every administrative change is tracked in the **Activity Logs**. This includes the administrator's name, their IP address, and the specific data that was changed.</p>
            <p class="small text-muted"><em>Tip: Use the logs to troubleshoot if a price was changed accidentally or if an order was deleted.</em></p>
        </div>
    </div>
</div>

<style>
#helpNav .list-group-item {
    background-color: transparent;
    color: var(--secondary-text);
    font-size: 14px;
    font-weight: 500;
}
#helpNav .list-group-item:hover {
    background-color: var(--hover-bg);
    color: var(--active-text);
}
#helpNav .list-group-item.active {
    background-color: var(--active-bg);
    color: var(--active-text);
    border-left: 4px solid var(--active-text) !important;
}
html {
    scroll-behavior: smooth;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Add scrollspy or active state logic for help nav
    const links = document.querySelectorAll('#helpNav a');
    links.forEach(link => {
        link.addEventListener('click', () => {
            links.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
