<?php

// Basic CORS headers for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check PATH
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

// If no path is provided, redirect to app or Telegram with UI
if (!$path || $path === '/') {
    header("Content-Type: text/html");
    echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Launching Injector App...</title>
  <meta http-equiv="refresh"
        content="1; url=intent://open#Intent;scheme=ankitinjector;package=com.ankit.injector;S.browser_fallback_url=https%3A%2F%2Ft.me%2F%2B-AZsrS8mmRU1ZmE9;end;">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #0f0f0f, #1f1f1f);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      flex-direction: column;
    }

    .loader {
      border: 6px solid #444;
      border-top: 6px solid #00ffcc;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    h1 {
      font-size: 1.5rem;
      margin: 10px 0;
      color: #00ffcc;
    }

    p {
      font-size: 1rem;
      color: #bbb;
      text-align: center;
      margin: 0 20px 20px;
    }

    .fallback-btn {
      background: #00ffcc;
      color: #000;
      padding: 10px 25px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.2s ease;
    }

    .fallback-btn:hover {
      background: #00ddaa;
    }

    .logo {
      width: 80px;
      height: 80px;
      border-radius: 20%;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <img src="https://cdn-icons-png.flaticon.com/512/833/833472.png" class="logo" alt="App Logo" />
  <div class="loader"></div>
  <h1>Launching Injector App...</h1>
  <p>If the app does not open automatically,<br> tap the button below:</p>
  <a class="fallback-btn" href="https://t.me/+-AZsrS8mmRU1ZmE9" target="_blank">Join Telegram Group</a>
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev only

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