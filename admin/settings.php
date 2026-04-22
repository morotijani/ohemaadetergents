<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<h2 class="mb-4 mt-2" style="font-weight: 400; font-size: 28px;">Store settings</h2>
<p class="text-muted mb-4">Configure your store's identity, contact information, and payment integration.</p>

<div class="row">
    <div class="col-lg-7">
        <div class="ohemaa-card p-0 overflow-hidden mb-4">
            <div class="p-4 border-bottom" style="border-color: var(--card-border) !important;">
                <h3 class="mb-0 fs-5">General configuration</h3>
            </div>
            
            <div class="p-4">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="setting_store_name" placeholder="Store Name">
                    <label>Store Name</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="setting_contact_email" placeholder="Contact Email">
                    <label>Contact Email</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="setting_contact_phone" placeholder="Contact Phone">
                    <label>Contact Phone</label>
                </div>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="setting_contact_address" style="height: 100px" placeholder="Store Address"></textarea>
                    <label>Store Address</label>
                </div>
            </div>
        </div>

        <div class="ohemaa-card p-0 overflow-hidden mb-4 border-primary" style="border-width: 1px !important;">
            <div class="p-4 border-bottom" style="border-color: var(--card-border) !important;">
                <div class="d-flex align-items-center">
                    <img src="https://paystack.com/favicon.png" width="20" height="20" class="me-2">
                    <h3 class="mb-0 fs-5">Payment Integration (Paystack)</h3>
                </div>
            </div>
            
            <div class="p-4">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="setting_paystack_public_key" placeholder="Public Key">
                    <label>Test/Live Public Key</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="setting_paystack_secret_key" placeholder="Secret Key">
                    <label>Test/Live Secret Key</label>
                </div>
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <i class="bi bi-info-circle me-1"></i> These keys are used to process payments during checkout.
                </div>
            </div>
        </div>
        
        <div class="mb-5 d-flex justify-content-end">
            <button class="btn-ohemaa" id="saveSettingsBtn" onclick="updateSettings()">Save All Settings</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadSettings);

async function loadSettings() {
    try {
        const response = await fetch(apiBase + '/settings/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            for (const [key, value] of Object.entries(data.data)) {
                const input = document.getElementById('setting_' + key);
                if (input) input.value = value;
            }
        }
    } catch (err) {
        console.error(err);
    }
}

async function updateSettings() {
    const btn = document.getElementById('saveSettingsBtn');
    const originalText = btn.innerHTML;
    const settings = {
        store_name: document.getElementById('setting_store_name').value,
        contact_email: document.getElementById('setting_contact_email').value,
        contact_phone: document.getElementById('setting_contact_phone').value,
        contact_address: document.getElementById('setting_contact_address').value,
        paystack_public_key: document.getElementById('setting_paystack_public_key').value,
        paystack_secret_key: document.getElementById('setting_paystack_secret_key').value
    };

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    try {
        const response = await fetch(apiBase + '/settings/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify(settings)
        });
        const data = await response.json();
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
