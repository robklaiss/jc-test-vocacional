<?php
require_once 'config.php';

try {
    // Get table info
    $stmt = $db->query("PRAGMA table_info(test_results)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns in test_results table:\n";
    echo str_pad("Column Name", 20) . "| " . str_pad("Type", 15) . "| Nullable\n";
    echo str_repeat("-", 40) . "\n";
    
    foreach ($columns as $col) {
        echo str_pad($col['name'], 20) . "| " . 
             str_pad($col['type'], 15) . "| " . 
             ($col['notnull'] ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // Check if we have any data
    $stmt = $db->query("SELECT COUNT(*) as count FROM test_results");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nTotal test results in database: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Error checking database schema: " . $e->getMessage() . "\n";
}
?>
