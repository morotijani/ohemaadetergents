<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Wholesale Products</h2>
        <button class="btn btn-primary d-flex align-items-center" onclick="openAddModal()">
            <span class="material-symbols-outlined me-1">add_circle</span> Add Product
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="wholesaleTable">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Bottle Type</th>
                            <th>Carton Size</th>
                            <th>Tier 1 (1-9)</th>
                            <th>Tier 2 (10-49)</th>
                            <th>Tier 3 (50+)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="wholesaleTableBody">
                        <tr><td colspan="8" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Product Name</label>
                            <input type="text" class="form-control" id="productName" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-muted small">Bottle Type</label>
                            <input type="text" class="form-control" id="bottleType" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-muted small">Carton Size</label>
                            <input type="number" class="form-control" id="cartonSize" min="1" required>
                        </div>
                    </div>
                    
                    <h6 class="mt-3 mb-2 text-primary">Pricing Tiers (GH₵)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Tier 1 Price (1-9 cartons)</label>
                            <input type="number" step="0.01" class="form-control" id="tier1Price" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Tier 2 Price (10-49 cartons)</label>
                            <input type="number" step="0.01" class="form-control" id="tier2Price" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Tier 3 Price (50+ cartons)</label>
                            <input type="number" step="0.01" class="form-control" id="tier3Price" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentProducts = [];
let productModal;

document.addEventListener('DOMContentLoaded', () => {
    productModal = new bootstrap.Modal(document.getElementById('productModal'));
    loadProducts();
});

async function loadProducts() {
    try {
        const res = await fetch('../../api/wholesale/read.php', {
            headers: getAuthHeaders()
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            currentProducts = data.data;
            const tbody = document.getElementById('wholesaleTableBody');
            tbody.innerHTML = '';
            
            if(currentProducts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No products found</td></tr>';
                return;
            }

            currentProducts.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold">${p.name}</td>
                    <td><span class="badge bg-light text-dark border">${p.bottle_type}</span></td>
                    <td>${p.carton_size} units</td>
                    <td>GH₵ ${parseFloat(p.tier1_price).toFixed(2)}</td>
                    <td>GH₵ ${parseFloat(p.tier2_price).toFixed(2)}</td>
                    <td>GH₵ ${parseFloat(p.tier3_price).toFixed(2)}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" 
                                ${p.status === 'active' ? 'checked' : ''} 
                                onchange="toggleStatus(${p.id}, this.checked)">
                            <label class="form-check-label small">${p.status}</label>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${p.id})">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})">
                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {
        console.error('Error loading products:', e);
    }
}

async function toggleStatus(id, isActive) {
    const status = isActive ? 'active' : 'inactive';
    try {
        await fetch('../../api/wholesale/update_status.php', {
            method: 'POST',
            headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({id, status})
        });
        loadProducts();
    } catch (e) {
        console.error('Error toggling status:', e);
    }
}

function openAddModal() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Wholesale Product';
    productModal.show();
}

function openEditModal(id) {
    const p = currentProducts.find(x => x.id == id);
    if (!p) return;
    
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    document.getElementById('bottleType').value = p.bottle_type;
    document.getElementById('cartonSize').value = p.carton_size;
    document.getElementById('tier1Price').value = p.tier1_price;
    document.getElementById('tier2Price').value = p.tier2_price;
    document.getElementById('tier3Price').value = p.tier3_price;
    
    document.getElementById('modalTitle').textContent = 'Edit Wholesale Product';
    productModal.show();
}

async function saveProduct() {
    const id = document.getElementById('productId').value;
    const url = id ? '../../api/wholesale/update.php' : '../../api/wholesale/create.php';
    
    const payload = {
        name: document.getElementById('productName').value,
        bottle_type: document.getElementById('bottleType').value,
        carton_size: document.getElementById('cartonSize').value,
        tier1Price: document.getElementById('tier1Price').value,
        tier2Price: document.getElementById('tier2Price').value,
        tier3Price: document.getElementById('tier3Price').value,
        tier1_price: document.getElementById('tier1Price').value,
        tier2_price: document.getElementById('tier2Price').value,
        tier3_price: document.getElementById('tier3Price').value
    };
    if (id) payload.id = id;
    
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            productModal.hide();
            loadProducts();
        } else {
            alert(data.message || 'Error saving product');
        }
    } catch (e) {
        console.error('Error saving product:', e);
        alert('An error occurred');
    }
}

async function deleteProduct(id) {
    if(!confirm("Are you sure you want to completely delete this product from the wholesale list?")) return;
    try {
        const res = await fetch('../../api/wholesale/delete.php', {
            method: 'POST',
            headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({id})
        });
        const data = await res.json();
        if (data.status === 'success') {
            loadProducts();
        } else {
            alert(data.message || 'Error deleting product');
        }
    } catch (e) {
        console.error('Error deleting product:', e);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
