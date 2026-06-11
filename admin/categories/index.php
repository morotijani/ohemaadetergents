<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Categories</h2>
    <button class="btn-ohemaa d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Add Category
    </button>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Name</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Slug</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Description</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">Actions</th>
                </tr>
            </thead>
            <tbody id="categoriesTableBody">
                <!-- Categories will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content ohemaa-card border-0 p-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="modalTitle" style="font-weight: 500;">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="categoryForm">
            <input type="hidden" id="categoryId">
            <div id="modalAlert" class="alert alert-danger d-none"></div>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="categoryName" required>
                <label>Name</label>
            </div>
            <div class="form-floating mb-3">
                <textarea class="form-control" id="categoryDescription" style="height: 100px"></textarea>
                <label>Description</label>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn-ohemaa-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-ohemaa" id="saveBtn" onclick="saveCategory()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let categories = [];
let categoryModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    categoryModalInstance = new bootstrap.Modal(document.getElementById('categoryModal'));
    loadCategories();
});

async function loadCategories() {
    try {
        const response = await fetch(apiBase + '/categories/read', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (data.status === 'success') {
            categories = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error fetching categories', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('categoriesTableBody');
    tbody.innerHTML = '';
    
    const filteredCategories = categories.filter(c => c.name.toLowerCase().includes(filter.toLowerCase()));
    
    if (filteredCategories.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5"><span class="material-symbols-outlined fs-1 mb-2">search_off</span><br>No categories found.</td></tr>`;
        return;
    }

    filteredCategories.forEach(c => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        
        tr.innerHTML = `
            <td class="px-4 py-3 fw-medium">${c.name}</td>
            <td class="px-4 py-3 text-muted">${c.slug}</td>
            <td class="px-4 py-3 text-muted" style="max-width: 300px; white-space: normal;">${c.description || '-'}</td>
            <td class="px-4 py-3 text-end" style="padding-right: 24px;">
                <button class="icon-btn d-inline-flex" onclick="openEditModal(${c.id})" title="Edit">
                    <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                </button>
                <button class="icon-btn d-inline-flex text-danger" onclick="deleteCategory(${c.id})" title="Delete">
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
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('modalTitle').innerText = 'Add Category';
    document.getElementById('modalAlert').classList.add('d-none');
}

function openEditModal(id) {
    const c = categories.find(x => x.id == id);
    if (!c) return;
    
    document.getElementById('categoryForm').reset();
    document.getElementById('modalAlert').classList.add('d-none');
    
    document.getElementById('categoryId').value = c.id;
    document.getElementById('categoryName').value = c.name;
    document.getElementById('categoryDescription').value = c.description || '';
    
    document.getElementById('modalTitle').innerText = 'Edit Category';
    categoryModalInstance.show();
}

async function saveCategory() {
    const id = document.getElementById('categoryId').value;
    const isEdit = !!id;
    const endpoint = isEdit ? '/categories/update' : '/categories/create';
    
    const body = {
        id: id,
        name: document.getElementById('categoryName').value,
        description: document.getElementById('categoryDescription').value
    };
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    try {
        const response = await fetch(apiBase + endpoint, {
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
            categoryModalInstance.hide();
            loadCategories();
        } else {
            showToast(data.message || 'Error saving category', 'error');
            const alert = document.getElementById('modalAlert');
            alert.innerText = data.message || 'Error saving category';
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

async function deleteCategory(id) {
    if (!confirm('Are you sure you want to delete this category? Products in this category will be unassigned.')) return;
    
    try {
        const response = await fetch(apiBase + '/categories/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            loadCategories();
        } else {
            alert(data.message || 'Error deleting category');
        }
    } catch (err) {
        console.error(err);
        alert('Network error');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
