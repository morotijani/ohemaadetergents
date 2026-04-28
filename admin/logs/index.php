<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Activity Audit Logs</h2>
    <div class="text-muted small">Tracking administrative accountability</div>
</div>

<div class="ohemaa-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--card-border) !important;">
        <h5 class="mb-0" style="font-weight: 500;">Recent Actions</h5>
        <button class="btn-ohemaa-outline d-flex align-items-center" onclick="loadLogs()" style="padding: 4px 12px; font-size: 13px;">
            <span class="material-symbols-outlined me-2" style="font-size: 16px;">refresh</span> Refresh
        </button>
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
</script>

<?php include '../includes/footer.php'; ?>
