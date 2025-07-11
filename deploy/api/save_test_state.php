<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['state'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing state data']);
    exit;
}

try {
    // Get user session ID or create one
    $sessionId = $_COOKIE['test_session_id'] ?? uniqid('test_', true);
    if (!isset($_COOKIE['test_session_id'])) {
        setcookie('test_session_id', $sessionId, [
            'expires' => time() + (86400 * 30), // 30 days
            'path' => '/',
            'samesite' => 'Lax'
        ]);
    }

    // Prepare the state data
    $state = json_encode($data['state']);
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Check if we already have a state for this session
    $stmt = $db->prepare('SELECT id FROM test_states WHERE session_id = :session_id');
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
    $stmt->execute();
    
    // Check if we have a result
    $existingState = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingState) {
        // Update existing state
        $stmt = $db->prepare('UPDATE test_states SET state = :state, updated_at = :updated_at WHERE session_id = :session_id');
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $timestamp, PDO::PARAM_STR);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
    } else {
        // Create new state
        $stmt = $db->prepare('INSERT INTO test_states (session_id, state, created_at, updated_at) VALUES (:session_id, :state, :created_at, :updated_at)');
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':created_at', $timestamp, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $timestamp, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save test state: ' . $e->getMessage()]);
}
?>
