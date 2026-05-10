<?php
// pages/public.php  — Read-only shared itinerary view (no auth required)
require_once 'config/db.php';
require_once 'src/Models/Trip.php';
require_once 'src/Models/Itinerary.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit;
}

$tripId = (int)$_GET['id'];
$tripModel = new Trip($pdo);
$itineraryModel = new Itinerary($pdo);

// Get the trip — must be public
$trip = $tripModel->getTripById($tripId); // no userId → any trip
if (!$trip || !$trip['is_public']) {
    http_response_code(404);
    echo "<h1>404 - This itinerary is private or does not exist.</h1>";
    exit;
}

$stops = $itineraryModel->getFullItinerary($tripId);

$totalCost = 0;
foreach ($stops as &$stop) {
    $stop['stop_cost'] = 0;
    foreach ($stop['activities'] as $act) {
        $totalCost += (float)$act['cost'];
        $stop['stop_cost'] += (float)$act['cost'];
    }
}
unset($stop);

$startDate = $trip['start_date'] ? date('M j, Y', strtotime($trip['start_date'])) : 'TBD';
$endDate   = $trip['end_date']   ? date('M j, Y', strtotime($trip['end_date']))   : 'TBD';

$pageStyles = "
    body { background: var(--bg-main); }
    .public-hero {
        height: 320px;
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 2rem;
        color: white;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    }
    .public-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
    }
    .public-hero-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    .public-hero-content {
        position: relative;
        z-index: 2;
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
    .activity-item {
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.5rem;
    }
    .summary-bar {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        padding: 1rem 1.5rem;
        border-left: 4px solid var(--primary);
    }
    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.15);
        color: white;
        border-radius: var(--radius-md);
        border: 1px solid rgba(255,255,255,0.3);
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        backdrop-filter: blur(4px);
        transition: background 0.2s;
    }
    .share-btn:hover { background: rgba(255,255,255,0.25); }
";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($trip['title']); ?> — Traveloop</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($trip['description'] ?? 'A shared travel itinerary on Traveloop.', 0, 160)); ?>">
    <!-- Open Graph for sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($trip['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($trip['description'] ?? 'Check out this travel itinerary on Traveloop.'); ?>">
    <?php if ($trip['cover_image']): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($trip['cover_image']); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style><?php echo $pageStyles; ?></style>
</head>
<body>

<!-- Hero Banner -->
<div class="public-hero">
    <?php if ($trip['cover_image']): ?>
    <div class="public-hero-bg" style="background-image: url('<?php echo htmlspecialchars($trip['cover_image']); ?>')"></div>
    <?php endif; ?>
    <div class="public-hero-content" style="width: 100%; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.5rem;">Shared Itinerary</div>
            <h1 style="margin: 0; font-size: clamp(1.5rem, 4vw, 2.5rem);"><?php echo htmlspecialchars($trip['title']); ?></h1>
            <p style="margin: 0.5rem 0 0; opacity: 0.85;"><?php echo $startDate; ?> — <?php echo $endDate; ?></p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <button class="share-btn" onclick="copyLink()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Share
            </button>
            <a href="/" class="share-btn">Plan My Own Trip →</a>
        </div>
    </div>
</div>

<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    <?php if ($trip['description']): ?>
    <p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 2rem;"><?php echo htmlspecialchars($trip['description']); ?></p>
    <?php endif; ?>

    <!-- Summary Bar -->
    <div class="glass-card summary-bar">
        <div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Stops</div>
            <strong style="font-size: 1.25rem;"><?php echo count($stops); ?></strong>
        </div>
        <div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Activities</div>
            <strong style="font-size: 1.25rem;"><?php echo array_sum(array_map(fn($s) => count($s['activities']), $stops)); ?></strong>
        </div>
        <div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Duration</div>
            <strong style="font-size: 1.25rem;">
                <?php
                if ($trip['start_date'] && $trip['end_date']) {
                    $days = (int)((strtotime($trip['end_date']) - strtotime($trip['start_date'])) / 86400) + 1;
                    echo $days . ' days';
                } else {
                    echo 'N/A';
                }
                ?>
            </strong>
        </div>
        <div style="margin-left: auto;">
            <div style="color: var(--text-muted); font-size: 0.875rem;">Total Budget</div>
            <strong style="font-size: 1.5rem; color: var(--primary);">$<?php echo number_format($totalCost, 2); ?></strong>
        </div>
    </div>

    <!-- Itinerary Timeline -->
    <?php if (empty($stops)): ?>
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <p>No stops have been added to this itinerary yet.</p>
        </div>
    <?php else: ?>
    <h2 style="margin-bottom: 0.5rem;">Itinerary</h2>
    <div class="timeline">
        <?php foreach ($stops as $i => $stop): ?>
        <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="glass-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div>
                        <h3 style="margin: 0;">
                            <span style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Stop <?php echo $i + 1; ?></span><br>
                            <?php echo htmlspecialchars($stop['city_name']); ?>
                        </h3>
                        <?php if ($stop['arrival_date'] || $stop['departure_date']): ?>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0;">
                            <?php
                            $arr = $stop['arrival_date'] ? date('M j, Y', strtotime($stop['arrival_date'])) : '';
                            $dep = $stop['departure_date'] ? date('M j, Y', strtotime($stop['departure_date'])) : '';
                            echo $arr ? $arr . ($dep ? ' – ' . $dep : '') : $dep;
                            ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.875rem; color: var(--text-muted);">Stop cost</span><br>
                        <strong style="color: var(--secondary);">$<?php echo number_format($stop['stop_cost'], 2); ?></strong>
                    </div>
                </div>

                <?php if (!empty($stop['activities'])): ?>
                <div style="margin-top: 0.75rem;">
                    <?php foreach ($stop['activities'] as $act): ?>
                    <div class="activity-item">
                        <div>
                            <strong style="display: block;"><?php echo htmlspecialchars($act['title']); ?></strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo $act['start_time'] ? date('h:i A', strtotime($act['start_time'])) : 'Time TBD'; ?>
                            </span>
                        </div>
                        <strong style="color: var(--secondary);">$<?php echo number_format((float)$act['cost'], 2); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">No activities listed.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA Footer -->
    <div class="glass-card" style="text-align: center; padding: 2rem; margin-top: 3rem;">
        <h2 style="margin-bottom: 0.5rem;">Love this itinerary?</h2>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Create your own travel plan on Traveloop — free, fast, and beautiful.</p>
        <a href="/signup" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">Get Started Free</a>
    </div>
</div>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.querySelector('.share-btn');
        btn.textContent = '✓ Copied!';
        setTimeout(() => { btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg> Share'; }, 2000);
    });
}
</script>
</body>
</html>
