<?php
// api/routes/cities.php
require_once 'config/db.php';

// Check Authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'search') {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($q)) {
        echo json_encode(["success" => true, "data" => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, country, cost_index FROM cities WHERE name LIKE ? OR country LIKE ? LIMIT 10");
    $searchTerm = "%$q%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $results]);
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
