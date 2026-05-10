<?php
// api/routes/trips.php
require_once 'src/Models/Trip.php';

// Check Authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$tripModel = new Trip($pdo);
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET') {
    if ($action === 'list') {
        $trips = $tripModel->getUserTrips($userId);
        echo json_encode(["success" => true, "data" => $trips]);
        exit;
    }
    
    if ($action === 'getStats') {
        // Total trips
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM trips WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalTrips = $stmt->fetchColumn();

        // Total cities visited (unique stops)
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT s.city_name) 
            FROM trip_stops s 
            JOIN trips t ON s.trip_id = t.id 
            WHERE t.user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalCities = $stmt->fetchColumn();

        // Total budget spent
        $stmt = $pdo->prepare("
            SELECT SUM(a.cost) 
            FROM activities a 
            JOIN trip_stops s ON a.stop_id = s.id 
            JOIN trips t ON s.trip_id = t.id 
            WHERE t.user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalSpent = $stmt->fetchColumn() ?: 0;

        echo json_encode([
            "success" => true, 
            "data" => [
                "total_trips" => $totalTrips,
                "total_cities" => $totalCities,
                "total_spent" => $totalSpent
            ]
        ]);
        exit;
    }
    
    if ($action === 'get' && isset($_GET['id'])) {
        $trip = $tripModel->getTripById($_GET['id'], $userId);
        if ($trip) {
            echo json_encode(["success" => true, "data" => $trip]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Trip not found or unauthorized"]);
        }
        exit;
    }
}

if ($method === 'POST') {
    // Read JSON payload for updates/creates without files
    // If files are included, we'll use $_POST and $_FILES
    
    if ($action === 'create') {
        // Assume basic JSON for now
        if (!isset($GLOBALS['_RAW_INPUT'])) { $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input'); }
        $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];
        
        $title = $input['title'] ?? '';
        $description = $input['description'] ?? '';
        $startDate = $input['start_date'] ?? null;
        $endDate = $input['end_date'] ?? null;
        $isPublic = isset($input['is_public']) && $input['is_public'] ? 1 : 0;
        
        if (empty($title)) {
            http_response_code(400);
            echo json_encode(["error" => "Title is required"]);
            exit;
        }

        $tripId = $tripModel->create($userId, $title, $description, $startDate, $endDate, $isPublic);
        
        if ($tripId) {
            echo json_encode(["success" => true, "message" => "Trip created", "trip_id" => $tripId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create trip"]);
        }
        exit;
    }

    // Handle multipart form upload
    if ($action === 'createWithUpload') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $isPublic = isset($_POST['is_public']) && $_POST['is_public'] == '1' ? 1 : 0;
        $coverImage = null;

        if (empty($title)) {
            http_response_code(400);
            echo json_encode(["error" => "Title is required"]);
            exit;
        }

        // Handle file upload
        if (!empty($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid file type. Use JPEG, PNG, or WebP."]);
                exit;
            }

            if ($file['size'] > $maxSize) {
                http_response_code(400);
                echo json_encode(["error" => "File too large. Max 2MB."]);
                exit;
            }

            // Verify it's actually an image
            $imgInfo = getimagesize($file['tmp_name']);
            if (!$imgInfo) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid image file."]);
                exit;
            }

            $uploadDir = 'uploads/covers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('cover_') . '.' . strtolower($ext);
            $destPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $coverImage = '/' . $destPath;
            }
        }

        $tripId = $tripModel->create($userId, $title, $description, $startDate, $endDate, $isPublic, $coverImage);

        if ($tripId) {
            echo json_encode(["success" => true, "message" => "Trip created", "trip_id" => $tripId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create trip"]);
        }
        exit;
    }

    if ($action === 'update') {
        if (!isset($GLOBALS['_RAW_INPUT'])) { $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input'); }
        $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];
        
        $id = $input['id'] ?? null;
        $title = $input['title'] ?? '';
        $description = $input['description'] ?? '';
        $startDate = $input['start_date'] ?? null;
        $endDate = $input['end_date'] ?? null;
        $isPublic = isset($input['is_public']) && $input['is_public'] ? 1 : 0;
        
        if (empty($title)) {
            http_response_code(400);
            echo json_encode(["error" => "Title is required"]);
            exit;
        }

        if ($tripModel->update($id, $userId, $title, $description, $startDate, $endDate, $isPublic)) {
            echo json_encode(["success" => true, "message" => "Trip updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update trip"]);
        }
        exit;
    }
}
if ($method === 'DELETE' && isset($_GET['id'])) {
    $result = $tripModel->delete($_GET['id'], $userId);
    if ($result) {
        echo json_encode(["success" => true, "message" => "Trip deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete trip"]);
    }
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
