<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Products</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>admin/products/bulk" class="btn-ohemaa-outline d-flex align-items-center text-decoration-none">
            <span class="material-symbols-outlined me-2" style="font-size: 20px;">batch_prediction</span> Bulk Edit
        </a>
        <button class="btn-ohemaa d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
            <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Add Product
        </button>
    </div>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Image</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Name</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Category</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Price</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Stock</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">Actions</th>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content ohemaa-card border-0 p-0 overflow-hidden">
      <div class="modal-header border-0 p-4 pb-0">
        <div>
            <h5 class="modal-title" id="modalTitle" style="font-weight: 500; font-size: 22px;">Add Product</h5>
            <p class="text-muted small mb-0">Fill in the details below to list a new item.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="productForm">
            <input type="hidden" id="productId">
            <div id="modalAlert" class="alert alert-danger d-none"></div>
            
            <div class="row g-4">
                <!-- Left Column: General Info -->
                <div class="col-md-7">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="material-symbols-outlined me-2 fs-5 text-primary">info</span>
                        General Information
                    </h6>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="productName" placeholder="Product Name" required>
                        <label>Product Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="productDescription" placeholder="Description" style="height: 150px"></textarea>
                        <label>Product Description</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select class="form-select" id="productCategory">
                            <option value="">Select a Category</option>
                        </select>
                        <label>Category</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="productIsFeatured" role="switch">
                        <label class="form-check-label fw-medium" for="productIsFeatured">
                            Mark as Featured Product
                        </label>
                        <div class="text-muted small">Featured products appear on the homepage hero slider.</div>
                    </div>
                </div>

                <!-- Right Column: Inventory & Pricing -->
                <div class="col-md-5">
                    <div class="p-4 rounded-4 bg-light border mb-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <span class="material-symbols-outlined me-2 fs-5 text-success">payments</span>
                            Inventory & Pricing
                        </h6>
                        <div class="form-floating mb-3">
                            <input type="number" step="0.01" class="form-control" id="productPrice" placeholder="0.00" required>
                            <label>Price (GHS)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="productStock" placeholder="0" required>
                            <label>Stock Quantity</label>
                        </div>
                        <div class="form-floating mb-0">
                            <input type="number" class="form-control" id="productThreshold" placeholder="5">
                            <label>Low Stock Threshold</label>
                            <div class="text-muted" style="font-size: 10px;">Default is 5. Turns red when stock hits this.</div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="material-symbols-outlined me-2 fs-5 text-warning">image</span>
                        Product Media
                    </h6>
                    <div class="mb-3">
                        <div class="upload-zone border border-dashed rounded-4 p-4 text-center position-relative" style="background: var(--hover-bg); border-style: dashed !important; border-width: 2px !important;">
                            <span class="material-symbols-outlined fs-1 text-muted mb-2">cloud_upload</span>
                            <p class="small text-muted mb-0">Click to upload images</p>
                            <p class="text-muted" style="font-size: 10px;">Max 4 images (JPG, PNG, WEBP)</p>
                            <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" id="productImages" accept="image/jpeg, image/png, image/webp" multiple style="cursor: pointer;">
                        </div>
                        <div id="imagePreviewContainer" class="row g-2 mt-2">
                            <!-- Previews will load here -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 p-4 pt-0">
        <button type="button" class="btn-ohemaa-outline border-0" data-bs-dismiss="modal">Discard Changes</button>
        <button type="button" class="btn-ohemaa px-5" id="saveBtn" onclick="saveProduct()">
            <span class="d-flex align-items-center">
                <span class="material-symbols-outlined me-2">check_circle</span>
                Save Product
            </span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let products = [];
let productModalInstance = null;
let currentExistingImages = [];
let pendingFiles = [];

document.addEventListener('DOMContentLoaded', async () => {
    productModalInstance = new bootstrap.Modal(document.getElementById('productModal'));
    
    document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
        if (window.returnToViewOnClose) {
            window.location.href = `${BASE_URL}/admin/products/view?id=' + window.returnToViewOnClose;
        }
    });

    await loadCategories();
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
        div.className = 'col-3 position-relative';
        div.innerHTML = `
            <div class="ratio ratio-1x1">
                <img src="<?php echo BASE_URL; ?>${imgUrl}" class="rounded-3 object-fit-cover shadow-sm border">
            </div>
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:20px; height:20px;" onclick="removeExistingImage(${index})">
                <span class="material-symbols-outlined" style="font-size: 14px;">close</span>
            </button>
        `;
        container.appendChild(div);
    });
    
    pendingFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'col-3 position-relative';
            div.innerHTML = `
                <div class="ratio ratio-1x1">
                    <img src="${e.target.result}" class="rounded-3 object-fit-cover shadow-sm border border-primary">
                </div>
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:20px; height:20px;" onclick="removePendingFile(${index})">
                    <span class="material-symbols-outlined" style="font-size: 14px;">close</span>
                </button>
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
            
            // Check if we need to auto-open the edit modal
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit_id');
            const source = urlParams.get('source');
            if (editId) {
                if (source === 'view') {
                    window.returnToViewOnClose = editId;
                }
                openEditModal(editId);
                // Clean the URL so refreshing doesn't reopen the modal
                window.history.replaceState({}, document.title, window.location.pathname);
            }
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
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5"><span class="material-symbols-outlined fs-1 mb-2">search_off</span><br>No products found.</td></tr>`;
        return;
    }

    filteredProducts.forEach(p => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row'; // Reset to table row behavior
        
        const imgUrl = p.image_url ? `${BASE_URL}/${p.image_url}` : 'https://via.placeholder.com/40';
        tr.innerHTML = `
            <td class="px-4 py-3"><img src="${imgUrl}" alt="img" width="48" height="48" class="rounded object-fit-cover shadow-sm border" style="border-color: var(--card-border) !important;"></td>
            <td class="px-4 py-3 fw-medium text-dark" style="font-size: 15px;">
                <a href="<?php echo BASE_URL; ?>admin/products/view?id=${p.id}" class="text-decoration-none text-dark hover-primary">${p.name}</a>
            </td>
            <td class="px-4 py-3 text-muted">${p.category_name || '-'}</td>
            <td class="px-4 py-3 text-muted">GHS ${parseFloat(p.price).toFixed(2)}</td>
            <td class="px-4 py-3 ${parseInt(p.stock) <= (parseInt(p.stock_threshold) || 5) ? 'text-danger fw-bold' : 'text-muted'}">${p.stock} in stock</td>
            <td class="px-4 py-3 text-end" style="padding-right: 24px;">
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
    document.getElementById('productThreshold').value = p.stock_threshold || '';
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
    formData.append('stock_threshold', document.getElementById('productThreshold').value);
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
