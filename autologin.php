<?php
require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;
use WHMCS\Authentication\Client;

$token = $_GET['token'] ?? '';
$secretKey = Capsule::table('tbladdonmodules')->where('module', 'hubapp_autologin')->where('setting', 'autologin_key')->value('value');

if (!$token || !$secretKey) die("Acesso inválido.");

$parts = explode('.', $token);
if (count($parts) !== 3) die("Token corrompido.");

list($header, $payload, $signature) = $parts;

// Validação da Assinatura PHP 8.3
$validSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));

if ($signature !== $validSignature) die("Token de segurança inválido.");

$data = json_decode(base64_decode($payload), true);

if ($data['exp'] < time()) die("Este link de login expirou.");

try {
    $client = Client::find((int)$data['uid']);
    $user = $client?->users()->first(); // PHP 8.x Nullsafe operator

    if ($user) {
        \Auth::login($user);
        header("Location: clientarea.php");
        exit;
    }
} catch (\Exception $e) {
    die("Erro ao autenticar.");
}