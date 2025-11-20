<?php
/*
 * GenieACS Settings
 */

session_start();
// hide all error
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {
    include('../include/config.php');
    include('../include/readcfg.php');
    include('../include/headhtml.php');
    include('../include/menu.php');
    include('config.php');
    
    // Save settings
    if (isset($_POST['save'])) {
        $genieacs_host = $_POST['host'];
        $genieacs_port = $_POST['port'];
        $genieacs_protocol = $_POST['protocol'];
        $genieacs_username = $_POST['username'];
        $genieacs_password = $_POST['password'];
        
        // Save to config file
        $config_content = "<?php\n";
        $config_content .= "/*\n";
        $config_content .= " * GenieACS Configuration\n";
        $config_content .= " */\n\n";
        $config_content .= "// GenieACS API Configuration\n";
        $config_content .= "\$genieacs_host = '" . addslashes($genieacs_host) . "';  // GenieACS server IP/hostname\n";
        $config_content .= "\$genieacs_port = " . intval($genieacs_port) . ";         // Using port " . intval($genieacs_port) . " as confirmed by actual server configuration\n";
        $config_content .= "\$genieacs_protocol = '" . addslashes($genieacs_protocol) . "';   // http or https\n";
        $config_content .= "\$genieacs_username = '" . addslashes($genieacs_username) . "';       // If authentication is required\n";
        $config_content .= "\$genieacs_password = '" . addslashes($genieacs_password) . "';       // If authentication is required\n\n";
        $config_content .= "// Alternative ports to try if default fails\n";
        $config_content .= "\$genieacs_alternative_ports = array(80, 7557, 8080, 3000);\n\n";
        $config_content .= "// API Endpoints\n";
        $config_content .= "\$genieacs_api_base = \$genieacs_protocol . '://' . \$genieacs_host . ':' . \$genieacs_port;\n\n";
        $config_content .= "// Common TR-069 Parameters\n";
        $config_content .= "\$genieacs_parameters = array(\n";
        $config_content .= "    // WiFi Parameters\n";
        $config_content .= "    'wifi_ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',\n";
        $config_content .= "    'wifi_password' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',\n";
        $config_content .= "    \n";
        $config_content .= "    // PPPoE Parameters\n";
        $config_content .= "    'pppoe_username' => 'VirtualParameters.pppoeUsername',\n";
        $config_content .= "    'pppoe_username2' => 'VirtualParameters.pppoeUsername2',\n";
        $config_content .= "    'pppoe_password' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',\n";
        $config_content .= "    'pppoe_ip' => 'VirtualParameters.pppoeIP',\n";
        $config_content .= "    'pppoe_mac' => 'VirtualParameters.pppoeMac',\n";
        $config_content .= "    \n";
        $config_content .= "    // Device Info Parameters\n";
        $config_content .= "    'device_model' => 'InternetGatewayDevice.DeviceInfo.ModelName',\n";
        $config_content .= "    'device_manufacturer' => 'InternetGatewayDevice.DeviceInfo.Manufacturer',\n";
        $config_content .= "    'device_serial' => 'VirtualParameters.getSerialNumber',\n";
        $config_content .= "    'device_uptime' => 'VirtualParameters.getdeviceuptime',\n";
        $config_content .= "    \n";
        $config_content .= "    // Optical Parameters\n";
        $config_content .= "    'optical_rx_power' => 'VirtualParameters.RXPower',\n";
        $config_content .= "    'optical_pon_mode' => 'VirtualParameters.getponmode',\n";
        $config_content .= "    'optical_temp' => 'VirtualParameters.gettemp',\n";
        $config_content .= "    'optical_mac' => 'VirtualParameters.PonMac',\n";
        $config_content .= "    \n";
        $config_content .= "    // Network Parameters\n";
        $config_content .= "    'ip_tr069' => 'VirtualParameters.IPTR069',\n";
        $config_content .= "    'hotspot' => 'VirtualParameters.hotspot',\n";
        $config_content .= "    \n";
        $config_content .= "    // Connection Info\n";
        $config_content .= "    'total_associations' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.TotalAssociations',\n";
        $config_content .= "    'product_class' => 'DeviceID.ProductClass',\n";
        $config_content .= "    'registered_time' => 'Events.Registered',\n";
        $config_content .= "    'last_inform' => 'Events.Inform'\n";
        $config_content .= ");\n\n";
        $config_content .= "// Virtual Parameters (to be populated from GenieACS server)\n";
        $config_content .= "\$genieacs_virtual_parameters = array();\n\n";
        $config_content .= "?>";
        
        file_put_contents('config.php', $config_content);
        
        // Redirect to avoid resubmission
        echo "<script>window.location='./?genieacs&action=settings&session=" . $session . "'</script>";
        exit();
    }
}

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-cog"></i> <?= $_genieacs_settings ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="host">GenieACS Host</label>
                        <input type="text" class="form-control" id="host" name="host" value="<?= htmlspecialchars($genieacs_host) ?>" placeholder="localhost">
                    </div>
                    <div class="form-group">
                        <label for="port">GenieACS Port</label>
                        <input type="number" class="form-control" id="port" name="port" value="<?= htmlspecialchars($genieacs_port) ?>" placeholder="7557">
                    </div>
                    <div class="form-group">
                        <label for="protocol">Protocol</label>
                        <select class="form-control" id="protocol" name="protocol">
                            <option value="http"<?= $genieacs_protocol == 'http' ? ' selected' : '' ?>>HTTP</option>
                            <option value="https"<?= $genieacs_protocol == 'https' ? ' selected' : '' ?>>HTTPS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="username">Username (optional)</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($genieacs_username) ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password (optional)</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?= htmlspecialchars($genieacs_password) ?>">
                    </div>
                    <button type="submit" name="save" class="btn btn-primary"><i class="fa fa-save"></i> Save Settings</button>
                    <a href="./?genieacs&action=list&session=<?= $session; ?>" class="btn btn-secondary"><i class="fa fa-list"></i> <?= $_genieacs_devices ?></a>
                </form>
            </div>
        </div>
    </div>
</div>