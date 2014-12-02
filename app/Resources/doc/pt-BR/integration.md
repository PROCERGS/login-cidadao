# Integração
Esta página descreve como fazer um integração básica com o login cidadao.
O login cidadaão é um gerenciador de identidades que utiliza o protocolo Oauth.
Para entender um pouco mais de Oauth por favor leia http://oauth.net/2/ e tambem http://aaronparecki.com/articles/2012/07/29/1/oauth2-simplified.

O protocolo Oauth é complexo, por isso, recomendamos utilizar alguma implementação pronta para uso de um desenvolvedor confiavel.
No nosso branch de exemplos há exemplos utilizando as tecnologias java e php para realizar a conexão.
Em todos os exemplos é necessario ter as seguintes informações em mão:

* O endereço para fazer autorização, no caso, seria https://meu.rs.gov.br/oauth/v2/auth
* O endereço para requisitar um token de acesso, no caso, seria https://meu.rs.gov.br/oauth/v2/token
* O endereço para requisitar os dados do usuario, no caso seria https://meu.rs.gov.br/api/v1/person.json
* O conjunto de escopos que vamos requisitar do usuario, no caso, poderia ser usar qualquer numero de escopos suportados separados por espaço. A lista dos escopo suportados pode ser encontrado [ aqui ](scopes.md).
* Precisamos ter um chave publica e um chave privada para utilizar o Oauth. Para conseguir essa chaves basta solicitar o cadastro da sua aplicação pelo seguinte endereço: https://meu.rs.gov.br/contact. Caso seu pedido seja aprovado será lhe enviado essas duas chaves.
* Precisamos espeficiar os endereços de retorno que o gerenciador de identidades esta autorizado a retornar dados.
   
Para continuar escolha uma dos seguintes exemplos para explicarmos como utilizar a autenticacao:

* [ Exemplo utilizando PHP 5.2 ](examplephp5_2.md)
* [ Exemplo utilizando PHP 5.3 ](examplephp5_3.md)
* [ Exemplo utilizando Java ](examplejava5_3.md) 