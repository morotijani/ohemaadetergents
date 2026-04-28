<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Bulk Inventory Editor</h2>
    <a href="/ohemaadetergents/admin/products/index" class="btn-ohemaa-outline d-flex align-items-center text-decoration-none">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">inventory_2</span> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="ohemaa-card p-5">
            <h4 class="mb-4" style="font-weight: 500;">Bulk Update Settings</h4>
            <p class="text-muted mb-5">Apply mass changes to prices or stock levels across specific categories or your entire inventory.</p>

            <form id="bulkForm">
                <!-- Select Category -->
                <div class="mb-4">
                    <label class="form-label fw-medium">1. Select Target Category</label>
                    <select id="categoryId" class="form-select form-select-lg">
                        <option value="">All Products</option>
                        <!-- Categories will be loaded here -->
                    </select>
                </div>

                <!-- Select Target Metric -->
                <div class="mb-4">
                    <label class="form-label fw-medium">2. What do you want to update?</label>
                    <div class="d-flex gap-3">
                        <input type="radio" class="btn-check" name="target" id="targetPrice" value="price" checked>
                        <label class="btn btn-outline-primary flex-grow-1 py-3" for="targetPrice">
                            <span class="material-symbols-outlined d-block mb-1">payments</span> Unit Price
                        </label>
                        
                        <input type="radio" class="btn-check" name="target" id="targetStock" value="stock">
                        <label class="btn btn-outline-primary flex-grow-1 py-3" for="targetStock">
                            <span class="material-symbols-outlined d-block mb-1">inventory</span> Stock Level
                        </label>
                    </div>
                </div>

                <!-- Select Action -->
                <div class="mb-4">
                    <label class="form-label fw-medium">3. Choose Action</label>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select id="action" class="form-select" onchange="toggleType()">
                                <option value="increase">Increase By</option>
                                <option value="decrease">Decrease By</option>
                                <option value="set">Set To Exact Value</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="typeContainer">
                            <select id="type" class="form-select">
                                <option value="fixed">Fixed Amount (GHS/Units)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" id="value" class="form-control" placeholder="Value" required>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-flex align-items-center mt-5" role="alert">
                    <span class="material-symbols-outlined me-3">warning</span>
                    <div style="font-size: 14px;">
                        <strong>Careful!</strong> This action will modify multiple products at once and cannot be undone.
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn-ohemaa w-100 py-3 fw-bold" onclick="executeBulkUpdate()" id="submitBtn">
                        Apply Bulk Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
});

async function loadCategories() {
    try {
        const response = await fetch(apiBase + '/categories/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            const select = document.getElementById('categoryId');
            data.data.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error loading categories', err);
    }
}

function toggleType() {
    const action = document.getElementById('action').value;
    const typeContainer = document.getElementById('typeContainer');
    if (action === 'set') {
        typeContainer.style.visibility = 'hidden';
        document.getElementById('type').value = 'fixed';
    } else {
        typeContainer.style.visibility = 'visible';
    }
}

async function executeBulkUpdate() {
    const actionVal = document.getElementById('action').value;
    const targetVal = document.querySelector('input[name="target"]:checked').value;
    const categoryName = document.getElementById('categoryId').options[document.getElementById('categoryId').selectedIndex].text;
    
    if (!confirm(`Are you sure you want to ${actionVal} ${targetVal} for ${categoryName}?`)) return;

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerText;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

    const body = {
        category_id: document.getElementById('categoryId').value,
        target: targetVal,
        action: actionVal,
        type: document.getElementById('type').value,
        value: document.getElementById('value').value
    };

    try {
        const response = await fetch(apiBase + '/products/bulk_update', {
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
            document.getElementById('bulkForm').reset();
            toggleType();
        } else {
            showToast(data.message || 'Error executing bulk update', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
