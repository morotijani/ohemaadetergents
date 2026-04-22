<?php include 'includes/header.php'; ?>
<script>checkAuth();</script>

<h2 class="mb-4 mt-2" style="font-weight: 400; font-size: 28px;">Personal info</h2>
<p class="text-muted mb-4">Info about you and your preferences across Ohemaa services</p>

<div class="ohemaa-card p-0 overflow-hidden mb-4">
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--card-border) !important;">
        <div>
            <h3 class="mb-0 fs-5">Basic info</h3>
            <p class="text-muted mb-0 small">Some info may be visible to other people using Ohemaa services.</p>
        </div>
        <div class="position-relative" style="width: 80px; height: 80px;">
            <div id="profileImageDisplay" class="rounded-circle overflow-hidden shadow-sm d-flex align-items-center justify-content-center text-white fw-bold fs-2" style="width: 80px; height: 80px; background-color: var(--active-bg); border: 2px solid var(--card-border);">
                A
            </div>
            <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle shadow-sm p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border: 1px solid var(--card-border);" onclick="document.getElementById('imageUploadInput').click()">
                <span class="material-symbols-outlined" style="font-size: 18px;">photo_camera</span>
            </button>
            <input type="file" id="imageUploadInput" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="uploadProfileImage(this)">
        </div>
    </div>
    
    <div class="ohemaa-list-item d-block" onclick="focusInput('profileName')">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted" style="width: 200px; font-size: 14px;">NAME</div>
            <div class="flex-grow-1">
                <input type="text" id="profileName" class="form-control border-0 p-0 fw-medium bg-transparent shadow-none" style="font-size: 15px;">
            </div>
            <span class="material-symbols-outlined text-muted" style="font-size: 20px;">chevron_right</span>
        </div>
    </div>

    <div class="ohemaa-list-item d-block" onclick="focusInput('profileEmail')">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted" style="width: 200px; font-size: 14px;">EMAIL</div>
            <div class="flex-grow-1">
                <input type="email" id="profileEmail" class="form-control border-0 p-0 fw-medium bg-transparent shadow-none" style="font-size: 15px;">
            </div>
            <span class="material-symbols-outlined text-muted" style="font-size: 20px;">chevron_right</span>
        </div>
    </div>
    
    <div class="p-3 d-flex justify-content-end">
        <button class="btn-ohemaa" id="saveProfileBtn" onclick="updateProfile()">Save Changes</button>
    </div>
</div>

<h2 class="mb-4 mt-5" style="font-weight: 400; font-size: 28px;">Security</h2>
<p class="text-muted mb-4">Settings and recommendations to help you keep your account secure</p>

<div class="ohemaa-card p-0 overflow-hidden">
    <div class="p-4 border-bottom" style="border-color: var(--card-border) !important;">
        <h3 class="mb-0 fs-5">Signing in to Ohemaa</h3>
    </div>
    
    <div class="p-4">
        <div id="passwordAlert" class="alert d-none"></div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="currentPassword" placeholder="Current Password">
                    <label>Current Password</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="newPassword" placeholder="New Password">
                    <label>New Password</label>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-2">
            <button class="btn-ohemaa" id="changePasswordBtn" onclick="changePassword()">Update Password</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadProfile);

function focusInput(id) {
    document.getElementById(id).focus();
}

async function loadProfile() {
    try {
        const response = await fetch(apiBase + '/profile/read', {
            headers: getAuthHeaders()
        });
        const data = await response.json();
        if (data.status === 'success') {
            document.getElementById('profileName').value = data.data.name;
            document.getElementById('profileEmail').value = data.data.email;
            
            const display = document.getElementById('profileImageDisplay');
            if (data.data.profile_image) {
                display.innerHTML = `<img src="/ohemaadetergents/${data.data.profile_image}" class="w-100 h-100 object-fit-cover">`;
            } else {
                display.innerText = data.data.name.charAt(0).toUpperCase();
            }
        }
    } catch (err) {
        console.error(err);
    }
}

async function updateProfile() {
    const name = document.getElementById('profileName').value;
    const email = document.getElementById('profileEmail').value;
    const btn = document.getElementById('saveProfileBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    try {
        const response = await fetch(apiBase + '/profile/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ name, email })
        });
        const data = await response.json();
        
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        
        if (data.status === 'success') {
            const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
            user.name = name;
            localStorage.setItem('admin_user', JSON.stringify(user));
            setTimeout(() => location.reload(), 1000);
        }
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function changePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const btn = document.getElementById('changePasswordBtn');
    const originalText = btn.innerHTML;
    const alertBox = document.getElementById('passwordAlert');
    
    if (!currentPassword || !newPassword) {
        showToast('Please fill in both fields', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
    alertBox.classList.add('d-none');
    
    try {
        const response = await fetch(apiBase + '/profile/change_password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify({ current_password: currentPassword, new_password: newPassword })
        });
        const data = await response.json();
        
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        
        if (data.status === 'success') {
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
        }
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function uploadProfileImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('profile_image', input.files[0]);
    
    const display = document.getElementById('profileImageDisplay');
    const originalContent = display.innerHTML;
    display.innerHTML = '<div class="spinner-border spinner-border-sm text-white"></div>';
    
    try {
        const response = await fetch(apiBase + '/profile/upload_image', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: formData
        });
        const data = await response.json();
        
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        
        if (data.status === 'success') {
            display.innerHTML = `<img src="/ohemaadetergents/${data.data.image_url}" class="w-100 h-100 object-fit-cover">`;
            // Update topbar avatar if possible
            const topbarAvatar = document.getElementById('userAvatar');
            if (topbarAvatar) {
                topbarAvatar.innerHTML = `<img src="/ohemaadetergents/${data.data.image_url}" class="w-100 h-100 object-fit-cover">`;
            }
        } else {
            display.innerHTML = originalContent;
        }
    } catch (err) {
        console.error(err);
        showToast('Upload error', 'error');
        display.innerHTML = originalContent;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
