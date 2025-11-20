<?php
/*
 * WhatsApp Webhook Handler
 * Handles incoming messages from multiple WhatsApp gateways
 */

// Include required files
include('../include/config.php');
include('../include/readcfg.php');

// Load WhatsApp configuration
$config_file = 'config.php';
if (file_exists($config_file)) {
    include($config_file);
}

// Log function
function log_message($message) {
    $log_file = 'whatsapp.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Function to detect which gateway sent the message
function detect_gateway($user_agent, $data) {
    // FONNTE detection
    if (isset($data['message']) && isset($data['from'])) {
        return 'fonnte';
    }
    
    // MPWA detection
    if (isset($data['number']) && isset($data['message'])) {
        return 'mpwa';
    }
    
    // WABLAS detection
    if (isset($data['phone']) && isset($data['message'])) {
        return 'wablas';
    }
    
    return 'unknown';
}

// Function to process FONNTE message
function process_fonnte_message($data) {
    $from = $data['from'];
    $message = trim($data['message']);
    
    return execute_command($from, $message);
}

// Function to process MPWA message
function process_mpwa_message($data) {
    $from = $data['number'];
    $message = trim($data['message']);
    
    return execute_command($from, $message);
}

// Function to process WABLAS message
function process_wablas_message($data) {
    $from = $data['phone'];
    $message = trim($data['message']);
    
    return execute_command($from, $message);
}

// Function to check if user is authorized admin
function is_authorized_admin($from) {
    global $whatsapp_admins;
    
    // Check if admins list is configured
    if (!isset($whatsapp_admins) || !is_array($whatsapp_admins)) {
        return false;
    }
    
    // Check if from number is in admins list
    return in_array($from, $whatsapp_admins);
}

// Function to get API connection
function get_api_connection() {
    global $iphost, $userhost, $passwdhost, $hotspotname, $dnsname, $currency;
    global $phone, $email, $mtuser, $mtpasswd, $mtip, $mtport;
    
    include('../lib/routeros_api.class.php');
    $API = new RouterosAPI();
    $API->debug = false;
    
    if ($API->connect($iphost, $userhost, $passwdhost)) {
        return $API;
    }
    
    return null;
}

// Function to get help message
function get_help_message() {
    $help = "Perintah yang tersedia:\n";
    $help .= "help - Tampilkan pesan bantuan ini\n";
    $help .= "ping - Uji gateway WhatsApp\n";
    $help .= "voucher <profile> - Buat voucher dengan profil tertentu\n";
    $help .= "member <username> <password> <profile> - Buat member dengan username/password\n";
    $help .= "generate <jumlah> <profile> - Generate otomatis voucher\n";
    $help .= "hotspot list - Daftar pengguna hotspot\n";
    $help .= "hotspot remove <username> - Hapus pengguna hotspot\n";
    $help .= "pppoe list - Daftar pengguna PPPoE\n";
    $help .= "pppoe add <username> <password> <profile> - Tambah pengguna PPPoE\n";
    $help .= "pppoe remove <username> - Hapus pengguna PPPoE";
    
    return $help;
}

// Function to execute MikroTik commands based on WhatsApp messages
function execute_command($from, $message) {
    global $session, $whatsapp_gateways, $default_whatsapp_gateway;
    
    // Check if user is authorized
    if (!is_authorized_admin($from)) {
        return "❌ Anda tidak diotorisasi untuk menjalankan perintah.";
    }
    
    // Parse command
    $parts = explode(' ', $message);
    $command = strtolower($parts[0]);
    
    switch ($command) {
        case 'help':
            return get_help_message();
            
        case 'ping':
            return "✅ Gateway WhatsApp berfungsi dengan baik!";
            
        case 'voucher':
            return create_voucher_user($parts);
            
        case 'member':
            return create_member_user($parts);
            
        case 'generate':
            return generate_voucher_users($parts);
            
        case 'hotspot':
            return handle_hotspot_command($parts);
            
        case 'pppoe':
            return handle_pppoe_command($parts);
            
        default:
            return "❓ Perintah tidak dikenali. Ketik 'help' untuk melihat perintah yang tersedia.";
    }
}

// Function to create voucher user (username = password)
function create_voucher_user($parts) {
    global $whatsapp_hotspot_settings;
    
    if (count($parts) < 2) {
        return "❓ Penggunaan: voucher <profile>";
    }
    
    $profile = $parts[1];
    
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Check if profile exists
    $profiles = $API->comm("/ip/hotspot/user/profile/print", array("?name" => $profile));
    if (empty($profiles)) {
        $API->disconnect();
        return "❌ Profil '$profile' tidak ditemukan.";
    }
    
    // Generate username/password (voucher style)
    $username = generate_random_string(6);
    $password = $username; // Same for voucher
    
    // Use default settings from config or fallback values
    $server = isset($whatsapp_hotspot_settings['default_server']) ? $whatsapp_hotspot_settings['default_server'] : 'all';
    $timelimit = isset($whatsapp_hotspot_settings['default_time_limit']) ? $whatsapp_hotspot_settings['default_time_limit'] : '';
    $datalimit = isset($whatsapp_hotspot_settings['default_data_limit']) ? $whatsapp_hotspot_settings['default_data_limit'] : '';
    $mbgb = isset($whatsapp_hotspot_settings['default_data_limit_unit']) ? $whatsapp_hotspot_settings['default_data_limit_unit'] : '1048576';
    $prefix = isset($whatsapp_hotspot_settings['default_prefix']) ? $whatsapp_hotspot_settings['default_prefix'] : '';
    
    // Apply prefix
    $username = $prefix . $username;
    
    // Convert data limit
    if ($datalimit != "") {
        $datalimit = $datalimit * $mbgb;
    }
    
    // Set comment for voucher
    $comment = "vc-whatsapp-" . date("m/d/Y");
    
    // Add user to MikroTik
    try {
        $API->comm("/ip/hotspot/user/add", array(
            "server" => $server,
            "name" => $username,
            "password" => $password,
            "profile" => $profile,
            "disabled" => "no",
            "limit-uptime" => $timelimit,
            "limit-bytes-total" => $datalimit,
            "comment" => $comment,
        ));
        $API->disconnect();
        return "✅ Voucher berhasil dibuat!\nUsername: $username\nPassword: $password\nProfil: $profile";
    } catch (Exception $e) {
        $API->disconnect();
        return "❌ Gagal membuat voucher: " . $e->getMessage();
    }
}

// Function to create member user (username != password)
function create_member_user($parts) {
    global $whatsapp_hotspot_settings;
    
    if (count($parts) < 4) {
        return "❓ Penggunaan: member <username> <password> <profile>";
    }
    
    $username = $parts[1];
    $password = $parts[2];
    $profile = $parts[3];
    
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Check if profile exists
    $profiles = $API->comm("/ip/hotspot/user/profile/print", array("?name" => $profile));
    if (empty($profiles)) {
        $API->disconnect();
        return "❌ Profil '$profile' tidak ditemukan.";
    }
    
    // Use default settings from config or fallback values
    $server = isset($whatsapp_hotspot_settings['default_server']) ? $whatsapp_hotspot_settings['default_server'] : 'all';
    $timelimit = isset($whatsapp_hotspot_settings['default_time_limit']) ? $whatsapp_hotspot_settings['default_time_limit'] : '';
    $datalimit = isset($whatsapp_hotspot_settings['default_data_limit']) ? $whatsapp_hotspot_settings['default_data_limit'] : '';
    $mbgb = isset($whatsapp_hotspot_settings['default_data_limit_unit']) ? $whatsapp_hotspot_settings['default_data_limit_unit'] : '1048576';
    
    // Convert data limit
    if ($datalimit != "") {
        $datalimit = $datalimit * $mbgb;
    }
    
    // Set comment for member
    $comment = "up-whatsapp-" . date("m/d/Y");
    
    // Add user to MikroTik
    try {
        $API->comm("/ip/hotspot/user/add", array(
            "server" => $server,
            "name" => $username,
            "password" => $password,
            "profile" => $profile,
            "disabled" => "no",
            "limit-uptime" => $timelimit,
            "limit-bytes-total" => $datalimit,
            "comment" => $comment,
        ));
        $API->disconnect();
        return "✅ Member berhasil dibuat!\nUsername: $username\nPassword: $password\nProfil: $profile";
    } catch (Exception $e) {
        $API->disconnect();
        return "❌ Gagal membuat member: " . $e->getMessage();
    }
}

// Function to generate multiple voucher users
function generate_voucher_users($parts) {
    global $whatsapp_hotspot_settings;
    
    if (count($parts) < 3) {
        return "❓ Penggunaan: generate <jumlah> <profile>";
    }
    
    $quantity = intval($parts[1]);
    $profile = $parts[2];
    
    if ($quantity <= 0 || $quantity > 100) {
        return "❌ Jumlah harus antara 1-100.";
    }
    
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Check if profile exists
    $profiles = $API->comm("/ip/hotspot/user/profile/print", array("?name" => $profile));
    if (empty($profiles)) {
        $API->disconnect();
        return "❌ Profil '$profile' tidak ditemukan.";
    }
    
    // Use default settings from config or fallback values
    $server = isset($whatsapp_hotspot_settings['default_server']) ? $whatsapp_hotspot_settings['default_server'] : 'all';
    $timelimit = isset($whatsapp_hotspot_settings['default_time_limit']) ? $whatsapp_hotspot_settings['default_time_limit'] : '';
    $datalimit = isset($whatsapp_hotspot_settings['default_data_limit']) ? $whatsapp_hotspot_settings['default_data_limit'] : '';
    $mbgb = isset($whatsapp_hotspot_settings['default_data_limit_unit']) ? $whatsapp_hotspot_settings['default_data_limit_unit'] : '1048576';
    $prefix = isset($whatsapp_hotspot_settings['default_prefix']) ? $whatsapp_hotspot_settings['default_prefix'] : '';
    
    // Convert data limit
    if ($datalimit != "") {
        $datalimit = $datalimit * $mbgb;
    }
    
    $created_users = array();
    $failed_count = 0;
    
    // Generate users
    for ($i = 0; $i < $quantity; $i++) {
        try {
            // Generate username/password (voucher style)
            $username = $prefix . generate_random_string(6);
            $password = $username; // Same for voucher
            
            // Set comment for voucher
            $comment = "vc-whatsapp-" . date("m/d/Y");
            
            // Add user to MikroTik
            $API->comm("/ip/hotspot/user/add", array(
                "server" => $server,
                "name" => $username,
                "password" => $password,
                "profile" => $profile,
                "disabled" => "no",
                "limit-uptime" => $timelimit,
                "limit-bytes-total" => $datalimit,
                "comment" => $comment,
            ));
            
            $created_users[] = "$username|$password";
        } catch (Exception $e) {
            $failed_count++;
        }
    }
    
    $API->disconnect();
    
    if (empty($created_users) && $failed_count > 0) {
        return "❌ Gagal membuat semua voucher. Tidak ada yang berhasil dibuat.";
    }
    
    $success_count = count($created_users);
    $response = "✅ Berhasil membuat $success_count voucher dengan profil '$profile'";
    
    if ($failed_count > 0) {
        $response .= "\n⚠️ $failed_count voucher gagal dibuat";
    }
    
    $response .= "\n\nDaftar Voucher:\n";
    foreach ($created_users as $user) {
        list($uname, $pass) = explode("|", $user);
        $response .= "$uname (Password: $pass)\n";
    }
    
    return $response;
}

// Function to handle hotspot commands
function handle_hotspot_command($parts) {
    if (count($parts) < 2) {
        return "❓ Penggunaan: hotspot voucher|member|generate|list|remove ...";
    }
    
    $subcommand = strtolower($parts[1]);
    
    switch ($subcommand) {
        case 'voucher':
            return create_voucher_user($parts);
            
        case 'member':
            return create_member_user($parts);
            
        case 'generate':
            return generate_voucher_users($parts);
            
        case 'list':
            return list_hotspot_users();
            
        case 'remove':
            return remove_hotspot_user($parts);
            
        default:
            return "❓ Subperintah hotspot tidak dikenali. Tersedia: voucher, member, generate, list, remove";
    }
}

// Function to list hotspot users
function list_hotspot_users() {
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Get all hotspot users
    $users = $API->comm("/ip/hotspot/user/print");
    $API->disconnect();
    
    if (empty($users)) {
        return "📋 Tidak ada pengguna hotspot.";
    }
    
    $response = "📋 Daftar pengguna hotspot:\n";
    foreach ($users as $user) {
        $response .= "- " . $user['name'] . " (" . $user['profile'] . ")\n";
    }
    
    return $response;
}

// Function to remove hotspot user
function remove_hotspot_user($parts) {
    if (count($parts) < 3) {
        return "❓ Penggunaan: hotspot remove <username>";
    }
    
    $username = $parts[2];
    
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Check if user exists
    $user = $API->comm("/ip/hotspot/user/print", array("?name" => $username));
    
    if (empty($user)) {
        $API->disconnect();
        return "❌ Pengguna hotspot '$username' tidak ditemukan.";
    }
    
    // Remove user from MikroTik
    try {
        $API->comm("/ip/hotspot/user/remove", array(".id" => $user[0]['.id']));
        $API->disconnect();
        return "✅ Pengguna hotspot '$username' berhasil dihapus.";
    } catch (Exception $e) {
        $API->disconnect();
        return "❌ Gagal menghapus pengguna: " . $e->getMessage();
    }
}

// Function to handle PPPoE commands
function handle_pppoe_command($parts) {
    global $whatsapp_pppoe_settings;
    
    if (count($parts) < 2) {
        return "❓ Penggunaan: pppoe list|add|remove ...";
    }
    
    $subcommand = strtolower($parts[1]);
    
    switch ($subcommand) {
        case 'list':
            return list_pppoe_users();
            
        case 'add':
            if (count($parts) < 5) {
                return "❓ Penggunaan: pppoe add <username> <password> <profile>";
            }
            
            $name = $parts[2];
            $password = $parts[3];
            $profile = $parts[4];
            
            // Get API connection
            $API = get_api_connection();
            if (!$API) {
                return "❌ Gagal terhubung ke MikroTik.";
            }
            
            // Check if profile exists
            $profiles = $API->comm("/ppp/profile/print", array("?name" => $profile));
            if (empty($profiles)) {
                $API->disconnect();
                return "❌ Profil PPPoE '$profile' tidak ditemukan.";
            }
            
            // Use default settings from config or fallback values
            $service = isset($whatsapp_pppoe_settings['default_service']) ? $whatsapp_pppoe_settings['default_service'] : 'pppoe';
            $callerid = isset($whatsapp_pppoe_settings['default_caller_id']) ? $whatsapp_pppoe_settings['default_caller_id'] : '';
            $interval = isset($whatsapp_pppoe_settings['default_interval']) ? $whatsapp_pppoe_settings['default_interval'] : '30d';
            
            // Add secret to MikroTik
            try {
                $API->comm("/ppp/secret/add", array(
                    "name" => $name,
                    "password" => $password,
                    "service" => $service,
                    "caller-id" => $callerid,
                    "profile" => $profile,
                ));
                
                // Add scheduler for expiry
                $start_date = date('M/d/Y');
                $start_time = date('H:i:s');
                $on_event = "/ppp secret set disabled=yes [/ppp secret find name=" . $name . "] \r /system scheduler disable [find name=" . $name . "]";
                
                $API->comm("/system/scheduler/add", array(
                    "name" => $name,
                    "start-date" => $start_date,
                    "start-time" => $start_time,
                    "interval" => $interval,
                    "on-event" => $on_event,
                ));
                
                $API->disconnect();
                return "✅ Pengguna PPPoE '$name' berhasil ditambahkan dengan profil '$profile'. Scheduler diatur untuk $interval.";
            } catch (Exception $e) {
                $API->disconnect();
                return "❌ Gagal menambahkan pengguna PPPoE: " . $e->getMessage();
            }
            
        case 'remove':
            if (count($parts) < 3) {
                return "❓ Penggunaan: pppoe remove <username>";
            }
            
            $name = $parts[2];
            
            // Get API connection
            $API = get_api_connection();
            if (!$API) {
                return "❌ Gagal terhubung ke MikroTik.";
            }
            
            // Check if user exists
            $user = $API->comm("/ppp/secret/print", array("?name" => $name));
            
            if (empty($user)) {
                $API->disconnect();
                return "❌ Pengguna PPPoE '$name' tidak ditemukan.";
            }
            
            // Remove user from MikroTik
            try {
                $API->comm("/ppp/secret/remove", array(".id" => $user[0]['.id']));
                
                // Remove scheduler if exists
                $scheduler = $API->comm("/system/scheduler/print", array("?name" => $name));
                
                if (!empty($scheduler)) {
                    $API->comm("/system/scheduler/remove", array(".id" => $scheduler[0]['.id']));
                }
                
                $API->disconnect();
                return "✅ Pengguna PPPoE '$name' berhasil dihapus.";
            } catch (Exception $e) {
                $API->disconnect();
                return "❌ Gagal menghapus pengguna PPPoE: " . $e->getMessage();
            }
            
        default:
            return "❓ Subperintah PPPoE tidak dikenali. Tersedia: list, add, remove";
    }
}

// Function to list PPPoE users
function list_pppoe_users() {
    // Get API connection
    $API = get_api_connection();
    if (!$API) {
        return "❌ Gagal terhubung ke MikroTik.";
    }
    
    // Get all PPPoE users
    $users = $API->comm("/ppp/secret/print");
    $API->disconnect();
    
    if (empty($users)) {
        return "📋 Tidak ada pengguna PPPoE.";
    }
    
    $response = "📋 Daftar pengguna PPPoE:\n";
    foreach ($users as $user) {
        $response .= "- " . $user['name'] . " (" . $user['profile'] . ")\n";
    }
    
    return $response;
}

// Function to generate random string
function generate_random_string($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Main webhook processing
log_message("Webhook called");

// Get raw POST data
$input = file_get_contents('php://input');

// Get gateway type
$gateway = detect_gateway($_SERVER['HTTP_USER_AGENT'] ?? '', $_POST);

// Process based on gateway
$response = "";
switch ($gateway) {
    case 'fonnte':
        $response = process_fonnte_message($_POST);
        break;
        
    case 'mpwa':
        $response = process_mpwa_message($_POST);
        break;
        
    case 'wablas':
        $response = process_wablas_message($_POST);
        break;
        
    default:
        $response = "❓ Gateway tidak dikenali atau format permintaan tidak valid.";
        break;
}

// Log the response
log_message("Response: " . $response);

// Send response
echo $response;
?>