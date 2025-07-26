<?php

// Basic CORS headers for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check PATH
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

// If no path is provided, redirect to app or Telegram fallback
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
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      color: #333;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .loader {
      border: 6px solid #eee;
      border-top: 6px solid #007aff;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    h1 {
      font-size: 20px;
      margin: 10px 0;
      color: #007aff;
    }

    p {
      font-size: 14px;
      text-align: center;
      margin: 0 20px 20px;
      color: #555;
    }

    .fallback-btn {
      background: #007aff;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .fallback-btn:hover {
      background: #005fd1;
    }

    .logo {
      width: 60px;
      height: 60px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <img src="https://cdn-icons-png.flaticon.com/512/833/833472.png" class="logo" alt="App Logo" />
  <div class="loader"></div>
  <h1>Opening Injector App...</h1>
  <p>If the app doesn\'t open automatically,<br> tap the button below:</p>
  <a class="fallback-btn" href="https://t.me/+-AZsrS8mmRU1ZmE9" target="_blank">Join Telegram Group</a>

  <script>
    window.onload = function () {
      var start = Date.now();
      // Create hidden iframe to try opening app
      var iframe = document.createElement("iframe");
      iframe.style.display = "none";
      iframe.src = "ankitinjector://open";
      document.body.appendChild(iframe);

      // Wait 7 seconds before redirecting to Telegram
      setTimeout(function () {
        if (Date.now() - start < 6500) {
          // App probably did not open, so redirect
          window.location.href = "https://t.me/+-AZsrS8mmRU1ZmE9";
        }
      }, 7000);
    };
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only

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