<?php
// api/routes/utilities.php
require_once 'src/Models/Utility.php';
require_once 'src/Models/Itinerary.php'; // For auth check

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$utilityModel = new Utility($pdo);
$itineraryModel = new Itinerary($pdo);
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET') {
    $tripId = $_GET['trip_id'] ?? null;
    
    if ($action === 'getPacking') {
        echo json_encode(["success" => true, "data" => $utilityModel->getPackingItems($tripId)]);
        exit;
    }
    
    if ($action === 'getNotes') {
        echo json_encode(["success" => true, "data" => $utilityModel->getNotes($tripId)]);
        exit;
    }
}

if ($method === 'POST') {
    if (!isset($GLOBALS['_RAW_INPUT'])) { $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input'); }
    $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];
    
    if ($action === 'addPacking') {
        $tripId = $input['trip_id'];
        $itemName = $input['item_name'];
        $category = $input['category'] ?? 'Misc';
        if ($itineraryModel->verifyTripOwnership($tripId, $userId)) {
            $utilityModel->addPackingItem($tripId, $itemName, $category);
            echo json_encode(["success" => true]);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
        }
        exit;
    }
    
    if ($action === 'togglePacking') {
        // Simple toggle
        $id = $input['id'];
        $isPacked = $input['is_packed'] ? 1 : 0;
        $utilityModel->togglePackingItem($id, $isPacked);
        echo json_encode(["success" => true]);
        exit;
    }
    
    if ($action === 'addNote') {
        $tripId = $input['trip_id'];
        $content = $input['content'];
        if ($itineraryModel->verifyTripOwnership($tripId, $userId)) {
            $utilityModel->addNote($tripId, $content);
            echo json_encode(["success" => true]);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
        }
        exit;
    }

    if ($action === 'updateNote') {
        $id = $input['id'];
        $content = $input['content'];
        $utilityModel->updateNote($id, $content);
        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === 'resetPacking') {
        $tripId = $input['trip_id'];
        if ($itineraryModel->verifyTripOwnership($tripId, $userId)) {
            $utilityModel->resetPackingItems($tripId);
            echo json_encode(["success" => true]);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
        }
        exit;
    }
}

if ($method === 'DELETE') {
    if ($action === 'deletePacking') {
        $id = $_GET['id'];
        $utilityModel->deletePackingItem($id);
        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === 'deleteNote') {
        $id = $_GET['id'];
        $utilityModel->deleteNote($id);
        echo json_encode(["success" => true]);
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
