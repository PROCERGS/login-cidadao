Exemplo de Logout
=================

Tão importante quanto autenticar os usuários em um sistema é permitir que eles
consigam encerrar sua sessão de forma segura e conveniente, dessa forma evitamos
que um terceiro acesse, indevidamente, uma sessão previamente estabelecida que
não tenha sido encerrada corretamente.

Quando o usuário autentica-se para um sistema, sua sessão fica ativa tanto no
Login Cidadão quanto no sistema para o qual a autenticação foi feita. Embora isso
ofereça grande comodidade ao usuário que não precisará logar-se novamente no
Login Cidadão caso pretenda autenticar-se em outro sistema, acabamos tendo uma
potencial vulnerabilidade caso o usuário utilize um computador público.

Ao terminar de utilizar o sistema desejado, o usuário encerrará sua sessão nesse 
sistema, entretanto sua sessão no Login Cidadão continuará ativa, permitindo que
uma outra pessoa que acesse o mesmo computador possa autenticar-se como o usuário
anterior. Para mitigar esse problema o Login Cidadão implementa a seção 5 da
especificação, ainda em draft, [OpenID Connect Session Management 1.0](http://openid.net/specs/openid-connect-session-1_0.html)
denominada [RP-Initiated Logout](http://openid.net/specs/openid-connect-session-1_0.html#RPLogout).

A especificação RP-Initiated Logout descreve um processo de logout do provedor
de identidade que é iniciado a partir de um Cliente/Serviço onde o serviço, **após
encerrar a sessão com o usuário**, redireciona o mesmo ao provedor de identidade 
para que sua sessão seja encerrada também e é isso que será demonstrado neste
exemplo.

**Importante**: Antes de continuar, vamos considerar que você já tem o processo
de login funcionando corretamente e que você armazenou o ID Token recebido no
momento do login.

Supondo que o usuário está autenticado e você está de posse do ID Token, basta
encerrar a sessão no sistema cliente e redirecionar para o Login Cidadão no
`end_session_endpoint`, que é o endpoint responsável por encerrar a sessão ativa.

``` php
<?php
// logout.php

// Salve o ID Token do usuário atual. Isso varia conforme a biblioteca
// OpenID Connect que você estiver usando.
$idToken = getIdToken();

// Execute seu procedimento para encerrar a sessão normalmente
yourLogoutFunction();

// Prepare o endereço para onde o usuário será redirecionado:
// Primeiro obtemos o endereço do end_session_endpoint. Isso varia conforme
// a biblioteca usada.
$endSessionEndpoint = getEndSessionEndpoint();
$query = ['id_token_hint' => $idToken];
if (function_exists('http_build_url')) {
    $url = parse_url($endSessionEndpoint);
    $url['query'] = $query;
    $url = http_build_url($url);
} else {
    $url = $endSessionEndpoint.'?'.http_build_query($query);
}

// Por último, redirecione o usuário para a URL gerada. Esse passo também
// dependerá das bibliotecas usadas em sua aplicação.
redirect($url);
```
