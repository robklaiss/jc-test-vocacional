<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Get user session ID
    $sessionId = $_COOKIE['test_session_id'] ?? null;
    
    if (!$sessionId) {
        echo json_encode(['success' => true]); // Nothing to clear
        exit;
    }
    
    // Delete the state for this session
    $stmt = $db->prepare('DELETE FROM test_states WHERE session_id = :session_id');
    $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
    $stmt->execute();
    
    // Clear the session cookie
    setcookie('test_session_id', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'samesite' => 'Lax'
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to clear test state: ' . $e->getMessage()]);
}
?>
