<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $db->query('SELECT 1');
    
    // Get test results
    $stmt = $db->query('SELECT id, result, created_at FROM test_results ORDER BY created_at DESC LIMIT 5');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'test_results' => $results,
        'count' => count($results)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
