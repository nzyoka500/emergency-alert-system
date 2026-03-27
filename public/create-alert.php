<?php
/**
 * Alert Creation Handler - Supports AJAX and Traditional Requests
 */

require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    http_response_code(401);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header('Location: login.php');
    }
    exit;
}

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$user_id = $_SESSION['user_id'];
$pdo = getPDO();

// Handle POST request to create alert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $alert_type_id = isset($_POST['alert_type_id']) ? (int)$_POST['alert_type_id'] : null;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $severity = isset($_POST['severity']) ? trim($_POST['severity']) : 'Medium';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;

        // Validate required fields
        if (!$alert_type_id || empty($title) || empty($description) || $latitude === null || $longitude === null) {
            throw new Exception('All fields are required.');
        }

        // Validate title length
        if (strlen($title) > 150) {
            throw new Exception('Title must not exceed 150 characters.');
        }

        // Validate severity
        if (!in_array($severity, ['Low', 'Medium', 'High'])) {
            throw new Exception('Invalid severity level selected.');
        }

        // Validate latitude and longitude ranges
        if ($latitude < -90 || $latitude > 90) {
            throw new Exception('Latitude must be between -90 and 90.');
        }
        if ($longitude < -180 || $longitude > 180) {
            throw new Exception('Longitude must be between -180 and 180.');
        }

        // UPDATED: Query AlertTypes table with prefixed columns
        $stmt = $pdo->prepare('SELECT AlertTypes_id, AlertTypes_name FROM AlertTypes WHERE AlertTypes_id = ?');
        $stmt->execute([$alert_type_id]);
        $alert_type = $stmt->fetch();
        if (!$alert_type) {
            throw new Exception('Invalid alert type selected.');
        }

        // UPDATED: Insert into Alerts table with prefixed columns and desc suffix
        $stmt = $pdo->prepare('
            INSERT INTO Alerts (Alerts_AlertTypes_id, Alerts_title, Alerts_desc, Alerts_latitude, Alerts_longitude, Alerts_Users_id, Alerts_status, Alerts_severity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $alert_type_id,
            $title,
            $description,
            $latitude,
            $longitude,
            $user_id,
            'pending',
            $severity 
        ]);

        $alert_id = $pdo->lastInsertId();

        // Return JSON response for AJAX requests
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Alert created successfully!',
                'alert_id' => $alert_id,
                'alert_type' => $alert_type['AlertTypes_name'] // UPDATED key
            ]);
            exit;
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        
        // Return JSON error for AJAX requests
        if ($is_ajax) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $error_message
            ]);
            exit;
        }
    }
}

// For AJAX GET requests, return alert types JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $is_ajax) {
    // UPDATED: Table 'AlertTypes' and column prefixes/desc suffix
    $stmt = $pdo->query('SELECT AlertTypes_id, AlertTypes_name, AlertTypes_desc FROM AlertTypes ORDER BY AlertTypes_name');
    $alert_types = $stmt->fetchAll();
    echo json_encode(['alert_types' => $alert_types]);
    exit;
}