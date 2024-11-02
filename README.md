# Desafio Back-end - Implementação de notificações

Este projeto implementa a feature de notificações na API. Ao criar um formulário, o usuário pode optar por ativar notificações por e-mail, WhatsApp e webhook. Além disso, há a opção de enviar notificações por e-mail para o respondente assim que o formulário é concluído.

## 🚀 Começando

Essas instruções permitirão que você obtenha uma cópia do projeto em operação na sua máquina local para fins de desenvolvimento e teste.

### 📋 Pré-requisitos

* PHP 8.1 ou superior
* Composer
* MySql 8

### 🔧 Instalação

Para utilizar a API siga os seguintes passos:

* Clone o repositório e utilize a branch principal`(master)`.
* Crie um arquivo `.env` baseado no .env.example.
* No arquivo `.env`, adicione uma URL de destino para as notificações via WhatsApp. Para testes, você pode utilizar https://webhook.site como exemplo, conforme mostrado abaixo:  
```
WHATSAPP_URL="https://webhook.site/{seu-token}"
```
* No arquivo `.env`, adicione as configurações de e-mail. Para testes, você pode utilizar https://mailtrap.io, conforme o exemplo abaixo:
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=4bb41*****
MAIL_PASSWORD=898b32*****
```

* Instale as dependências com composer install.
* Rode as migrations com artisan migrate.
* Inicie o servidor local com artisan serve.

## ⚙️ Testes

Para realizar testes manuais, utilize o arquivo `/tests/Http/code-scenario.http`.

As notificações podem ser ativadas ou desativadas por meio do objeto `notification`. Caso a notificação via webhook esteja ativada, adicione uma URL de destino. Para testes, você pode utilizar novamente https://webhook.site como exemplo.

```
    "notification": {
        "email": boolean,
        "whatsapp": boolean,
        "respondent_email": boolean,
        "webhook": {
            "active": boolean,
            "url": "https://webhook.site/{seu-token}"
        }
    }
```

### 🔩 Executando os testes

 Você pode verificá-los executando o seguinte comando:

```
php artisan test tests/Feature
```