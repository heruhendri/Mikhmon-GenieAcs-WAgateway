<?php
// Test GenieACS Connection with Authentication

include('config.php');

echo "<h2>GenieACS Configuration Test with Authentication</h2>";
echo "<p>Host: " . $genieacs_host . "</p>";
echo "<p>Port: " . $genieacs_port . "</p>";
echo "<p>Protocol: " . $genieacs_protocol . "</p>";
echo "<p>Username: " . $genieacs_username . "</p>";
echo "<p>Password: " . (empty($genieacs_password) ? "Not set" : "Set (hidden)") . "</p>";
echo "<p>API Base URL: " . $genieacs_api_base . "</p>";

// Check if cURL is available
if (!function_exists('curl_init')) {
    echo "<p style='color: red; font-weight: bold;'>✗ cURL extension is not available in this PHP installation</p>";
    echo "<p>Please enable cURL extension in your PHP configuration (php.ini) to use GenieACS integration.</p>";
    exit;
}

// Test network connectivity
echo "<h3>Network Connectivity Test:</h3>";
$connection = @fsockopen($genieacs_host, $genieacs_port, $errno, $errstr, 5);
if ($connection) {
    echo "<p style='color: green;'>✓ Connection to " . $genieacs_host . ":" . $genieacs_port . " successful</p>";
    fclose($connection);
} else {
    echo "<p style='color: red;'>✗ Connection to " . $genieacs_host . ":" . $genieacs_port . " failed: " . $errstr . " (" . $errno . ")</p>";
}

// Test connection
$url = $genieacs_api_base . '/devices/';
echo "<p>Testing URL: " . $url . "</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Set headers
$headers = array('Content-Type: application/json');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Add authentication if required
if (!empty($genieacs_username)) {
    curl_setopt($ch, CURLOPT_USERPWD, $genieacs_username . ':' . $genieacs_password);
    echo "<p>Authentication: Enabled</p>";
} else {
    echo "<p>Authentication: Disabled</p>";
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Connection Test Results:</h3>";
echo "<p>HTTP Code: " . $http_code . "</p>";
echo "<p>cURL Error: " . $error . "</p>";

if (!empty($response)) {
    // Limit response display to first 1000 characters to avoid overwhelming output
    $display_response = strlen($response) > 1000 ? substr($response, 0, 1000) . '...' : $response;
    echo "<p>Response (first 1000 chars): " . htmlspecialchars($display_response) . "</p>";
    
    // Try to decode JSON
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Decoded Response (first item):</h3>";
        if (is_array($decoded) && !empty($decoded)) {
            echo "<pre>" . htmlspecialchars(print_r($decoded[0], true)) . "</pre>";
        } else {
            echo "<pre>" . htmlspecialchars(print_r($decoded, true)) . "</pre>";
        }
    } else {
        echo "<p>JSON Decode Error: " . json_last_error_msg() . "</p>";
    }
} else {
    echo "<p>No response received</p>";
}
?>