<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<h3 class="mb-4 text-secondary">Dashboard</h3>

<div class="row">
    <div class="col-md-4">
        <div class="google-card">
            <h5 class="text-muted" style="font-weight: 500;">Total Products</h5>
            <h2 id="productCount" class="text-primary mt-3" style="font-size: 3rem;">0</h2>
        </div>
    </div>
</div>

<script>
async function loadDashboard() {
    try {
        const response = await fetch(apiBase + '/products/read.php', {
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
document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

<?php include 'includes/footer.php'; ?>
