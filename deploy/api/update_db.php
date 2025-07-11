<?php
require_once 'config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    // Check if full_answers column exists
    $stmt = $db->query("PRAGMA table_info(test_results)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    $alterations = [];
    
    // Add missing columns if they don't exist
    if (!in_array('full_answers', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN full_answers TEXT DEFAULT '[]'");
        $alterations[] = "Added full_answers column";
    }
    
    if (!in_array('top_routes', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN top_routes TEXT DEFAULT '[]'");
        $alterations[] = "Added top_routes column";
    }
    
    if (!in_array('test_date', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN test_date TEXT");
        $alterations[] = "Added test_date column";
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database schema updated successfully',
        'alterations' => $alterations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update database: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
