<?php
/**
 * Alert Response Handler - Allows responders to respond to pending alerts
 */

require_once '../includes/config.php';

// Check if user is logged in as responder
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    http_response_code(401);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header('Location: login.php');
    }
    exit;
}

// Check if user has responder role (role_id = 2 for Responder)
if ($_SESSION['role_id'] != 2) {
    http_response_code(403);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Only responders can respond to alerts']);
    } else {
        header('Location: alerts.php');
    }
    exit;
}

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$responder_id = $_SESSION['user_id'];
$pdo = getPDO();

// Handle POST request to submit response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $alert_id = isset($_POST['alert_id']) ? (int)$_POST['alert_id'] : null;
        $response_status = isset($_POST['status']) ? trim($_POST['status']) : 'accepted';
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';

        // Validate required fields
        if (!$alert_id) {
            throw new Exception('Alert ID is required.');
        }

        // Validate status
        if (!in_array($response_status, ['accepted', 'in_progress', 'completed'])) {
            throw new Exception('Invalid response status.');
        }

        // Validate alert exists and is pending
        $stmt = $pdo->prepare('
            SELECT a.id, a.title, a.status, a.alert_type_id, at.name as alert_type
            FROM alerts a
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            WHERE a.id = ?
        ');
        $stmt->execute([$alert_id]);
        $alert = $stmt->fetch();

        if (!$alert) {
            throw new Exception('Alert not found.');
        }

        if ($alert['status'] !== 'pending' && $alert['status'] !== 'verified') {
            throw new Exception('Can only respond to pending or verified alerts.');
        }

        // Check if responder already responded to this alert
        $stmt = $pdo->prepare('
            SELECT id FROM alert_responses 
            WHERE alert_id = ? AND responder_id = ?
        ');
        $stmt->execute([$alert_id, $responder_id]);
        $existing_response = $stmt->fetch();

        if ($existing_response) {
            // Update existing response
            $stmt = $pdo->prepare('
                UPDATE alert_responses 
                SET status = ?, note = ?, responded_at = CURRENT_TIMESTAMP
                WHERE alert_id = ? AND responder_id = ?
            ');
            $stmt->execute([$response_status, $note, $alert_id, $responder_id]);
            $response_id = $existing_response['id'];
            $action = 'updated';
        } else {
            // Create new response
            $stmt = $pdo->prepare('
                INSERT INTO alert_responses (alert_id, responder_id, status, note, responded_at)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([$alert_id, $responder_id, $response_status, $note]);
            $response_id = $pdo->lastInsertId();
            $action = 'created';
        }

        // Log system action
        $log_stmt = $pdo->prepare('
            INSERT INTO system_logs (user_id, action)
            VALUES (?, ?)
        ');
        $log_stmt->execute([
            $responder_id,
            "Responder responded to alert #{$alert_id} ({$alert['alert_type']}): {$response_status}"
        ]);

        // Return JSON response for AJAX requests
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Response ' . $action . ' successfully!',
                'response_id' => $response_id,
                'alert_id' => $alert_id,
                'alert_title' => $alert['title'],
                'status' => $response_status
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

// Handle GET request for pending alerts (for responder to see)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $is_ajax) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                a.id,
                a.title,
                a.description,
                a.status,
                a.latitude,
                a.longitude,
                a.created_at,
                at.name as alert_type,
                u.full_name as created_by_name,
                (SELECT COUNT(*) FROM alert_responses WHERE alert_id = a.id) as response_count,
                (SELECT ar.status FROM alert_responses ar WHERE ar.alert_id = a.id AND ar.responder_id = ?) as my_response_status,
                (SELECT ar.note FROM alert_responses ar WHERE ar.alert_id = a.id AND ar.responder_id = ?) as my_note
            FROM alerts a
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.status IN (\'pending\', \'verified\')
            ORDER BY a.created_at DESC
            LIMIT 50
        ');
        $stmt->execute([$responder_id, $responder_id]);
        $pending_alerts = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'pending_alerts' => $pending_alerts
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching pending alerts: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>
