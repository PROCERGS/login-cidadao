# Instalação [Login Cidadão](https://github.com/PROCERGS/login-cidadao)
Esta documentação foi baseada em um servidor GNU/Linux Debian 8.1. Se sua intenção é instalar em sistema de base diferente algumas adaptações podem ser necessárias. 

## Server User
Para instalar a aplicação é necessário ter acesso a dois usuários: um usuário padrão e um usuário com poderes de sudo. Você pode usar o usuário padrão de costume de seu servidor e o root, mas recomendamos criar um usuário só para o gerenciamente do Login Cidadão, facilitando assim o controle e o registro de logs do sistema. 
```
    // logado como root, crie o novo user:
    # useradd --create-home --groups sudo -s /bin/bash login-cidadao
    // Insira uma senha para o novo user
    # passwd login-cidadao
``` 

## Instalando Dependências
Para que o Login Cidadão funcione corretamente será necessário que estejam instaladas as seguintes dependências: 
* Apache ou Nginx
* PHP >=5.3.3
* memcached
* postgres ou mysql
* composer
* node.js
* PHP Extensions
  * php5-curl
  * php5-intl
  * php5-mysql ou php5-pgsql ou integração de base de dados de sua preferência
  * php5-memcache (Também é possível usar php5-memcached mas será necessário mudar algumas classes de Memcache para Memcached)

```
    // Instale o servidor Nginx 
    $ sudo apt-get install nginx

    //Caso tenha algum gerenciador de bases de dados(mysql ou postgres) você poderá usá-lo. 
    //Se não tiver, instale o banco que quer usar. Optamos aqui por postgres
    $ sudo apt-get install postgresql

    // Instale o git para auxiliá-lo no processo de obtenção do código da aplicação
    $ sudo apt-get install git

    // Instalando pacote do server memcached 
    $ sudo apt-get install memcached
    
    // Instalando pacotes do php. 
    // Observe que aqui vamos optar por usar o mysql, mas é possível usar mysql sem problemas
    $ sudo apt-get install php5 php5-curl php5-intl php5-pgsql php5-memcache
       
    // Instalando nodejs
    $ sudo curl -sL https://deb.nodesource.com/setup_5.x | bash -
    $ sudo apt-get install --yes nodejs
```

## Obtendo o Login Cidadão 

1. Após instalar as dependências, clone o repositório da aplicação e mude as permissões dos arquivos para operar com apache ou nginx. Recomendamos fazer isso com o usuário criado e dentro de uma estrutura de diretórios padrão /var/www/login-cidadao, mas é possível adaptar para qualquer contexto. 
```
    //Logado como login-cidadao user, vá para o diretório /var/www
    $ cd /var/www
    // Dentro do diretório, clone o repositório da aplicação
    $ sudo git clone https://github.com/redelivre/login-cidadao.git
    // Entre no diretório criado 
    $ cd login-cidadao
    // Mude a permissão dos arquivos. Pode ser necessário fazer isso no final do processo novamente.
    $ sudo chown -R login-cidadao:www-data *
    $ sudo chown -R login-cidadao:www-data .*
```

Depois de realizar a clonagem do repositório e a mudança no permissionamento dos arquivos, aplique o comando de listagem de arquivos no diretório para verificar se a mudança de permissionamento foi aplicada com sucesso. Seu diretório deve estar assim:

```
	// aplicando o comando de listagem de arquivos e permissões, você deverá ver algo como isso:

	login-cidadao@localhost:/var/www/login-cidadao$ ls -la
	drwxr-xr-x  9 login-cidadao www-data   4096 Dec 15 20:32 .
	drwxr-xr-x  3 login-cidadao www-data   4096 Dec 15 20:26 ..
	drwxr-xr-x  7 login-cidadao www-data   4096 Dec 15 20:46 app
	drwxr-xr-x  2 login-cidadao www-data   4096 Dec 15 20:26 batch
	drwxr-xr-x  2 login-cidadao www-data   4096 Dec 15 20:33 bin
	-rw-r--r--  1 login-cidadao www-data     42 Dec 15 20:26 .bowerrc
	-rw-r--r--  1 login-cidadao www-data   4320 Dec 15 20:26 composer.json
	-rw-r--r--  1 login-cidadao www-data 159040 Dec 15 20:26 composer.lock
	drwxr-xr-x  8 login-cidadao www-data   4096 Dec 15 20:26 .git
	-rw-r--r--  1 login-cidadao www-data    315 Dec 15 20:26 .gitignore
	-rwxr-xr-x  1 login-cidadao www-data   3991 Dec 15 20:26 install.sh
	-rw-r--r--  1 login-cidadao www-data  34521 Dec 15 20:26 LICENSE
	-rw-r--r--  1 login-cidadao www-data    332 Dec 15 20:26 lvp_mock.bat
	-rw-r--r--  1 login-cidadao www-data   3602 Dec 15 20:26 README.md
	drwxr-xr-x  4 login-cidadao www-data   4096 Dec 15 20:26 src
	-rw-r--r--  1 login-cidadao www-data    106 Dec 15 20:26 .travis.yml
	-rw-r--r--  1 login-cidadao www-data   1308 Dec 15 20:26 UPGRADE-2.2.md
	-rw-r--r--  1 login-cidadao www-data   1962 Dec 15 20:26 UPGRADE-2.3.md
	-rw-r--r--  1 login-cidadao www-data   8495 Dec 15 20:26 UPGRADE.md
	-rw-r--r--  1 login-cidadao www-data   2470 Dec 15 20:26 Vagrantfile
	drwxr-xr-x 38 login-cidadao www-data   4096 Dec 15 20:46 vendor
	drwxr-xr-x  5 login-cidadao www-data   4096 Dec 15 20:33 web

```

obs: se o apache estiver instalado ele pode dar conflito com o Nginx. Fique atento para a necessidade de desinstalá-lo. 

2. Dentro do diretório login-cidadao, mude para o branch `cleanup`. Este é o branch stable da aplicação. 

```
	$ sudo cd /var/www/login-cidadao
	$ sudo git checkout master
```

## Parametrizando a aplicação pré-instalação

3. Agora vamos parametrizar a aplicação. Será necessário criar um arquivo `app/config/parameters.yml` a partir deo template contido em `app/config/parameters.yml.vagrant`. 
 

```
	// Copiando o arquivo de template para o arquivo default
	$ sudo cp /var/www/login-cidadao/app/config/parameters.yml.dist /var/www/login-cidadao/app/config/parameters.yml
```

O conteúdo do arquivo é este: 

```
    database_driver:   pdo_mysql
    database_host:     127.0.0.1
    database_port:     ~
    database_name:     symfony
    database_user:     root
    database_password: ~

    memcached_host: 127.0.0.1
    memcached_port: 11211
    session_prefix: lc_sess_
    session_lifetime: 86400

    mailer_transport:  smtp
    mailer_host:       127.0.0.1
    mailer_user:       ~
    mailer_password:   ~

    locale:            en
    secret:            ThisTokenIsNotSoSecretChangeIt

    web_profiler_toolbar: true

    facebook_app_id: facebook_app_id_here
    facebook_app_secret: facebook_app_secret_here
    facebook_app_url: facebook_app_url
    facebook_app_this_url: "https://%site_domain%"
    facebook_app_scope: [email, user_about_me, user_birthday, user_location]

    twitter_app_key:    twitter_app_key_goes_here
    twitter_app_secret: twitter_app_secret_here

    http_proxy:
        type: HTTP
        host: host.domain.tld
        port: 3128
        auth: 'login:senha'

    site_domain: meu.rs.gov.br
    mailer_sender_mail: webmaster@example.com
    mailer_sender_name: webmaster
    recaptcha_public_key: ~
    recaptcha_private_key: ~
    # This must be compatible with strtotime()
    registration.cpf.empty_time: '+1 month'
    # Compatible with strtotime() but without + and - sign
    registration.email.unconfirmed_time: '1 day'
    brute_force_threshold: 4

    background_picture.author: 'Guilherme da Silva Donato'
    background_picture.link: 'http://www.flickr.com/photos/lordjedi/2295119043/'
    mailer_receiver_mail: ~

    user_profile_upload_dir: %kernel.root_dir%/../web/uploads/profile-pictures
    client_image_upload_dir: '%kernel.root_dir%/../web/uploads/client-pictures'
    tre_search_link: 'http://www.tse.jus.br/eleitor/servicos/titulo-e-local-de-votacao/consulta-por-nome'
    uri_root: /

    google_app_key: YOUR_API_KEY
    google_app_secret: YOUR_API_SECRET
    igp_ws_url: ~
    igp_username: ~
    igp_password: ~

    # Default Client
    oauth_default_client.uid: login-cidadao
    # Notification's categories IDs
    notifications_categories_alert.uid: login-cidadao-alert

    # Postal Code Search Link
    postalcode_search_link: 'http://m.correios.com.br/movel/buscaCep.do'

    # Default Country ISO 2
    default_country_iso2: BR

    # Should missing translations be logged in dev?
    log_translator: false

    # OpenID Connect
    # JWT Config
    #  - this is necessary since we can't access the oidc.issuer_url in the Compiler Pass
    jwt_iss: http://%site_domain%%base_path%
    # JWKS Config
    jwks_dir: %kernel.root_dir%/../app/config/jwks
    jwks_private_key_file: private.pem

    # Two Factor Auth
    two_factor_issuer: Login Cidadão
```

Fique atento aos seguintes pontos: 

3.1. Conectando a base de dados: 

Se você estiver usando 


3.2. Configurações memcached

3.3. Configurações de envio de email (smtp)

3.4. Configurações de token

    locale:            en
    secret:            ThisTokenIsNotSoSecretChangeIt


    locale:            pt_BR
    secret:            cALL-g83trinzafederuserall#set900@set8**

4. Instale os requisitos do Composer

    // Instalando Composer
    $ cd ~
    $ sudo apt-get install curl
    $ sudo curl -sS https://getcomposer.org/installer | php
    $ sudo mv composer.phar /usr/local/bin/composer

```
	$ cd /var/www/login-cidadao
	$ composer install
```

5. Cheque os requisitos do PHP 

Verifique se todas os requisitos estão sendo cumpridos antes de iniciar a instalação
    `php app/check.php`
 

## Configurando base de dados

Crie um usuário no postgres e depois uma base. Sugerimos usar o mesmo nome. 

```
  $ sudo -u postgres createuser -d login-cidadao
  $ createdb login-cidadao
```

7. Se a verificação for bem sucedida inicie a instalação
    `./install.sh`
 
## Arquivo de configuração `parameters.yml`
 
`locale:` -> substitua pelo seu locale (ex. pt_BR)
 
`secret:` -> substitua por uma longa cadeia de letras, números e símbolos
 
`site_domain:` -> substitua pelo seu domínio/subdomínio
 
`recaptcha_public_key:` e `recaptcha_private_key:` -> gere essas chaves em https://www.google.com/recaptcha/
 
`registration.cpf.empty_time:` e `registration.email.unconfirmed_time:` -> define quanto tempo deve ser dado para que o usuário confirme o CPF e o email, respectivamente
 
`brute_force_threshold:` -> quantas tentativas devem ser toleradas antes de considerar um ataque de força bruta
 

 
## Configurando Nginx
 
Copie o scritp padrão para o repositório de seu nginx

```
    $ sudo cp /var/www/login-cidadao/batch/nginx-login-cidadao.conf /etc/nginx/sites-available/login-cidadao.conf
```

Faça as alterações necessárias! 



  //Faça um link simbólico para o arquivo
  $ sudo ln -s /etc/nginx/sites-available/login-cidadao.conf /etc/nginx/sites-enabled/login-cidadao.conf
```

 
```
<VirtualHost *:80>
    ServerName sub.dominio.com.br
    ServerAdmin usuario@email
 
    DocumentRoot /var/www/login-cidadao/web
 
    <Directory / >
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>
 
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
 
* Em `DocumentRoot` é preciso apontar para o diretório `web`, neste exemplo o caminho completo é `/var/www/login-cidadao/web`.
 
* ServerName deve ser preenchido com o domínio (ex. dominio.com.br) ou subdomínio completo (ex. sub.dominio.com.br)
 
### [Primeiros passos pós-instalação](id:pos-instalacao)
 
1. Adicione os seguintes aliases ao seu arquivo `.bashrc`  
    `alias prod='php app/console --env=prod'`  
    `alias dev='php app/console --env=dev'`
 
2. Atualize o perfil do terminal  
    `source ~/.bashrc`
    * Obs.: Etapa desnecessária para logins futuros já que o .bashrc será executado no processo de login.
 
3. Processe e ative todos os assets  
    `prod assets:install`  
    `prod assetic:dump`
 
4. Dar poderes de super administrador para o primeiro usuário  
    `prod fos:user:promote <username> ROLE_SUPER_ADMIN`
 
    * Obs. 1: Substitua "<username>" pelo nome do usuário como mostrado na área superior direita da página, geralmente o que precede o '@' do email usado na hora da criação do usuário.
 
    * Obs. 2: Para confirmar visualmente o novo papel de super administrador faça um logout e depois um login. Junto ao nome deverá haver um campo 'impersonate', ver esse campo é a confirmação.
 
## Navegando em modo de desenvolvimento
 
Adicione `/app_dev.php` na URL.
 
## Alguns comandos práticos
 
Assume-se que as estapas 1 e 2 dos [Primeiros passos pós-instalação](#pos-instalacao) tenham sido cumpridos para seguir estes comandos.
 
* Limpar o cache  
    `prod cache:clear`  
    `dev cache:clear`  
    se não funcionar, em última instância use  
    `rm -rf app/cache/*`
* Criar ou atualizar os assets  
    `prod assets:install`  
    `prod assetic:dump`
* Criar ou atualizar os vendors (útil, por exemplo, quando se muda de branch)  
    `composer install`
 
## Adicionando serviços
 
em breve
 
## Integrando com o Mapas Culturais
 
```
'auth.provider' => 'OpauthLoginCidadao',
'auth.config' => array(
    'client_id' => 'minha_chave_publica',
    'client_secret' => 'minha_chave_privada',
    'auth_endpoint' => 'https://sub.dominio/oauth/v2/auth',
    'token_endpoint' => 'https://sub.dominio/oauth/v2/token',
    'user_info_endpoint' => 'https://sub.dominio/api/v1/person.json'
),
```
* Obs. 1: As chaves pública e privada são geradas na adição do serviço.  
* Obs. 2: substituir o domínio/subdomínio das três últimas linhas.
