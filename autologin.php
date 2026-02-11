<?php
/**
 * HubApp AutoLogin WHMCS - Gateway v9 (Cloudflare & Proxy Friendly)
 */

require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;
use WHMCS\Session;

$token = $_GET['token'] ?? '';

try {
    $secretKey = Capsule::table('tbladdonmodules')
        ->where('module', 'hubapp_autologin')
        ->where('setting', 'autologin_key')
        ->value('value');

    if (!$token || !$secretKey) throw new \Exception("Configuração incompleta.");

    list($header, $payload, $signature) = explode('.', $token);
    $checkSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));

    if (!hash_equals($checkSignature, $signature)) throw new \Exception("Assinatura inválida.");

    $data = json_decode(base64_decode($payload), true);
    if ($data['exp'] < time()) throw new \Exception("Link expirado.");

    $clientId = (int)$data['uid'];
    $userRelation = Capsule::table('tblusers_clients')->where('client_id', $clientId)->first();

    if ($userRelation) {
        $user = \WHMCS\User\User::find($userRelation->auth_user_id);
        
        if ($user) {
            // 1. Autenticação via Fachada Nativa
            \Auth::login($user);

            // 2. Uso do Helper de Sessão do WHMCS (Mais forte que $_SESSION puro)
            Session::set("uid", $clientId);
            Session::set("user_id", $user->id);
            Session::set("upw", $user->password);

            // 3. Destino pós-login
            $destination = !empty($data['goto']) ? $data['goto'] : 'clientarea.php';

            // 4. Redirecionamento via Meta Refresh (Evita quebras de Proxy/Cloudflare)
            echo "<html><head><meta http-equiv='refresh' content='0;url={$destination}'></head><body>
                  <p>Autenticando, aguarde...</p>
                  <script>window.location.href='{$destination}';</script>
                  </body></html>";
            exit;
        }
    }
    
    throw new \Exception("Usuário não encontrado.");

} catch (\Exception $e) {
    header("Location: clientarea.php");
    exit;
}