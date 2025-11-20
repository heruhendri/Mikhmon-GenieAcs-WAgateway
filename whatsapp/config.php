<?php
/*
 * WhatsApp Gateway Configuration
 * Multi-platform support for FONNTE, MPWA, WABLAS, etc.
 */

// WhatsApp Gateway configurations
$whatsapp_gateways = array(
    'fonnte' => array(
        'name' => 'FONNTE',
        'api_url' => 'https://api.fonnte.com',
        'token' => '',
        'enabled' => false
    ),
    'mpwa' => array(
        'name' => 'MPWA',
        'api_url' => 'https://mpwa.id',
        'token' => '',
        'enabled' => false
    ),
    'wablas' => array(
        'name' => 'WABLAS',
        'api_url' => 'https://wablas.com',
        'token' => '',
        'enabled' => false
    )
);

// Admin WhatsApp numbers (authorized users)
$whatsapp_admins = array();

// Default gateway to use
$default_whatsapp_gateway = 'fonnte';

// Webhook URL for receiving messages
$whatsapp_webhook_url = '';

// Hotspot user generation settings
$whatsapp_hotspot_settings = array(
    'default_server' => 'all',
    'default_profile' => '',
    'default_character_type' => 'mix', // lower, upper, upplow, mix, mix1, mix2, num
    'default_username_length' => 4,
    'default_prefix' => '',
    'default_time_limit' => '',
    'default_data_limit' => '',
    'default_data_limit_unit' => '1048576', // MB = 1048576, GB = 1073741824
    'user_mode' => 'vc' // vc = voucher (username=password), up = user/pass (username!=password)
);

// PPPoE user settings
$whatsapp_pppoe_settings = array(
    'default_service' => 'pppoe',
    'default_profile' => '',
    'default_caller_id' => '',
    'default_interval' => '30d'
);

?>