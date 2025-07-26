<?php

// Basic CORS headers for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check PATH
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

// If no path, show app launcher page
if (!$path || $path === '/') {
    header("Content-Type: text/html");
    echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Launching Injector App...</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      margin: 0;
      background: #f2f2f7;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .card {
      background: #fff;
      padding: 30px 24px;
      border-radius: 18px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.05);
      max-width: 360px;
      width: 90%;
      text-align: center;
    }

    .loader {
      width: 36px;
      height: 36px;
      border: 4px solid #ddd;
      border-top: 4px solid #007aff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 16px;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    h1 {
      font-size: 18px;
      margin: 0 0 8px;
      color: #111;
    }

    p {
      font-size: 14px;
      color: #555;
      margin: 0 0 20px;
    }

    .button {
      display: inline-block;
      background: #007aff;
      color: #fff;
      padding: 10px 24px;
      font-size: 14px;
      border: none;
      border-radius: 10px;
      text-decoration: none;
      margin: 6px;
      transition: background 0.2s ease;
    }

    .button:hover {
      background: #005fd1;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="loader"></div>
    <h1>Launching Injector App...</h1>
    <p>If the app doesn\'t open in 2 seconds,<br> use the buttons below.</p>
    <a href="#" class="button" onclick="retryAppOpen()">Reload App</a>
    <a href="https://t.me/+-AZsrS8mmRU1ZmE9" class="button" target="_blank">Join Telegram</a>
  </div>

  <script>
    function openApp() {
      var iframe = document.createElement("iframe");
      iframe.style.display = "none";
      iframe.src = "ankitinjector://open";
      document.body.appendChild(iframe);

      setTimeout(function () {
        // App didn\'t open? Redirect fallback
        window.location.href = "https://t.me/+-AZsrS8mmRU1ZmE9";
      }, 2000);
    }

    function retryAppOpen() {
      openApp();
    }

    // Try app open on load
    window.onload = openApp;
  </script>
</body>
</html>';
    exit;
}

// For valid path, send JSON
header("Content-Type: application/json");

// Firebase URL builder
$firebase_url = "https://yush-6896d-default-rtdb.firebaseio.com" . $path;
$firebase_url .= isset($_GET['ts']) ? "?ts=" . $_GET['ts'] : "?print=pretty";

// Request method
$method = $_SERVER['REQUEST_METHOD'];

// cURL function
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

// Handle request
if ($method === 'GET') {
    echo sendCurlRequest($firebase_url);
} elseif ($method === 'PUT') {
    $input = file_get_contents("php://input");
    echo sendCurlRequest($firebase_url, 'PUT', $input);
} else {
    echo json_encode(["error" => "Unsupported Method: $method"]);
}