<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Get `path` from query string
$path = isset($_GET['path']) ? $_GET['path'] : '';
if (!$path) {
    echo json_encode(["error" => "No path provided"]);
    exit;
}

// Firebase Realtime DB URL
$firebase_url = "https://yush-6896d-default-rtdb.firebaseio.com" . $path;

// Optional: Add query param
if (isset($_GET['ts'])) {
    $firebase_url .= "?ts=" . $_GET['ts'];
} else {
    $firebase_url .= "?print=pretty";
}

$method = $_SERVER['REQUEST_METHOD'];

// CURL function
function sendCurlRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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

// Method handler
if ($method === 'GET') {
    echo sendCurlRequest($firebase_url);
} elseif ($method === 'PUT') {
    $input = file_get_contents("php://input");
    echo sendCurlRequest($firebase_url, 'PUT', $input);
} else {
    echo json_encode(["error" => "Unsupported Method: $method"]);
}