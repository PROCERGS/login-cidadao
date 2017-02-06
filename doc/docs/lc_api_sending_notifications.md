Exemplos da API REST
====================

Enviando Notificações
---------------------

Enviar notificações pelo Login Cidadão é tão fácil quanto fazer uma requisição POST autenticada por OAuth 2.

### Autenticação

Você pode autenticar via Client Credentials ou informando diretamente o Access Token de uma pessoa. Se você está enviando mais de uma notificação e faz algum tempo desde que o Access Token foi emitido, então usar Client Credentials é altamente recomendado e é uma ideia melhor já que você economizará seu limite da API visto que não terá que solicitar novos Access Tokens para diversas pessoas.

### Enviando uma Notificação

Para criar uma notificação basta fazer uma requisição POST para nossa API de Notificações, autenticada com OAuth 2 com um Access Token e informando, pelo menos, estes parâmetros:

  * title: o título da notificação
  * shortText: uma breve descrição
  * person: o Identificador Único do destinatário
  * sender: o Identificador Único do seu Client OAuth
  * category: o Identificador Único da categoria da notificação
  * placeholders: os Placeholders da notificação

Um exemplo de requisição POST poderia ser o seguinte:

```
POST /api/v1/person/notification.json HTTP/1.1
Host: meu.rs.gov.br
Content-Type: application/x-www-form-urlencoded
Authorization: Bearer M2M5YjgzNj...ZDg4OTJiNA

title=My+Nice+Title&shortText=This+is+the+notification%27s+brief+description&person=23521&sender=42&category=953&placeholders%5Bname%5D=Fulano+de+Tal&placeholders%5BextraInfo%5D=Something+here
```

Neste exemplo, estamos enviando os seguintes dados:

``` js
{
	"title": "My Nice Title",
	"shortText": "This is the notification's brief description",
	"person": 23521,
	"sender": 42,
	"category": 953,
	"placeholders": {
		"name": "Fulano de Tal",
		"extraInfo": "Something here"
	}
}
```

Como resposta, você receberá um status code HTTP 201, como qualquer API RESTful deveria, mas como cortesia nós também lhe retornaremos o ID da notificação recém criada como nesse exemplo:

``` js
{
	"id": 654
}
```

### Recebendo o retorno de leitura da notificação

Para receber um aviso de que o destinatario da notificação leu a mensagem deve-se acrescentar na notificação o parâmentro `callbackUrl`:

```js
{
	...
	"callbackUrl" : "https://mysite/"
	...
}
```

Caso tenha sido informado uma URL no parâmetro `callbackUrl` da notificação, no momento em que usuário ler a notificação será enviando um requisição para aquele endereço.
A requisição será do tipo POST e devolverá dois parâmetros, o parametro `data` e o parâmetro `signature`.
O parâmetro `data` é uma estrutura de dados serializada no formato JSON. Nessa estrutura temos os seguintes dados:

```js
{
	//o identificador único da notificação
	'id': 1,
	//o identificador único do usuário
	'person_id': 1,
	//a data e hora da leitura da notificação no formato Unix timestamp
    'read_date': 1272509157
}
```

O parâmetro `signature` é uma assinatura do tipo `HMAC`, usando a função `sha256` sobre a informação contida no parâmetro `data`. A senha utilizada para fazer essa assinatura é a Chave privada (ou Secret) do Client OAuth. Dessa forma, é possivel garantir a autenticidade do mensagem enviada.
