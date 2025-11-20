<?php
// Include configuration
include('config.php');

// Test connection to GenieACS server on multiple ports

$host = $genieacs_host;
$ports = $genieacs_alternative_ports;

echo "<h2>GenieACS Connection Test</h2>";
echo "<p>Testing connection to GenieACS server at " . $host . " on multiple ports:</p>";

foreach ($ports as $port) {
    echo "<h3>Testing port " . $port . ":</h3>";
    
    // Test using fsockopen
    $connection = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($connection) {
        echo "<p style='color: green;'>✓ Connection successful</p>";
        fclose($connection);
        
        // Try to get a simple response
        $url = $genieacs_protocol . "://" . $host . ":" . $port . "/";
        $response = @file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'timeout' => 5,
                'method' => 'GET'
            )
        )));
        
        if ($response !== false) {
            echo "<p>Response received (first 200 chars): " . htmlspecialchars(substr($response, 0, 200)) . "...</p>";
        } else {
            echo "<p>No response received</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Connection failed: " . $errstr . " (" . $errno . ")</p>";
    }
    
    echo "<hr>";
}

echo "<h3>Recommendations:</h3>";
echo "<ul>";
echo "<li>If no ports are accessible, check if GenieACS is running on the target server</li>";
echo "<li>Verify firewall settings on both servers</li>";
echo "<li>Check if GenieACS is configured to listen on the correct interface (0.0.0.0 vs 127.0.0.1)</li>";
echo "<li>Try accessing GenieACS directly through a browser to verify it's running</li>";
echo "</ul>";
?>