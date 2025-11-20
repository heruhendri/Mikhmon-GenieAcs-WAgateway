<?php
// Check if cURL is available in web server context

echo "<h2>cURL Availability Check</h2>";

if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✓ cURL extension is available</p>";
    
    // Test a simple cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.8.89:7557/devices/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>Test Request Results:</h3>";
    echo "<p>HTTP Code: " . $http_code . "</p>";
    echo "<p>cURL Error: " . $error . "</p>";
    
    if (!empty($response)) {
        echo "<p>Response received (first 200 chars): " . htmlspecialchars(substr($response, 0, 200)) . "...</p>";
    } else {
        echo "<p>No response received</p>";
    }
} else {
    echo "<p style='color: red;'>✗ cURL extension is NOT available</p>";
    echo "<p>Please check your web server's PHP configuration.</p>";
}

// Show PHP info
echo "<h3>PHP Configuration Info:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>SAPI: " . php_sapi_name() . "</p>";
?>