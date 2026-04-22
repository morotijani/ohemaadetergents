<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Customers</h2>
</div>

<div class="google-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="background-color: var(--hover-bg);">
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Name</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Email</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Phone</th>
                    <th class="border-0 px-4 py-3 text-muted fw-medium" style="font-size: 14px;">Joined Date</th>
                </tr>
            </thead>
            <tbody id="customersTableBody">
                <!-- Customers will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<script>
let customers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadCustomers();
});

async function loadCustomers() {
    try {
        const response = await fetch(apiBase + '/customers/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            customers = data.data;
            renderTable();
        }
    } catch (err) {
        console.error('Error loading customers', err);
    }
}

function renderTable(filter = '') {
    const tbody = document.getElementById('customersTableBody');
    tbody.innerHTML = '';
    
    const filteredCustomers = customers.filter(c => 
        c.first_name.toLowerCase().includes(filter.toLowerCase()) || 
        c.last_name.toLowerCase().includes(filter.toLowerCase()) ||
        c.email.toLowerCase().includes(filter.toLowerCase())
    );
    
    if (filteredCustomers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5">No customers found.</td></tr>`;
        return;
    }

    filteredCustomers.forEach(c => {
        const date = new Date(c.created_at).toLocaleDateString();
        const tr = document.createElement('tr');
        tr.className = 'google-list-item';
        tr.style.display = 'table-row';
        tr.innerHTML = `
            <td class="px-4 py-3 fw-medium">${c.first_name} ${c.last_name}</td>
            <td class="px-4 py-3">${c.email}</td>
            <td class="px-4 py-3 text-muted">${c.phone || 'N/A'}</td>
            <td class="px-4 py-3 text-muted">${date}</td>
        `;
        tbody.appendChild(tr);
    });
}

function handleSearch(term) {
    renderTable(term);
}
</script>

<?php include '../includes/footer.php'; ?>
