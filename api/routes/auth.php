<?php
// api/routes/auth.php
require_once 'src/Models/User.php';

$userModel = new User($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST') {
    // Use cached raw input — php://input can only be read once.
    // csrf.php already read and cached it in $GLOBALS['_RAW_INPUT'].
    if (!isset($GLOBALS['_RAW_INPUT'])) {
        $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input');
    }
    $input = json_decode($GLOBALS['_RAW_INPUT'], true) ?? [];

    if ($action === 'register') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "All fields are required"]);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid email format"]);
            exit;
        }

        $result = $userModel->register($name, $email, $password);
        
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_name'] = $name;
            echo json_encode(["success" => true, "message" => "Registration successful"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $result['message']]);
        }
        exit;
    }

    if ($action === 'login') {
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Email and password are required"]);
            exit;
        }

        $result = $userModel->login($email, $password);
        
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_name'] = $result['user']['name'];
            echo json_encode(["success" => true, "message" => "Login successful"]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => $result['message']]);
        }
        exit;
    }
}

if ($method === 'GET' && $action === 'logout') {
    session_destroy();
    header("Location: /login");
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Action not found"]);
