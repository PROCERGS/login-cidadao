# Exemplo utilizando php 5.2

Uma vez de posse de todos os dados descritos [ aqui ](integration.md), podemos seguir com esse tutorial.

Para conectar no login cidadao usando php 5.2 utilizamos o cliente Oauth disponibilizado pelo site PHPCLASSES (http://www.phpclasses.org/oauth-api).
Essa implementacao tem como dependetencia a classe http-client também diponivel pelo site PHPCLASSES (http://www.phpclasses.org/httpclient).

Primeiro instanciamos a classe oauth_client_class:

``` php
$client = new oauth_client_class();
//aqui especificacamos o nome do servidor que vamos utilizar. Isso server para carregar as configurações extras do cliente oauth.
$client->server = 'Meu';
//colocamos um dos nossos endereços que autorizamos o login cidadao a retornar dados
$client->redirect_uri = '';
//colocamos nossa chave pública aqui
$client->client_id = '';
//colocamos nossa chave privada aqui
$client->client_secret = '';
//colocamos a lista de escopos desejados separados por espaços
$client->scope = '';
```

Temos que criar um arquivo de configuração ("oauth_configuration.json
") do servidor Oauth que desejamos utilizar. Nesse arquivo, especificamos a versão do protocolo Oauth, o endereço para fazer a autenticação (com marcadores para fazer substiuições das variaveis) e o endereço para obter a token de acesso aos dados:

``` js
{
    "servers":
    {
        //mesmo nome que atribuimos em $client->server
        "Meu":
        {
            "oauth_version": "2.0",
            "dialog_url": "https://meu.rs.gov.br/oauth/v2/auth?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={STATE}&scope={SCOPE}",
            "access_token_url": "https://meu.rs.gov.br/oauth/v2/token"
        }
    }
}
```

Por padrão, esse arquivo deve ser colocado no mesmo diretorio que o nosso "script" esta rodando, todavia é possivel mudar esse diretorio:

``` php
$client->configuration_file='';
```

Com os dados preenchidos, temos que inicializar a instancia:

``` php
// aqui a classe vai carregar as informações extras do servidor
if (($success = $client->Initialize())) {
    //aqui ele vai processar os dados do $_REQUEST para descobrir se precisa realizar a autorização no gerenciador de identidades e/ou solicitar uma token de acesso
    if (($success = $client->Process())) {
        if (strlen($client->authorization_error)) {
            $client->error = $client->authorization_error;
            $success = false;
        } elseif (strlen($client->access_token)) {
            //quando coneseguirmos a token de acesso podemos pegar os dados 
            $success = $client->CallAPI('https://meu.rs.gov.br/api/v1/person', 'GET', array(), array(
                'FailOnAccessError' => true
            ), $user);
        }
    }
    //aqui ele retorna o status das operações realizadas pela instancia
    $success = $client->Finalize($success);
}
//caso a classe precise encerrar a execucao dessa pagina, como por exemplo, quando o gerenciador de identidades retorna dados para essa pagina, apos solicitar a autorização
if ($client->exit) {
    exit();
}
```

Uma vez obtido a token de acesso podemos pegar os dados do usuario. Na variavel "$user" vai ser preenchida com os dados solicitados, declarados na propriedade "scope". A lista de escopos suportados pode ser encontrado [ aqui ](scopes.md).

