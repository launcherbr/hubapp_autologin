# HubApp AutoLogin WHMCS 🛡️

Módulo de Addon para **WHMCS 9.x** e **PHP 8.3** que permite a geração de links de acesso seguro (One-Click Login) utilizando **JSON Web Tokens (JWT)**. 
Ideal para integrar com notificações de E-mail e WhatsApp.

## 🚀 Funcionalidades

- **Autenticação Segura:** Login via tokens JWT assinados com HMAC-SHA256.
- **Expiração Configurável:** Controle a validade dos links diretamente no painel administrativo.

## 📂 Estrutura de Arquivos

```text
/
├── autologin.php                # Gateway de processamento (Raiz do WHMCS)
├── includes/
│   └── hooks/
│       └── hubapp_autologin_vars.php  # Hook para variáveis auto_
└── modules/
    └── addons/
        └── hubapp_autologin/
            └── hubapp_autologin.php   # Interface e Configurações
```

## 🛠️ Instalação

Upload: Envie as pastas includes, modules e o arquivo autologin.php para o diretório raiz da sua instalação WHMCS.

Ativação: - Vá em System Settings > Addon Modules.

Ative o módulo HubApp AutoLogin WHMCS.

Configuração:

Clique em Configure.

Insira uma Chave Mestra JWT (ex: uma sequência longa e aleatória de caracteres).

Defina o tempo de expiração (padrão 72 horas).

Salve as alterações.

## 📧 Uso nos Templates de E-mail

Você deve copiar e colar estes blocos conforme o objetivo do e-mail que está editando:

```
<p>Para sua conveniência, você pode <a href="{$autologin_url}">clicar aqui para acessar sua conta</a> sem precisar digitar sua senha.</p>
```

💡 Dicas Importantes:
Editor de E-mail: Certifique-se de colar esse código no modo "Source" (Código-Fonte) do editor de templates do WHMCS para que o HTML seja interpretado corretamente.

Cores: Você pode alterar o código hexadecimal (ex: #28a745) para as cores da sua marca.

## 🔒 Segurança (JWT)

O sistema utiliza três camadas de validação:

Integridade: Se qualquer caractere do token for alterado, a assinatura SHA256 falha.

Expiração: O link deixa de funcionar automaticamente após o período definido (exp).

Identidade: O token é vinculado ao UID específico do cliente.

---

## 💎 Recomendado para seu WHMCS

> **TENHA SEU WHMCS VERIFICADO**
>
> Garanta mais credibilidade e segurança para o seu sistema por apenas **R$ 250,00 anuais**.
>
> [**👉 CLIQUE AQUI PARA CONTRATAR AGORA**](https://licencas.digital/store/whmcs/whmcs-validado)

---

## 🆘 Suporte e Documentação

* **Desenvolvido por**: LD | HubApp / Launcher & Co.
* **Suporte**: [licencas.digital](https://licencas.digital)
