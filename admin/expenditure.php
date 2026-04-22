<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Expenditure</h2>
    <button class="btn-ohemaa d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#expenditureModal" onclick="openCreateModal()">
        <span class="material-symbols-outlined me-2" style="font-size: 20px;">add</span> Add Expenditure
    </button>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Date</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Category</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Description</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Amount</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Recorded By</th>
                    <th class="border-0 px-4 py-3 text-end fw-medium" style="font-size: 14px; padding-right: 24px;">Action</th>
                </tr>
            </thead>
            <tbody id="expenditureTableBody">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Expenditure Modal -->
<div class="modal fade" id="expenditureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expenditure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenditureForm">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="expDate" required value="<?php echo date('Y-m-d'); ?>">
                        <label>Date</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select class="form-select" id="expCategory" required>
                            <option value="" selected disabled>Select Category</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Stock/Inventory">Stock/Inventory</option>
                            <option value="Salary">Salary</option>
                            <option value="Rent">Rent</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Transport">Transport</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                        <label>Category</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control" id="expAmount" placeholder="Amount" required>
                        <label>Amount (GHS)</label>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="expDescription" style="height: 100px" placeholder="Description"></textarea>
                        <label>Description</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ohemaa-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-ohemaa" id="saveExpBtn" onclick="saveExpenditure()">Save Expenditure</button>
            </div>
        </div>
    </div>
</div>

<script>
let expenditures = [];
let expenditureModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    expenditureModalInstance = new bootstrap.Modal(document.getElementById('expenditureModal'));
    loadExpenditures();
});

async function loadExpenditures() {
    try {
        const response = await fetch(apiBase + '/expenditure/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            expenditures = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error loading expenditures', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('expenditureTableBody');
    tbody.innerHTML = '';
    
    const filtered = expenditures.filter(e => 
        e.category.toLowerCase().includes(filter.toLowerCase()) || 
        (e.description && e.description.toLowerCase().includes(filter.toLowerCase()))
    );
    
    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5">No expenditures found.</td></tr>`;
        return;
    }

    filtered.forEach(e => {
        const date = new Date(e.date).toLocaleDateString();
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        tr.innerHTML = `
            <td class="px-4 py-3 text-muted">${date}</td>
            <td class="px-4 py-3 fw-medium">${e.category}</td>
            <td class="px-4 py-3 text-muted small">${e.description || '-'}</td>
            <td class="px-4 py-3 fw-bold">GHS ${parseFloat(e.amount).toFixed(2)}</td>
            <td class="px-4 py-3 small text-muted">${e.admin_name}</td>
            <td class="px-4 py-3 text-end" style="padding-right: 24px;">
                <button class="icon-btn text-danger" onclick="deleteExpenditure(${e.id})" title="Delete">
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
    document.getElementById('expenditureForm').reset();
    document.getElementById('expDate').value = new Date().toISOString().split('T')[0];
}

async function saveExpenditure() {
    const date = document.getElementById('expDate').value;
    const category = document.getElementById('expCategory').value;
    const amount = document.getElementById('expAmount').value;
    const description = document.getElementById('expDescription').value;
    const btn = document.getElementById('saveExpBtn');
    
    if (!category || !amount || !date) {
        showToast('Please fill all required fields', 'error');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

    try {
        const response = await fetch(apiBase + '/expenditure/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ date, category, amount, description })
        });
        
        const data = await response.json();
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        
        if (data.status === 'success') {
            expenditureModalInstance.hide();
            loadExpenditures();
        }
    } catch (err) {
        console.error(err);
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function deleteExpenditure(id) {
    if (!confirm('Are you sure you want to delete this expenditure?')) return;
    
    try {
        const response = await fetch(apiBase + '/expenditure/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        
        if (data.status === 'success') {
            loadExpenditures();
        }
    } catch (err) {
        console.error(err);
        showToast('Error deleting expenditure', 'error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
