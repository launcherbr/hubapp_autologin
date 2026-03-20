<?php
/**
 * HubApp AutoLogin WHMCS - Configuração
 */

if (!defined("WHMCS")) die("Acesso negado");

use WHMCS\Database\Capsule;

function hubapp_autologin_config() {
    return [
        'name' => 'HubApp AutoLogin WHMCS',
        'description' => 'Login automático seguro para Área do Cliente (Home).',
        'version' => '1.0',
        'author' => 'HubApp',
        'fields' => [
            'autologin_key' => [
                'FriendlyName' => 'Chave Secreta',
                'Type' => 'password',
                'Size' => '50',
                'Description' => 'Chave para assinar os tokens (JWT).',
            ],
            'expiration_hours' => [
                'FriendlyName' => 'Validade (Horas)',
                'Type' => 'text',
                'Size' => '3',
                'Default' => '72',
            ],
        ]
    ];
}

function hubapp_autologin_output($vars) {
    // Variáveis fornecidas pelo WHMCS
    $modulelink = $vars['modulelink'];
    $secretKey = $vars['autologin_key'];
    $expHours = (int)($vars['expiration_hours'] ?? 72);
    
    // Busca a URL do sistema
    $systemUrl = rtrim(Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');

    // Início da Interface
    $html = '<div style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">';
    
    // Bloco de Informação
    $html .= '<h3><i class="fas fa-check-circle" style="color: #28a745;"></i> Módulo Ativo</h3>';
    $html .= '<p>Utilize a variável abaixo em seus templates de e-mail:</p>';
    $html .= '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #28a745; margin-bottom: 30px;">';
    $html .= '<code>&lt;a href="{$autologin_url}"&gt;Acessar Minha Conta&lt;/a&gt;</code>';
    $html .= '</div>';

    // Formulário Gerador Manual
    $html .= '<hr style="margin: 20px 0;">';
    $html .= '<h3><i class="fas fa-link" style="color: #007bff;"></i> Gerador Manual de Link</h3>';
    $html .= '<p>Insira a ID do Cliente abaixo para gerar um link de auto login temporário.</p>';
    
    $html .= '<form method="post" action="' . $modulelink . '" style="margin-bottom: 20px;">';
    $html .= '<input type="hidden" name="generate_hubapp_link" value="1">';
    $html .= '<div style="display: flex; align-items: center; gap: 10px;">';
    $html .= '<input type="number" name="client_id" placeholder="ID do Cliente (Ex: 15)" required style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">';
    $html .= '<button type="submit" class="btn btn-primary">Gerar Link</button>';
    $html .= '</div>';
    $html .= '</form>';

    // Processamento da submissão do formulário
    if (isset($_POST['generate_hubapp_link']) && !empty($_POST['client_id'])) {
        $clientId = (int)$_POST['client_id'];

        if (!empty($secretKey)) {
            // Cria o Token com a mesma lógica do Hook
            $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])));
            $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
                'uid' => $clientId,
                'exp' => time() + ($expHours * 3600),
                'iss' => 'HubApp'
            ])));
            
            $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $secretKey, true)));
            
            // Monta a URL final
            $generatedLink = "$systemUrl/autologin.php?token=$header.$payload.$signature";

            // Mostra o resultado na tela
            $html .= '<div style="background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px;">';
            $html .= '<strong><i class="fas fa-check"></i> Link gerado com sucesso para o Cliente ID ' . $clientId . '!</strong><br><br>';
            $html .= '<input type="text" value="' . $generatedLink . '" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace;" readonly onclick="this.select()">';
            $html .= '<p style="font-size: 12px; margin-top: 8px; color: #666;">Este link expira em ' . $expHours . ' horas. Clique no campo acima para copiar.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px;">';
            $html .= '<strong>Erro:</strong> A Chave Secreta ainda não foi configurada nas definições do módulo.';
            $html .= '</div>';
        }
    }

    $html .= '</div>';
    
    // Imprime a interface
    echo $html;
}