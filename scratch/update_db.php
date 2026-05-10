<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            cost_index VARCHAR(10) NOT NULL
        );
    ");
    echo "Cities table ensured.\n";

    $pdo->exec("
        INSERT INTO cities (name, country, cost_index) VALUES
        ('Paris', 'France', '$$'),
        ('Tokyo', 'Japan', '$$$'),
        ('Bali', 'Indonesia', '$'),
        ('Rome', 'Italy', '$$'),
        ('New York', 'USA', '$$$'),
        ('Dubai', 'UAE', '$$$'),
        ('Bangkok', 'Thailand', '$'),
        ('London', 'UK', '$$$'),
        ('Singapore', 'Singapore', '$$$'),
        ('Barcelona', 'Spain', '$$'),
        ('Amsterdam', 'Netherlands', '$$'),
        ('Istanbul', 'Turkey', '$'),
        ('Prague', 'Czech Republic', '$'),
        ('Lisbon', 'Portugal', '$'),
        ('Coimbatore', 'India', '$'),
        ('Chennai', 'India', '$'),
        ('Mumbai', 'India', '$$'),
        ('Delhi', 'India', '$$'),
        ('Jaipur', 'India', '$'),
        ('Goa', 'India', '$'),
        ('Kolkata', 'India', '$'),
        ('Bangalore', 'India', '$$'),
        ('Hyderabad', 'India', '$$'),
        ('Kochi', 'India', '$'),
        ('Mysore', 'India', '$')
        ON DUPLICATE KEY UPDATE name=name;
    ");
    echo "Cities populated.\n";
    
    try {
        $pdo->exec("ALTER TABLE activities ADD COLUMN category ENUM('Transport', 'Stay', 'Activities', 'Meals') DEFAULT 'Activities'");
        echo "Added category to activities.\n";
    } catch(PDOException $e) { echo "Activities category already exists or error: " . $e->getMessage() . "\n"; }

    try {
        $pdo->exec("ALTER TABLE packing_items ADD COLUMN category ENUM('Clothing', 'Documents', 'Electronics', 'Misc') DEFAULT 'Misc'");
        echo "Added category to packing_items.\n";
    } catch(PDOException $e) { echo "Packing category already exists or error: " . $e->getMessage() . "\n"; }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255)");
        echo "Added profile_image to users.\n";
    } catch(PDOException $e) { echo "Users profile_image already exists or error: " . $e->getMessage() . "\n"; }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
