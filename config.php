<?php
/*
 * GenieACS Configuration
 */

// GenieACS API Configuration
$genieacs_host = '192.168.8.185';  // Change to your GenieACS server IP/hostname
$genieacs_port = 7557;         // Default GenieACS NBI port
$genieacs_protocol = 'http';   // http or https
$genieacs_username = 'alijaya';       // If authentication is required
$genieacs_password = '060111';       // If authentication is required

// API Endpoints
$genieacs_api_base = $genieacs_protocol . '://' . $genieacs_host . ':' . $genieacs_port;

// Common TR-069 Parameters
$genieacs_parameters = array(
    // WiFi Parameters
    'wifi_ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
    'wifi_password' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
    
    // PPPoE Parameters
    'pppoe_username' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
    'pppoe_password' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',
    'pppoe_ip' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress',
    
    // Device Info Parameters
    'device_model' => 'InternetGatewayDevice.DeviceInfo.ModelName',
    'device_manufacturer' => 'InternetGatewayDevice.DeviceInfo.Manufacturer',
    'device_serial' => 'InternetGatewayDevice.DeviceInfo.SerialNumber',
    'device_uptime' => 'InternetGatewayDevice.DeviceInfo.UpTime',
    
    // Optical Parameters (may vary by device)
    'optical_rx_power' => 'InternetGatewayDevice.Optical.Power.RxPower',
    'optical_tx_power' => 'InternetGatewayDevice.Optical.Power.TxPower'
);

// Virtual Parameters (to be populated from GenieACS server)
$genieacs_virtual_parameters = array();

?>