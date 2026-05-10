<?php
// pages/index.php
// Landing Page

// Additional styles specific to the landing page
$pageStyles = "
    .hero {
        padding: 6rem 0;
        text-align: center;
        background: linear-gradient(135deg, rgba(37,99,235,0.05) 0%, rgba(56,189,248,0.1) 100%);
        border-bottom: 1px solid var(--border-color);
    }
    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        color: var(--secondary);
    }
    .hero p {
        font-size: 1.25rem;
        color: var(--text-muted);
        max-width: 600px;
        margin: 0 auto 2.5rem;
    }
    .features {
        padding: 5rem 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }
    .feature-card {
        text-align: center;
        padding: 2.5rem 1.5rem;
    }
    .feature-icon {
        background: var(--primary);
        color: white;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.5rem;
    }
    .feature-card h3 {
        margin-bottom: 1rem;
    }
";

include 'includes/components/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Plan Your Perfect Trip with <span style="color: var(--primary);">Traveloop</span></h1>
        <p>The smart, modern way to organize itineraries, manage budgets, and keep track of your travel plans all in one place.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="/signup" class="btn btn-primary" style="font-size: 1.125rem; padding: 0.8rem 2rem;">Get Started for Free</a>
            <a href="#features" class="btn btn-outline" style="font-size: 1.125rem; padding: 0.8rem 2rem;">Learn More</a>
        </div>
    </div>
</section>

<section id="features" class="container features">
    <div class="glass-card feature-card">
        <div class="feature-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        </div>
        <h3>Smart Itineraries</h3>
        <p style="color: var(--text-muted);">Build detailed day-by-day timelines for your trip, add activities, and reorder them with ease.</p>
    </div>
    
    <div class="glass-card feature-card">
        <div class="feature-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
        </div>
        <h3>Budget Management</h3>
        <p style="color: var(--text-muted);">Track your estimated vs actual expenses. Know exactly where your money goes during the trip.</p>
    </div>
    
    <div class="glass-card feature-card">
        <div class="feature-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
        </div>
        <h3>Packing Checklists</h3>
        <p style="color: var(--text-muted);">Never forget essential items again. Create custom packing lists and check them off as you go.</p>
    </div>
</section>

<?php include 'includes/components/footer.php'; ?>
