<?php
// api/migrations/002_add_constraints.php
// Adds NOT NULL constraints to prevent empty rows

require_once __DIR__ . '/../config.php';

try {
    $db = getDb();
    
    // First, clean up any existing empty rows
    $db->exec("DELETE FROM test_sessions WHERE session_id IS NULL OR TRIM(session_id) = ''");
    
    // Add NOT NULL constraints by creating a new table with the same structure but with constraints
    $db->exec("
        CREATE TABLE IF NOT EXISTS test_sessions_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            test_started_at DATETIME,
            test_completed_at DATETIME,
            questions_answered INTEGER NOT NULL DEFAULT 0,
            total_questions INTEGER,
            result_data TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Copy data from old table to new one, ensuring no NULL values in required fields
    $db->exec("
        INSERT INTO test_sessions_new (
            id, session_id, test_started_at, test_completed_at, 
            questions_answered, total_questions, result_data, created_at
        )
        SELECT 
            id, 
            COALESCE(NULLIF(TRIM(session_id), ''), 'anonymous_' || id) as session_id,
            test_started_at,
            test_completed_at,
            COALESCE(questions_answered, 0) as questions_answered,
            total_questions,
            result_data,
            COALESCE(created_at, CURRENT_TIMESTAMP) as created_at
        FROM test_sessions
        WHERE session_id IS NOT NULL 
        AND TRIM(session_id) != ''
        AND created_at IS NOT NULL
    ");
    
    // Drop old table and rename new one
    $db->exec("DROP TABLE IF EXISTS test_sessions_old");
    $db->exec("ALTER TABLE test_sessions RENAME TO test_sessions_old");
    $db->exec("ALTER TABLE test_sessions_new RENAME TO test_sessions");
    
    // Recreate indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_test_sessions_session_id ON test_sessions(session_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_test_sessions_created_at ON test_sessions(created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_test_sessions_event_type ON test_sessions(event_type)");
    
    echo "Migration completed successfully. Added NOT NULL constraints to test_sessions table.\n";
    
} catch (Exception $e) {
    error_log('Migration error: ' . $e->getMessage());
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
