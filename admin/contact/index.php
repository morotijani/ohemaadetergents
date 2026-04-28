<?php include '../includes/header.php'; ?>
<script>checkAuth();</script>

<div class="d-flex justify-content-between align-items-center mt-2 mb-4">
    <h2 class="mb-0 fw-light">Customer Messages</h2>
</div>

<div class="row g-4">
    <!-- Messages List -->
    <div class="col-md-4">
        <div class="ohemaa-card p-0 overflow-hidden" style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
            <div class="p-3 border-bottom bg-light">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><span class="material-symbols-outlined small">search</span></span>
                    <input type="text" class="form-control border-start-0" id="msgSearch" placeholder="Search inbox..." onkeyup="filterMessages()">
                </div>
            </div>
            <div class="overflow-auto flex-grow-1" id="messageList">
                <div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>
            </div>
        </div>
    </div>

    <!-- Message Detail -->
    <div class="col-md-8">
        <div class="ohemaa-card d-none" id="messageDetailView" style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold" id="detailSubject">Subject Line</h5>
                    <p class="text-muted small mb-0" id="detailMeta">From: Name (email@example.com) • Date</p>
                </div>
                <div class="dropdown">
                    <button class="btn-ohemaa-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Mark as...
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('unread')">Unread</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('read')">Read</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('replied')">Replied</a></li>
                    </ul>
                </div>
            </div>
            <div class="p-4 flex-grow-1 overflow-auto bg-light-subtle">
                <div class="p-4 bg-white rounded-4 border shadow-sm" id="detailBody" style="white-space: pre-wrap; font-size: 15px; line-height: 1.6;">
                    Message content goes here...
                </div>
            </div>
            <div class="p-4 border-top">
                <a id="replyBtn" href="#" class="btn-ohemaa d-inline-flex align-items-center">
                    <span class="material-symbols-outlined me-2">reply</span> Reply via Email
                </a>
            </div>
        </div>
        <div class="ohemaa-card h-100 d-flex flex-column align-items-center justify-content-center text-center p-5" id="noMessageView">
            <div class="rounded-circle bg-light p-4 mb-3">
                <span class="material-symbols-outlined text-muted" style="font-size: 48px;">mail</span>
            </div>
            <h5 class="text-muted">Select a message to read</h5>
            <p class="small text-muted">Customer inquiries from your website contact form will appear here.</p>
        </div>
    </div>
</div>

<script>
    let messages = [];
    let selectedMessageId = null;

    document.addEventListener('DOMContentLoaded', loadMessages);

    async function loadMessages() {
        try {
            const res = await fetch(apiBase + '/contact/read', { headers: getAuthHeaders() });
            const data = await res.json();
            if (data.status === 'success') {
                messages = data.data;
                renderList();
            }
        } catch (e) { console.error(e); }
    }

    function renderList(filter = '') {
        const list = document.getElementById('messageList');
        list.innerHTML = '';
        
        const filtered = messages.filter(m => 
            m.name.toLowerCase().includes(filter.toLowerCase()) || 
            m.subject.toLowerCase().includes(filter.toLowerCase()) || 
            m.message.toLowerCase().includes(filter.toLowerCase())
        );

        if (filtered.length === 0) {
            list.innerHTML = '<div class="text-center py-5 text-muted small">No messages found.</div>';
            return;
        }

        filtered.forEach(m => {
            const item = document.createElement('div');
            item.className = `p-3 border-bottom cursor-pointer hover-bg transition-all ${selectedMessageId == m.id ? 'bg-active border-primary border-start border-4' : ''}`;
            item.style.cursor = 'pointer';
            item.onclick = () => selectMessage(m.id);
            
            const date = new Date(m.created_at).toLocaleDateString();
            const isUnread = m.status === 'unread';
            
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small ${isUnread ? 'text-primary' : 'text-muted'}">${m.name}</span>
                    <span class="text-muted" style="font-size: 11px;">${date}</span>
                </div>
                <div class="small fw-medium text-truncate ${isUnread ? 'text-dark' : 'text-muted'}">${m.subject || '(No Subject)'}</div>
                <div class="small text-muted text-truncate" style="font-size: 12px;">${m.message}</div>
            `;
            list.appendChild(item);
        });
    }

    function selectMessage(id) {
        selectedMessageId = id;
        const m = messages.find(x => x.id == id);
        if (!m) return;

        document.getElementById('noMessageView').classList.add('d-none');
        document.getElementById('messageDetailView').classList.remove('d-none');
        
        document.getElementById('detailSubject').innerText = m.subject || '(No Subject)';
        document.getElementById('detailMeta').innerText = `From: ${m.name} (${m.email}) • ${new Date(m.created_at).toLocaleString()}`;
        document.getElementById('detailBody').innerText = m.message;
        document.getElementById('replyBtn').href = `mailto:${m.email}?subject=Re: ${encodeURIComponent(m.subject || 'Ohemaa Detergents Inquiry')}`;

        if (m.status === 'unread') {
            updateStatus('read', false); // Silently mark as read
        }
        
        renderList(document.getElementById('msgSearch').value);
    }

    async function updateStatus(status, reload = true) {
        if (!selectedMessageId) return;
        try {
            const res = await fetch(apiBase + '/contact/update_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
                body: JSON.stringify({ id: selectedMessageId, status })
            });
            const data = await res.json();
            if (data.status === 'success') {
                if (reload) {
                    showToast('Status updated to ' + status);
                    loadMessages();
                } else {
                    // Update local state and notifications badge
                    const m = messages.find(x => x.id == selectedMessageId);
                    if (m) m.status = status;
                    updateNotifications();
                }
            }
        } catch (e) { console.error(e); }
    }

    function filterMessages() {
        const val = document.getElementById('msgSearch').value;
        renderList(val);
    }
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    .hover-bg:hover { background-color: var(--hover-bg); }
    .bg-active { background-color: var(--active-bg); }
    .transition-all { transition: all 0.2s ease; }
</style>

<?php include '../includes/footer.php'; ?>
