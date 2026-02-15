<?php
/**
 * HubApp AutoLogin WHMCS - Gateway Estável
 * Foco: Login na Área do Cliente (Home)
 */

ob_start(); // Previne erros de header

require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;
use WHMCS\Session;

$token = $_GET['token'] ?? '';

try {
    // 1. Busca Configuração
    $secretKey = Capsule::table('tbladdonmodules')
        ->where('module', 'hubapp_autologin')
        ->where('setting', 'autologin_key')
        ->value('value');

    if (!$token || !$secretKey) throw new \Exception("Acesso inválido.");

    // 2. Valida Assinatura JWT
    list($header, $payload, $signature) = explode('.', $token);
    $checkSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
    
    if (!hash_equals($checkSignature, $signature)) throw new \Exception("Link inválido ou corrompido.");

    $data = json_decode(base64_decode($payload), true);
    if ($data['exp'] < time()) throw new \Exception("Link expirado.");

    $clientId = (int)$data['uid'];

    // 3. Autenticação (Método Nativo WHMCS 9)
    $userRelation = Capsule::table('tblusers_clients')->where('client_id', $clientId)->first();

    if ($userRelation && $user = \WHMCS\User\User::find($userRelation->auth_user_id)) {
        
        // Limpeza preventiva
        if (\Auth::user()) \Auth::logout();

        // Login Oficial
        \Auth::login($user);

        // Fixação de Sessão (Essencial para persistência)
        Session::set("uid", $clientId);
        Session::set("user_id", $user->id);
        Session::set("upw", $user->password);
        
        // Grava sessão imediatamente
        session_write_close();
        ob_end_clean();

        // 4. Redirecionamento com Tela de Carregamento (Bypass de Cache/Proxy)
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Autenticando...</title>
            <style>
                body { font-family: sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .loader { border: 4px solid #e9ecef; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 15px; }
                .content { text-align: center; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </head>
        <body>
            <div class="content">
                <div class="loader" style="margin: 0 auto;"></div>
                <h3 style="color: #555;">Acessando sua conta...</h3>
            </div>
            <script>
                // Força o navegador a reconhecer o cookie antes de mudar de página
                setTimeout(function() {
                    window.location.href = "clientarea.php";
                }, 1000); // 1 segundo de espera estratégica
            </script>
        </body>
        </html>
        <?php
        exit;
    }
    
    throw new \Exception("Usuário não encontrado.");

} catch (\Exception $e) {
    ob_end_clean();
    // Redireciona para login limpo em caso de erro
    header("Location: clientarea.php");
    exit;
}