<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Get user session ID
    $sessionId = $_COOKIE['test_session_id'] ?? null;
    
    if (!$sessionId) {
        echo json_encode(['state' => null]);
        exit;
    }
    
    // Get the latest state for this session
    $stmt = $db->prepare('SELECT state FROM test_states WHERE session_id = :session_id ORDER BY updated_at DESC LIMIT 1');
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && !empty($row['state'])) {
        $state = json_decode($row['state'], true);
        echo json_encode(['state' => $state]);
    } else {
        echo json_encode(['state' => null]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve test state: ' . $e->getMessage()]);
}
?>
