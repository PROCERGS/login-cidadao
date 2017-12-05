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
estivesse pronto. Em 2015 o Login Cidadão passou a suportar OpenID Connect e esse
é o protocolo padrão desde então.

Tecnologias Utilizadas
----------------------

O Login Cidadão foi construído utilizando o framework PHP **Symfony 2** e visando
compatibilidade com **PostgreSQL** e **MySQL**. Para armazenamento de dados da
sessão, utiliza-se **Memcached**.

Tópicos da Documentação
-----------------------

  - **Instalação**
    - [Instruções detalhadas](deploy.md)
    - [Passo-a-passo para Debian](cookbook/deploy_debian_os.md)
    - [Troubleshooting](deploy_troubleshooting.md)
  - **Configuração**
    - [Arquivo parameters.yml](config_parameters.md)
  - **Gerenciamento da Instalação**
    - [Atualizando o Login Cidadão](maintenance.md)
    - [Gerenciamento de Usuários](maintenance_user_management.md)
    - [Comandos do Symfony](maintenance_symfony_commands.md)
  - **Uso do Login Cidadão**
    - [Usando OpenID Connect](cookbook/using_openid_connect.md)
    - [Deslogando usuários "remotamente"](cookbook/using_logout.md)
    - [Documentação da API](api.md)
    - [Migração/Importação de Usuários](cookbook/import_users.md)
