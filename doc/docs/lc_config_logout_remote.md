Logout "Remoto"
===============

Logout Remoto é o nome da funcionalidade que permite um Client OAuth, devidamente autorizado por um usuário, a gerar uma Logout Key. A Logout Key será usada para deslogar esse usuário com o simples acessar de um link.

Passos
------

1. O Client OAuth (também conhecido como Serviço ou Aplicação de Terceiros) solicita, caso já não tenha, um Access Token atraves do grant type de Client Credentials. ([Leia mais sobre OAuth](http://aaronparecki.com/articles/2012/07/29/1/oauth2-simplified))
2. O Client OAuth usa seu Access Token para solicitar a Logout Key para a pessoa que já autorizou, previamente, esse Client utilizando o escopo `logout`.
3. O Client OAuth recebe a Logout Key e então redireciona o usuário para a URL de logout passando essa chave recém obtida.
4. O Usuário é desconectado do Login Cidadão.

**Nota:** Cada Logout Key tem um tempo de vida de 5 minutos e só pode ser usada uma única vez.

Passos Detalhados
-----------------

### Obtendo o Access Token

Essa etapa é específica da sua linguagem de programação e biblioteca OAuth utilizadas, então cabe a você pesquisar como usar as ferramentas de sua escolha.

### Solicitando uma Logout Key

A Logout Key é obtida ao chamar a API do Login Cidadão `GET /api/v1/person/{personId}/logout-key.json` e autenticando usando o Access Token da sua aplicação.

#### Exemplo:

Request:
```
GET /api/v1/person/1/logout-key.json
Authorization: Bearer ZTQzNWMzMWM...TY4M2JkNTdhZGFhMTFiZmYwZA
```

Response:
``` json
{
    "key": "7cfe184...4d71c6e",
    "url": "http://meu.rs.gov.br/logout/if-not-remembered/7cfe184...4d71c6e"
}
```

Observe que, para sua conveniência, nós já retornamos a URL completa para onde o usuário deve ser encaminhado.

### Redirecionando o usuário

De posse da Logout Key e da URL você pode alternativamente redirectionar o usuário para essa URL ou abri-la em um frame escondido na sua página.


