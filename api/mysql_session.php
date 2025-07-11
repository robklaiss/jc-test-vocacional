<?php
// api/mysql_session.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// --- MySQL connection config ---
$host = 'localhost';
$db   = 'test_vocacional';
$user = 'vocacional_user';
$pass = 'Su@mB_w13+5a&Lumba%321'; // <--- CHANGE THIS TO YOUR ACTUAL PASSWORD
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// --- Handle input ---
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['session_id']) || !isset($input['state'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing session_id or state']);
    exit();
}

$session_id = $input['session_id'];
$state_json = json_encode($input['state'], JSON_UNESCAPED_UNICODE);

try {
    $stmt = $pdo->prepare("INSERT INTO sessions (session_id, state, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$session_id, $state_json]);
    echo json_encode(['success' => true]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}