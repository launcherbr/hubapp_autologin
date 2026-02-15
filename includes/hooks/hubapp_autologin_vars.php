<?php
/**
 * HubApp AutoLogin WHMCS - Hook Simples
 * Gera apenas {$autologin_url} para a Área do Cliente
 */

use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    
    $config = Capsule::table('tbladdonmodules')->where('module', 'hubapp_autologin')->pluck('value', 'setting');
    $secretKey = $config['autologin_key'] ?? '';
    
    // Validação básica: tem chave e tem usuário?
    $userid = $vars['userid'] ?? ($vars['relid'] ?? 0);
    
    if ($secretKey && $userid > 0) {
        
        $systemUrl = rtrim(Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        $expHours = (int)($config['expiration_hours'] ?? 72);
        
        // Criação do Token (Simples e Limpo)
        $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])));
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
            'uid' => (int)$userid,
            'exp' => time() + ($expHours * 3600),
            'iss' => 'HubApp'
        ])));
        
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
        
        $finalUrl = "$systemUrl/autologin.php?token=$header.$payload.$signature";

        return ['autologin_url' => $finalUrl];
    }
});