<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the request body
$input = json_decode(file_get_contents('php://input'), true);

// Verify confirmation
if (!isset($input['confirm']) || $input['confirm'] !== true) {
    http_response_code(400);
    echo json_encode(['error' => 'Confirmation required']);
    exit();
}

try {
    // Include database configuration
    require_once 'config.php';
    
    // Delete all test results
    $stmt = $pdo->prepare("DELETE FROM test_results");
    $success = $stmt->execute();
    
    if ($success) {
        // Reset auto-increment counter
        $pdo->exec("DELETE FROM sqlite_sequence WHERE name='test_results'");
        
        echo json_encode(['success' => true, 'message' => 'Todos los resultados han sido eliminados']);
    } else {
        throw new Exception('No se pudieron eliminar los resultados');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
