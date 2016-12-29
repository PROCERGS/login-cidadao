Login Cidadão
=============

Sobre o Projeto
---------------

Login Cidadão é um software livre que atua como um provedor de identidade
implementado protocolos como **OpenID Connect** e **OAuth 2**. Seu foco de
utilização é em entidades governamentais, entretanto não há nenhuma restrição
no projeto que impeça seu isso por entes privados.

O projeto surgiu em Dezembro de 2013 e começou a ser desenvolvido na PROCERGS, no
Rio Grande do Sul. Nessa época a motivação era criar uma prova de conceito de um
sistema para login unificado de vários sistemas do Governo do Estado do Rio
Grande do Sul.

Como em 2013/2014 o OpenID Connect ainda estava em Draft, optou-se por adotar
OAuth 2.0 como protocolo de autorização para o projeto até que o OpenID Connect
estivesse pronto.

Tecnologias Utilizadas
----------------------

O Login Cidadão foi construído utilizando o framework PHP **Symfony 2** e visando
compatibilidade com **PostgreSQL** e **MySQL**. Para armazenamento de dados da
sessão, utiliza-se **Memcached**.
