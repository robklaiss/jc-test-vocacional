<?php
// api/analytics.php
// Analytics API endpoints for dashboard

require_once 'config.php';

// Set headers for JSON response and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 3600');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Track a visit
function trackVisit($db, $sessionId) {
    try {
        if (empty($sessionId)) {
            $sessionId = session_id();
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $landingPage = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check if this session has visited before in the last 30 minutes
        $stmt = $db->prepare("SELECT id, visit_count FROM visits WHERE session_id = ? AND last_seen_at > datetime('now', '-30 minutes') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$sessionId]);
        $visit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($visit) {
            // Update existing visit
            $stmt = $db->prepare("UPDATE visits SET last_seen_at = CURRENT_TIMESTAMP, visit_count = visit_count + 1 WHERE id = ?");
            $stmt->execute([$visit['id']]);
            return ['status' => 'updated', 'visit_id' => $visit['id']];
        } else {
            // Create new visit
            $stmt = $db->prepare("INSERT INTO visits (session_id, ip_address, user_agent, referrer, landing_page) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sessionId, $ip, $userAgent, $referrer, $landingPage]);
            return ['status' => 'created', 'visit_id' => $db->lastInsertId()];
        }
    } catch (Exception $e) {
        error_log('Error tracking visit: ' . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Get test result distribution
function getTestResultDistribution($db) {
    try {
        $stmt = $db->query("
            SELECT 
                result,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM test_results WHERE result IS NOT NULL AND result != ''), 1) as percentage
            FROM test_results
            WHERE result IS NOT NULL AND result != ''
            GROUP BY result
            ORDER BY count DESC
            LIMIT 5
        ");
        
        $distribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $distribution[] = [
                'result' => $row['result'],
                'count' => (int)$row['count'],
                'percentage' => (float)$row['percentage']
            ];
        }
        return $distribution;
    } catch (Exception $e) {
        error_log('Error getting test result distribution: ' . $e->getMessage());
        return [];
    }
}

// Get analytics summary
function getAnalyticsSummary($db) {
    $summary = [
        'total_visitors' => 0,
        'total_completions' => 0,
        'result_distribution' => [],
        'daily_metrics' => [],
        'weekly_metrics' => [],
        'monthly_metrics' => []
    ];
    
    // Get test result distribution
    $summary['result_distribution'] = getTestResultDistribution($db);
    
    try {
        // Total visitors
        $stmt = $db->query("SELECT COUNT(DISTINCT session_id) as total_visitors FROM visits");
        $summary['total_visitors'] = (int)$stmt->fetchColumn();
        
        // Total test completions
        $stmt = $db->query("SELECT COUNT(*) as total_completions FROM test_results");
        $summary['total_completions'] = (int)$stmt->fetchColumn();
        
        // Generate a series of dates for the last 30 days
        $dateSeries = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dateSeries[$date] = [
                'date' => $date,
                'visitors' => 0,
                'completions' => 0
            ];
        }
        
        // Get daily metrics for the last 30 days
        $stmt = $db->query("
            WITH RECURSIVE dates(date) AS (
                SELECT date('now', '-29 days')
                UNION ALL
                SELECT date(date, '+1 day')
                FROM dates
                WHERE date < date('now')
            )
            SELECT 
                dates.date as date,
                COUNT(DISTINCT visits.session_id) as visitors,
                COUNT(DISTINCT test_results.id) as completions
            FROM dates
            LEFT JOIN visits ON DATE(visits.created_at) = dates.date
            LEFT JOIN test_results ON DATE(test_results.created_at) = dates.date
            GROUP BY dates.date
            ORDER BY dates.date ASC
        ");
        
        // Process the query results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = $row['date'];
            if (isset($dateSeries[$date])) {
                $dateSeries[$date] = [
                    'date' => $date,
                    'visitors' => (int)$row['visitors'],
                    'completions' => (int)$row['completions']
                ];
            }
        }
        
        $summary['daily_metrics'] = array_values(array_filter($dateSeries));
        
        // Weekly metrics (last 12 weeks)
        $stmt = $db->query("
            SELECT 
                strftime('%Y-W%W', created_at) as week,
                COUNT(DISTINCT session_id) as visitors,
                COUNT(DISTINCT CASE WHEN test_results.id IS NOT NULL THEN test_results.id END) as completions
            FROM visits
            LEFT JOIN test_results ON visits.session_id = test_results.session_id 
                AND DATE(visits.created_at) = DATE(test_results.created_at)
            WHERE visits.created_at >= date('now', '-84 days')
            GROUP BY strftime('%Y-W%W', visits.created_at)
            ORDER BY week DESC
            LIMIT 12
        ");
        
        $weekly = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $weekly[] = [
                'week' => $row['week'],
                'visitors' => (int)$row['visitors'],
                'completions' => (int)$row['completions']
            ];
        }
        $summary['weekly_metrics'] = $weekly;
        
        // Monthly metrics (last 12 months)
        $stmt = $db->query("
            SELECT 
                strftime('%Y-%m', visits.created_at) as month,
                COUNT(DISTINCT visits.session_id) as visitors,
                COUNT(DISTINCT test_results.id) as completions
            FROM visits
            LEFT JOIN test_results ON visits.session_id = test_results.session_id 
                AND strftime('%Y-%m', visits.created_at) = strftime('%Y-%m', test_results.created_at)
            WHERE visits.created_at >= date('now', '-365 days')
            GROUP BY strftime('%Y-%m', visits.created_at)
            ORDER BY month DESC
            LIMIT 12
        ");
        
        $monthly = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $monthly[] = [
                'month' => $row['month'],
                'visitors' => (int)$row['visitors'],
                'completions' => (int)$row['completions']
            ];
        }
        $summary['monthly_metrics'] = $monthly;
        
    } catch (Exception $e) {
        error_log('Error getting analytics summary: ' . $e->getMessage());
    }
    
    return $summary;
}

try {
    $db = getDb();
    
    // Track visit for analytics
    $sessionId = $_COOKIE['PHPSESSID'] ?? session_id();
    trackVisit($db, $sessionId);
    
    // Get analytics data
    $analyticsData = getAnalyticsSummary($db);
    
    // Add debug information
    $analyticsData['debug'] = [
        'total_results' => count($analyticsData['result_distribution']),
        'server_time' => date('Y-m-d H:i:s')
    ];
    
    // Return the data
    sendJsonResponse($analyticsData);
    
} catch (Exception $e) {
    error_log('Error in analytics endpoint: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ], 500);
}
