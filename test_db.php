<?php
// test_db.php - Test database connection and table creation
require_once 'api/config.php';

echo "Testing database connection...\n";

try {
    // Test connection
    $db = getDb();
    echo "✅ Connected to database successfully\n";
    
    // Test sessions table
    $db->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        user_agent TEXT,
        ip_address TEXT,
        state TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created/verified 'sessions' table\n";
    
    // List all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    echo "\n📊 Database tables:\n";
    foreach ($tables as $table) {
        echo "- {$table['name']}\n";
    }
    
    // Try to insert test data
    $stmt = $db->prepare("INSERT INTO sessions (session_id, state) VALUES (?, ?)");
    $stmt->execute(['test_' . time(), '{"test": true}']);
    echo "✅ Inserted test data into 'sessions' table\n";
    
    // Count sessions
    $count = $db->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
    echo "📊 Total sessions: $count\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'no such table') !== false) {
        echo "\nTry running: sqlite3 api/db/test_vocacional.db \"CREATE TABLE sessions (id INTEGER PRIMARY KEY AUTOINCREMENT, session_id TEXT, user_agent TEXT, ip_address TEXT, state TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP);\"\n";
    }
}
