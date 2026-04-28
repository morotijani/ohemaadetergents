<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Products</h2>
    <button class="btn-ohemaa d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Add Product
    </button>
</div>

<div class="ohemaa-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Image</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Name</th>
                    <th class="border-top-0 text-muted" style="font-weight: 500;">Category</th>
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
    <div class="modal-content ohemaa-card border-0 p-0">
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
                <textarea class="form-control" id="productDescription" style="height: 100px"></textarea>
                <label>Description</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="productCategory">
                    <option value="">No Category</option>
                </select>
                <label>Category</label>
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
                <label class="form-label text-muted">Product Images (JPG, PNG, WEBP) - Max 4</label>
                <input type="file" class="form-control" id="productImages" accept="image/jpeg, image/png, image/webp" multiple>
                <small class="text-muted">You can upload up to 4 images. The first image is the primary.</small>
                <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-3"></div>
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
        <button type="button" class="btn-ohemaa-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-ohemaa" id="saveBtn" onclick="saveProduct()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let products = [];
let productModalInstance = null;
let currentExistingImages = [];
let pendingFiles = [];

document.addEventListener('DOMContentLoaded', () => {
    productModalInstance = new bootstrap.Modal(document.getElementById('productModal'));
    loadCategories();
    loadProducts();
});

async function loadCategories() {
    try {
        const response = await fetch(apiBase + '/categories/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            const select = document.getElementById('productCategory');
            data.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.innerText = c.name;
                select.appendChild(opt);
            });
        }
    } catch (err) { console.error('Error loading categories', err); }
}

document.getElementById('productImages').addEventListener('change', function(e) {
    if (currentExistingImages.length + pendingFiles.length + this.files.length > 4) {
        alert('You can only have up to 4 images total.');
        this.value = '';
        return;
    }
    
    for(let file of this.files) {
        pendingFiles.push(file);
    }
    this.value = ''; // clear so same file can be selected again if removed
    renderPreviews();
});

function removeExistingImage(index) {
    currentExistingImages.splice(index, 1);
    renderPreviews();
}

function removePendingFile(index) {
    pendingFiles.splice(index, 1);
    renderPreviews();
}

function renderPreviews() {
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = '';
    
    currentExistingImages.forEach((imgUrl, index) => {
        const div = document.createElement('div');
        div.className = 'position-relative';
        div.innerHTML = `
            <img src="/ohemaadetergents/${imgUrl}" class="rounded object-fit-cover shadow-sm" style="width: 80px; height: 80px;">
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 d-flex align-items-center justify-content-center" style="line-height:1; width:20px; height:20px; font-size:12px;" onclick="removeExistingImage(${index})"><i class="bi bi-x"></i></button>
        `;
        container.appendChild(div);
    });
    
    pendingFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'position-relative';
            div.innerHTML = `
                <img src="${e.target.result}" class="rounded object-fit-cover shadow-sm border border-primary" style="width: 80px; height: 80px;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 d-flex align-items-center justify-content-center" style="line-height:1; width:20px; height:20px; font-size:12px;" onclick="removePendingFile(${index})"><i class="bi bi-x"></i></button>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

async function loadProducts() {
    try {
        const response = await fetch(apiBase + '/products/read', {
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

function renderTable(filter = '') {
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = '';
    
    const filteredProducts = products.filter(p => p.name.toLowerCase().includes(filter.toLowerCase()));
    
    if (filteredProducts.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5"><span class="material-symbols-outlined fs-1 mb-2">search_off</span><br>No products found.</td></tr>`;
        return;
    }

    filteredProducts.forEach(p => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row'; // Reset to table row behavior
        
        const imgUrl = p.image_url ? `/ohemaadetergents/${p.image_url}` : 'https://via.placeholder.com/40';
        tr.innerHTML = `
            <td style="padding: 16px 24px;"><img src="${imgUrl}" alt="img" width="48" height="48" class="rounded object-fit-cover shadow-sm border" style="border-color: var(--card-border) !important;"></td>
            <td class="fw-medium text-dark" style="font-size: 15px;">${p.name}</td>
            <td class="text-muted">${p.category_name || '-'}</td>
            <td class="text-muted">GHS ${parseFloat(p.price).toFixed(2)}</td>
            <td class="text-muted">${p.stock} in stock</td>
            <td class="text-end" style="padding-right: 24px;">
                <button class="icon-btn d-inline-flex" onclick="openEditModal(${p.id})" title="Edit">
                    <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                </button>
                <button class="icon-btn d-inline-flex text-danger" onclick="deleteProduct(${p.id})" title="Delete">
                    <span class="material-symbols-outlined" style="font-size: 20px;">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function handleSearch(term) {
    renderTable(term);
}

function openCreateModal() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalTitle').innerText = 'Add Product';
    document.getElementById('modalAlert').classList.add('d-none');
    
    currentExistingImages = [];
    pendingFiles = [];
    renderPreviews();
}

function openEditModal(id) {
    const p = products.find(x => x.id == id);
    if (!p) return;
    
    document.getElementById('productForm').reset();
    document.getElementById('modalAlert').classList.add('d-none');
    
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    document.getElementById('productDescription').value = p.description;
    document.getElementById('productPrice').value = p.price;
    document.getElementById('productStock').value = p.stock;
    document.getElementById('productCategory').value = p.category_id || '';
    document.getElementById('productIsFeatured').checked = p.is_featured == 1;
    
    currentExistingImages = p.images ? [...p.images] : [];
    pendingFiles = [];
    renderPreviews();

    document.getElementById('modalTitle').innerText = 'Edit Product';
    productModalInstance.show();
}

async function saveProduct() {
    const id = document.getElementById('productId').value;
    const isEdit = !!id;
    const endpoint = isEdit ? '/products/update' : '/products/create';
    
    const formData = new FormData();
    if (isEdit) formData.append('id', id);
    formData.append('name', document.getElementById('productName').value);
    formData.append('description', document.getElementById('productDescription').value);
    formData.append('price', document.getElementById('productPrice').value);
    formData.append('stock', document.getElementById('productStock').value);
    formData.append('category_id', document.getElementById('productCategory').value);
    formData.append('is_featured', document.getElementById('productIsFeatured').checked ? 1 : 0);
    
    currentExistingImages.forEach(img => formData.append('existing_images[]', img));
    pendingFiles.forEach(file => formData.append('images[]', file));
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    try {
        const response = await fetch(apiBase + endpoint, {
            method: 'POST',
            headers: getAuthHeaders(),
            body: formData
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            showToast(data.message);
            productModalInstance.hide();
            loadProducts();
        } else {
            showToast(data.message || 'Error saving product', 'error');
            const alert = document.getElementById('modalAlert');
            alert.innerText = data.message || 'Error saving product';
            alert.classList.remove('d-none');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    
    try {
        const response = await fetch(apiBase + '/products/delete', {
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
