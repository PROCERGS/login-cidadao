Importação de Usuários
======================

Caso você já possua uma basse de usuários, é possível facilitar a migração
desses usuários para o Login Cidadão utilizando o formulário de registro
pré-preenchido.

Embora o Login Cidadão não disponha de uma interface de migração automática
pois acreditamos que o usuário deve estar ciente de onde estão seus dados,
existe uma funcionalidade que permite a exibição da tela de registro com
os dados já preenchidos, bastando que o usuário escolha sua senha e faça as
correções que julgar necessárias.

Sugerimos que você envie esse link por email aos seus usuários informando
o motivo da migração e explicando que acessando esse link eles estarão
criando uma conta em um novo serviço que servirá para fazer login em outras
aplicações.

Modo de Usar
------------

A utilização dessa funcionalidade é bastante simples. Basta redirecionar
o usuário para o endereço normal de cadastro (`/register`) passando os dados
desejados no parâmetro `prefill` conforme o exemplo:

```
GET /register/
             ?prefill[full_name]=Fulano+de+Tal
             &prefill[cpf]=12312312387
             &prefill[birthdate]=1901-01-01
             &prefill[mobile]=+5551987654321
             &prefill[email]=fulano@detal.net
```

Todos os parâmetros são opcionais. A seguir você pode conferir uma lista
com todos os parâmetros disponíveis:

| Parâmetro               | Tipo   | Descrição                                          |
| ----------------------- | ------ | -------------------------------------------------- |
| `prefill[full_name]`    | string | Serão preenchidos o nome e sobrenome.              |
| `prefill[name]`         | string | Define o (primeiro) nome.                          |
| `prefill[first_name]`   | string | Define o (primeiro) nome.                          |
| `prefill[surname]`      | string | Define o sobrenome.                                |
| `prefill[last_name]`    | string | Define o sobrenome.                                |
| `prefill[email]`        | email  | Define o email.                                    |
| `prefill[cpf]`          | CPF    | Define o CPF. Preferencialmente apenas números.    |
| `prefill[mobile]`       | E.164  | Define o telefone. Precisa estar no formato E.164. |
| `prefill[phone_number]` | E.164  | Define o telefone. Precisa estar no formato E.164. |
| `prefill[birthday]`     | data   | Data de nascimento no formato YYYY-mm-dd           |
| `prefill[birthdate]`    | data   | Data de nascimento no formato YYYY-mm-dd           |
