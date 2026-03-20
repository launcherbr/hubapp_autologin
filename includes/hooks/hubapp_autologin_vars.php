<?php
/**
 * HubApp AutoLogin WHMCS - Hook Variável Única (Versão Final de Produção)
 */

use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    
    // =================================================================
    // MODO DE DEPURAÇÃO (LOGS)
    // Mude para 'true' se quiser gravar no Log do WHMCS para testes.
    // Mantenha 'false' no dia a dia para não encher o banco de dados.
    // =================================================================
    $debugMode = false; 
    
    try {
        $secretKey = Capsule::table('tbladdonmodules')
            ->where('module', 'hubapp_autologin')
            ->where('setting', 'autologin_key')
            ->value('value');
            
        $expHours = Capsule::table('tbladdonmodules')
            ->where('module', 'hubapp_autologin')
            ->where('setting', 'expiration_hours')
            ->value('value');
            
        if (!$expHours) $expHours = 72;
        
        $clientId = 0;
        
        // 1. Tenta pegar a ID do cliente (padrão)
        if (!empty($vars['client_id'])) $clientId = (int)$vars['client_id'];
        elseif (!empty($vars['clientsdetails']['userid'])) $clientId = (int)$vars['clientsdetails']['userid'];
        elseif (!empty($vars['userid'])) $clientId = (int)$vars['userid'];
        
        // 2. Tenta pelo E-mail (A Tática de Rastreador)
        elseif (!empty($vars['client_email'])) {
            $clientId = Capsule::table('tblclients')->where('email', $vars['client_email'])->value('id');
        }
        
        // 3. Tenta pelas variáveis de Fatura (suportando os dois formatos do WHMCS)
        elseif (!empty($vars['invoice_id'])) {
            $clientId = Capsule::table('tblinvoices')->where('id', $vars['invoice_id'])->value('userid');
        }
        elseif (!empty($vars['invoiceid'])) { 
            $clientId = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('userid');
        }
        
        // 4. Fallback (RelID para Boas-vindas, Tickets e Faturas antigas)
        elseif ($clientId == 0 && !empty($vars['relid'])) {
            $relid = (int)$vars['relid'];
            $msgName = strtolower($vars['messagename'] ?? '');
            
            if (strpos($msgName, 'invoice') !== false || strpos($msgName, 'fatura') !== false || strpos($msgName, 'boleto') !== false || strpos($msgName, 'cobrança') !== false) {
                $clientId = Capsule::table('tblinvoices')->where('id', $relid)->value('userid');
            } elseif (strpos($msgName, 'signup') !== false || strpos($msgName, 'welcome') !== false || strpos($msgName, 'password') !== false) {
                $clientId = $relid;
            }
        }

        // --------------------------------------------------------
        // Grava no log apenas se o modo de depuração estiver ativo
        // --------------------------------------------------------
        if ($debugMode) {
            $availableKeys = implode(', ', array_keys($vars));
            logActivity("HubApp AutoLogin [Email: " . ($vars['messagename'] ?? 'Desconhecido') . "] -> ClientID Resolvido: " . $clientId . " | Variáveis: " . $availableKeys);
        }
        
        // 5. Gera o Link de Acesso Seguro (JWT)
        if (!empty($secretKey) && $clientId > 0) {
            
            $systemUrl = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
            $systemUrl = trim(rtrim($systemUrl, '/'));
            
            // Força HTTPS
            if (strpos($systemUrl, 'http://') === false && strpos($systemUrl, 'https://') === false) {
                $systemUrl = 'https://' . $systemUrl;
            }
            
            $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])));
            $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
                'uid' => (int)$clientId,
                'exp' => time() + ((int)$expHours * 3600),
                'iss' => 'HubApp'
            ])));
            $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
            
            return ['autologin_url' => "$systemUrl/autologin.php?token=$header.$payload.$signature"];
        }
        
    } catch (\Exception $e) {
        if ($debugMode) {
            logActivity("HubApp AutoLogin ERRO: " . $e->getMessage());
        }
    }
});
