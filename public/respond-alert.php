<?php
/**
 * Alert Response Handler - Allows responders to respond to pending alerts
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

// Check if user has responder role (role_id = 2)
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
$responder_id = $_SESSION['user_id']; // This is the Users_id
$pdo = getPDO();

// Handle POST request to submit response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $alert_id = isset($_POST['alert_id']) ? (int)$_POST['alert_id'] : null;
        $response_status = isset($_POST['status']) ? trim($_POST['status']) : 'accepted';
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';

        if (!$alert_id) {
            throw new Exception('Alert ID is required.');
        }

        if (!in_array($response_status, ['accepted', 'in_progress', 'completed'])) {
            throw new Exception('Invalid response status.');
        }

        // UPDATED: Query using Alerts and AlertTypes tables with prefixed columns
        $stmt = $pdo->prepare('
            SELECT a.Alerts_id, a.Alerts_title, a.Alerts_status, at.AlertTypes_name as alert_type
            FROM Alerts a
            LEFT JOIN AlertTypes at ON a.Alerts_AlertTypes_id = at.AlertTypes_id
            WHERE a.Alerts_id = ?
        ');
        $stmt->execute([$alert_id]);
        $alert = $stmt->fetch();

        if (!$alert) {
            throw new Exception('Alert not found.');
        }

        if ($alert['Alerts_status'] !== 'pending' && $alert['Alerts_status'] !== 'verified') {
            throw new Exception('Can only respond to pending or verified alerts.');
        }

        // UPDATED: Check AlertResponses table
        $stmt = $pdo->prepare('
            SELECT AlertResponses_id FROM AlertResponses 
            WHERE AlertResponses_Alerts_id = ? AND AlertResponses_Users_id = ?
        ');
        $stmt->execute([$alert_id, $responder_id]);
        $existing_response = $stmt->fetch();

        if ($existing_response) {
            // UPDATED: Update AlertResponses table
            $stmt = $pdo->prepare('
                UPDATE AlertResponses 
                SET AlertResponses_status = ?, AlertResponses_note = ?, AlertResponses_responded_at = CURRENT_TIMESTAMP
                WHERE AlertResponses_Alerts_id = ? AND AlertResponses_Users_id = ?
            ');
            $stmt->execute([$response_status, $note, $alert_id, $responder_id]);
            $response_id = $existing_response['AlertResponses_id'];
            $action = 'updated';
        } else {
            // UPDATED: Insert into AlertResponses table
            $stmt = $pdo->prepare('
                INSERT INTO AlertResponses (AlertResponses_Alerts_id, AlertResponses_Users_id, AlertResponses_status, AlertResponses_note, AlertResponses_responded_at)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([$alert_id, $responder_id, $response_status, $note]);
            $response_id = $pdo->lastInsertId();
            $action = 'created';
        }

        // UPDATED: Insert into SystemLogs table
        $log_stmt = $pdo->prepare('
            INSERT INTO SystemLogs (SystemLogs_Users_id, SystemLogs_action)
            VALUES (?, ?)
        ');
        $log_stmt->execute([
            $responder_id,
            "Responder responded to alert #{$alert_id} ({$alert['alert_type']}): {$response_status}"
        ]);

        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Response ' . $action . ' successfully!',
                'response_id' => $response_id,
                'alert_id' => $alert_id,
                'alert_title' => $alert['Alerts_title'],
                'status' => $response_status
            ]);
            exit;
        }

    } catch (Exception $e) {
        if ($is_ajax) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// Handle GET request for pending alerts (for responder)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $is_ajax) {
    try {
        // UPDATED: Full prefixing for all columns and Title Case tables
        $stmt = $pdo->prepare("
            SELECT 
                a.Alerts_id,
                a.Alerts_title,
                a.Alerts_desc,
                a.Alerts_status,
                a.Alerts_latitude,
                a.Alerts_longitude,
                a.Alerts_created_at,
                at.AlertTypes_name as alert_type,
                u.Users_full_name as created_by_name,
                (SELECT COUNT(*) FROM AlertResponses WHERE AlertResponses_Alerts_id = a.Alerts_id) as response_count,
                (SELECT ar.AlertResponses_status FROM AlertResponses ar WHERE ar.AlertResponses_Alerts_id = a.Alerts_id AND ar.AlertResponses_Users_id = ?) as my_response_status,
                (SELECT ar.AlertResponses_note FROM AlertResponses ar WHERE ar.AlertResponses_Alerts_id = a.Alerts_id AND ar.AlertResponses_Users_id = ?) as my_note
            FROM Alerts a
            LEFT JOIN AlertTypes at ON a.Alerts_AlertTypes_id = at.AlertTypes_id
            LEFT JOIN Users u ON a.Alerts_Users_id = u.Users_id
            WHERE a.Alerts_status IN ('pending', 'verified')
            ORDER BY a.Alerts_created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$responder_id, $responder_id]);
        $pending_alerts = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'pending_alerts' => $pending_alerts
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
?>