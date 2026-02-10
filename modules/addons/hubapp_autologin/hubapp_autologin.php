<?php
/**
 * HubApp AutoLogin WHMCS
 * Compatível com WHMCS 9.x & PHP 8.3
 */

if (!defined("WHMCS")) die("Acesso negado");

function hubapp_autologin_config() {
    return [
        "name" => "HubApp AutoLogin WHMCS",
        "description" => "Gera links JWT seguros para acesso direto à área do cliente.",
        "version" => "1.0",
        "author" => "HubApp",
        "fields" => [
            "autologin_key" => [
                "FriendlyName" => "Chave Mestra JWT",
                "Type" => "password",
                "Size" => "64",
                "Description" => "Insira uma chave secreta longa (ex: SHA256) para assinar os tokens.",
            ],
            "expiration_hours" => [
                "FriendlyName" => "Expiração (Horas)",
                "Type" => "text",
                "Size" => "3",
                "Default" => "72",
                "Description" => "Tempo de validade do link enviado por e-mail.",
            ],
        ]
    ];
}

function hubapp_autologin_activate() {
    return ['status' => 'success', 'description' => 'Módulo HubApp ativado.'];
}