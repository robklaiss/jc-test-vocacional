<?php
require_once 'api/config.php';

header('Content-Type: text/plain');

try {
    // Get the 10 most recent test results
    $stmt = $db->query("
        SELECT id, result, created_at, ip_address 
        FROM test_results 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Last 10 Test Results ===\n\n";
    
    if (empty($results)) {
        echo "No test results found in the database.\n";
    } else {
        foreach ($results as $row) {
            echo "ID: " . $row['id'] . "\n";
            echo "Date: " . $row['created_at'] . "\n";
            echo "IP: " . $row['ip_address'] . "\n";
            echo "Result: " . substr($row['result'], 0, 100) . (strlen($row['result']) > 100 ? '...' : '') . "\n";
            echo str_repeat("-", 50) . "\n\n";
        }
    }
    
    // Show total count
    $count = $db->query("SELECT COUNT(*) as total FROM test_results")->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal test results in database: " . $count['total'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'no such table') !== false) {
        echo "\nThe test_results table doesn't exist. You may need to run the database migrations.\n";
    }
}
