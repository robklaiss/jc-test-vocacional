<?php
// Simple router for PHP built-in server
$request = $_SERVER['REQUEST_URI'];

// Add CORS headers for all requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route API requests
if (strpos($request, '/api/') === 0) {
    $apiFile = __DIR__ . $request;
    if (file_exists($apiFile)) {
        require $apiFile;
        return true;
    }
}

// Default: serve the requested file or index.html
if (file_exists(__DIR__ . $request) && is_file(__DIR__ . $request)) {
    return false; // Serve the file directly
}

// For all other requests, serve index.html (single page app)
include __DIR__ . '/index.html';
?>
