-- Database Schema for Traveloop

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    cover_image VARCHAR(255),
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS trip_stops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    city_name VARCHAR(255) NOT NULL,
    arrival_date DATE,
    departure_date DATE,
    order_index INT NOT NULL DEFAULT 0,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stop_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_time DATETIME,
    cost DECIMAL(10,2) DEFAULT 0.00,
    category ENUM('Transport', 'Stay', 'Activities', 'Meals') DEFAULT 'Activities',
    status ENUM('planned', 'completed', 'cancelled') DEFAULT 'planned',
    FOREIGN KEY (stop_id) REFERENCES trip_stops(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    estimated_amount DECIMAL(10,2) DEFAULT 0.00,
    actual_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS packing_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category ENUM('Clothing', 'Documents', 'Electronics', 'Misc') DEFAULT 'Misc',
    is_packed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS trip_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

-- Adding some useful indexes
CREATE INDEX idx_trips_user ON trips(user_id);
CREATE INDEX idx_trip_stops_trip ON trip_stops(trip_id);
CREATE INDEX idx_activities_stop ON activities(stop_id);

CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(255) NOT NULL,
    cost_index VARCHAR(10) NOT NULL
);

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
