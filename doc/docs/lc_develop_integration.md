# Integração

## Introdução

Esta página descreve como fazer um integração básica com o Login Cidadao, um gerenciador de identidades que utiliza o protocolo OAuth 2.

Caso queira mais informações sobre OAuth 2, por favor, leia [ a página do OAuth 2 ](http://oauth.net/2/) e também [ este post de Aaron Parecki ](http://aaronparecki.com/articles/2012/07/29/1/oauth2-simplified).

O protocolo OAuth 2 é complexo, por isso, a menos que você conheça profundamente tanto a [ RFC-6749 ](http://tools.ietf.org/html/rfc6749) quanto as melhores práticas em segurança, utilize alguma biblioteca implementada e mantida por alguém que conheça.

## Antes de Começar

### <a name="basic_info"></a>Informações Básicas

São informações básicas e fundamentais para o funcionamento dos exemplos e do processo de autenticação:

 * URLs do Login Cidadão
   * URL de Autorização. Exemplo: `https://meu.rs.gov.br/oauth/v2/auth`
   * URL para solicitação de Access Token. Exemplo: `https://meu.rs.gov.br/oauth/v2/token`
   * URL com os dados do usuário autenticado. Exemplo: `https://meu.rs.gov.br/api/v1/person.json`
 * O conjunto de escopos que serão acessados
 * Chave pública do Client OAuth
 * Chave privada (ou Secret) do Client OAuth
 * Precisamos especificar as URLs de Retorno que o gerenciador de identidades esta autorizado a retornar dados.

Tanto as URLs do Login Cidadão quanto as chaves de acesso devem ser obtidas na instalação do Login Cidadão na qual você deseja autenticar-se enquanto as URLs de Retorno dependem do seu Client OAuth.

### Escopos

Você pode solicitar quantos e quais escopos desejar, entretanto esteja ciente de que quanto mais informações você solicitar ao usuário menor será a probabilidade do mesmo lhe autorizar a recebê-las, portanto solicite apenas o **absolutamente necessário**.

A lista dos escopos suportados pode ser encontrada [ aqui ](lc_develop_scopes.md).

## Exemplos

No nosso branch `exemplos` demonstramos a integração utilizando `Java`, `PHP 5.2` e `PHP 5.3`.
Em todos os exemplos é necessário ter as [ Informações Básicas ](#basic_info).

Para continuar escolha um dos seguintes exemplos para explicarmos como utilizar a autenticação:

* [ Exemplo utilizando PHP 5.2 ](lc_develop_connecting_with_php5.2_applications.md)
* [ Exemplo utilizando PHP 5.3 ](lc_develop_connecting_with_php5.3_applications.md)
* [ Exemplo utilizando Java ](lc_develop_integration.md)


