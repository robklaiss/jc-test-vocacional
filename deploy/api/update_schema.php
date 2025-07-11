<?php
require_once 'config.php';

try {
    // Create test_states table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS test_states (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT NOT NULL,
        state TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(session_id) ON CONFLICT REPLACE
    )");
    
    // Add missing columns to test_results if they don't exist
    $db->exec("ALTER TABLE test_results ADD COLUMN full_answers TEXT DEFAULT ''");
    $db->exec("ALTER TABLE test_results ADD COLUMN top_routes TEXT DEFAULT ''");
    $db->exec("ALTER TABLE test_results ADD COLUMN test_date DATETIME DEFAULT CURRENT_TIMESTAMP");
    
    echo "Database schema updated successfully. The following changes were made:\n";
    echo "- Created test_states table if it didn't exist\n";
    echo "- Added the following columns to test_results if they didn't exist:\n";
    echo "  - full_answers (TEXT)\n";
    echo "  - top_routes (TEXT)\n";
    echo "  - test_date (DATETIME)\n";
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        // Ignore "column already exists" errors
        echo "Note: " . $e->getMessage() . "\n";
    } else if (strpos($e->getMessage(), 'already exists') !== false) {
        // Ignore "table already exists" errors
        echo "Note: " . $e->getMessage() . "\n";
    } else {
        echo "Error updating database schema: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
