<?php
/**
 * HubApp AutoLogin WHMCS - Configuração Básica
 */

if (!defined("WHMCS")) die("Acesso negado");

function hubapp_autologin_config() {
    return [
        'name' => 'HubApp AutoLogin WHMCS',
        'description' => 'Login automático simples e seguro para a Área do Cliente.',
        'version' => '1.0 Stable',
        'author' => 'HubApp',
        'fields' => [
            'autologin_key' => [
                'FriendlyName' => 'Chave Secreta',
                'Type' => 'password',
                'Size' => '50',
                'Description' => 'Digite uma chave aleatória forte.',
            ],
            'expiration_hours' => [
                'FriendlyName' => 'Expiração (Horas)',
                'Type' => 'text',
                'Size' => '3',
                'Default' => '72',
            ],
        ]
    ];
}

function hubapp_autologin_output($vars) {
    echo '<div style="padding: 20px; background: #fff; border: 1px solid #ddd;">
            <h3>Módulo Ativo</h3>
            <p>Para usar, adicione a variável abaixo em qualquer template de e-mail:</p>
            <pre style="background: #eee; padding: 10px;">&lt;a href="{$autologin_url}"&gt;Acessar Área do Cliente&lt;/a&gt;</pre>
          </div>';
}