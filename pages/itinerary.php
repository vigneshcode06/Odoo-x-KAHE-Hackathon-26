<?php
// pages/itinerary.php
if(!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: /dashboard");
    exit;
}

$tripId = $_GET['id'];

$pageStyles = "
    .trip-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .timeline {
        position: relative;
        padding-left: 2rem;
        margin-top: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-dot {
        position: absolute;
        left: -2.35rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid var(--bg-main);
    }
    .stop-card {
        margin-bottom: 1rem;
    }
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .activity-item {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        padding: 1rem;
        border-radius: var(--radius-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    /* Autocomplete Styles */
    .autocomplete-container {
        position: relative;
    }
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        margin-top: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1001;
        display: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .autocomplete-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
    }
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    .autocomplete-item:hover {
        background: var(--bg-main);
    }
";

include 'includes/components/header.php';
?>

<div class="container">
    <div class="trip-header" id="tripHeader">
        <!-- Trip details loaded here -->
        <div class="skeleton" style="width: 300px; height: 40px; border-radius: var(--radius-sm); margin-bottom: 0.5rem;"></div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Itinerary</h2>
        <button class="btn btn-primary" onclick="openStopModal()">+ Add Stop (City)</button>
    </div>

    <!-- Sticky Summary Box -->
    <div class="glass-card" style="position: sticky; top: 1rem; z-index: 900; margin-bottom: 2rem; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--primary);">
        <div style="display: flex; gap: 2rem;">
            <div><span style="color: var(--text-muted); font-size: 0.875rem;">Total Stops:</span> <strong id="sumStops">0</strong></div>
            <div><span style="color: var(--text-muted); font-size: 0.875rem;">Activities:</span> <strong id="sumActivities">0</strong></div>
            <div><span style="color: var(--text-muted); font-size: 0.875rem;">Duration:</span> <strong id="sumDuration">0 days</strong></div>
        </div>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
            Total: $<span id="sumCost">0.00</span>
        </div>
    </div>

    <div class="timeline" id="itineraryTimeline">
        <!-- Itinerary loaded here -->
    </div>
</div>

<!-- Custom Confirm Modal -->
<div id="confirmModal" class="modal">
    <div class="glass-card modal-content" style="max-width: 400px; text-align: center;">
        <h3 id="confirmTitle" style="margin-bottom: 1rem; color: #EF4444;">Confirm Action</h3>
        <p id="confirmMessage" style="margin-bottom: 1.5rem;">Are you sure?</p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <button class="btn btn-outline" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-primary" style="background: #EF4444; color: white;" id="confirmActionBtn">Yes</button>
        </div>
    </div>
</div>


<!-- Edit Trip Modal -->
<div id="tripModal" class="modal">
    <div class="glass-card modal-content">
        <h3 style="margin-bottom: 1rem;">Edit Trip Details</h3>
        <form id="tripForm">
            <input type="hidden" id="editTripId" value="<?php echo htmlspecialchars($tripId); ?>">
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


<!-- Stop Modal -->
<div id="stopModal" class="modal">
    <div class="glass-card modal-content">
        <h3 style="margin-bottom: 1rem;" id="stopModalTitle">Add a Stop</h3>
        <form id="stopForm">
            <input type="hidden" id="stopId">
            <input type="hidden" id="stopTripId" value="<?php echo htmlspecialchars($tripId); ?>">
            <div class="form-group autocomplete-container">
                <label class="form-label">City Name</label>
                <input type="text" id="cityName" class="form-control" required autocomplete="off">
                <div id="cityDropdown" class="autocomplete-dropdown"></div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Arrival Date</label>
                    <input type="date" id="arrivalDate" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Departure Date</label>
                    <input type="date" id="departureDate" class="form-control">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Stop</button>
            </div>
        </form>
    </div>
</div>

<!-- Activity Modal -->
<div id="activityModal" class="modal">
    <div class="glass-card modal-content">
        <h3 style="margin-bottom: 1rem;" id="activityModalTitle">Add Activity</h3>
        <form id="activityForm">
            <input type="hidden" id="activityId">
            <input type="hidden" id="activityStopId">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" id="activityTitle" class="form-control" required placeholder="E.g., Eiffel Tower Visit">
            </div>
            <div class="form-group">
                <label class="form-label">Start Time</label>
                <input type="datetime-local" id="activityTime" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Estimated Cost ($)</label>
                <input type="number" id="activityCost" class="form-control" step="0.01">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Activity</button>
            </div>
        </form>
    </div>
</div>

<?php 
$pageScripts = "
const tripId = " . json_encode($tripId) . ";
let currentTrip = null;
let currentStops = [];

async function loadTripData() {
    try {
        const tripRes = await App.apiRequest(`/api/trips?action=get&id=\${tripId}`);
        currentTrip = tripRes.data;
        
        document.getElementById('tripHeader').innerHTML = `
            <div>
                <h1 style=\"margin-bottom: 0.5rem;\">\${currentTrip.title}</h1>
                <p style=\"color: var(--text-muted);\">\${currentTrip.description || 'No description'}</p>
            </div>
            <div style=\"text-align: right; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;\">
                <span class=\"badge\" style=\"background: var(--bg-card); padding: 0.4rem 1rem; border-radius: var(--radius-lg); font-size: 0.875rem; border: 1px solid var(--border-color);\">
                    \${currentTrip.is_public ? 'Public' : 'Private'} Trip
                </span>
                <div style=\"display: flex; gap: 0.5rem;\">
                    <button onclick=\"openEditTripModal()\" class=\"btn btn-outline\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem;\">Edit Trip</button>
                    <a href=\"/budget?id=\${tripId}\" class=\"btn btn-outline\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem;\">View Budget</a>
                    <a href=\"/trip-tools?id=\${tripId}\" class=\"btn btn-outline\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem;\">Tools &amp; Notes &rarr;</a>
                    \${currentTrip.is_public == 1 ? `<button onclick=\"sharePublicLink()\" class=\"btn btn-outline\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem;\">Share &#128279;</button>` : ''}
                </div>
            </div>
        `;
        
        loadItinerary();
    } catch (error) {
        App.showToast('Error loading trip details', 'error');
    }
}

async function loadItinerary() {
    try {
        const res = await App.apiRequest(`/api/itinerary?action=getFull&trip_id=\${tripId}`);
        currentStops = res.data;
        const timeline = document.getElementById('itineraryTimeline');
        
        if (currentStops.length === 0) {
            timeline.innerHTML = `<p style=\"color: var(--text-muted);\">No stops added yet. Click 'Add Stop' to begin building your itinerary.</p>`;
            document.getElementById('sumStops').innerText = '0';
            document.getElementById('sumActivities').innerText = '0';
            document.getElementById('sumDuration').innerText = '0 days';
            document.getElementById('sumCost').innerText = '0.00';
            return;
        }
        
        timeline.innerHTML = '';
        let totalCost = 0;
        let totalActivities = 0;

        currentStops.forEach((stop, index) => {
            let stopCost = 0;
            let activitiesHtml = stop.activities.map(act => {
                totalActivities++;
                totalCost += parseFloat(act.cost);
                stopCost += parseFloat(act.cost);
                return `
                <div class=\"activity-item\">
                    <div>
                        <strong style=\"display: block;\">\${act.title}</strong>
                        <span style=\"font-size: 0.875rem; color: var(--text-muted);\">
                            \${act.start_time ? new Date(act.start_time).toLocaleString([], {hour: '2-digit', minute:'2-digit'}) : 'Time TBD'}
                        </span>
                    </div>
                    <div style=\"display: flex; align-items: center; gap: 1rem;\">
                        <span style=\"font-weight: 500; color: var(--secondary);\">\$\${act.cost}</span>
                        <button onclick='openEditActivityModal(\${JSON.stringify(act)})' class=\"btn btn-outline\" style=\"padding: 0.2rem 0.5rem; font-size: 0.75rem;\">Edit</button>
                        <button onclick='confirmDelete(\"activity\", \${act.id})' class=\"btn btn-outline\" style=\"padding: 0.2rem 0.5rem; font-size: 0.75rem; color: #EF4444; border-color: #EF4444;\">&times;</button>
                    </div>
                </div>
            `}).join('');
            
            const arrDate = stop.arrival_date ? new Date(stop.arrival_date).toLocaleDateString() : '';
            const depDate = stop.departure_date ? new Date(stop.departure_date).toLocaleDateString() : '';
            const dateStr = arrDate ? `\${arrDate} \${depDate ? '- ' + depDate : ''}` : 'Dates TBD';

            // Reorder buttons logic
            const upBtn = index > 0 ? `<button onclick='moveStop(\${index}, -1)' class=\"btn btn-outline\" style=\"padding: 0.1rem 0.4rem; font-size: 0.75rem; border: none;\">&uarr;</button>` : '';
            const downBtn = index < currentStops.length - 1 ? `<button onclick='moveStop(\${index}, 1)' class=\"btn btn-outline\" style=\"padding: 0.1rem 0.4rem; font-size: 0.75rem; border: none;\">&darr;</button>` : '';

            timeline.innerHTML += `
                <div class=\"timeline-item\">
                    <div class=\"timeline-dot\"></div>
                    <div class=\"glass-card stop-card\">
                        <div style=\"display: flex; justify-content: space-between; align-items: center;\">
                            <div>
                                <h3 style=\"display: flex; align-items: center; gap: 0.5rem;\">
                                    \${stop.city_name}
                                    <div style=\"display: flex;\">
                                        \${upBtn}
                                        \${downBtn}
                                    </div>
                                    <button onclick='openEditStopModal(\${JSON.stringify(stop)})' class=\"btn btn-outline\" style=\"padding: 0.1rem 0.4rem; font-size: 0.75rem; border: none; text-decoration: underline;\">Edit Stop</button>
                                </h3>
                                <p style=\"color: var(--text-muted); font-size: 0.875rem;\">\${dateStr} | Stop Cost: $\${stopCost.toFixed(2)}</p>
                            </div>
                            <div style=\"display: flex; gap: 0.5rem;\">
                                <button class=\"btn btn-outline\" onclick=\"openActivityModal(\${stop.id})\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem;\">+ Activity</button>
                                <button class=\"btn btn-outline\" onclick=\"confirmDelete('stop', \${stop.id})\" style=\"padding: 0.3rem 0.8rem; font-size: 0.875rem; color: #EF4444; border-color: #EF4444;\">Delete Stop</button>
                            </div>
                        </div>
                        <div class=\"activity-list\">
                            \${activitiesHtml}
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Update Summary
        document.getElementById('sumStops').innerText = currentStops.length;
        document.getElementById('sumActivities').innerText = totalActivities;
        document.getElementById('sumCost').innerText = totalCost.toFixed(2);
        
        // Calculate duration based on trip start/end date
        let durationStr = '0 days';
        if (currentTrip.start_date && currentTrip.end_date) {
            const start = new Date(currentTrip.start_date);
            const end = new Date(currentTrip.end_date);
            const days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
            durationStr = `\${days} days`;
        }
        document.getElementById('sumDuration').innerText = durationStr;
        
    } catch(error) {
        App.showToast('Error loading itinerary', 'error');
    }
}

function openEditTripModal() {
    document.getElementById('editTripTitle').value = currentTrip.title;
    document.getElementById('editTripDesc').value = currentTrip.description || '';
    document.getElementById('editTripStart').value = currentTrip.start_date || '';
    document.getElementById('editTripEnd').value = currentTrip.end_date || '';
    document.getElementById('editTripPublic').checked = currentTrip.is_public == 1;
    document.getElementById('tripModal').classList.add('active');
}

function openStopModal() { 
    document.getElementById('stopForm').reset();
    document.getElementById('stopId').value = '';
    document.getElementById('stopModalTitle').innerText = 'Add a Stop';
    document.getElementById('stopModal').classList.add('active'); 
}

function openEditStopModal(stop) {
    document.getElementById('stopForm').reset();
    document.getElementById('stopId').value = stop.id;
    document.getElementById('cityName').value = stop.city_name;
    document.getElementById('arrivalDate').value = stop.arrival_date || '';
    document.getElementById('departureDate').value = stop.departure_date || '';
    document.getElementById('stopModalTitle').innerText = 'Edit Stop';
    document.getElementById('stopModal').classList.add('active');
}

function openActivityModal(stopId) { 
    document.getElementById('activityForm').reset();
    document.getElementById('activityId').value = '';
    document.getElementById('activityStopId').value = stopId;
    document.getElementById('activityModalTitle').innerText = 'Add Activity';
    document.getElementById('activityModal').classList.add('active'); 
}

function openEditActivityModal(act) {
    document.getElementById('activityForm').reset();
    document.getElementById('activityId').value = act.id;
    document.getElementById('activityStopId').value = act.stop_id;
    document.getElementById('activityTitle').value = act.title;
    document.getElementById('activityTime').value = act.start_time ? act.start_time.slice(0, 16) : ''; // format for datetime-local
    document.getElementById('activityCost').value = act.cost;
    document.getElementById('activityModalTitle').innerText = 'Edit Activity';
    document.getElementById('activityModal').classList.add('active');
}

function sharePublicLink() {
    const url = `\${window.location.origin}/public?id=\${tripId}`;
    navigator.clipboard.writeText(url).then(() => {
        App.showToast('Public link copied to clipboard!', 'success');
    }).catch(() => {
        prompt('Copy this link:', url);
    });
}

function closeModals() { 
    document.getElementById('tripModal').classList.remove('active'); 
    document.getElementById('stopModal').classList.remove('active'); 
    document.getElementById('activityModal').classList.remove('active'); 
    closeConfirmModal();
}

// Delete Logic
let deleteTarget = null;
let deleteId = null;

function confirmDelete(type, id) {
    deleteTarget = type;
    deleteId = id;
    document.getElementById('confirmTitle').innerText = type === 'stop' ? 'Delete Stop?' : 'Delete Activity?';
    document.getElementById('confirmMessage').innerText = type === 'stop' ? 'Deleting this stop will also delete all its activities. Are you sure?' : 'Are you sure you want to delete this activity?';
    document.getElementById('confirmModal').classList.add('active');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

document.getElementById('confirmActionBtn').addEventListener('click', async () => {
    if(!deleteTarget || !deleteId) return;
    try {
        const action = deleteTarget === 'stop' ? 'deleteStop' : 'deleteActivity';
        await App.apiRequest(`/api/itinerary?action=\${action}&id=\${deleteId}`, 'DELETE');
        closeConfirmModal();
        App.showToast(`\${deleteTarget === 'stop' ? 'Stop' : 'Activity'} deleted`, 'success');
        loadItinerary();
    } catch(error) {
        App.showToast(error.message, 'error');
    }
});

// Reorder Logic
async function moveStop(index, direction) {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= currentStops.length) return;
    
    // Swap in array
    const temp = currentStops[index];
    currentStops[index] = currentStops[newIndex];
    currentStops[newIndex] = temp;
    
    const stopsOrder = currentStops.map(s => s.id);
    
    try {
        await App.apiRequest('/api/itinerary?action=reorderStops', 'POST', {
            stops_order: stopsOrder
        });
        loadItinerary();
    } catch(error) {
        App.showToast('Failed to reorder stops', 'error');
        loadItinerary(); // reload from server on fail
    }
}

document.getElementById('tripForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        await App.apiRequest('/api/trips?action=update', 'POST', {
            id: tripId,
            title: document.getElementById('editTripTitle').value,
            description: document.getElementById('editTripDesc').value,
            start_date: document.getElementById('editTripStart').value,
            end_date: document.getElementById('editTripEnd').value,
            is_public: document.getElementById('editTripPublic').checked
        });
        closeModals();
        App.showToast('Trip updated!', 'success');
        loadTripData(); // refresh
    } catch(error) {
        App.showToast(error.message, 'error');
    }
});

let isSubmittingStop = false; // guard against double-fire

document.getElementById('stopForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (isSubmittingStop) return; // prevent double submit
    
    const cityNameVal = document.getElementById('cityName').value.trim();
    if (!cityNameVal) {
        App.showToast('Please enter a city name', 'error');
        return;
    }
    
    const stopId = document.getElementById('stopId').value;
    const arrDateStr = document.getElementById('arrivalDate').value;
    const depDateStr = document.getElementById('departureDate').value;
    
    // Date Conflict Checking
    if (arrDateStr || depDateStr) {
        const start = arrDateStr ? new Date(arrDateStr) : new Date(depDateStr);
        const end = depDateStr ? new Date(depDateStr) : new Date(arrDateStr);
        
        let hasConflict = false;
        for (let stop of currentStops) {
            if (stopId && stop.id == stopId) continue;
            if (stop.arrival_date || stop.departure_date) {
                const sStart = stop.arrival_date ? new Date(stop.arrival_date) : new Date(stop.departure_date);
                const sEnd = stop.departure_date ? new Date(stop.departure_date) : new Date(stop.arrival_date);
                if (start <= sEnd && end >= sStart) { hasConflict = true; break; }
            }
        }
        if (hasConflict) {
            const proceed = confirm('Warning: This date overlaps with another stop. Continue anyway?');
            if (!proceed) return;
        }
    }
    
    const saveBtn = e.target.querySelector('button[type=\'submit\']');
    isSubmittingStop = true;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    try {
        const action = stopId ? 'updateStop' : 'addStop';
        await App.apiRequest(`/api/itinerary?action=\${action}`, 'POST', {
            id: stopId,
            trip_id: tripId,
            city_name: cityNameVal,
            arrival_date: arrDateStr,
            departure_date: depDateStr
        });
        closeModals();
        App.showToast('Stop saved!', 'success');
        loadItinerary();
    } catch(error) {
        App.showToast(error.message || 'Failed to save stop', 'error');
    } finally {
        isSubmittingStop = false;
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Stop';
    }
});

document.getElementById('activityForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const actId = document.getElementById('activityId').value;
    try {
        const action = actId ? 'updateActivity' : 'addActivity';
        await App.apiRequest(`/api/itinerary?action=\${action}`, 'POST', {
            id: actId,
            stop_id: document.getElementById('activityStopId').value,
            title: document.getElementById('activityTitle').value,
            start_time: document.getElementById('activityTime').value,
            cost: document.getElementById('activityCost').value
        });
        closeModals();
        App.showToast('Activity saved!', 'success');
        loadItinerary();
    } catch(error) {
        App.showToast(error.message, 'error');
    }
});

// ── Autocomplete (free-text friendly) ─────────────────────────────────────
let searchTimeout = null;
const cityInput = document.getElementById('cityName');
const cityDropdown = document.getElementById('cityDropdown');

cityInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        cityDropdown.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            const res = await App.apiRequest(`/api/cities?action=search&q=\${encodeURIComponent(query)}`);
            if (res.data && res.data.length > 0) {
                cityDropdown.innerHTML = res.data.map(city => `
                    <div class=\"autocomplete-item\" onclick=\"selectCity('\${city.name.replace(/'/g, \"\\\\'\")}')\">
                        <div>
                            <strong>\${city.name}</strong><br>
                            <small style=\"color: var(--text-muted);\">\${city.country}</small>
                        </div>
                        <span style=\"color: var(--primary); font-weight: 600;\">\${city.cost_index}</span>
                    </div>
                `).join('');
                cityDropdown.style.display = 'block';
            } else {
                // Hide instead of showing 'No matches' — user can still type freely
                cityDropdown.style.display = 'none';
            }
        } catch (error) {
            cityDropdown.style.display = 'none';
        }
    }, 300);
});

// Escape key closes dropdown
cityInput.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cityDropdown.style.display = 'none';
    }
});

// Click on suggestion fills input — does NOT submit
function selectCity(name) {
    cityInput.value = name;
    cityDropdown.style.display = 'none';
    cityInput.focus();
}

// Click outside closes dropdown
document.addEventListener('click', (e) => {
    if (!e.target.closest('.autocomplete-container')) {
        cityDropdown.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', loadTripData);
";
$hideFooter = true;
include 'includes/components/footer.php'; 
?>
