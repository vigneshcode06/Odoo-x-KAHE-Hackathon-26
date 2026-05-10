<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
    <title>Traveloop - Smart Travel Planner</title>
    <meta name="description" content="Plan your trips professionally with Traveloop. Modern travel SaaS application.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Global CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Page Specific CSS if any -->
    <?php if(isset($pageStyles)): ?>
        <style>
            <?php echo $pageStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body>
    <header class="app-header" style="background: var(--bg-card); padding: 1rem 0; box-shadow: var(--shadow-sm); position: sticky; top: 0; z-index: 100;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="/" class="logo" style="font-size: 1.5rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                <!-- Simple SVG Logo -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                    <path d="M14 3v5h5M16 13H8M16 17H8M10 9H8"/>
                </svg>
                Traveloop
            </a>
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/dashboard" style="color: var(--text-main); font-weight: 500;">Dashboard</a>
                    <a href="/create-trip" style="color: var(--text-main); font-weight: 500;">+ Trip</a>
                    <a href="/profile" style="color: var(--text-main); font-weight: 500;">Profile</a>
                    <a href="/api/auth?action=logout" class="btn btn-outline" style="padding: 0.4rem 1rem;">Logout</a>
                <?php else: ?>
                    <a href="/login" style="color: var(--text-main); font-weight: 500;">Login</a>
                    <a href="/signup" class="btn btn-primary" style="padding: 0.4rem 1rem;">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main-content">
