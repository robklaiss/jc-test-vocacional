<?php
// api/mysql_sessions_fetch.php
// Fetch all sessions from MySQL for dashboard

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$db   = 'test_vocacional';
$user = 'vocacional_user';
$pass = 'Su@mB_w13+5a&Lumba%321'; // Use your actual password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

try {
    $stmt = $pdo->query('SELECT id, session_id, state, created_at FROM sessions ORDER BY created_at DESC LIMIT 100');
    $sessions = $stmt->fetchAll();
    // Decode JSON state for each session
    foreach ($sessions as &$session) {
        $state = $session['state'];
        $session['state'] = is_string($state) ? json_decode($state, true) : $state;
    }
    echo json_encode(['success' => true, 'sessions' => $sessions]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
