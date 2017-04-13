Introdução à CLI do Symfony
===========================

O Login Cidadão foi construído utilizando o framework PHP *Symfony 2*, dessa
forma dispomos de uma importante e poderosa ferramenta para auxiliar a
instalação, configuração e manutenção do Login Cidadão: os comandos.

Nesse capítulo abordaremos os conceitos básicos dos comandos do *Symfony* para
que você possa, rapidamente, familiarizar-se com esse conceito.

O que são?
----------

Os comandos do *Symfony* são artefatos de código feitos especialmente para serem
executados através do seu terminal. Esses scripts são capazes de utilizar as
mesmas funcionalidades do framework para os mais variados propósitos, tais como
limpeza de cache, gerenciamento de permissões de usuários, atualização da
estrutura do banco de dados.

Os comandos do *Symfony* seguem o formato `./app/console nome:do-comando` onde
`./app/console` é o script responsável por inicializar o console de comandos e
`nome:do-comando` é o comando que será executado.

Como usar?
----------

Para utilizar essa versátil ferramenta é ideal que você tenha se autenticado
como o usuário responsável por executar o Login Cidadão. No caso do [exemplo de
instalação em Debian](cookbook/deploy_debian_os.md) esse usuário é `logincidadao`.

    sudo su logincidadao

Além de estar utilizando o usuário correto, para que os comandos funcionem
corretamente você deve estar na raiz do projeto que, no caso do exemplo de
instalação em Debian, localiza-se em `/home/logincidadao/login-cidadao`.

    cd /home/logincidadao/login-cidadao

Estando na pasta correta, você pode listar todos os comandos disponíveis ao
executar o console do *Symfony* sem informar nenhum comando. Isso resultará
na exibição de uma lista de comandos com suas respectivas descrições resumidas.

    ./app/console

Outro conceito importante de se observar é que os comandos podem ser executados
em modo de Produção ou de Desenvolvimento através da opção  `env`. Se você
quiser, por exemplo, limpar o cache de Desenvolvimento não é necessário informar
nenhuma opção adicional pois o console assumirá o ambiente de Desenvolvimento
como padrão.

    ./app/console cache:clear

Entretanto, para limpar o cache do ambiente de Produção você precisará informar
o valor `prod` para a opção `env`, sendo possível, também, usar o atalho `-e` no
lugar do nome completo `--env`.

    ./app/console cache:clear -e prod
