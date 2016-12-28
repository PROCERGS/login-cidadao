API
===

Nesse capítulo falaremos sobre os princípios da API do Login Cidadão, que segue
o princípio de que apenas o usuário proprietário da conta deve conseguir alterar
seus dados, portanto não há nenhuma API com acesso de escrita.

Atualmente o Login Cidadão possui apenas dois endpoints ativos em sua API: o
UserInfo endpoint para obter informações do usuário autenticado via OpenID
Connect e o "Wait Update" para que serviços possam aguardar que uma determinada
alteração seja realizada na conta do usuário.

UserInfo Endpoint
-----------------

Esse endpoint serve para atender os requisitos do OpenID Connect
([Section 5.3](http://openid.net/specs/openid-connect-core-1_0.html#UserInfo))
e retorna para o Client todas as informações que ele tem acesso sobre o usuário
autenticado.

O endereço desse endpoint pode ser obtido, conforme explicado na
[Section 4 da especificação OpenID Connect Discovery](http://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig),
no valor da chave `userinfo_endpoint` localizada nos metadados do OpenID Provider.

Normalmente toda a interação necessária com esse endpoint será realizado pela
biblioteca OpenID Connect utilizada por seus serviços clientes.

Wait Update Endpoint
--------------------

O Wait Update Endpoint é semelhante ao UserInfo Endpoint no sentido de retornar
informações sobre o usuário, entretanto esse endpoint realizará um Long Polling
para aguardar uma atualização cadastral por parte do usuário para, só então,
responder a requisição. Esse endpoint é especialmente útil para serviços que
necessitem de uma informação preenchida incorretamente pelo usuário e que
desejam aguardar sua correção para dar um retorno em tempo real.

Essa API é usada na tela de confirmação de email do Login Cidadão onde aguarda-se
que o usuário confirme seu email para realizar uma animação na tela, em tempo
real, e redirecioná-lo à próxima etapa.

A utilização de Long Polling foi preferida no lugar de padrões mais consolidados
devido a falta de suporte a WebSockets em alguns servidores.

Esse endpoint pode ser acessado da mesma forma que o UserInfo e seu endereço é
`/api/v1/wait/person/update`.
