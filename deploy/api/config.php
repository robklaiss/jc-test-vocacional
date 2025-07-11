<?php
// Database configuration
define('DB_PATH', __DIR__ . '/db/test_vocacional.db');

// Function to get database connection
function getDb() {
    static $db = null;
    if ($db === null) {
        // Create database directory if it doesn't exist
        if (!file_exists(dirname(DB_PATH))) {
            mkdir(dirname(DB_PATH), 0755, true);
        }
        
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize database schema if needed
        initDatabase($db);
    }
    return $db;
}

// Initialize database schema
function initDatabase($db) {
    // Create test_results table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS test_results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        result TEXT NOT NULL,
        answers TEXT NOT NULL,
        full_answers TEXT,
        top_routes TEXT,
        test_date DATETIME,
        ip_address TEXT,
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add any missing columns if they don't exist
    $db->exec("PRAGMA table_info(test_results)");
    $columns = $db->query("PRAGMA table_info(test_results)")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('full_answers', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN full_answers TEXT");
    }
    if (!in_array('top_routes', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN top_routes TEXT");
    }
    if (!in_array('test_date', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN test_date DATETIME");
    }
    
    // Create test_states table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS test_states (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT UNIQUE NOT NULL,
        state TEXT NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    )");
    
    // Create sessions table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        user_agent TEXT,
        ip_address TEXT,
        state TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

try {
    // Initialize database connection
    $db = getDb();
    
} catch(PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
