<?php
// pages/public-trip.php
if(!isset($_GET['id'])) {
    header("Location: /");
    exit;
}

$tripId = $_GET['id'];

$pageStyles = "
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
    .cover-image {
        height: 300px;
        background-color: var(--primary);
        background-size: cover;
        background-position: center;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-end;
        padding: 2rem;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
";

include 'includes/components/header.php';
?>

<div class="container" style="max-width: 800px; padding: 2rem 1.5rem;">
    <div id="publicContent">
        <div class="skeleton" style="height: 300px; border-radius: var(--radius-lg); margin-bottom: 2rem;"></div>
        <div class="skeleton" style="height: 100px; border-radius: var(--radius-md); margin-bottom: 1rem;"></div>
    </div>
</div>

<?php 
$pageScripts = "
const tripId = " . json_encode($tripId) . ";

async function loadPublicTrip() {
    try {
        // Load Trip Info
        const tripRes = await App.apiRequest(`/api/trips?action=get&id=\${tripId}`);
        const trip = tripRes.data;
        
        if (!trip.is_public && (!window.sessionStorage || trip.user_id != '$_SESSION[user_id]')) {
            document.getElementById('publicContent').innerHTML = `
                <div style=\"text-align: center; padding: 4rem 0;\">
                    <h2>This trip is private.</h2>
                    <p style=\"color: var(--text-muted);\">You don't have permission to view it.</p>
                </div>
            `;
            return;
        }

        let bgStyle = trip.cover_image ? `background-image: linear-gradient(to top, rgba(0,0,0,0.8), transparent), url('\${trip.cover_image}')` : `background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%)`;

        let html = `
            <div class=\"cover-image\" style=\"\${bgStyle}\">
                <div>
                    <h1 style=\"color: white; margin-bottom: 0.5rem;\">\${trip.title}</h1>
                    <p style=\"font-size: 1.125rem;\">\${trip.description || ''}</p>
                </div>
            </div>
            <h2>Itinerary</h2>
            <div class=\"timeline\" id=\"timelineContainer\">
                <!-- skeleton or content -->
            </div>
        `;
        
        document.getElementById('publicContent').innerHTML = html;
        
        // Load Itinerary
        const itRes = await App.apiRequest(`/api/itinerary?action=getFull&trip_id=\${tripId}`);
        const stops = itRes.data;
        const timeline = document.getElementById('timelineContainer');
        
        if (stops.length === 0) {
            timeline.innerHTML = `<p style=\"color: var(--text-muted);\">No itinerary details added yet.</p>`;
            return;
        }
        
        timeline.innerHTML = stops.map(stop => {
            let activitiesHtml = stop.activities.map(act => `
                <div class=\"activity-item\">
                    <div>
                        <strong style=\"display: block;\">\${act.title}</strong>
                        <span style=\"font-size: 0.875rem; color: var(--text-muted);\">
                            \${act.start_time ? new Date(act.start_time).toLocaleString([], {hour: '2-digit', minute:'2-digit'}) : 'Time TBD'}
                        </span>
                    </div>
                </div>
            `).join('');
            
            const arrDate = stop.arrival_date ? new Date(stop.arrival_date).toLocaleDateString() : '';
            return `
                <div class=\"timeline-item\">
                    <div class=\"timeline-dot\"></div>
                    <div class=\"glass-card\">
                        <h3 style=\"margin-bottom: 0.25rem;\">\${stop.city_name}</h3>
                        <p style=\"color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;\">\${arrDate}</p>
                        <div class=\"activity-list\">
                            \${activitiesHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
    } catch (error) {
        document.getElementById('publicContent').innerHTML = `
            <div style=\"text-align: center; padding: 4rem 0;\">
                <h2>Trip not found.</h2>
                <p style=\"color: var(--text-muted);\">The link might be broken or the trip was deleted.</p>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', loadPublicTrip);
";
include 'includes/components/footer.php'; 
?>
