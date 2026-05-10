<?php
// api/routes/budget.php
require_once 'src/Models/Budget.php';

// Check Authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$budgetModel = new Budget($pdo);
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET') {
    if ($action === 'get' && isset($_GET['trip_id'])) {
        $tripId = $_GET['trip_id'];
        $data = $budgetModel->getBudgetBreakdown($tripId, $userId);
        
        if ($data !== false) {
            echo json_encode(["success" => true, "data" => $data]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Trip not found or unauthorized"]);
        }
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
