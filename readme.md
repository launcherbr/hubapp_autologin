# HubApp AutoLogin WHMCS 🛡️

Módulo de Addon para **WHMCS 9.x** e **PHP 8.3** que permite a geração de links de acesso seguro (One-Click Login) utilizando **JSON Web Tokens (JWT)**. 
Ideal para integrar com notificações de E-mail e WhatsApp (Meta API / Z-PRO).

## 🚀 Funcionalidades

- **Autenticação Segura:** Login via tokens JWT assinados com HMAC-SHA256.
- **Expiração Configurável:** Controle a validade dos links diretamente no painel administrativo.
- **Variável Nativa de E-mail:** Injeta automaticamente a variável `{$autologin_url}` em todos os templates de e-mail do sistema.
- **Focado em Conversão:** Reduz o atrito no pagamento de faturas e suporte técnico.

## 📂 Estrutura de Arquivos

```text
/
├── autologin.php # Gateway de processamento (Raiz do WHMCS)
├── includes/
│   └── hooks/
│       └── hubapp_autologin_vars.php # Hook para variáveis de e-mail
└── modules/
    └── addons/
        └── hubapp_autologin/
            └── hubapp_autologin.php # Configurações do Addon
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

## 🧠 Redirecionamento Automático (Smart Redirect)

O módulo identifica o contexto do e-mail enviado e ajusta o link de destino:
* **E-mails de Fatura:** Redireciona direto para a visualização da fatura (`viewinvoice.php`).
* **E-mails de Produto/Serviço:** Redireciona para os detalhes do serviço.
* **E-mails de Domínio:** Redireciona para a gestão do domínio.
* **Suporte:** Redireciona para o ticket correspondente.

## 📧 Uso nos Templates de E-mail

O módulo injeta automaticamente a variável {$autologin_url}. Para usar, edite um template de e-mail e adicione o botão:

```text
<a href="{$autologin_url}" style="background:#28a745; color:#fff; padding:10px; text-decoration:none; border-radius:5px;">
    Acessar Área do Cliente sem Senha
</a>
```

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

* **Desenvolvido por**: HubApp / Launcher & Co.
* **Suporte**: [licencas.digital](https://licencas.digital)
