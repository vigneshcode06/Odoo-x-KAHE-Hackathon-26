<?php
// pages/create-trip.php
if(!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

$pageStyles = "
    .form-container {
        max-width: 640px;
        margin: 2rem auto;
    }
    .form-row {
        display: flex;
        gap: 1rem;
    }
    .form-row > .form-group {
        flex: 1;
    }
    .upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        background: var(--bg-main);
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }
    .upload-zone input[type='file'] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .upload-preview {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: var(--radius-md);
        display: none;
        margin-top: 1rem;
    }
    .upload-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        color: var(--text-muted);
    }
";

include 'includes/components/header.php';
?>

<div class="container form-container">
    <div class="glass-card">
        <div style="margin-bottom: 2rem;">
            <h1>Create New Trip</h1>
            <p style="color: var(--text-muted);">Fill in the details to start planning your adventure.</p>
        </div>

        <form id="createTripForm" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="title">Trip Title *</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Summer in Paris">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="What is the purpose of this trip?"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
            </div>

            <!-- Cover Photo Upload -->
            <div class="form-group">
                <label class="form-label">Cover Photo (Optional)</label>
                <div class="upload-zone" id="uploadZone">
                    <input type="file" id="coverPhoto" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Click or drag & drop</p>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">JPEG, PNG, WebP – max 2MB</p>
                </div>
                <img id="previewImg" class="upload-preview" alt="Cover preview">
                <p id="uploadError" style="color: #EF4444; font-size: 0.875rem; margin-top: 0.5rem; display: none;"></p>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                <input type="checkbox" id="is_public" name="is_public" style="width: 1rem; height: 1rem; accent-color: var(--primary);">
                <label for="is_public" style="font-weight: 500; color: var(--secondary); cursor: pointer;">Make this itinerary public</label>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="/dashboard" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">Create Trip</button>
            </div>
        </form>
    </div>
</div>

<?php 
$pageScripts = "
const uploadZone = document.getElementById('uploadZone');
const coverPhoto = document.getElementById('coverPhoto');
const previewImg = document.getElementById('previewImg');
const uploadError = document.getElementById('uploadError');

// Drag-and-drop visual feedback
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('drag-over');
});
uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('drag-over');
});
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
        coverPhoto.files = e.dataTransfer.files;
        handleFilePreview(e.dataTransfer.files[0]);
    }
});

coverPhoto.addEventListener('change', (e) => {
    if (e.target.files.length) {
        handleFilePreview(e.target.files[0]);
    }
});

function handleFilePreview(file) {
    uploadError.style.display = 'none';
    
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        uploadError.innerText = 'Invalid file type. Please use JPEG, PNG, or WebP.';
        uploadError.style.display = 'block';
        coverPhoto.value = '';
        previewImg.style.display = 'none';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        uploadError.innerText = 'File too large. Maximum size is 2MB.';
        uploadError.style.display = 'block';
        coverPhoto.value = '';
        previewImg.style.display = 'none';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

document.getElementById('createTripForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    
    // Client-side validation
    if (uploadError.style.display === 'block') {
        App.showToast('Please fix the file error first', 'error');
        return;
    }

    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
        App.showToast('End date cannot be before start date', 'error');
        return;
    }
    
    try {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        
        // Use FormData to support file uploads
        const formData = new FormData();
        formData.append('title', document.getElementById('title').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('is_public', document.getElementById('is_public').checked ? '1' : '0');
        
        const file = coverPhoto.files[0];
        if (file) {
            formData.append('cover_photo', file);
        }
        
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
        const response = await fetch('/api/trips?action=createWithUpload', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken || '' },
            body: formData
        });
        
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Something went wrong');
        
        App.showToast('Trip created successfully!', 'success');
        setTimeout(() => {
            window.location.href = `/itinerary?id=\${result.trip_id}`;
        }, 1200);
        
    } catch (error) {
        App.showToast(error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Trip';
    }
});
";
include 'includes/components/footer.php'; 
?>
