# Exemplo utilizando PHP 5.3

## Antes de Começar

Certifique-se de que você tem todos os dados descritos [ aqui ](integration.md#basic_info) antes de seguir o tutorial.

### Dependências

Para conectar no Login Cidadão usando `PHP 5.3` é utilizado o cliente OAuth desenvolvido pelo [fkooman](https://github.com/fkooman/php-oauth-client). Uma vez que é usado php5.3 é possivel usar o `composer` para instalar o componente e suas dependecias. Primeiramente, é necessario instalar o [composer](https://getcomposer.org/).
No arquivo `composer.json` basta adicionar a seguinte configuração:

``` js
{
    "name": "fkooman/php-oauth-client-example", 
    "require": {
        "fkooman/guzzle-bearer-auth-plugin": "dev-master", 
        "fkooman/php-oauth-client": "dev-master"
    }
}
```

Utilizando a linha de comando, no mesmo diretorio que esta o arquivo `composer.json` é possivel executar o seguinte comando: `composer install --prefer-dist`. Assim o `composer` instalará o componente desejado e suas dependencias.

## Começando

### Arquivo de configuração

Primeiramente criamos um arquivo de configuração `config.ini` referente ao servidor OAuth que desejamos utilizar. Nesse arquivo, especificamos o endereço para fazer a autenticação, o endereço para obter o Access Token, o endereço para onde o gerenciador de identidades irá retornar os dados, a chave pública, a chave privada, o nome do servidor, os escopos desejados e o endereço para obter os dados do usuário:

```
[fkooman_client_config]
//endereço para fazer a autenticação
authorize_endpoint = "https://meu.rs.gov.br/oauth/v2/auth";
//endereço para requerer o Access Token
token_endpoint = "https://meu.rs.gov.br/oauth/v2/token";
//endereço para retornar os dados
redirect_uri = "http://localhost/callback.php";
//chave pública
client_id = "";
//chave privada
client_secret = "";

[fkooman_api_config]
//nome do servidor
api_context = "Meu"
//escopos
api_scopes = "id username full_name cpf birthdate email city picture public_profile badges country state city addresses id_cards get_all_notifications notifications cep mobile";
//endereço para requerer os dados do usuário
api_url = "https://meu.rs.gov.br/api/v1/person.json";
```

### Criando um script de autenticação

É necessário criar um script que oferece a possibilidade do usuario autenticar. Esse script tem que testar se o usuário ja esta autenticado. No momento que o script detectar que o usuário esta autenticado, ele que pegar as informação sobre esse usuário.
 
``` php
//primeiro carregamos as configurações
$config = parse_ini_file('config.ini');
if (false === $config) {
    die('please you need to have a config.ini file. Make one based on config.ini.dist');
}
//com as configurações é criado um instância das configurações do cliente
$clientConfig = new \fkooman\OAuth\Client\ClientConfig($config);

//criamos uma instância para armazenar os dados utilizados pelo Oauth
$tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
//criamos uma instância para fazer a comunicação http
$httpClient = new \Guzzle\Http\Client();
//criamos uma instância para utilizar a API do nosso gerenciador de identidades 
$api = new fkooman\OAuth\Client\Api($config['api_context'], $clientConfig, $tokenStorage, $httpClient);
//configuramos o contexto da nossa API
$context = new \fkooman\OAuth\Client\Context($config['api_context'], explode(" ", $config['api_scopes']));
//verificamos se já temos uma Access Token
$accessToken = $api->getAccessToken($context);
if (false === $accessToken) {
	//caso não tenhamos uma Access Token
	//se temos uma variável indicando que desejamos autenticar
    if ($_GET['authorize'] == 1) {        
    	//enviamos o usuario para a tela de autorização do gerenciador de identidades    
        header("HTTP/1.1 302 Found");
        header("Location: " . $api->getAuthorizeUri($context));
    } else {
    	//oferecemos a autenticação para o usuário
        echo "<a href='index.php?authorize=1'>Logar com o Login do Cidadao</a>";
    }
    exit;
} else {
	//caso tenhamos a Access token podemos consultar a API e obter os dados dele
    try {
        $client = new \Guzzle\Http\Client();
        $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
        $client->addSubscriber($bearerAuth);
        $response = $client->get($config['api_url'])->send();
        header("Content-Type: application/json");
        //o retorno da API é um no formato JSON, por isso temos que decodifica-la
        $json = json_decode($response->getBody(), true);
        if (false !== $json) {
            print_r($json);
        } else {
            print_r($response->getBody());
        }
    } catch (\fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
    	//caso tenhamos um erro de token inválida, jogamos essa token fora, tentamos autenticar novamente
        if ("invalid_token" === $e->getBearerReason()) {            
            $api->deleteAccessToken($context);
            $api->deleteRefreshToken($context);            
            header("HTTP/1.1 302 Found");
            header("Location: " . $api->getAuthorizeUri($context));
            exit;
        }
        throw $e;
    } catch (\Exception $e) {
        die(sprintf('ERROR: %s', $e->getMessage()));
    }    
}

}
```

### Criando um script que recebe o retorno do gerenciador de identidades

Também é necessario criar um script que recebe os dados de retorno do gerenciador de identidades. Esse script quando receber os dados do servidor deve encaminhar para o script de autenticação

```php
//primeiro carregamos as configurações
$config = parse_ini_file('config.ini');
if (false === $config) {
    die('please you need to have a config.ini file. Make one based on config.ini.dist');
}
//com as configurações é criado um instância das configurações do cliente
$clientConfig = new \fkooman\OAuth\Client\ClientConfig($config);
try {
	//criamos uma instância para armazenar os dados utilizados pelo Oauth
    $tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
    //criamos uma instância para fazer a comunicação http
    $httpClient = new \Guzzle\Http\Client();
    //criamos uma instância para lidar com o retorno do gerenciador de identidades
    $cb = new \fkooman\OAuth\Client\Callback($config['api_context'], $clientConfig, $tokenStorage, $httpClient);    
    $cb->handleCallback($_GET);
    //direcionamos para o script de autenticação
    header("HTTP/1.1 302 Found");
    header("Location: index.php");
    exit;
} catch (\fkooman\OAuth\Client\AuthorizeException $e) {
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (\Exception $e) {
    die(sprintf("ERROR: %s", $e->getMessage()));
}
```

Com esses dois script é possivel autenticar o usuário e recuperar suas informações do gerenciador de identidades

[ Voltar ao Índice ](index.md)
