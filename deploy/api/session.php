<?php
// api/session.php
// Additive, isolated API for session persistence (DO NOT BREAK guideline)
// Handles saving and retrieving test sessions in SQLite

require_once 'config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = getDb();
    // Create sessions table if not exists (additive, safe)
    $db->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        user_agent TEXT,
        ip_address TEXT,
        state TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['session_id']) || !isset($input['state'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing session_id or state']);
            exit;
        }
        $stmt = $db->prepare("INSERT INTO sessions (session_id, user_agent, ip_address, state) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $input['session_id'],
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            json_encode($input['state'])
        ]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $session_id = $_GET['session_id'] ?? null;
        if ($session_id) {
            $stmt = $db->prepare("SELECT * FROM sessions WHERE session_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$session_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row);
        } else {
            $stmt = $db->query("SELECT * FROM sessions ORDER BY created_at DESC LIMIT 100");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        }
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
