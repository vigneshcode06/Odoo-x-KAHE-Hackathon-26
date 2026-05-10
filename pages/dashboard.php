<?php
// pages/dashboard.php
if(!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

$pageStyles = "
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-top: 2rem;
    }
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        padding: 1.5rem;
        text-align: center;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    .trips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .trip-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }
    .trip-card-image {
        height: 160px;
        background-color: var(--border-color);
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        margin: -1.5rem -1.5rem 1rem -1.5rem;
        background-size: cover;
        background-position: center;
    }
    .trip-card-content {
        flex: 1;
    }
    .trip-card-footer {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .rec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 3rem;
    }
    .rec-card {
        position: relative;
        height: 120px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: flex-end;
        padding: 1rem;
        color: white;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        overflow: hidden;
        transition: transform 0.2s ease;
    }
    .rec-card:hover {
        transform: scale(1.05);
    }
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px dashed var(--border-color);
    }
    .skeleton {
        animation: pulse 1.5s infinite;
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        border-radius: var(--radius-md);
    }
    @keyframes pulse {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(5px);
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        width: 100%;
        max-width: 500px;
    }
";

include 'includes/components/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>My Dashboard</h1>
            <p style="color: var(--text-muted);">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        <a href="/create-trip" class="btn btn-primary">+ Create New Trip</a>
    </div>

    <!-- Quick Stats -->
    <div class="stats-bar" id="statsBar">
        <div class="glass-card stat-card skeleton" style="height: 100px;"></div>
        <div class="glass-card stat-card skeleton" style="height: 100px;"></div>
        <div class="glass-card stat-card skeleton" style="height: 100px;"></div>
    </div>

    <h2 style="margin-bottom: 1rem;">My Trips</h2>
    <div id="tripsContainer" class="trips-grid">
        <!-- Skeleton Loaders -->
        <div class="glass-card trip-card skeleton" style="height: 300px;"></div>
        <div class="glass-card trip-card skeleton" style="height: 300px;"></div>
        <div class="glass-card trip-card skeleton" style="height: 300px;"></div>
    </div>

    <h2 style="margin-bottom: 1rem;">Budget Highlights</h2>
    <div id="budgetHighlights" class="trips-grid">
        <!-- Populated via JS -->
    </div>

    <h2 style="margin-bottom: 1rem;">Recommended Destinations</h2>
    <div class="rec-grid" id="recContainer">
        <!-- Populated via JS -->
    </div>
</div>

<!-- Edit Trip Modal -->
<div id="tripModal" class="modal">
    <div class="glass-card modal-content">
        <h3 style="margin-bottom: 1rem;">Edit Trip</h3>
        <form id="tripForm">
            <input type="hidden" id="editTripId">
            <div class="form-group">
                <label class="form-label">Trip Title</label>
                <input type="text" id="editTripTitle" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="editTripDesc" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Start Date</label>
                    <input type="date" id="editTripStart" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">End Date</label>
                    <input type="date" id="editTripEnd" class="form-control">
                </div>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="editTripPublic" style="width: 1rem; height: 1rem;">
                <label class="form-label" style="margin-bottom:0;">Public Trip</label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="glass-card modal-content" style="max-width: 400px; text-align: center;">
        <h3 style="margin-bottom: 1rem; color: #EF4444;">Delete Trip?</h3>
        <p style="margin-bottom: 1.5rem;">Are you sure you want to delete this trip? This action cannot be undone.</p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <button class="btn btn-outline" onclick="closeModals()">Cancel</button>
            <button class="btn btn-primary" style="background: #EF4444; color: white;" id="confirmDeleteBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<?php 
$pageScripts = "
let tripsData = [];
let deleteTripId = null;

async function loadDashboard() {
    loadStats();
    loadTrips();
    loadRecommendations();
}

async function loadStats() {
    try {
        const res = await App.apiRequest('/api/trips?action=getStats');
        const stats = res.data;
        document.getElementById('statsBar').innerHTML = `
            <div class=\"glass-card stat-card\">
                <div class=\"stat-value\">\${stats.total_trips}</div>
                <div style=\"color: var(--text-muted); font-size: 0.875rem;\">Total Trips</div>
            </div>
            <div class=\"glass-card stat-card\">
                <div class=\"stat-value\">\${stats.total_cities}</div>
                <div style=\"color: var(--text-muted); font-size: 0.875rem;\">Cities Visited</div>
            </div>
            <div class=\"glass-card stat-card\">
                <div class=\"stat-value\">\$\${parseFloat(stats.total_spent).toFixed(0)}</div>
                <div style=\"color: var(--text-muted); font-size: 0.875rem;\">Budget Spent</div>
            </div>
        `;
    } catch(e) {
        console.error('Failed to load stats', e);
    }
}

async function loadTrips() {
    const container = document.getElementById('tripsContainer');
    const budgetContainer = document.getElementById('budgetHighlights');
    try {
        const response = await App.apiRequest('/api/trips?action=list');
        tripsData = response.data;
        
        container.innerHTML = '';
        budgetContainer.innerHTML = '';
        
        if (tripsData.length === 0) {
            container.innerHTML = `
                <div class=\"empty-state\" style=\"grid-column: 1 / -1;\">
                    <svg width=\"64\" height=\"64\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"var(--border-color)\" stroke-width=\"2\" style=\"margin: 0 auto 1rem;\">
                        <path d=\"M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z\"></path>
                    </svg>
                    <h3>No trips planned yet</h3>
                    <p style=\"color: var(--text-muted); margin-bottom: 1.5rem;\">Start exploring the world by planning your first trip.</p>
                    <a href=\"/create-trip\" class=\"btn btn-primary\">Create a Trip</a>
                </div>
            `;
            return;
        }
        
        for (let trip of tripsData) {
            const startDate = trip.start_date ? new Date(trip.start_date).toLocaleDateString() : 'TBD';
            const endDate = trip.end_date ? new Date(trip.end_date).toLocaleDateString() : 'TBD';
            const imgStyle = trip.cover_image ? `background-image: url('\${trip.cover_image}')` : `background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%)`;
            
            const card = document.createElement('div');
            card.className = 'glass-card trip-card';
            card.innerHTML = `
                <div class=\"trip-card-image\" style=\"\${imgStyle}\"></div>
                <div class=\"trip-card-content\">
                    <h3 style=\"margin-bottom: 0.5rem;\">\${trip.title}</h3>
                    <p style=\"color: var(--text-muted); font-size: 0.875rem;\">\${startDate} - \${endDate}</p>
                </div>
                <div class=\"trip-card-footer\">
                    <a href=\"/itinerary?id=\${trip.id}\" class=\"btn btn-primary\" style=\"padding: 0.4rem 1rem;\">View</a>
                    <div style=\"display:flex; gap: 0.5rem;\">
                        <button class=\"btn btn-outline\" onclick='openEditModal(\${JSON.stringify(trip)})' style=\"padding: 0.4rem 0.8rem;\">Edit</button>
                        <button class=\"btn btn-outline\" onclick='openDeleteModal(\${trip.id})' style=\"padding: 0.4rem 0.8rem; color: #EF4444; border-color: #EF4444;\">Delete</button>
                    </div>
                </div>
            `;
            container.appendChild(card);

            // Fetch budget for highlight
            try {
                const bRes = await App.apiRequest(`/api/budget?action=get&trip_id=\${trip.id}`);
                const bCard = document.createElement('div');
                bCard.className = 'glass-card stat-card';
                bCard.innerHTML = `
                    <div style=\"font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;\">\${trip.title}</div>
                    <div class=\"stat-value\" style=\"color: var(--secondary);\">\$\${bRes.data.total_cost.toFixed(2)}</div>
                    <div style=\"color: var(--text-muted); font-size: 0.875rem;\">Estimated Cost</div>
                `;
                budgetContainer.appendChild(bCard);
            } catch(e) {}
        }
        
    } catch (error) {
        App.showToast('Failed to load trips', 'error');
        container.innerHTML = `<p style=\"color: red;\">Error loading trips.</p>`;
    }
}

function loadRecommendations() {
    const recs = [
        { city: 'Paris', country: 'France', style: 'linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%)' },
        { city: 'Tokyo', country: 'Japan', style: 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)' },
        { city: 'Bali', country: 'Indonesia', style: 'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)' },
        { city: 'Rome', country: 'Italy', style: 'linear-gradient(135deg, #fccb90 0%, #d57eeb 100%)' },
        { city: 'New York', country: 'USA', style: 'linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%)' },
        { city: 'Dubai', country: 'UAE', style: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }
    ];

    const container = document.getElementById('recContainer');
    container.innerHTML = recs.map(r => `
        <div class=\"rec-card\" style=\"background: \${r.style};\">
            <div style=\"position: relative; z-index: 2;\">
                <h3 style=\"color: #111827; margin: 0;\">\${r.city}</h3>
                <small style=\"color: rgba(17, 24, 39, 0.7);\">\${r.country}</small>
            </div>
        </div>
    `).join('');
}

function openEditModal(trip) {
    document.getElementById('editTripId').value = trip.id;
    document.getElementById('editTripTitle').value = trip.title;
    document.getElementById('editTripDesc').value = trip.description || '';
    document.getElementById('editTripStart').value = trip.start_date || '';
    document.getElementById('editTripEnd').value = trip.end_date || '';
    document.getElementById('editTripPublic').checked = trip.is_public == 1;
    document.getElementById('tripModal').classList.add('active');
}

function openDeleteModal(id) {
    deleteTripId = id;
    document.getElementById('deleteModal').classList.add('active');
}

function closeModals() {
    document.getElementById('tripModal').classList.remove('active');
    document.getElementById('deleteModal').classList.remove('active');
}

document.getElementById('tripForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        await App.apiRequest('/api/trips?action=update', 'POST', {
            id: document.getElementById('editTripId').value,
            title: document.getElementById('editTripTitle').value,
            description: document.getElementById('editTripDesc').value,
            start_date: document.getElementById('editTripStart').value,
            end_date: document.getElementById('editTripEnd').value,
            is_public: document.getElementById('editTripPublic').checked
        });
        closeModals();
        App.showToast('Trip updated!', 'success');
        loadTrips(); 
    } catch(error) {
        App.showToast(error.message, 'error');
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
    if(!deleteTripId) return;
    try {
        await App.apiRequest(`/api/trips?id=\${deleteTripId}`, 'DELETE');
        closeModals();
        App.showToast('Trip deleted.', 'success');
        loadDashboard();
    } catch(error) {
        App.showToast(error.message, 'error');
    }
});

document.addEventListener('DOMContentLoaded', loadDashboard);
";
include 'includes/components/footer.php'; 
?>
