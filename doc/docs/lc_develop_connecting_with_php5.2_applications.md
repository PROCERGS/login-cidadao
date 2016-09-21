# Exemplo utilizando PHP 5.2

## Antes de Começar

Certifique-se de que você tem todos os dados descritos [ aqui ](lc_develop_integration.md#basic_info) antes de seguir o tutorial.

### Dependências

Para conectar no Login Cidadão usando `PHP 5.2` utilizamos o cliente OAuth disponibilizado pelo site [PHPCLASSES](http://www.phpclasses.org/oauth-api).
Essa implementação tem como dependência a classe `http-client` também disponível pelo site [PHPCLASSES](http://www.phpclasses.org/httpclient).

## Começando

### Arquivo de configuração

Primeiramente criamos um arquivo de configuração `oauth_configuration.json` referente ao servidor OAuth que desejamos utilizar. Nesse arquivo, especificamos a versão do protocolo OAuth, o endereço para fazer a autenticação, com marcadores para fazer substituições de variáveis, e o endereço para obter o Access Token:

``` js
// oauth_configuration.json
{
    "servers":
    {
        // ATENÇÃO: Você deverá informar esse nome na próxima etapa, em $client->server
        "Meu":
        {
            "oauth_version": "2.0",
            "dialog_url": "https://meu.rs.gov.br/oauth/v2/auth?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={STATE}&scope={SCOPE}",
            "access_token_url": "https://meu.rs.gov.br/oauth/v2/token"
        }
    }
}
```

### Configurando o Client OAuth

Primeiro instanciamos a classe `oauth_client_class`:

``` php
$client = new oauth_client_class();
//aqui especificamos o nome do servidor que vamos utilizar. Isso carregar as configurações extras do cliente OAuth
$client->server = 'Meu';
//colocamos um dos nossos endereços que autorizamos o Login Cidadão a retornar dados
$client->redirect_uri = '';
//colocamos nossa chave pública aqui
$client->client_id = '';
//colocamos nossa chave privada aqui
$client->client_secret = '';
//colocamos a lista de escopos desejados *separados por espaços*
$client->scope = '';
```

Por padrão, o arquivo de configuração deve ser colocado no mesmo diretório que o nosso script está rodando, todavia é possível mudar esse diretório através do seguinte atributo:

``` php
$client->configuration_file='';
```

### Fazendo a mágica acontecer

Com os dados preenchidos, podemos inicializar a instância:

``` php
// aqui a classe vai carregar as informações extras do servidor
if (($success = $client->Initialize())) {
    // aqui ele vai processar os dados do $_REQUEST para descobrir se precisa realizar a autorização no gerenciador de identidades e/ou solicitar um Access Token
    if (($success = $client->Process())) {
        if (strlen($client->authorization_error)) {
            $client->error = $client->authorization_error;
            $success = false;
        } elseif (strlen($client->access_token)) {
            // quando conseguimos o Access Token podemos pegar os dados 
            $success = $client->CallAPI('https://meu.rs.gov.br/api/v1/person', 'GET', array(), array(
                'FailOnAccessError' => true
            ), $user);
        }
    }
    // aqui ele retorna o status das operações realizadas pela instância
    $success = $client->Finalize($success);
}
// caso a classe precise encerrar a execução desta pagina como, por exemplo, quando o gerenciador de identidades retorna dados para essa pagina, apos solicitar a autorização
if ($client->exit) {
    exit();
}
```

Uma vez obtido o Access Token podemos pegar os dados do usuário através da variável `$user` que estará populada com as informações solicitadas na propriedade `scope`.


