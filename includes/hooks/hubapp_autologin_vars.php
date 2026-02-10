<?php
use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    $config = Capsule::table('tbladdonmodules')->where('module', 'hubapp_autologin')->get()->pluck('value', 'setting');
    
    $secretKey = $config['autologin_key'] ?? '';
    $expHours = (int)($config['expiration_hours'] ?? 72);
    $userid = $vars['userid'] ?? 0;

    if ($userid > 0 && $secretKey) {
        $systemUrl = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
        
        $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])));
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
            'uid' => $userid,
            'exp' => time() + ($expHours * 3600),
            'iss' => 'HubApp_AutoLogin'
        ])));

        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
        
        $token = "$header.$payload.$signature";
        
        return ['autologin_url' => rtrim($systemUrl, '/') . "/autologin.php?token=" . $token];
    }
});