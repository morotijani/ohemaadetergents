<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Admins</h2>
    <button class="btn-google d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#adminModal" onclick="openCreateModal()">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Add Admin
    </button>
</div>

<div class="google-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Name</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Email</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Date Created</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">Action</th>
                </tr>
            </thead>
            <tbody id="adminsTableBody">
                <!-- Admins will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Admin Modal -->
<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="adminForm">
                    <div id="modalAlert" class="alert alert-danger d-none"></div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="adminName" placeholder="Full Name" required>
                        <label>Full Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="adminEmail" placeholder="Email" required>
                        <label>Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="adminPassword" placeholder="Password" required>
                        <label>Password</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-google-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-google" id="saveAdminBtn" onclick="saveAdmin()">Save Admin</button>
            </div>
        </div>
    </div>
</div>

<script>
let admins = [];
let adminModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    adminModalInstance = new bootstrap.Modal(document.getElementById('adminModal'));
    loadAdmins();
});

async function loadAdmins() {
    try {
        const response = await fetch(apiBase + '/admins/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            admins = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error loading admins', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('adminsTableBody');
    tbody.innerHTML = '';
    
    const filteredAdmins = admins.filter(a => 
        a.name.toLowerCase().includes(filter.toLowerCase()) || 
        a.email.toLowerCase().includes(filter.toLowerCase())
    );
    
    if (filteredAdmins.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5">No admins found.</td></tr>`;
        return;
    }

    const currentUser = JSON.parse(localStorage.getItem('admin_user') || '{}');

    filteredAdmins.forEach(a => {
        const date = new Date(a.created_at).toLocaleDateString();
        const tr = document.createElement('tr');
        tr.className = 'google-list-item';
        tr.style.display = 'table-row';
        
        let actions = '';
        if (a.email !== currentUser.email) {
            actions = `
                <button class="icon-btn d-inline-flex text-danger" onclick="deleteAdmin(${a.id})" title="Delete">
                    <span class="material-symbols-outlined" style="font-size: 20px;">delete</span>
                </button>
            `;
        } else {
            actions = `<span class="text-muted small px-2">You</span>`;
        }

        tr.innerHTML = `
            <td class="px-4 py-3 fw-medium">${a.name}</td>
            <td class="px-4 py-3">${a.email}</td>
            <td class="px-4 py-3 text-muted">${date}</td>
            <td class="px-4 py-3 text-end" style="padding-right: 24px;">
                ${actions}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openCreateModal() {
    document.getElementById('adminForm').reset();
    document.getElementById('modalAlert').classList.add('d-none');
}

async function saveAdmin() {
    const name = document.getElementById('adminName').value;
    const email = document.getElementById('adminEmail').value;
    const password = document.getElementById('adminPassword').value;
    const saveBtn = document.getElementById('saveAdminBtn');
    
    if (!name || !email || !password) return;

    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    
    try {
        const response = await fetch(apiBase + '/admins/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ name, email, password })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            adminModalInstance.hide();
            loadAdmins();
        } else {
            const alert = document.getElementById('modalAlert');
            alert.innerText = data.message;
            alert.classList.remove('d-none');
        }
    } catch (err) {
        console.error(err);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save Admin';
    }
}

async function deleteAdmin(id) {
    if (!confirm('Are you sure you want to delete this admin?')) return;
    
    try {
        const response = await fetch(apiBase + '/admins/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            loadAdmins();
        } else {
            alert(data.message);
        }
    } catch (err) {
        console.error(err);
    }
}

function handleSearch(term) {
    renderTable(term);
}
</script>

<?php include '../includes/footer.php'; ?>
