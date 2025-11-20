<?php
/*
 * WhatsApp Gateway Test
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
    }
    
    $test_results = array();
    
    // Test each enabled gateway
    if (isset($whatsapp_gateways)) {
        foreach ($whatsapp_gateways as $key => $gateway) {
            if ($gateway['enabled']) {
                $test_results[$key] = test_gateway($gateway);
            }
        }
    }
}

// Function to test gateway connectivity
function test_gateway($gateway) {
    $result = array(
        'name' => $gateway['name'],
        'status' => 'unknown',
        'message' => ''
    );
    
    // For now, we'll just check if we can connect to the API URL
    if (!empty($gateway['api_url'])) {
        $ch = curl_init($gateway['api_url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 400) {
            $result['status'] = 'success';
            $result['message'] = 'Connection successful (HTTP ' . $http_code . ')';
        } else if (!empty($error)) {
            $result['status'] = 'error';
            $result['message'] = 'Connection failed: ' . $error;
        } else {
            $result['status'] = 'warning';
            $result['message'] = 'Connection response: HTTP ' . $http_code;
        }
    } else {
        $result['status'] = 'error';
        $result['message'] = 'API URL is not configured';
    }
    
    return $result;
}

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-test"></i> WhatsApp Gateway Test</h3>
            </div>
            <div class="card-body">
                <h4>Connection Tests</h4>
                <?php if (empty($test_results)): ?>
                    <div class="alert alert-warning">No enabled WhatsApp gateways found. Please configure them in the settings.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Gateway</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($test_results as $key => $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['name']); ?></td>
                                        <td>
                                            <?php if ($result['status'] == 'success'): ?>
                                                <span class="badge badge-success">Success</span>
                                            <?php elseif ($result['status'] == 'error'): ?>
                                                <span class="badge badge-danger">Error</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Warning</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['message']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <h4>Webhook URL</h4>
                <div class="form-group">
                    <input type="text" class="form-control" readonly value="<?php echo isset($whatsapp_webhook_url) ? htmlspecialchars($whatsapp_webhook_url) : ''; ?>">
                    <small class="form-text text-muted">Configure this URL in your WhatsApp gateway dashboard</small>
                </div>
                
                <h4>Test Message</h4>
                <p>To test the webhook, you can send a POST request to the webhook URL with the following JSON payload:</p>
                <pre>{
  "message": "ping",
  "from": "6281234567890"
}</pre>
                
                <a href="./?whatsapp=settings&session=<?= $session; ?>" class="btn bg-primary"><i class="fa fa-cog"></i> Configure Settings</a>
            </div>
        </div>
    </div>
</div>