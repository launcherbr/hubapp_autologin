<?php
/**
 * Gateway de Login com Redirecionamento Dinâmico
 */

require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;
use WHMCS\Authentication\Client;

$token = $_GET['token'] ?? '';
$secretKey = Capsule::table('tbladdonmodules')->where('module', 'hubapp_autologin')->where('setting', 'autologin_key')->value('value');

if (!$token || !$secretKey) die("Acesso inválido.");

$parts = explode('.', $token);
if (count($parts) !== 3) die("Token inválido.");

list($header, $payload, $signature) = $parts;

$validSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));

if ($signature !== $validSignature) die("Falha na assinatura de segurança.");

$data = json_decode(base64_decode($payload), true);

if ($data['exp'] < time()) die("O link de acesso expirou.");

try {
    $client = Client::find((int)$data['uid']);
    $user = $client?->users()->first();

    if ($user) {
        \Auth::login($user);
        
        // Redirecionamento para o destino gravado no token ou clientarea por padrão
        $destination = $data['goto'] ?? 'clientarea.php';
        
        header("Location: " . $destination);
        exit;
    }
} catch (\Exception $e) {
    die("Erro na autenticação.");
}
