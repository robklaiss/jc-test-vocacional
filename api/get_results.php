<?php
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get all results for the dashboard
    $stmt = $db->query("
        SELECT 
            id,
            result,
            answers,
            full_answers,
            top_routes,
            test_date,
            created_at,
            ip_address
        FROM test_results 
        ORDER BY created_at DESC
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the results for the dashboard
    $formattedResults = array_map(function($row) {
        // Clean up the result text by removing any HTML tags and extra whitespace
        $resultText = trim(strip_tags($row['result']));
        
        // Parse the answers if they're stored as JSON
        $answers = [];
        if (!empty($row['answers'])) {
            $decodedAnswers = json_decode($row['answers'], true);
            $answers = $decodedAnswers !== null ? $decodedAnswers : $row['answers'];
        }
        
        // Parse full answers if available
        $fullAnswers = [];
        if (!empty($row['full_answers'])) {
            $decodedFullAnswers = json_decode($row['full_answers'], true);
            $fullAnswers = $decodedFullAnswers !== null ? $decodedFullAnswers : [];
        }
        
        // Parse top routes if available
        $topRoutes = [];
        if (!empty($row['top_routes'])) {
            $decodedTopRoutes = json_decode($row['top_routes'], true);
            $topRoutes = $decodedTopRoutes !== null ? $decodedTopRoutes : [];
        }
        
        // Use test_date if available, otherwise use created_at
        $date = !empty($row['test_date']) ? $row['test_date'] : $row['created_at'];
        
        return [
            'id' => $row['id'],
            'result' => $resultText,
            'answers' => $answers,
            'fullAnswers' => $fullAnswers,
            'topRoutes' => $topRoutes,
            'date' => $date,
            'ipAddress' => $row['ip_address']
        ];
    }, $results);
    
    // Get some statistics
    $totalTests = count($results);
    $ipAddresses = array_filter(array_column($results, 'ip_address'));
    $uniqueIPs = $ipAddresses ? count(array_unique($ipAddresses)) : 0;
    $dates = array_filter(array_column($results, 'date'));
    
    echo json_encode([
        'success' => true,
        'data' => $formattedResults,
        'stats' => [
            'totalTests' => $totalTests,
            'uniqueIPs' => $uniqueIPs,
            'firstTestDate' => !empty($dates) ? min($dates) : null,
            'lastTestDate' => !empty($dates) ? max($dates) : null
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch results: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
