<?php
/*
 * WhatsApp Gateway Log
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
    
    // Log file path
    $log_file = 'whatsapp.log';
    
    // Clear log
    if (isset($_GET['clear'])) {
        file_put_contents($log_file, '');
        echo "<script>window.location='./?whatsapp=log&session=" . $session . "'</script>";
        exit();
    }
    
    // Read log content
    $log_content = '';
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
    }
}

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-file-text"></i> WhatsApp Gateway Log</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <a href="./?whatsapp=log&clear=true&session=<?= $session; ?>" class="btn bg-danger"><i class="fa fa-trash"></i> Clear Log</a>
                    <a href="./?whatsapp=settings&session=<?= $session; ?>" class="btn bg-primary"><i class="fa fa-cog"></i> Settings</a>
                </div>
                
                <div class="form-group">
                    <textarea class="form-control" rows="20" readonly style="font-family: monospace;"><?= htmlspecialchars($log_content); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>