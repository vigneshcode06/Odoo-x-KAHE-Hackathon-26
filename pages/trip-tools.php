<?php
// pages/trip-tools.php
if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: /dashboard");
    exit;
}

$tripId = $_GET['id'];

$pageStyles = "
    .tools-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    .tool-section {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }
    .check-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .check-item:last-child {
        border-bottom: none;
    }
    .note-item {
        background: var(--bg-main);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        position: relative;
    }
    .note-content {
        cursor: text;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        min-height: 2rem;
    }
    .note-content:hover {
        background: rgba(0,0,0,0.05);
    }
    .note-content:focus {
        background: white;
        outline: 1px solid var(--primary);
    }
    .progress-bar-container {
        width: 100%;
        background: var(--bg-main);
        border-radius: 99px;
        height: 8px;
        margin-top: 0.5rem;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: var(--primary);
        transition: width 0.3s ease;
    }
    .category-group {
        margin-top: 1.5rem;
    }
    .category-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--secondary);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    @media (max-width: 768px) {
        .tools-grid { grid-template-columns: 1fr; }
    }
";

include 'includes/components/header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; margin-top: 2rem;">
        <h2>Trip Tools & Utilities</h2>
        <a href="/itinerary?id=<?php echo htmlspecialchars($tripId); ?>" class="btn btn-outline">&larr; Back to Itinerary</a>
    </div>

    <div class="tools-grid">
        <!-- Packing List -->
        <div class="tool-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin-bottom: 1rem;">Packing Checklist</h3>
                <button class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="resetChecklist()">Reset All</button>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.875rem;">
                    <span>Progress</span>
                    <span id="progressText">0 of 0 items packed</span>
                </div>
                <div class="progress-bar-container">
                    <div id="progressBar" class="progress-bar-fill" style="width: 0%;"></div>
                </div>
            </div>

            <form id="packingForm" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="text" id="packItem" class="form-control" placeholder="Add an item..." required style="flex: 2;">
                <select id="packCategory" class="form-control" style="flex: 1;">
                    <option value="Clothing">Clothing</option>
                    <option value="Documents">Documents</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Misc">Misc</option>
                </select>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
            <div id="packingList"></div>
        </div>

        <!-- Notes/Journal -->
        <div class="tool-section">
            <h3 style="margin-bottom: 1rem;">Trip Notes & Journal</h3>
            <form id="noteForm" style="margin-bottom: 1rem; position: relative;">
                <textarea id="noteContent" class="form-control" rows="3" placeholder="Write a note..." required style="margin-bottom: 0.5rem;" maxlength="500"></textarea>
                <div style="text-align: right; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                    <span id="charCount">0</span>/500
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Note</button>
            </form>
            <div id="notesList"></div>
        </div>
    </div>
</div>

<?php 
$pageScripts = "
const tripId = " . json_encode($tripId) . ";

async function loadPacking() {
    try {
        const res = await App.apiRequest(`/api/utilities?action=getPacking&trip_id=\${tripId}`);
        const list = document.getElementById('packingList');
        const items = res.data;
        
        let packedCount = 0;
        let totalCount = items.length;
        
        // Group by category
        const groups = {
            'Documents': [],
            'Electronics': [],
            'Clothing': [],
            'Misc': []
        };
        
        items.forEach(item => {
            if(item.is_packed) packedCount++;
            const cat = item.category || 'Misc';
            if(!groups[cat]) groups[cat] = [];
            groups[cat].push(item);
        });
        
        // Update Progress
        document.getElementById('progressText').innerText = `\${packedCount} of \${totalCount} items packed`;
        const percentage = totalCount === 0 ? 0 : (packedCount / totalCount) * 100;
        document.getElementById('progressBar').style.width = `\${percentage}%`;
        
        list.innerHTML = '';
        for (let cat in groups) {
            if(groups[cat].length === 0) continue;
            
            list.innerHTML += `<div class=\"category-title\">\${cat}</div>`;
            groups[cat].forEach(item => {
                list.innerHTML += `
                    <div class=\"check-item\">
                        <div style=\"display:flex; align-items:center; gap:0.5rem; flex:1;\">
                            <input type=\"checkbox\" \${item.is_packed ? 'checked' : ''} onchange=\"toggleItem(\${item.id}, this.checked)\" style=\"width: 1.2rem; height: 1.2rem;\">
                            <span style=\"\${item.is_packed ? 'text-decoration: line-through; color: var(--text-muted);' : ''}\">\${item.item_name}</span>
                        </div>
                        <button onclick=\"deleteItem(\${item.id})\" class=\"btn btn-outline\" style=\"padding: 0.1rem 0.4rem; font-size: 0.75rem; color: #EF4444; border: none;\">&times;</button>
                    </div>
                `;
            });
        }
        
    } catch(e) {}
}

async function loadNotes() {
    try {
        const res = await App.apiRequest(`/api/utilities?action=getNotes&trip_id=\${tripId}`);
        const list = document.getElementById('notesList');
        list.innerHTML = res.data.map(note => `
            <div class=\"note-item\">
                <div style=\"display:flex; justify-content:space-between; margin-bottom:0.5rem;\">
                    <small style=\"color: var(--text-muted);\">\${new Date(note.created_at).toLocaleDateString()}</small>
                    <button onclick=\"deleteNote(\${note.id})\" class=\"btn btn-outline\" style=\"padding: 0 0.4rem; font-size: 0.75rem; color: #EF4444; border: none;\">&times;</button>
                </div>
                <div class=\"note-content\" contenteditable=\"true\" onblur=\"updateNote(\${note.id}, this.innerText)\">\${note.content}</div>
            </div>
        `).join('');
    } catch(e) {}
}

window.toggleItem = async (id, isPacked) => {
    try {
        await App.apiRequest('/api/utilities?action=togglePacking', 'POST', { id, is_packed: isPacked });
        loadPacking();
    } catch(e) { App.showToast('Failed to update', 'error'); }
};

window.deleteItem = async (id) => {
    if(!confirm('Delete this item?')) return;
    try {
        await App.apiRequest(`/api/utilities?action=deletePacking&id=\${id}`, 'DELETE');
        loadPacking();
    } catch(e) { App.showToast('Failed to delete', 'error'); }
};

window.resetChecklist = async () => {
    if(!confirm('Are you sure you want to uncheck all items?')) return;
    try {
        await App.apiRequest('/api/utilities?action=resetPacking', 'POST', { trip_id: tripId });
        loadPacking();
        App.showToast('Checklist reset', 'success');
    } catch(e) { App.showToast('Failed to reset', 'error'); }
};

window.deleteNote = async (id) => {
    if(!confirm('Delete this note?')) return;
    try {
        await App.apiRequest(`/api/utilities?action=deleteNote&id=\${id}`, 'DELETE');
        loadNotes();
        App.showToast('Note deleted', 'success');
    } catch(e) { App.showToast('Failed to delete note', 'error'); }
};

window.updateNote = async (id, newContent) => {
    const text = newContent.trim();
    if(text.length === 0) return;
    if(text.length > 500) {
        App.showToast('Note exceeds 500 characters', 'error');
        loadNotes();
        return;
    }
    try {
        await App.apiRequest('/api/utilities?action=updateNote', 'POST', { id, content: text });
        // Optional toast
    } catch(e) { App.showToast('Failed to update note', 'error'); }
};

document.getElementById('packingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        await App.apiRequest('/api/utilities?action=addPacking', 'POST', {
            trip_id: tripId, 
            item_name: document.getElementById('packItem').value,
            category: document.getElementById('packCategory').value
        });
        e.target.reset();
        loadPacking();
    } catch(e) { App.showToast('Failed to add item', 'error'); }
});

const noteInput = document.getElementById('noteContent');
const charCount = document.getElementById('charCount');
noteInput.addEventListener('input', () => {
    charCount.innerText = noteInput.value.length;
});

document.getElementById('noteForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        await App.apiRequest('/api/utilities?action=addNote', 'POST', {
            trip_id: tripId, content: noteInput.value
        });
        e.target.reset();
        charCount.innerText = '0';
        loadNotes();
    } catch(e) { App.showToast('Failed to add note', 'error'); }
});

document.addEventListener('DOMContentLoaded', () => {
    loadPacking();
    loadNotes();
});
";
include 'includes/components/footer.php'; 
?>
