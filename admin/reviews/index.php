<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0" style="font-weight: 400; font-size: 28px;">Product Reviews</h2>
    <div class="text-muted small">Moderate customer feedback and ratings</div>
</div>

<div class="ohemaa-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--card-border) !important;">
        <h5 class="mb-0" style="font-weight: 500;">Customer Reviews</h5>
        <button class="btn-ohemaa-outline d-flex align-items-center" onclick="loadReviews()" style="padding: 4px 12px; font-size: 13px;">
            <span class="material-symbols-outlined me-2" style="font-size: 16px;">refresh</span> Refresh
        </button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4 text-muted" style="font-weight: 500; font-size: 13px;">PRODUCT</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">CUSTOMER</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">RATING</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">COMMENT</th>
                    <th class="text-muted" style="font-weight: 500; font-size: 13px;">STATUS</th>
                    <th class="pe-4 text-end text-muted" style="font-weight: 500; font-size: 13px;">ACTION</th>
                </tr>
            </thead>
            <tbody id="reviewsTableBody">
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">Loading reviews...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadReviews();
});

async function loadReviews() {
    try {
        const response = await fetch(apiBase + '/reviews/read', {
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            logout(); return;
        }

        const data = await response.json();
        if (data.status === 'success') {
            renderReviews(data.data);
        }
    } catch (err) {
        console.error('Error fetching reviews', err);
    }
}

function renderReviews(reviews) {
    const tbody = document.getElementById('reviewsTableBody');
    tbody.innerHTML = '';
    
    if (reviews.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No reviews found.</td></tr>';
        return;
    }

    reviews.forEach(r => {
        const tr = document.createElement('tr');
        tr.className = 'ohemaa-list-item';
        tr.style.display = 'table-row';
        
        const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
        let statusClass = 'bg-warning text-dark';
        if (r.status === 'approved') statusClass = 'bg-success';
        if (r.status === 'rejected') statusClass = 'bg-danger';

        tr.innerHTML = `
            <td class="ps-4 fw-medium">${r.product_name}</td>
            <td>${r.customer_name}</td>
            <td class="text-warning fw-bold">${stars}</td>
            <td class="text-muted" style="font-size: 14px; max-width: 300px; white-space: normal;">${r.comment}</td>
            <td><span class="badge ${statusClass}" style="text-transform: capitalize;">${r.status}</span></td>
            <td class="pe-4 text-end">
                <div class="dropdown">
                    <button class="icon-btn" type="button" data-bs-toggle="dropdown">
                        <span class="material-symbols-outlined">more_vert</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(${r.id}, 'approved')"><span class="material-symbols-outlined me-2 fs-6 text-success">check_circle</span> Approve</a></li>
                        <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(${r.id}, 'rejected')"><span class="material-symbols-outlined me-2 fs-6 text-danger">cancel</span> Reject</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="#" onclick="deleteReview(${r.id})"><span class="material-symbols-outlined me-2 fs-6">delete</span> Delete</a></li>
                    </ul>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function updateStatus(id, status) {
    try {
        const response = await fetch(apiBase + '/reviews/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id, status })
        });
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            loadReviews();
            showToast('Review status updated');
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteReview(id) {
    if (!confirm('Are you sure you want to delete this review?')) return;
    try {
        const response = await fetch(apiBase + '/reviews/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            loadReviews();
            showToast('Review deleted');
        }
    } catch (err) {
        console.error(err);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
