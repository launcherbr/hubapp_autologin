<?php
/**
 * HubApp AutoLogin WHMCS - Hook Inteligente
 * Identifica o contexto do e-mail e define o destino pós-login.
 */

use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    
    $config = Capsule::table('tbladdonmodules')
        ->where('module', 'hubapp_autologin')
        ->get()
        ->pluck('value', 'setting');
    
    $secretKey = $config['autologin_key'] ?? '';
    $expHours = (int)($config['expiration_hours'] ?? 72);
    $userid = $vars['userid'] ?? 0;
    $relid = $vars['relid'] ?? 0;
    $messagename = $vars['messagename'];

    if ($userid > 0 && !empty($secretKey)) {
        
        // Lógica de Redirecionamento Automático
        $goto = 'clientarea.php';
        
        // Mapeamento por tipo de mensagem/módulo
        if (str_contains($messagename, 'Invoice')) {
            $goto = "viewinvoice.php?id={$relid}";
        } elseif (str_contains($messagename, 'Service') || str_contains($messagename, 'Hosting')) {
            $goto = "clientarea.php?action=productdetails&id={$relid}";
        } elseif (str_contains($messagename, 'Domain')) {
            $goto = "clientarea.php?action=domaindetails&id={$relid}";
        } elseif (str_contains($messagename, 'Support') || str_contains($messagename, 'Ticket')) {
            $goto = "viewticket.php?tid={$relid}";
        }

        $systemUrl = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
        
        // Construção do JWT com parâmetro 'goto'
        $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])));
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
            'uid'  => (int)$userid,
            'goto' => $goto,
            'exp'  => time() + ($expHours * 3600),
            'iss'  => 'HubApp_AutoLogin'
        ])));

        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
        
        return [
            'autologin_url' => rtrim($systemUrl, '/') . "/autologin.php?token=$header.$payload.$signature"
        ];
    }
});
