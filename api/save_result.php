<?php
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['result']) || !isset($input['answers'])) {
        throw new Exception('Missing required fields');
    }

    // Prepare data for insertion
    $result = strip_tags($input['result']);
    $answers = is_array($input['answers']) ? json_encode($input['answers']) : $input['answers'];
    $fullAnswers = isset($input['fullAnswers']) ? json_encode($input['fullAnswers']) : '[]';
    $topRoutes = isset($input['topRoutes']) ? json_encode($input['topRoutes']) : '[]';
    $date = isset($input['date']) ? $input['date'] : date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // First, make sure the table has all the columns we need
    $db->exec("PRAGMA table_info(test_results)");
    $columns = $db->query("PRAGMA table_info(test_results)")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    // Add any missing columns
    if (!in_array('full_answers', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN full_answers TEXT DEFAULT '[]'");
    }
    if (!in_array('top_routes', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN top_routes TEXT DEFAULT '[]'");
    }
    if (!in_array('test_date', $columns)) {
        $db->exec("ALTER TABLE test_results ADD COLUMN test_date TEXT");
    }

    // Prepare and execute the insert statement
    $stmt = $db->prepare("
        INSERT INTO test_results 
        (result, answers, full_answers, top_routes, test_date, ip_address, user_agent, created_at) 
        VALUES 
        (:result, :answers, :full_answers, :top_routes, :test_date, :ip_address, :user_agent, CURRENT_TIMESTAMP)
    ");
    
    $success = $stmt->execute([
        ':result' => $result,
        ':answers' => $answers,
        ':full_answers' => $fullAnswers,
        ':top_routes' => $topRoutes,
        ':test_date' => $date,
        ':ip_address' => $ipAddress,
        ':user_agent' => $userAgent
    ]);
    
    if (!$success) {
        throw new Exception('Failed to save result to database');
    }
    
    $id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'id' => $id,
        'message' => 'Result saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
