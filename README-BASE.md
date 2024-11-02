## Base Form Project
Esse projeto simula uma versão simplificada da nossa API. Ele possui as entidades básicas para iniciar uma API que pode ser consumida por um front-end.


O projeto roda em Laravel 10, você pode ler a documentação do framework [aqui](https://laravel.com/docs/10.x)

## Requisitos
- PHP 8.1 ou superior
- Composer
- MySql 8

## Instalação
1. Crie um arquivo .env baseado no .env.example
2. Altere as configurações necessárias no .env
3. Instale as dependências com `composer install`
4. Rode as migrations com `artisan migrate`
4. Inicie o servidor local com `artisan serve`

### Seed
Se desejar iniciar com alguns dados no banco de dados, existe um arquivo de seed em `./database/seeders/UserDataSeeder.php`. Esse arquivo cria um numero X de usuários, com Y formulários, e Z respondentes em cada form. Você pode alterar as configurações nas propriedades correspondentes.

Para rodar o seed, use `artisan db:seed`

### Queue & Jobs
Para simplificar, em ambiente de desenvolvimento use o driver `database` para rodar os jobs. Em produção, essa fila é implementada via Redis.

# Arquitetura básica
Essa API possui as entidades mínimas para funcionar como o produto funciona:
- Users: um usuário logado
- Forms: um formulário que pertence a um usuário
- Respondents: um visitante anônimo que responde um formulário
- Answers: uma resposta individual a uma pergunta do formulário, feita por um respondente

Além dos models acima, uma outra entidade secundária que não é representada como uma tabela são as "questions/fields". Essa entidade é uma coluna json na tabela forms.


Um usuário pode ter vários forms, com múltiplas questions. Um form pode ter vários Respondents. Um respondente pode ter até uma Answer por question.


# Testes
Existem testes simples de integração para cada endpoint implementado na API. Eles são testes fracos, considerando apenas o "caminho feliz", mas são um começo. Você pode rodar com `artisan test`.

Se preferir realizar testes manualmente, existe um arquivo de collection HTTP em `/tests/Http/code-scenario.http`

# Além disso...
Note que várias funcionalidade foram implementadas de forma simplificada. Esse projeto retrata apenas um start, e não cobre todos so casos de uso de uma API real.

...
# Implementação de Notificações
Adicione qualquer documentação necessária para rodar sua implementação a partir daqui...
