<?php

// Basic CORS headers for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check PATH
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

// If no path is provided, redirect to app
if (!$path || $path === '/') {
    header("Content-Type: text/html"); // ✅ Fix: Show HTML not JSON
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
        <meta http-equiv="refresh" content="0; url=intent://open#Intent;scheme=ankitinjector;package=com.ankit.injector;end;">
    </head>
    <body>
        <p>Opening app...</p>
    </body>
    </html>';
    exit;
}

// For valid path, send JSON
header("Content-Type: application/json");

// Build Firebase URL
$firebase_url = "https://yush-6896d-default-rtdb.firebaseio.com" . $path;

// Add query string
if (isset($_GET['ts'])) {
    $firebase_url .= "?ts=" . $_GET['ts'];
} else {
    $firebase_url .= "?print=pretty";
}

// Request method
$method = $_SERVER['REQUEST_METHOD'];

// cURL function
function sendCurlRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        http_response_code(500);
        return json_encode([
            "error" => true,
            "curl_error" => $error,
            "firebase_url" => $url
        ]);
    }

    http_response_code($http_code);
    return $response;
}

// Handle request
if ($method === 'GET') {
    echo sendCurlRequest($firebase_url);
} elseif ($method === 'PUT') {
    $input = file_get_contents("php://input");
    echo sendCurlRequest($firebase_url, 'PUT', $input);
} else {
    echo json_encode(["error" => "Unsupported Method: $method"]);
}