<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Stockist Applications</h2>
</div>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Shop / Owner</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Contact</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Location</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Type & Vol</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Date</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Status</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium text-end" style="font-size: 14px;">Actions</th>
                </tr>
            </thead>
            <tbody id="stockistsTableBody">
                <!-- Stockists will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Stockist Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
            <input type="hidden" id="editUuid">
            <div class="mb-3">
                <label class="form-label text-muted small">Shop Name</label>
                <input type="text" class="form-control" id="editShopName" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Owner Name</label>
                <input type="text" class="form-control" id="editOwnerName" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Phone</label>
                <input type="text" class="form-control" id="editPhone" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">Region</label>
                    <select class="form-control" id="editRegion" required>
                        <option value="Ahafo">Ahafo</option>
                        <option value="Ashanti">Ashanti</option>
                        <option value="Bono">Bono</option>
                        <option value="Bono East">Bono East</option>
                        <option value="Central">Central</option>
                        <option value="Eastern">Eastern</option>
                        <option value="Greater Accra">Greater Accra</option>
                        <option value="North East">North East</option>
                        <option value="Northern">Northern</option>
                        <option value="Oti">Oti</option>
                        <option value="Savannah">Savannah</option>
                        <option value="Upper East">Upper East</option>
                        <option value="Upper West">Upper West</option>
                        <option value="Volta">Volta</option>
                        <option value="Western">Western</option>
                        <option value="Western North">Western North</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">Town / Area</label>
                    <input type="text" class="form-control" id="editTownArea" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">Business Type</label>
                    <select class="form-control" id="editBusinessType" required>
                        <option value="Provision shop">Provision shop</option>
                        <option value="Supermarket">Supermarket</option>
                        <option value="Salon / spa">Salon / spa</option>
                        <option value="Hotel / hospitality">Hotel / hospitality</option>
                        <option value="Cleaning service">Cleaning service</option>
                        <option value="Wholesale distributor">Wholesale distributor</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">Monthly Volume</label>
                    <select class="form-control" id="editMonthlyVolume" required>
                        <option value="Under 50 units">Under 50 units</option>
                        <option value="50–200 units">50–200 units</option>
                        <option value="200–500 units">200–500 units</option>
                        <option value="500+ units">500+ units</option>
                    </select>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-ohemaa" onclick="saveEdit()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script>
let stockists = [];

document.addEventListener('DOMContentLoaded', () => {
    loadStockists();
});

async function loadStockists() {
    try {
        const response = await fetch(apiBase + '/stockists/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            stockists = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error loading stockists', err);
        showToast('Error loading stockists', 'error');
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('stockistsTableBody');
    tbody.innerHTML = '';
    
    const filteredStockists = stockists.filter(s => 
        s.shop_name.toLowerCase().includes(filter.toLowerCase()) || 
        s.owner_name.toLowerCase().includes(filter.toLowerCase()) ||
        s.region.toLowerCase().includes(filter.toLowerCase()) ||
        s.town_area.toLowerCase().includes(filter.toLowerCase())
    );
    
    if (filteredStockists.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5">No applications found.</td></tr>`;
        return;
    }

    filteredStockists.forEach(s => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        
        let statusBadge = '';
        if (s.status === 'approved') statusBadge = '<span class="badge bg-success">Approved</span>';
        else if (s.status === 'rejected') statusBadge = '<span class="badge bg-danger">Rejected</span>';
        else statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';

        tr.innerHTML = `
            <td class="px-4 py-3">
                <div class="fw-bold text-dark">${s.shop_name}</div>
                <div class="text-muted small">${s.owner_name}</div>
            </td>
            <td class="px-4 py-3">${s.phone}</td>
            <td class="px-4 py-3">
                <div class="text-dark">${s.town_area}</div>
                <div class="text-muted small">${s.region}</div>
            </td>
            <td class="px-4 py-3">
                <div class="text-dark">${s.business_type}</div>
                <div class="text-muted small">${s.monthly_volume}</div>
            </td>
            <td class="px-4 py-3 text-muted">${s.created_at_formatted}</td>
            <td class="px-4 py-3">${statusBadge}</td>
            <td class="px-4 py-3 text-end" style="white-space: nowrap;">
                <button class="btn btn-sm btn-light py-1 px-2 text-muted me-1" style="font-size: 12px;" onclick="openEditModal('${s.uuid}')">Edit</button>
                ${s.status === 'pending' ? `
                    <button class="btn-ohemaa btn-sm py-1 px-2" style="font-size: 12px; background-color: #81c995; color: #fff;" onclick="updateStatus('${s.uuid}', 'approved')">Approve</button>
                    <button class="btn-ohemaa btn-sm py-1 px-2" style="font-size: 12px; background-color: #f28b82; color: #fff;" onclick="updateStatus('${s.uuid}', 'rejected')">Reject</button>
                ` : `
                    <button class="btn btn-sm btn-light py-1 px-2 text-muted" style="font-size: 12px;" onclick="updateStatus('${s.uuid}', 'pending')">Undo</button>
                `}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function handleSearch(term) {
    renderTable(term);
}

async function updateStatus(uuid, newStatus) {
    try {
        const res = await fetch(apiBase + '/stockists/update_status', {
            method: 'POST',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ uuid: uuid, status: newStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('Status updated');
            // update local state
            const index = stockists.findIndex(s => s.uuid === uuid);
            if (index !== -1) {
                stockists[index].status = newStatus;
                renderTable(document.getElementById('globalSearch').value);
            }
        } else {
            showToast(data.message || 'Error updating status', 'error');
        }
    } catch(err) {
        console.error(err);
        showToast('Network error', 'error');
    }
}

let editModalInstance = null;
function openEditModal(uuid) {
    const s = stockists.find(x => x.uuid === uuid);
    if (!s) return;
    
    document.getElementById('editUuid').value = s.uuid;
    document.getElementById('editShopName').value = s.shop_name;
    document.getElementById('editOwnerName').value = s.owner_name;
    document.getElementById('editPhone').value = s.phone;
    document.getElementById('editRegion').value = s.region;
    document.getElementById('editTownArea').value = s.town_area;
    document.getElementById('editBusinessType').value = s.business_type;
    document.getElementById('editMonthlyVolume').value = s.monthly_volume;
    
    if(!editModalInstance) editModalInstance = new bootstrap.Modal(document.getElementById('editModal'));
    editModalInstance.show();
}

async function saveEdit() {
    const uuid = document.getElementById('editUuid').value;
    const payload = {
        uuid: uuid,
        shop_name: document.getElementById('editShopName').value,
        owner_name: document.getElementById('editOwnerName').value,
        phone: document.getElementById('editPhone').value,
        region: document.getElementById('editRegion').value,
        town_area: document.getElementById('editTownArea').value,
        business_type: document.getElementById('editBusinessType').value,
        monthly_volume: document.getElementById('editMonthlyVolume').value
    };

    try {
        const res = await fetch(apiBase + '/stockists/update', {
            method: 'POST',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('Application updated successfully');
            editModalInstance.hide();
            loadStockists(); // reload from server to ensure fresh data
        } else {
            showToast(data.message || 'Error updating', 'error');
        }
    } catch(err) {
        console.error(err);
        showToast('Network error', 'error');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
