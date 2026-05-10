<?php
// api/routes/profile.php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST') {

    if ($action === 'update') {
        if (!isset($GLOBALS['_RAW_INPUT'])) { $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input'); }
        $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];
        $name  = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($name) || empty($email)) {
            http_response_code(400);
            echo json_encode(["error" => "Name and email are required"]);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid email address"]);
            exit;
        }

        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "Email already in use by another account"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $userId])) {
            $_SESSION['user_name'] = $name;
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update profile"]);
        }
        exit;
    }

    if ($action === 'changePassword') {
        if (!isset($GLOBALS['_RAW_INPUT'])) { $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input'); }
        $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];
        $currentPwd = $input['current_password'] ?? '';
        $newPwd     = $input['new_password'] ?? '';

        if (strlen($newPwd) < 8) {
            http_response_code(400);
            echo json_encode(["error" => "New password must be at least 8 characters"]);
            exit;
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPwd, $user['password'])) {
            http_response_code(401);
            echo json_encode(["error" => "Current password is incorrect"]);
            exit;
        }

        $hash = password_hash($newPwd, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash, $userId])) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update password"]);
        }
        exit;
    }

    if ($action === 'uploadAvatar') {
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["error" => "No file uploaded"]);
            exit;
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid file type"]);
            exit;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(["error" => "File too large. Max 2MB"]);
            exit;
        }
        if (!getimagesize($file['tmp_name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid image"]);
            exit;
        }

        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'avatar_' . $userId . '_' . uniqid() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $imagePath = '/' . $destPath;
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$imagePath, $userId]);
            echo json_encode(["success" => true, "path" => $imagePath]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to save image"]);
        }
        exit;
    }
}

if ($method === 'DELETE') {
    if ($action === 'deleteAccount') {
        // Delete everything in order (activities → stops → utilities → trips → user)
        $pdo->beginTransaction();
        try {
            // Activities
            $pdo->prepare("DELETE a FROM activities a 
                JOIN trip_stops s ON a.stop_id = s.id 
                JOIN trips t ON s.trip_id = t.id 
                WHERE t.user_id = ?")->execute([$userId]);
            // Stops
            $pdo->prepare("DELETE s FROM trip_stops s 
                JOIN trips t ON s.trip_id = t.id 
                WHERE t.user_id = ?")->execute([$userId]);
            // Packing
            $pdo->prepare("DELETE p FROM packing_items p 
                JOIN trips t ON p.trip_id = t.id 
                WHERE t.user_id = ?")->execute([$userId]);
            // Notes
            $pdo->prepare("DELETE n FROM trip_notes n 
                JOIN trips t ON n.trip_id = t.id 
                WHERE t.user_id = ?")->execute([$userId]);
            // Trips
            $pdo->prepare("DELETE FROM trips WHERE user_id = ?")->execute([$userId]);
            // User
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

            $pdo->commit();

            session_destroy();
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete account"]);
        }
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
