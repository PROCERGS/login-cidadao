Instalação Detalhada do Login Cidadão
=====================================

> **Se você procura instruções passo-a-passo, como uma receita, verifique as
[instruções para instalação em Debian](cookbook/deploy_debian_os.md).**

## Arquitetura

O Login Cidadão é escrito em PHP com apoio do framework **Symfony 2**
e utiliza **Doctrine** para persistência de dados, suportando tanto
**MySQL** quanto **PostgreSQL**. Além disso, você vai precisar de um
servidor **memcached** para armazenamento de sessões e cache de metadados
do Doctrine.

## O que você vai precisar

Essa documentação não irá descrever os passos necessários para instalar
os componentes necessários que não sejam específicos do Login Cidadão
visto que a configuração de cada um pode variar significativamente de
acordo com as características e necessidades de segurança de cada
organização. Além disso, por se tratar de softwares mantidos por terceiros,
os passos necessários para sua instalação podem variar com o tempo ou com
a versão escolhida.

Portanto, você deve providenciar a instalação dos seguintes componentes
**atualizados**:

  * **Linux ou Mac OS X**: o Login Cidadão suporta oficialmente apenas
Linux e, consequentemente, Mac. Você provavelmente conseguirá instalar em
sistemas como *Microsoft Windows*, entretanto não oferecemos suporte para
esse procedimento;

  * **Usuário não privilegiado**: você precisará de um usuário não-root para
executar o Login Cidadão. Aqui utilizaremos o usuário `logincidadao`.

  * **Certificado para HTTPS**: o Login Cidadão **não suporta HTTP**, sendo 
obrigatório o uso de HTTPS. Caso sua organização não tenha um provedor de
certificados previamente acordado, recomendamos o uso do
[Let's Encrypt](https://letsencrypt.org/), onde você poderá obter
certificados válidos de forma totalmente gratuita. Seu uso é simples e
os certificados podem ser renovados automaticamente;

  * [**nginx**](https://nginx.org/) ou [**Apache**](https://httpd.apache.org/):
para receber as requisições HTTPS, você precisará de um desses dois
softwares. Recomendamos o [**nginx**](https://nginx.org/) por apresentar
boa performance;

  * **PHP 5** ou **7**: a forma mais simples de instalar o Login Cidadão é
utilizando PHP 5. Caso você queira utilizar PHP 7 serão necessárias algumas
modificações uma vez que certas extensões tais como a `memcache` (sem um 'd'
no final) não estão disponíveis;

  * **PHP Extensions**: além do PHP, serão necessárias algumas extensões
para o correto funcionamento do Login Cidadão, tais como `curl`,
`intl`, `memcache`. Note que a disponibilidade de algumas extensões pode
variar de acordo com a versão do PHP que você estiver usando;

  * [**php-fpm**](https://php-fpm.org/): esse será o componente responsável
por processar as requisições PHP. Caso você escolha usar *Apache*, é possível
fazer com que ele processe também as requisições PHP sem a necessidade de um
componente adicional;

  * [**PostgreSQL**](https://www.postgresql.org/) ou
[**MySQL**](https://www.mysql.com/): as informações do Login Cidadão podem
ser armazenadas em bancos de dados **PostgreSQL** ou **MySQL**. Escolha o
que melhor lhe atende;

  * [**memcached**](https://memcached.org/): as informações de sessão e o
cache do *Doctrine* são armazenados em um servidor **memcached**;

  * [**Git**](https://git-scm.com/): é o sistema de controle de versão
utilizado no Login Cidadão. Você precisará dele para baixar e manter
atualizado o código fonte;

  * [**composer**](http://getcomposer.org/): é um gerenciador de
dependências para PHP. Recomendamos que você instale ele globalmente, de
forma que ele seja acessível a partir de qualquer diretório do sistema;

  * [**Node.js**](https://nodejs.org/en/): é um runtime JavaScript
utilizado pelo Login Cidadão para gerar assets tais como scripts e CSS.

## Tipos de Instalação

Atualmente o Login Cidadão é uma aplicação inteira e não um *Bundle*. Isso
quer dizer que você não o instala em uma aplicação *Symfony 2* já existente
como você faria normalmente com um *Bundle*.

Por esse motivo, antes de iniciar a instalação do Login Cidadão você precisa
decidir se deseja customizar sua instalação (trocando cores, imagens, textos)
ou se a versão padrão já lhe atende.

Existem planos para transformar o Login Cidadão em um *Bundle*, o que irá
facilitar o processo de instalação e customização, mas ainda não temos
previsão para que isso ocorra.

### Instalação personalizada
Se você deseja fazer alterações em sua instalação, é necessário que você
primeiramente crie um fork do projeto base. Dessa forma você conseguirá
manter sua instalação atualizada com o projeto principal sem perder suas
modificações.

Para orientações sobre como fazer um fork, verifique a
[documentação do GitHub](https://help.github.com/articles/fork-a-repo/).

### Instalação Padrão
Utilizar o Login Cidadão sem nenhuma alteração é a forma mais fácil e rápida
de iniciar. Para isso, basta clonar o repositório principal conforme será
demonstrado no passo-a-passo.

Se você não tem experiência com Git, você pode consultar a documentação
do GitHub sobre [como clonar um repositório](https://help.github.com/articles/cloning-a-repository/).

## Preparação do Banco de Dados

Caso você ainda não tenha configurado seu banco de dados, esse é o momento ideal.
Tenha em mente que o *Doctrine* pode conectar-se ao seu banco de dados de duas
formas, a primeira é utilizando um login e senha e um endereço IP e a segunda é
com um *Socket Unix*. Você deve configurar seu banco de dados de acordo com a forma
desejada e configurar o Login Cidadão para utilizar o tipo de conexão escolhido.

### Utilizando *Unix Sockets*

No caso de conexão via sockets, que a documentação do *Symfony* não cobre muito
bem, você deverá utilizar o valor `~` para os parâmetros `database_host`,
`database_port` e `database_password`, bastando informar os valores dos parâmetros
`database_name` e `database_user` conforme configuração do seu banco de dados.

Independentemente da forma de conexão escolhida, você deverá criar um *schema*
para utilização do Login Cidadão e informar o nome desse *schema* no parâmetro
`database_name`.

## Instalando o Login Cidadão

A partir desse ponto, consideramos que você já tenha instalado os softwares
necessários listados nos itens anteriores além de ter conferido o nosso
`README.md` que apresenta vários requisitos esperados da pessoa ou entidade
que deseja ter seu próprio *Provedor de Identidade*.

Todos os passos descritos assumem que você está autenticado como o usuário
`logincidadao`. Caso você não esteja utilizando o usuário que executará a
aplicação, verifique as instruções específicas do seu sistema operacional
referentes à criação de usuários.

    # sudo su logincidadao

O primeiro passo será obter o código do Login Cidadão. Se você está usando
um fork, como é o caso de uma `Instalação Personalizada`, troque o endereço
do repositório pelo endereço de seu fork.

    $ git clone https://github.com/redelivre/login-cidadao.git

Após ter baixado o código do Login Cidadão através do Git, você precisará
instalar as dependências do projeto. Entre no diretório que foi criado pelo
Git e instale as dependências utilizando Composer.

    $ cd login-cidadao
    $ composer install

Essa etapa pode levar vários minutos já que são diversas dependências.
Além das bibliotecas solicitadas pelo Login Cidadão, o Composer também
verificará se o PHP instalado em seu sistema possui as extensões que
são necessárias para a execução das dependências. Esse pode ser um bom
momento para reabastecer seu café ou chá.

    ☕

Caso você não tenha alguma das extensões basta instalar usando o
gerenciador de pacotes do seu sistema. Por exemplo, se o composer detectar
que está faltando a `ext-curl` você pode solicitar a instalação do pacote
`php5-curl` ou `php7.0-curl`, dependendo da versão do PHP utilizada.

    $ sudo apt-get install php5-curl

Após a conclusão da instalação das dependências, o composer executará
scripts de pós-instalação do Symfony onde serão solicitados os parâmetros
do seu ambiente de forma iterativa. Você pode consultar a descrição dos
parâmetros no arquivo `app/config/parameters.yml.dist` ou, em maiores detalhes,
na [documentação específica](config_parameters.md).

Os parâmetros da sua instância serão salvos no arquivo `app/config/parameters.yml`,
onde você pode alterar posteriormente caso queira fazer algum ajuste.

É importante lembrar de limpar o cache da aplicação a cada alteração feita
no diretório `app/config`.

    $ ./app/console cache:clear -e prod

Depois de configurar seu `parameters.yml` você já deve conseguir criar a
estrutura necessária do banco de dados e, em seguida, popular com os dados
iniciais.

    $ ./app/console doctrine:schema:update --force
    $ ./app/console lc:database:populate batch/

Por último, execute o comando `lc:deploy` que irá preparar sua instalação
para execução em produção. Esse comando limpará o cache de metadados do
Doctrine, verificará se o banco de dados está atualizado e providenciará a
geração dos assets necessários.

    $ ./app/console lc:deploy

A instalação do Login Cidadão está pronta, mas você provavelmente ainda não
conseguirá acessá-la. Isso ocorre pois é necessário que um servidor HTTPS
receba suas requisições e as encaminhe para o PHP. Para isso, no próximo
capítulo, configuraremos o `php-fpm` para aceitar requisições para o Login
Cidadão, inclusive usando o usuário correto.

## Configurando o php-fpm

Antes de configurar seu servidor HTTPS para encaminhar as requisições ao PHP,
você precisa ter um software para recebê-las e esse será o papel do `php-fpm`.

Começaremos criando o arquivo `logincidadao.conf` dentro do diretório
`/etc/php5/fpm/pool.d`.

**Nota:** Caso esse diretório não exista no seu sistema operacional você deve
consultar a documentação específica para seu sistema para definir o caminho
correto.

Abra o arquivo recém criado (`/etc/php5/fpm/pool.d/logincidadao.conf`) no seu
editor preferido e deixe-o conforme o exemplo:

    # /etc/php5/fpm/pool.d/logincidadao.conf
    [logincidadao]
    user = logincidadao
    group = logincidadao
    listen = /var/run/php5-fpm-logincidadao.sock
    listen.owner = www-data
    listen.group = www-data
    php_admin_value[disable_functions] = exec,passthru,shell_exec,system
    php_admin_flag[allow_url_fopen] = off
    pm = dynamic
    pm.max_children = 5
    pm.start_servers = 2
    pm.min_spare_servers = 1
    pm.max_spare_servers = 3
    chdir = /

Certifique-se de que os valores `listen.owner` e `listen.group` estão de acordo
com os respectivos usuário e grupo usados por seu servidor HTTPS escolhido, já
que essa configuração pode variar de acordo com o sistema operacional usado e
entre `nginx` e `apache`, por exemplo.

Após configurar o arquivo `/etc/php5/fpm/pool.d/logincidadao.conf` conforme as
instruções, salve-o e reinicie o serviço do `php-fpm` utilizando os comandos
pertinentes ao seu sistema operacional.

## Configurando o servidor HTTPS

Estando com o Login Cidadão instalado e configurado e tendo o `php-fpm` pronto
para receber as requisições, você só precisa configurar um servidor HTTPS.
Nesse exemplo será demonstrado como configurar o `nginx`, mas você pode adaptar
as instruções para qualquer outro software equivalente como o `Apache`.

Antes de criar o arquivo de configuração para interagir com o Login Cidadão,
certifique-se de que você já tenha um arquivo `dhparam.pem` gerado no caminho
`/etc/ssl/certs/dhparam.pem`. Caso esse arquivo não exista, você pode gerá-lo
utilizando o comando `openssl`. Esse processo deverá demorar bastante, hora
de reabastecer. ☕

    # openssl dhparam -out /etc/ssl/certs/dhparam.pem 4096

Para que uma instalação padrão do `nginx` esteja apta a encaminhar requisições
ao Login Cidadão, você precisará criar o arquivo `logincidadao.conf` no diretório
`/etc/nginx/conf.d`, ou equivalente. Esse arquivo deve conter o seguinte:

    # /etc/nginx/conf.d/logincidadao.conf
    server {
        server_name  SEU.DOMINIO;
    
        ####### \/ Let's Encrypt INÍCIO \/ #######
        # Permite tráfego HTTP para que o Let's Encrypt funcione
        location '/.well-known/acme-challenge' {
             root /var/letsencrypt;
        }
        ######## /\ Let's Encrypt FIM! /\ ########
        
    
        location / {
            return 301 https://SEU.DOMINIO$request_uri;
        }
    }
    
    server {
        listen [::]:443 default_server ssl http2 ipv6only=off;
    
        add_header Strict-Transport-Security max-age=63072000;
        ssl_session_timeout 5m;
        ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers  'AES256+EECDH:AES256+EDH';
        ssl_prefer_server_ciphers   on;
        ssl_session_cache           shared:SSL:10m;
        ssl_dhparam                 /etc/ssl/certs/dhparam.pem;
    
        server_name  SEU.DOMINIO;
        root /home/logincidadao/login-cidadao/web;
    
        ##### ! Estes caminhos são padrão do Let's Encrypt.
        ##### ! Ajuste de acordo com o seu certificado
        ssl_certificate /etc/letsencrypt/live/SEU.DOMINIO/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/SEU.DOMINIO/privkey.pem;
    
        location / {
            try_files $uri /app.php$is_args$args;
        }
    
        fastcgi_buffers 4 256k;
        fastcgi_buffer_size 128k;
        fastcgi_busy_buffers_size 256k;
    
        location ~ ^/(app_dev|config)\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm-logincidadao.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $document_root;
        }
    
        # PROD
        location ~ ^/app\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm-logincidadao.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $document_root;
            internal;
        }
    
        location ~ \.php$ {
          return 404;
        }
    
        error_log /var/log/nginx/logincidadao_error.log;
        access_log /var/log/nginx/logincidadao_access.log;
    }

**Importante:** nesse exemplo temos configurações específicas para permitir a 
emissão de certificados HTTPS usando **Let's Encrypt**. Caso você não pretenda 
utilizar esse serviço, basta remover ou alterar os trechos referentes ao
*Let's Encrypt* e executar os passos requeridos por seu provedor de certificados.

**Importante:** caso queira utilizar *Let's Encrypt* e essa seja a primeira emissão
de certificado para o domínio desejado, você precisará comentar as opções
`ssl_certificate` e `ssl_certificate_key`, caso contrário não será possível
reiniciar o `nginx` haja visto que os certificados ali informados ainda não existem.

Após modificar esse arquivo de configuração de acordo com seu ambiente e reiniciar
o `nginx` será a hora de configurar o Let's Encrypt ou seu certificado HTTPS obtido
de outra forma.

## Utilizando Let's Encrypt

Para realizar a instalação de um certificado HTTPS *Let's Encrypt* pela primeira
vez, você deverá comentar as opções `ssl_certificate` e `ssl_certificate_key` em
seu `logincidadao.conf` que criamos na etapa anterior. Depois de comentar as linhas
citadas, reinicie o `nginx` de acordo com seu sistema operacional.

Agora basta seguir as instruções de utilização do [Certbot](https://certbot.eff.org/),
lembrando de informar que você está utilizando `nginx` e seu sistema operacional
escolhido. A documentação do *Certbot* irá lhe guiar na instalação do Certbot
bem como na instalação do seu certificado HTTPS. A forma de instalação a ser usada
deve ser *webroot* e você deve informar o diretório `/var/letsencrypt`.

Por exemplo, caso queira usar o dominio `meu.logincidadao.org`, o comando será da
seguinte forma:

    certbot certonly --webroot -w /var/letsencrypt -d meu.logincidadao.org

Caso tenha configurado tudo corretamente você encontrará os certificados nos
diretórios informados pelo *certbot*. Agora basta descomentar as linhas
`ssl_certificate` e `ssl_certificate_key` e reiniciar o *nginx* para concluir a
instalação.

### Renovação Automática

Como os certificados emitidos pelo serviço *Let's Encrypt* tem validade de apenas
90 dias, é recomendado que se configure a renovação automática dos certificados.

A configuração é bastante simples, você só precisa adicionar a seguinte linha
em seu *crontab*:

    0 14 * * * certbot renew --post-hook "systemctl reload nginx"

**Importante:** observe que há um comando "post-hook", que é executado depois da
renovação. Caso em seu sistema operacional o comando para recarregar as
configurações do *nginx* seja diferente, lembre-se de alterar de acordo.

# Pós-Instalação

Agora que você já deve ter uma instância funcional do Login Cidadão, você
provavelmente deseja [definir um usuário administrador](?doc=cookbook/admin_user).
