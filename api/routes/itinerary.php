<?php
// api/routes/itinerary.php
ob_start(); // Buffer all output — prevents any stray warning from corrupting JSON

// Silence display of warnings — they go to error log, not output
error_reporting(0);

// All responses from this file are JSON
header('Content-Type: application/json');

require_once 'src/Models/Itinerary.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $itineraryModel = new Itinerary($pdo);
    $userId  = $_SESSION['user_id'];
    $method  = $_SERVER['REQUEST_METHOD'];
    $action  = $_GET['action'] ?? '';

    // ── GET ──────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        if ($action === 'getFull' && isset($_GET['trip_id'])) {
            $data = $itineraryModel->getFullItinerary((int)$_GET['trip_id']);
            ob_clean();
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        }
    }

    // ── POST ─────────────────────────────────────────────────────────────
    if ($method === 'POST') {
        // Use cached body (csrf.php may have already consumed php://input)
        if (!isset($GLOBALS['_RAW_INPUT'])) {
            $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input');
        }
        $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];

        // ── addStop ──────────────────────────────────────────────────────
        if ($action === 'addStop') {
            $tripId = isset($input['trip_id']) ? (int)$input['trip_id'] : 0;

            if (!$tripId || !$itineraryModel->verifyTripOwnership($tripId, $userId)) {
                ob_clean();
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized to modify this trip']);
                exit;
            }

            $cityName      = trim($input['city_name'] ?? '');
            $arrivalDate   = !empty($input['arrival_date'])   ? $input['arrival_date']   : null;
            $departureDate = !empty($input['departure_date']) ? $input['departure_date'] : null;
            $orderIndex    = $input['order_index'] ?? 0;

            if (empty($cityName)) {
                ob_clean();
                http_response_code(400);
                echo json_encode(['error' => 'City name is required']);
                exit;
            }

            $stopId = $itineraryModel->addStop($tripId, $cityName, $arrivalDate, $departureDate, $orderIndex);
            ob_clean();
            if ($stopId) {
                echo json_encode(['success' => true, 'stop_id' => $stopId]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add stop']);
            }
            exit;
        }

        // ── addActivity ──────────────────────────────────────────────────
        if ($action === 'addActivity') {
            $stopId      = isset($input['stop_id']) ? (int)$input['stop_id'] : 0;
            $title       = trim($input['title'] ?? '');
            $description = $input['description'] ?? '';
            $startTime   = !empty($input['start_time']) ? $input['start_time'] : null;
            $cost        = isset($input['cost']) ? (float)$input['cost'] : 0;

            $activityId = $itineraryModel->addActivity($stopId, $title, $description, $startTime, $cost);
            ob_clean();
            if ($activityId) {
                echo json_encode(['success' => true, 'activity_id' => $activityId]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add activity']);
            }
            exit;
        }

        // ── updateStop ───────────────────────────────────────────────────
        if ($action === 'updateStop') {
            $id            = isset($input['id']) ? (int)$input['id'] : 0;
            $cityName      = trim($input['city_name'] ?? '');
            $arrivalDate   = !empty($input['arrival_date'])   ? $input['arrival_date']   : null;
            $departureDate = !empty($input['departure_date']) ? $input['departure_date'] : null;

            ob_clean();
            if ($itineraryModel->updateStop($id, $cityName, $arrivalDate, $departureDate)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update stop']);
            }
            exit;
        }

        // ── updateActivity ───────────────────────────────────────────────
        if ($action === 'updateActivity') {
            $id        = isset($input['id']) ? (int)$input['id'] : 0;
            $title     = trim($input['title'] ?? '');
            $startTime = !empty($input['start_time']) ? $input['start_time'] : null;
            $cost      = isset($input['cost']) ? (float)$input['cost'] : 0;

            ob_clean();
            if ($itineraryModel->updateActivity($id, $title, $startTime, $cost)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update activity']);
            }
            exit;
        }

        // ── reorderStops ─────────────────────────────────────────────────
        if ($action === 'reorderStops') {
            $stopsOrder = $input['stops_order'] ?? [];

            ob_clean();
            if ($itineraryModel->reorderStops($stopsOrder)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to reorder stops']);
            }
            exit;
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────────
    if ($method === 'DELETE') {
        if ($action === 'deleteStop' && isset($_GET['id'])) {
            ob_clean();
            if ($itineraryModel->deleteStop((int)$_GET['id'])) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete stop']);
            }
            exit;
        }

        if ($action === 'deleteActivity' && isset($_GET['id'])) {
            ob_clean();
            if ($itineraryModel->deleteActivity((int)$_GET['id'])) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete activity']);
            }
            exit;
        }
    }

    // No matching route
    ob_clean();
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
