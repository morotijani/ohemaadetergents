<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-secondary mb-0">Products</h3>
    <button class="btn-google" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">Add Product</button>
</div>

<div class="google-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Image</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Name</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Price</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Stock</th>
                    <th class="border-top-0 text-muted text-end" style="font-weight: 500;">Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                <!-- Products will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content google-card border-0 p-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="modalTitle" style="font-weight: 500;">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="productForm">
            <input type="hidden" id="productId">
            <div id="modalAlert" class="alert alert-danger d-none"></div>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="productName" required>
                <label>Name</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="productSlug" required>
                <label>Slug</label>
            </div>
            <div class="form-floating mb-3">
                <textarea class="form-control" id="productDescription" style="height: 100px"></textarea>
                <label>Description</label>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control" id="productPrice" required>
                        <label>Price</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="productStock" required>
                        <label>Stock</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Product Image (JPG, PNG, WEBP)</label>
                <input type="file" class="form-control" id="productImage" accept="image/jpeg, image/png, image/webp">
                <small id="currentImageInfo" class="text-muted d-none">Current image exists. Upload a new one to replace.</small>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="productIsFeatured">
                <label class="form-check-label" for="productIsFeatured">
                    Featured Product
                </label>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn-google-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-google" id="saveBtn" onclick="saveProduct()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let products = [];
let productModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    productModalInstance = new bootstrap.Modal(document.getElementById('productModal'));
    loadProducts();
});

async function loadProducts() {
    try {
        const response = await fetch(apiBase + '/products/read.php', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (data.status === 'success') {
            products = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error fetching products', err);
    }
}

function renderTable() {
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = '';
    
    if (products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>`;
        return;
    }

    products.forEach(p => {
        const tr = document.createElement('tr');
        const imgUrl = p.image_url ? `/ohemaadetergents/${p.image_url}` : 'https://via.placeholder.com/40';
        tr.innerHTML = `
            <td><img src="${imgUrl}" alt="img" width="40" height="40" class="rounded object-fit-cover shadow-sm"></td>
            <td class="fw-medium text-dark">${p.name}</td>
            <td>GHS ${parseFloat(p.price).toFixed(2)}</td>
            <td>${p.stock}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-google-outline me-2" onclick="openEditModal(${p.id})">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openCreateModal() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalTitle').innerText = 'Add Product';
    document.getElementById('modalAlert').classList.add('d-none');
    document.getElementById('currentImageInfo').classList.add('d-none');
}

function openEditModal(id) {
    const p = products.find(x => x.id == id);
    if (!p) return;
    
    document.getElementById('productForm').reset();
    document.getElementById('modalAlert').classList.add('d-none');
    
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    document.getElementById('productSlug').value = p.slug;
    document.getElementById('productDescription').value = p.description;
    document.getElementById('productPrice').value = p.price;
    document.getElementById('productStock').value = p.stock;
    document.getElementById('productIsFeatured').checked = p.is_featured == 1;
    
    if (p.image_url) {
        document.getElementById('currentImageInfo').classList.remove('d-none');
    } else {
        document.getElementById('currentImageInfo').classList.add('d-none');
    }

    document.getElementById('modalTitle').innerText = 'Edit Product';
    productModalInstance.show();
}

async function saveProduct() {
    const id = document.getElementById('productId').value;
    const isEdit = !!id;
    const endpoint = isEdit ? '/products/update.php' : '/products/create.php';
    
    const formData = new FormData();
    if (isEdit) formData.append('id', id);
    formData.append('name', document.getElementById('productName').value);
    formData.append('slug', document.getElementById('productSlug').value);
    formData.append('description', document.getElementById('productDescription').value);
    formData.append('price', document.getElementById('productPrice').value);
    formData.append('stock', document.getElementById('productStock').value);
    formData.append('is_featured', document.getElementById('productIsFeatured').checked ? 1 : 0);
    
    const imageFile = document.getElementById('productImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    
    try {
        const response = await fetch(apiBase + endpoint, {
            method: 'POST',
            headers: getAuthHeaders(), // Don't set Content-Type, fetch sets multipart boundary automatically
            body: formData
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            productModalInstance.hide();
            loadProducts();
        } else {
            const alert = document.getElementById('modalAlert');
            alert.innerText = data.message || 'Error saving product';
            alert.classList.remove('d-none');
        }
    } catch (err) {
        console.error(err);
        const alert = document.getElementById('modalAlert');
        alert.innerText = 'Network error';
        alert.classList.remove('d-none');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
    }
}

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    
    try {
        const response = await fetch(apiBase + '/products/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            loadProducts();
        } else {
            alert(data.message || 'Error deleting product');
        }
    } catch (err) {
        console.error(err);
        alert('Network error');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
