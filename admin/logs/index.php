<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Activity Audit Logs</h2>
    <div class="text-muted small">Tracking administrative accountability</div>
</div>

<div class="ohemaa-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--card-border) !important;">
        <h5 class="mb-0" style="font-weight: 500;">Recent Actions</h5>
        <div class="d-flex gap-2">
            <button class="btn-ohemaa-outline d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exportModal" style="padding: 4px 12px; font-size: 13px;">
                <span class="material-symbols-outlined me-2" style="font-size: 16px;">download</span> Export
            </button>
            <button class="btn btn-outline-danger d-flex align-items-center border-0" data-bs-toggle="modal" data-bs-target="#clearModal" style="padding: 4px 12px; font-size: 13px; background-color: rgba(220,53,69,0.1);">
                <span class="material-symbols-outlined me-2" style="font-size: 16px;">delete</span> Clear
            </button>
            <button class="btn-ohemaa-outline d-flex align-items-center" onclick="loadLogs()" style="padding: 4px 12px; font-size: 13px;">
                <span class="material-symbols-outlined me-2" style="font-size: 16px;">refresh</span> Refresh
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4 text-muted" style="font-weight: 500; font-size: 13px;">DATE & TIME</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">ADMIN</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">ACTION</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">DESCRIPTION</th>
                    <th class="pe-4 text-end text-muted" style="font-weight: 500; font-size: 13px;">IP ADDRESS</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">Loading activity logs...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content ohemaa-card border-0 p-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" style="font-weight: 500;">Export Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Select a date range to export, or export all time.</p>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="small text-muted mb-1">From Date</label>
                <input type="date" class="form-control" id="exportFrom">
            </div>
            <div class="col-6">
                <label class="small text-muted mb-1">To Date</label>
                <input type="date" class="form-control" id="exportTo">
            </div>
        </div>
        <button class="btn-ohemaa-outline w-100 mb-2" onclick="exportLogs('range')">Export Selected Range</button>
        <button class="btn-ohemaa w-100" onclick="exportLogs('all')">Export All Time</button>
      </div>
    </div>
  </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content ohemaa-card border-0 p-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-danger" style="font-weight: 500;">Clear Activity Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning border-0 small">
            <span class="material-symbols-outlined align-middle fs-6 me-1">warning</span> 
            Clearing logs is permanent and cannot be undone. An audit record of this deletion will be automatically generated.
        </div>
        <button class="btn btn-warning w-100 mb-2 d-flex justify-content-center align-items-center" onclick="clearLogs('older_than_90')">
            <span class="material-symbols-outlined me-2 fs-5">calendar_clock</span> Clear Logs Older Than 90 Days
        </button>
        <button class="btn btn-danger w-100 d-flex justify-content-center align-items-center" onclick="clearLogs('all')">
            <span class="material-symbols-outlined me-2 fs-5">delete_forever</span> Clear ALL Logs
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadLogs();
});

async function loadLogs() {
    try {
        const response = await fetch(apiBase + '/logs/read', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (data.status === 'success') {
            renderLogs(data.data);
        }
    } catch (err) {
        console.error('Error fetching logs', err);
    }
}

function renderLogs(logs) {
    const tbody = document.getElementById('logsTableBody');
    tbody.innerHTML = '';
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No activity logs found.</td></tr>';
        return;
    }

    logs.forEach(log => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        
        const date = new Date(log.created_at).toLocaleString('en-GB', {
            day: 'numeric', month: 'short', year: 'numeric', 
            hour: '2-digit', minute: '2-digit'
        });

        let actionClass = 'bg-secondary';
        if (log.action.includes('create')) actionClass = 'bg-success';
        if (log.action.includes('update')) actionClass = 'bg-primary';
        if (log.action.includes('delete')) actionClass = 'bg-danger';
        if (log.action.includes('bulk')) actionClass = 'bg-warning text-dark';

        tr.innerHTML = `
            <td class="ps-4 text-muted small">${date}</td>
            <td class="fw-medium">${log.admin_name || 'System'}</td>
            <td><span class="badge ${actionClass}" style="text-transform: capitalize; font-size: 11px;">${log.action.replace('_', ' ')}</span></td>
            <td class="text-muted" style="font-size: 14px; max-width: 400px; white-space: normal;">${log.description}</td>
            <td class="pe-4 text-end text-muted small font-monospace">${log.ip_address || 'Unknown'}</td>
        `;
        tbody.appendChild(tr);
    });
}

function exportLogs(type) {
    let url = apiBase + '/logs/export';
    if (type === 'range') {
        const from = document.getElementById('exportFrom').value;
        const to = document.getElementById('exportTo').value;
        if (!from || !to) {
            alert('Please select both From and To dates.');
            return;
        }
        url += `?from=${from}&to=${to}`;
    }
    
    // Create a hidden link to download the CSV with Auth headers
    fetch(url, {
        headers: getAuthHeaders()
    })
    .then(response => {
        if (!response.ok) throw new Error('Export failed');
        return response.blob();
    })
    .then(blob => {
        const objUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = objUrl;
        a.download = `audit_logs_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(objUrl);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        if (modal) modal.hide();
    })
    .catch(err => {
        console.error(err);
        alert('Error exporting logs');
    });
}

async function clearLogs(type) {
    const confirmMsg = type === 'all' 
        ? 'Are you ABSOLUTELY sure you want to delete ALL activity logs?' 
        : 'Are you sure you want to delete logs older than 90 days?';
        
    if (!confirm(confirmMsg)) return;
    
    try {
        const response = await fetch(apiBase + '/logs/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ action_type: type })
        });
        
        const data = await response.json();
        
        if (response.ok && data.status === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('clearModal'));
            if (modal) modal.hide();
            loadLogs();
            alert(data.message);
        } else {
            alert(data.message || 'Error clearing logs');
        }
    } catch (err) {
        console.error('Error clearing logs', err);
        alert('Network error');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
