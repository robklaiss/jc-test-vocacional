<?php
// Database migration to add analytics tables
// This is an additive migration that won't break existing functionality

require_once __DIR__ . '/../config.php';

function runMigration($db) {
    // Create visits table to track all site visits
    $db->exec("CREATE TABLE IF NOT EXISTS visits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        ip_address TEXT,
        user_agent TEXT,
        referrer TEXT,
        landing_page TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_seen_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        visit_count INTEGER DEFAULT 1
    )");
    
    // Create test_sessions table for detailed test progress tracking
    $db->exec("CREATE TABLE IF NOT EXISTS test_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        test_started_at DATETIME,
        test_completed_at DATETIME,
        questions_answered INTEGER DEFAULT 0,
        total_questions INTEGER,
        result_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add indexes for better query performance
    $db->exec("CREATE INDEX IF NOT EXISTS idx_visits_session_id ON visits(session_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_visits_created_at ON visits(created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_test_sessions_session_id ON test_sessions(session_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_test_sessions_completed ON test_sessions(test_completed_at)");
}

// Run the migration
try {
    $db = getDb();
    $db->beginTransaction();
    
    runMigration($db);
    
    $db->commit();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
