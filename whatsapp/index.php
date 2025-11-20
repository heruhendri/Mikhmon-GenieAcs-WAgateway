<?php
/*
 * WhatsApp Gateway Settings
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
    
    // Load WhatsApp configuration
    $config_file = 'config.php';
    if (file_exists($config_file)) {
        include($config_file);
    } else {
        // Default configuration
        $whatsapp_gateways = array();
        $whatsapp_admins = array();
        $default_whatsapp_gateway = '';
        $whatsapp_webhook_url = '';
        $whatsapp_hotspot_settings = array();
        $whatsapp_pppoe_settings = array();
    }
    
    // Save settings
    if (isset($_POST['save'])) {
        // Process form data
        $whatsapp_gateways = array();
        $whatsapp_admins = array();
        
        // Process gateways
        if (isset($_POST['gateways'])) {
            foreach ($_POST['gateways'] as $key => $gateway) {
                $whatsapp_gateways[$key] = array(
                    'name' => $gateway['name'],
                    'api_url' => $gateway['api_url'],
                    'token' => $gateway['token'],
                    'enabled' => isset($gateway['enabled']) ? true : false
                );
            }
        }
        
        // Process admins
        if (isset($_POST['admins'])) {
            $whatsapp_admins = explode("\n", trim($_POST['admins']));
            $whatsapp_admins = array_map('trim', $whatsapp_admins);
            $whatsapp_admins = array_filter($whatsapp_admins);
        }
        
        $default_whatsapp_gateway = $_POST['default_gateway'];
        $whatsapp_webhook_url = $_POST['webhook_url'];
        
        // Process Hotspot settings
        $whatsapp_hotspot_settings = array(
            'default_server' => $_POST['hotspot_server'],
            'default_profile' => $_POST['hotspot_profile'],
            'default_character_type' => $_POST['hotspot_char'],
            'default_username_length' => $_POST['hotspot_userl'],
            'default_prefix' => $_POST['hotspot_prefix'],
            'default_time_limit' => $_POST['hotspot_timelimit'],
            'default_data_limit' => $_POST['hotspot_datalimit'],
            'default_data_limit_unit' => $_POST['hotspot_mbgb'],
            'user_mode' => $_POST['hotspot_user']
        );
        
        // Process PPPoE settings
        $whatsapp_pppoe_settings = array(
            'default_service' => $_POST['pppoe_service'],
            'default_profile' => $_POST['pppoe_profile'],
            'default_caller_id' => $_POST['pppoe_callerid'],
            'default_interval' => $_POST['pppoe_interval']
        );
        
        // Save to config file
        save_whatsapp_config($config_file, $whatsapp_gateways, $whatsapp_admins, $default_whatsapp_gateway, $whatsapp_webhook_url, $whatsapp_hotspot_settings, $whatsapp_pppoe_settings);
        
        // Redirect to avoid resubmission
        echo "<script>window.location='./?whatsapp=settings&session=" . $session . "'</script>";
        exit();
    }
}

// Function to save WhatsApp configuration
function save_whatsapp_config($file, $gateways, $admins, $default_gateway, $webhook_url, $hotspot_settings, $pppoe_settings) {
    $content = "<?php\n";
    $content .= "/*\n";
    $content .= " * WhatsApp Gateway Configuration\n";
    $content .= " * Multi-platform support for FONNTE, MPWA, WABLAS, etc.\n";
    $content .= " */\n\n";
    
    $content .= "// WhatsApp Gateway configurations\n";
    $content .= "\$whatsapp_gateways = array(\n";
    foreach ($gateways as $key => $gateway) {
        $content .= "    '$key' => array(\n";
        $content .= "        'name' => '" . addslashes($gateway['name']) . "',\n";
        $content .= "        'api_url' => '" . addslashes($gateway['api_url']) . "',\n";
        $content .= "        'token' => '" . addslashes($gateway['token']) . "',\n";
        $content .= "        'enabled' => " . ($gateway['enabled'] ? 'true' : 'false') . "\n";
        $content .= "    ),\n";
    }
    $content .= ");\n\n";
    
    $content .= "// Admin WhatsApp numbers (authorized users)\n";
    $content .= "\$whatsapp_admins = array(\n";
    foreach ($admins as $admin) {
        $content .= "    '" . addslashes($admin) . "',\n";
    }
    $content .= ");\n\n";
    
    $content .= "// Default gateway to use\n";
    $content .= "\$default_whatsapp_gateway = '" . addslashes($default_gateway) . "';\n\n";
    
    $content .= "// Webhook URL for receiving messages\n";
    $content .= "\$whatsapp_webhook_url = '" . addslashes($webhook_url) . "';\n\n";
    
    $content .= "// Hotspot user generation settings\n";
    $content .= "\$whatsapp_hotspot_settings = array(\n";
    $content .= "    'default_server' => '" . addslashes($hotspot_settings['default_server']) . "',\n";
    $content .= "    'default_profile' => '" . addslashes($hotspot_settings['default_profile']) . "',\n";
    $content .= "    'default_character_type' => '" . addslashes($hotspot_settings['default_character_type']) . "',\n";
    $content .= "    'default_username_length' => '" . addslashes($hotspot_settings['default_username_length']) . "',\n";
    $content .= "    'default_prefix' => '" . addslashes($hotspot_settings['default_prefix']) . "',\n";
    $content .= "    'default_time_limit' => '" . addslashes($hotspot_settings['default_time_limit']) . "',\n";
    $content .= "    'default_data_limit' => '" . addslashes($hotspot_settings['default_data_limit']) . "',\n";
    $content .= "    'default_data_limit_unit' => '" . addslashes($hotspot_settings['default_data_limit_unit']) . "',\n";
    $content .= "    'user_mode' => '" . addslashes($hotspot_settings['user_mode']) . "'\n";
    $content .= ");\n\n";
    
    $content .= "// PPPoE user settings\n";
    $content .= "\$whatsapp_pppoe_settings = array(\n";
    $content .= "    'default_service' => '" . addslashes($pppoe_settings['default_service']) . "',\n";
    $content .= "    'default_profile' => '" . addslashes($pppoe_settings['default_profile']) . "',\n";
    $content .= "    'default_caller_id' => '" . addslashes($pppoe_settings['default_caller_id']) . "',\n";
    $content .= "    'default_interval' => '" . addslashes($pppoe_settings['default_interval']) . "'\n";
    $content .= ");\n\n";
    
    $content .= "?>";
    
    file_put_contents($file, $content);
}

// Get hotspot profiles and servers for dropdowns
$getprofile = array();
$srvlist = array();
if (isset($API)) {
    $getprofile = $API->comm("/ip/hotspot/user/profile/print");
    $srvlist = $API->comm("/ip/hotspot/print");
}

// Get PPPoE profiles
$getpppprofile = array();
if (isset($API)) {
    $getpppprofile = $API->comm("/ppp/profile/print");
}

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-whatsapp"></i> WhatsApp Gateway Settings</h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#gateway">Gateway</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#admins">Admins</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#hotspot">Hotspot</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pppoe">PPPoE</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div id="gateway" class="container tab-pane active"><br>
                            <div class="form-group">
                                <label>Webhook URL</label>
                                <input type="text" class="form-control" name="webhook_url" value="<?php echo htmlspecialchars($whatsapp_webhook_url); ?>" placeholder="https://yourdomain.com/whatsapp/webhook.php">
                                <small class="form-text text-muted">URL where WhatsApp gateways will send messages</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Default Gateway</label>
                                <select class="form-control" name="default_gateway">
                                    <option value="">Select Default Gateway</option>
                                    <?php foreach ($whatsapp_gateways as $key => $gateway): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($default_whatsapp_gateway == $key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($gateway['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <h4>WhatsApp Gateways</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Gateway</th>
                                            <th>API URL</th>
                                            <th>Token</th>
                                            <th>Enabled</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $default_gateways = array(
                                            'fonnte' => array('name' => 'FONNTE', 'api_url' => 'https://api.fonnte.com'),
                                            'mpwa' => array('name' => 'MPWA', 'api_url' => 'https://mpwa.id'),
                                            'wablas' => array('name' => 'WABLAS', 'api_url' => 'https://wablas.com')
                                        );
                                        
                                        // Merge with existing gateways
                                        foreach ($default_gateways as $key => $gateway) {
                                            if (!isset($whatsapp_gateways[$key])) {
                                                $whatsapp_gateways[$key] = array(
                                                    'name' => $gateway['name'],
                                                    'api_url' => $gateway['api_url'],
                                                    'token' => '',
                                                    'enabled' => false
                                                );
                                            }
                                        }
                                        
                                        foreach ($whatsapp_gateways as $key => $gateway): ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="gateways[<?php echo $key; ?>][name]" value="<?php echo htmlspecialchars($gateway['name']); ?>">
                                                    <?php echo htmlspecialchars($gateway['name']); ?>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="gateways[<?php echo $key; ?>][api_url]" value="<?php echo htmlspecialchars($gateway['api_url']); ?>">
                                                </td>
                                                <td>
                                                    <input type="password" class="form-control" name="gateways[<?php echo $key; ?>][token]" value="<?php echo htmlspecialchars($gateway['token']); ?>">
                                                </td>
                                                <td>
                                                    <input type="checkbox" name="gateways[<?php echo $key; ?>][enabled]" <?php echo ($gateway['enabled']) ? 'checked' : ''; ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div id="admins" class="container tab-pane fade"><br>
                            <h4>Authorized Admins</h4>
                            <div class="form-group">
                                <textarea class="form-control" name="admins" rows="5" placeholder="Enter one WhatsApp number per line&#10;Example:&#10;6281234567890&#10;6281234567891"><?php echo htmlspecialchars(implode("\n", $whatsapp_admins)); ?></textarea>
                                <small class="form-text text-muted">List of WhatsApp numbers authorized to execute commands</small>
                            </div>
                        </div>
                        
                        <div id="hotspot" class="container tab-pane fade"><br>
                            <h4>Hotspot User Generation Settings</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default Server</label>
                                        <select class="form-control" name="hotspot_server">
                                            <option value="all" <?php echo (isset($whatsapp_hotspot_settings['default_server']) && $whatsapp_hotspot_settings['default_server'] == 'all') ? 'selected' : ''; ?>>all</option>
                                            <?php 
                                            if (isset($srvlist) && is_array($srvlist)) {
                                                foreach ($srvlist as $srv) {
                                                    $selected = (isset($whatsapp_hotspot_settings['default_server']) && $whatsapp_hotspot_settings['default_server'] == $srv['name']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($srv['name']) . "' $selected>" . htmlspecialchars($srv['name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Default Profile</label>
                                        <select class="form-control" name="hotspot_profile">
                                            <option value="">Select Profile</option>
                                            <?php 
                                            if (isset($getprofile) && is_array($getprofile)) {
                                                foreach ($getprofile as $prof) {
                                                    $selected = (isset($whatsapp_hotspot_settings['default_profile']) && $whatsapp_hotspot_settings['default_profile'] == $prof['name']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($prof['name']) . "' $selected>" . htmlspecialchars($prof['name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>User Mode</label>
                                        <select class="form-control" name="hotspot_user">
                                            <option value="vc" <?php echo (isset($whatsapp_hotspot_settings['user_mode']) && $whatsapp_hotspot_settings['user_mode'] == 'vc') ? 'selected' : ''; ?>>Voucher (Username = Password)</option>
                                            <option value="up" <?php echo (isset($whatsapp_hotspot_settings['user_mode']) && $whatsapp_hotspot_settings['user_mode'] == 'up') ? 'selected' : ''; ?>>User/Password (Username ≠ Password)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Character Type</label>
                                        <select class="form-control" name="hotspot_char">
                                            <option value="lower" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'lower') ? 'selected' : ''; ?>>Random abcd</option>
                                            <option value="upper" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'upper') ? 'selected' : ''; ?>>Random ABCD</option>
                                            <option value="upplow" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'upplow') ? 'selected' : ''; ?>>Random aBcD</option>
                                            <option value="mix" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'mix') ? 'selected' : ''; ?>>Random abcd123</option>
                                            <option value="mix1" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'mix1') ? 'selected' : ''; ?>>Random ABCD123</option>
                                            <option value="mix2" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'mix2') ? 'selected' : ''; ?>>Random aBcD123</option>
                                            <option value="num" <?php echo (isset($whatsapp_hotspot_settings['default_character_type']) && $whatsapp_hotspot_settings['default_character_type'] == 'num') ? 'selected' : ''; ?>>Random 1234</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username Length</label>
                                        <select class="form-control" name="hotspot_userl">
                                            <?php for ($i = 3; $i <= 8; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo (isset($whatsapp_hotspot_settings['default_username_length']) && $whatsapp_hotspot_settings['default_username_length'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Prefix</label>
                                        <input type="text" class="form-control" name="hotspot_prefix" value="<?php echo isset($whatsapp_hotspot_settings['default_prefix']) ? htmlspecialchars($whatsapp_hotspot_settings['default_prefix']) : ''; ?>" placeholder="Prefix for usernames">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Time Limit</label>
                                        <input type="text" class="form-control" name="hotspot_timelimit" value="<?php echo isset($whatsapp_hotspot_settings['default_time_limit']) ? htmlspecialchars($whatsapp_hotspot_settings['default_time_limit']) : ''; ?>" placeholder="e.g., 1h, 30m, 1d">
                                        <small class="form-text text-muted">Format: [wdhm] e.g., 30d = 30 days, 12h = 12 hours</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Data Limit</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="hotspot_datalimit" value="<?php echo isset($whatsapp_hotspot_settings['default_data_limit']) ? htmlspecialchars($whatsapp_hotspot_settings['default_data_limit']) : ''; ?>" placeholder="Data limit">
                                            <select class="form-control" name="hotspot_mbgb">
                                                <option value="1048576" <?php echo (isset($whatsapp_hotspot_settings['default_data_limit_unit']) && $whatsapp_hotspot_settings['default_data_limit_unit'] == '1048576') ? 'selected' : ''; ?>>MB</option>
                                                <option value="1073741824" <?php echo (isset($whatsapp_hotspot_settings['default_data_limit_unit']) && $whatsapp_hotspot_settings['default_data_limit_unit'] == '1073741824') ? 'selected' : ''; ?>>GB</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="pppoe" class="container tab-pane fade"><br>
                            <h4>PPPoE User Settings</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default Service</label>
                                        <select class="form-control" name="pppoe_service">
                                            <option value="any" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'any') ? 'selected' : ''; ?>>any</option>
                                            <option value="async" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'async') ? 'selected' : ''; ?>>async</option>
                                            <option value="l2tp" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'l2tp') ? 'selected' : ''; ?>>l2tp</option>
                                            <option value="ovpn" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'ovpn') ? 'selected' : ''; ?>>ovpn</option>
                                            <option value="pppoe" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'pppoe') ? 'selected' : ''; ?>>pppoe</option>
                                            <option value="pptp" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'pptp') ? 'selected' : ''; ?>>pptp</option>
                                            <option value="sstp" <?php echo (isset($whatsapp_pppoe_settings['default_service']) && $whatsapp_pppoe_settings['default_service'] == 'sstp') ? 'selected' : ''; ?>>sstp</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Default Profile</label>
                                        <select class="form-control" name="pppoe_profile">
                                            <option value="">Select Profile</option>
                                            <?php 
                                            if (isset($getpppprofile) && is_array($getpppprofile)) {
                                                foreach ($getpppprofile as $prof) {
                                                    $selected = (isset($whatsapp_pppoe_settings['default_profile']) && $whatsapp_pppoe_settings['default_profile'] == $prof['name']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($prof['name']) . "' $selected>" . htmlspecialchars($prof['name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Caller ID</label>
                                        <input type="text" class="form-control" name="pppoe_callerid" value="<?php echo isset($whatsapp_pppoe_settings['default_caller_id']) ? htmlspecialchars($whatsapp_pppoe_settings['default_caller_id']) : ''; ?>" placeholder="Caller ID">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Interval</label>
                                        <input type="text" class="form-control" name="pppoe_interval" value="<?php echo isset($whatsapp_pppoe_settings['default_interval']) ? htmlspecialchars($whatsapp_pppoe_settings['default_interval']) : '30d'; ?>" placeholder="e.g., 30d">
                                        <small class="form-text text-muted">Format: [wdhm] e.g., 30d = 30 days</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save" class="btn bg-primary"><i class="fa fa-save"></i> Save Settings</button>
                    <a href="./?whatsapp=test&session=<?= $session; ?>" class="btn bg-success"><i class="fa fa-test"></i> Test Connection</a>
                </form>
            </div>
        </div>
    </div>
</div>