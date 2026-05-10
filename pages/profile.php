<?php
// pages/profile.php
if(!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

require_once 'config/db.php';
$userId = $_SESSION['user_id'];

// Load current user
$stmt = $pdo->prepare("SELECT id, name, email, profile_image, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: /login");
    exit;
}

// Load trip count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE user_id = ?");
$stmt->execute([$userId]);
$tripCount = $stmt->fetchColumn();

$pageStyles = "
    .profile-container {
        max-width: 720px;
        margin: 2rem auto;
    }
    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary);
    }
    .avatar-placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        border: 3px solid var(--primary);
        flex-shrink: 0;
    }
    .avatar-upload-zone {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .avatar-upload-zone input[type='file'] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        border-radius: 50%;
    }
    .avatar-overlay {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .avatar-upload-zone:hover .avatar-overlay {
        opacity: 1;
    }
    .danger-zone {
        border: 1px solid #EF4444;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-top: 1rem;
    }
";

include 'includes/components/header.php';
?>

<div class="container profile-container">

    <!-- Profile Header -->
    <div class="glass-card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <div class="avatar-upload-zone" id="avatarZone" title="Click to change avatar">
                <?php if ($user['profile_image']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" class="profile-avatar" id="avatarImg" alt="Profile photo">
                <?php else: ?>
                    <div class="avatar-placeholder" id="avatarPlaceholder">
                        <?php echo strtoupper(mb_substr($user['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="avatar-overlay">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
                <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp">
            </div>
            <div>
                <h1 style="margin: 0;"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p style="color: var(--text-muted); margin: 0.25rem 0;"><?php echo htmlspecialchars($user['email']); ?></p>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                    Member since <?php echo date('M Y', strtotime($user['created_at'])); ?> &bull; <?php echo $tripCount; ?> trip<?php echo $tripCount !== 1 ? 's' : ''; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="glass-card" style="margin-bottom: 1.5rem;">
        <h2 style="margin-bottom: 1.5rem;">Edit Profile</h2>
        <form id="profileForm">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" id="profileName" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" id="profileEmail" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="glass-card" style="margin-bottom: 1.5rem;">
        <h2 style="margin-bottom: 1.5rem;">Change Password</h2>
        <form id="passwordForm">
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" id="currentPassword" class="form-control" required placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" id="newPassword" class="form-control" required placeholder="Min 8 characters">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" id="confirmPassword" class="form-control" required placeholder="Repeat new password">
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="glass-card">
        <h2 style="color: #EF4444; margin-bottom: 1rem;">Danger Zone</h2>
        <div class="danger-zone">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <strong>Delete Account</strong>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0;">
                        Permanently deletes your account and all associated trips, stops, and activities. This cannot be undone.
                    </p>
                </div>
                <button class="btn btn-outline" id="deleteAccountBtn" style="color: #EF4444; border-color: #EF4444; white-space: nowrap;">
                    Delete Account
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Delete Account Confirmation Modal -->
<div id="deleteAccountModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(5px);">
    <div class="glass-card" style="max-width: 420px; width: 90%; text-align: center; padding: 2rem;">
        <h3 style="color: #EF4444; margin-bottom: 1rem;">Delete Account</h3>
        <p style="margin-bottom: 0.5rem;">This will permanently delete everything. Type <strong>DELETE</strong> to confirm:</p>
        <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Type DELETE" style="text-align: center; margin: 1rem 0; font-weight: 700; letter-spacing: 2px;">
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-primary" id="confirmDeleteAccountBtn" style="background: #EF4444; color: white;" disabled>Yes, Delete Forever</button>
        </div>
    </div>
</div>

<?php 
$pageScripts = "
// Avatar upload
const avatarInput = document.getElementById('avatarInput');
avatarInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) { App.showToast('Invalid file type', 'error'); return; }
    if (file.size > 2 * 1024 * 1024) { App.showToast('Max 2MB', 'error'); return; }
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
    try {
        const res = await fetch('/api/profile?action=uploadAvatar', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken || '' },
            body: formData
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.error);
        
        App.showToast('Avatar updated!', 'success');
        // Reload to show new avatar
        setTimeout(() => location.reload(), 1000);
    } catch(err) {
        App.showToast(err.message, 'error');
    }
});

// Profile form
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        await App.apiRequest('/api/profile?action=update', 'POST', {
            name: document.getElementById('profileName').value,
            email: document.getElementById('profileEmail').value
        });
        App.showToast('Profile updated!', 'success');
    } catch(err) {
        App.showToast(err.message, 'error');
    }
});

// Password form
document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const newPwd = document.getElementById('newPassword').value;
    const confirmPwd = document.getElementById('confirmPassword').value;
    
    if (newPwd.length < 8) {
        App.showToast('Password must be at least 8 characters', 'error'); return;
    }
    if (newPwd !== confirmPwd) {
        App.showToast('Passwords do not match', 'error'); return;
    }
    
    try {
        await App.apiRequest('/api/profile?action=changePassword', 'POST', {
            current_password: document.getElementById('currentPassword').value,
            new_password: newPwd
        });
        App.showToast('Password changed successfully!', 'success');
        e.target.reset();
    } catch(err) {
        App.showToast(err.message, 'error');
    }
});

// Delete account
document.getElementById('deleteAccountBtn').addEventListener('click', () => {
    document.getElementById('deleteAccountModal').style.display = 'flex';
    document.getElementById('deleteConfirmInput').value = '';
    document.getElementById('confirmDeleteAccountBtn').disabled = true;
});

document.getElementById('deleteConfirmInput').addEventListener('input', (e) => {
    document.getElementById('confirmDeleteAccountBtn').disabled = e.target.value !== 'DELETE';
});

function closeDeleteModal() {
    document.getElementById('deleteAccountModal').style.display = 'none';
}

document.getElementById('confirmDeleteAccountBtn').addEventListener('click', async () => {
    try {
        await App.apiRequest('/api/profile?action=deleteAccount', 'DELETE');
        App.showToast('Account deleted. Goodbye!', 'success');
        setTimeout(() => { window.location.href = '/'; }, 2000);
    } catch(err) {
        App.showToast(err.message, 'error');
    }
});
";
include 'includes/components/footer.php'; 
?>
