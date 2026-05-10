<?php
// includes/csrf.php
// Token generation — runs on every page load (session already started in index.php)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && strlen($token) > 0
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------------
// Global CSRF validation for mutating requests to the API only.
// We skip validation for GET and HEAD (safe methods).
// ---------------------------------------------------------------
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST' || $requestMethod === 'DELETE' || $requestMethod === 'PUT') {

    $csrfToken = '';

    // ----------------------------------------------------------
    // 1. Try the X-CSRF-Token REQUEST HEADER (set by main.js).
    //    Use $_SERVER directly — it works in all PHP environments
    //    including Apache mod_php inside Docker where getallheaders()
    //    may silently return false or an empty array.
    // ----------------------------------------------------------
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    // ----------------------------------------------------------
    // 2. Fallback: read the raw request body once and cache it.
    //    Both csrf.php and subsequent route files (auth.php, etc.)
    //    may need the body — caching avoids the "stream read once"
    //    problem that caused auth.php to always get null input.
    // ----------------------------------------------------------
    if (empty($csrfToken)) {
        // Cache body into a global so route files can reuse it
        if (!isset($GLOBALS['_RAW_INPUT'])) {
            $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input');
        }

        // Check multipart / urlencoded POST field
        if (!empty($_POST['csrf_token'])) {
            $csrfToken = $_POST['csrf_token'];
        }
        // Check JSON body
        elseif (!empty($GLOBALS['_RAW_INPUT'])) {
            $decoded = json_decode($GLOBALS['_RAW_INPUT'], true);
            if (is_array($decoded) && !empty($decoded['csrf_token'])) {
                $csrfToken = $decoded['csrf_token'];
            }
        }
    }

    if (!verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF token validation failed']);
        exit;
    }
}
