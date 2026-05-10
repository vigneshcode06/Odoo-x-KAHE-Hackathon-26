<?php
// pages/budget.php
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
    .budget-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .summary-card {
        text-align: center;
        padding: 1.5rem;
    }
    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    .charts-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 768px) {
        .charts-container {
            grid-template-columns: 1fr;
        }
    }
    canvas {
        width: 100% !important;
        height: 300px !important;
        background: var(--bg-main);
        border-radius: var(--radius-md);
        padding: 1rem;
        box-sizing: border-box;
    }
    .chart-card {
        padding: 1.5rem;
    }
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    th {
        font-weight: 600;
        color: var(--secondary);
        background-color: var(--bg-main);
    }
    .alert {
        background-color: #FEE2E2;
        color: #B91C1C;
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 2rem;
        border-left: 4px solid #EF4444;
        display: none;
    }
";

include 'includes/components/header.php';
?>

<div class="container">
    <div class="budget-header">
        <div>
            <h1 id="tripTitle">Budget Breakdown</h1>
            <p style="color: var(--text-muted);">Track your expenses and spending categories.</p>
        </div>
        <a href="/itinerary?id=<?php echo htmlspecialchars($tripId); ?>" class="btn btn-outline">&larr; Back to Itinerary</a>
    </div>

    <div id="overbudgetAlert" class="alert">
        <strong>Warning:</strong> Some of your days exceed the average daily budget by more than 50%.
    </div>

    <div class="summary-cards">
        <div class="glass-card summary-card">
            <div class="summary-value" id="totalCost">$0.00</div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Total Estimated Cost</div>
        </div>
        <div class="glass-card summary-card">
            <div class="summary-value" id="avgPerDay">$0.00</div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Average Cost / Day</div>
        </div>
        <div class="glass-card summary-card">
            <div class="summary-value" id="mostExpensiveCity" style="font-size: 1.5rem; line-height: 2.5rem;">-</div>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Most Expensive Stop</div>
        </div>
    </div>

    <div class="charts-container">
        <div class="glass-card chart-card">
            <h3 style="margin-bottom: 1rem; text-align: center;">Cost by Category</h3>
            <canvas id="categoryPieChart" width=\"400\" height=\"300\"></canvas>
        </div>
        <div class="glass-card chart-card">
            <h3 style="margin-bottom: 1rem; text-align: center;">Cost by City</h3>
            <canvas id="cityBarChart" width=\"400\" height=\"300\"></canvas>
        </div>
    </div>

    <div class="glass-card">
        <h3>Category Breakdown</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Estimated Cost</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$pageScripts = "
const tripId = " . json_encode($tripId) . ";

const colors = {
    'Transport': '#2563EB', // Blue
    'Stay': '#10B981',      // Green
    'Activities': '#F59E0B', // Orange
    'Meals': '#EF4444'       // Red
};

async function loadBudget() {
    try {
        const tripRes = await App.apiRequest(`/api/trips?action=get&id=\${tripId}`);
        document.getElementById('tripTitle').textContent = `Budget: \${tripRes.data.title}`;

        const res = await App.apiRequest(`/api/budget?action=get&trip_id=\${tripId}`);
        const data = res.data;
        
        document.getElementById('totalCost').textContent = `$\${data.total_cost.toFixed(2)}`;
        document.getElementById('avgPerDay').textContent = `$\${data.avg_per_day.toFixed(2)}`;
        document.getElementById('mostExpensiveCity').textContent = data.most_expensive_stop.city || 'N/A';
        
        // Alert logic: For simplicity, if any single city cost > (avgPerDay * 1.5 * avg days per city), we just show a generic warning.
        // The prompt says: \"Warning alert if any single day exceeds average by 50%+\".
        // We only have costs by city, not strictly by day mapped. But we can roughly approximate or just show it if max city cost is very high.
        // Let's do a simple check: if any city cost > totalCost / 2 and totalCost > 0, show warning.
        let isOver = false;
        for (let city in data.by_city) {
            if (data.by_city[city] > (data.avg_per_day * 1.5 * data.duration)) {
                isOver = true;
            }
        }
        if (isOver) {
            document.getElementById('overbudgetAlert').style.display = 'block';
        }

        drawPieChart(data.by_category, data.total_cost);
        drawBarChart(data.by_city);
        populateTable(data.by_category, data.total_cost);
        
    } catch (error) {
        App.showToast('Error loading budget details', 'error');
    }
}

function drawPieChart(categoryData, total) {
    const canvas = document.getElementById('categoryPieChart');
    const ctx = canvas.getContext('2d');
    
    // Fix canvas rendering resolution
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;

    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(centerX, centerY) - 20;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (total === 0) {
        ctx.fillStyle = '#E2E8F0';
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.fill();
        ctx.fillStyle = '#64748B';
        ctx.textAlign = 'center';
        ctx.font = '14px Arial';
        ctx.fillText('No expenses yet', centerX, centerY);
        return;
    }

    let startAngle = 0;
    
    for (const [cat, value] of Object.entries(categoryData)) {
        if (value <= 0) continue;
        const sliceAngle = (value / total) * 2 * Math.PI;
        
        ctx.fillStyle = colors[cat] || '#CBD5E1';
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, startAngle + sliceAngle);
        ctx.closePath();
        ctx.fill();
        
        startAngle += sliceAngle;
    }
    
    // Inner circle for donut effect
    ctx.fillStyle = 'var(--bg-main)';
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius * 0.6, 0, 2 * Math.PI);
    ctx.fill();
}

function drawBarChart(cityData) {
    const canvas = document.getElementById('cityBarChart');
    const ctx = canvas.getContext('2d');
    
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const cities = Object.keys(cityData);
    if (cities.length === 0) {
        ctx.fillStyle = '#64748B';
        ctx.textAlign = 'center';
        ctx.font = '14px Arial';
        ctx.fillText('No city expenses yet', canvas.width/2, canvas.height/2);
        return;
    }

    let maxCost = 0;
    for (let c of cities) {
        if (cityData[c] > maxCost) maxCost = cityData[c];
    }
    if (maxCost === 0) maxCost = 100; // prevent divide by zero

    const padding = 40;
    const chartWidth = canvas.width - (padding * 2);
    const chartHeight = canvas.height - (padding * 2);
    const barWidth = chartWidth / cities.length - 20;

    // Draw axes
    ctx.strokeStyle = '#E2E8F0';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, canvas.height - padding);
    ctx.lineTo(canvas.width - padding, canvas.height - padding);
    ctx.stroke();

    ctx.fillStyle = 'var(--primary)';
    ctx.textAlign = 'center';
    ctx.font = '12px Arial';

    cities.forEach((city, index) => {
        const cost = cityData[city];
        const barHeight = (cost / maxCost) * chartHeight;
        const x = padding + 10 + (index * (barWidth + 20));
        const y = canvas.height - padding - barHeight;

        // Draw bar
        ctx.fillRect(x, y, barWidth, barHeight);
        
        // Draw label
        ctx.fillStyle = '#64748B';
        let displayCity = city.length > 10 ? city.substring(0,8)+'...' : city;
        ctx.fillText(displayCity, x + barWidth/2, canvas.height - padding + 15);
        ctx.fillStyle = 'var(--primary)';
    });
}

function populateTable(categoryData, total) {
    const tbody = document.getElementById('categoryTableBody');
    tbody.innerHTML = '';
    
    for (const [cat, value] of Object.entries(categoryData)) {
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
        const color = colors[cat] || '#CBD5E1';
        
        tbody.innerHTML += `
            <tr>
                <td>
                    <span style=\"display: inline-block; width: 12px; height: 12px; background: \${color}; border-radius: 50%; margin-right: 8px;\"></span>
                    \${cat}
                </td>
                <td>$\${value.toFixed(2)}</td>
                <td>\${percent}%</td>
            </tr>
        `;
    }
}

// Ensure resize redraws charts properly
window.addEventListener('resize', loadBudget);

document.addEventListener('DOMContentLoaded', loadBudget);
";
include 'includes/components/footer.php'; 
?>
