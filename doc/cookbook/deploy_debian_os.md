Instalação em Debian
====================

A seguir demonstraremos os passos para instalação do Login Cidadão em ambiente
*Debian* utilizando *nginx*, *Postgres*, *Postfix* e *Let's Encrypt*. Recomendamos
que você não se baseie apenas nos passos apresentados aqui no caso de um servidor
de produção. Para servidores de produção ou para ambientes diferentes do exposto,
consulte as [instruções completas](index.md), com explicações mais amplas, ou,
ao final do presente documento, no item [Instalação Manual](#manual) onde os
comandos utilizados pelo script de instalação são explicados.

Antes de começar
----------------

### Servidor

Para essa instalação estamos assumindo que o servidor utilizado foi recém
instalado e não possui outros softwares em execução, como, por exemplo, uma
máquina virtual.

### Domínio e Conectividade

Certifique-se de que o hostname desejado (https://suaurl.gov.br) já está apontando para o(s) endereço(s)
IP de seu servidor e que esses endereços sejam acessíveis pela Internet. Isso é
fundamental para que o script de instalação consiga obter o certificado HTTPS
necessário.

### Envio de emails

Como iremos configurar um servidor de envio de emails é importante que você
informe-se sobre políticas de envio de email de seu provedor de infraestrutura
para saber se sequer é permitido o envio de emails ou se há algum tipo de
restrição técnica como o bloqueio da porta 25.

Além disso, para enviar emails corretamente utilizando IPv6 é fundamental que
seu IPv6 possua um registro PTR (DNS reverso) válido. Por exemplo, o endereço
IPv6 `a:b:c:d:e:f:g:h` deve apontar para `seu.hostname` que, por sua vez, deve
apontar para `a:b:c:d:e:f:g:h`. Em caso de dúvidas procure seu provedor de
infraestrutura para saber como configurar isso.

Script de instalação
--------------------

O script de instalação bem como os templates de arquivos necessários são:

 - [`doc/cookbook/scripts/install`](scripts/install)
 - [`doc/cookbook/templates/nginx.conf`](templates/nginx.conf)
 - [`doc/cookbook/templates/php-fpm.conf`](templates/php-fpm.conf)

Você deve **copiar os três arquivos** para uma pasta qualquer e digitar
o seguinte comando:

    chmod +x install ; sudo ./install

Processo de Instalação
----------------------

Assim que executado, o script irá solicitar o domínio que será usado pelo Login
Cidadão. Assim que você informar seu hostname e pressionar `[ENTER]` será iniciada
a execução e sua ação só será requerida nos passos **2** e **3**.

Os passos serão:

0. Instalação do comando `sudo`, necessário para as etapas seguintes;
1. Instalação de dependências como `nginx`, `postgres`, `certbot`, `php5`...
2. Obtenção do certificado HTTPS usando *Let's Encrypt*: nesse momento será
   solicitado um endereço de email e a aceitação dos termos de uso do serviço;
3. Instalação e Configuração do *Postfix*: aqui você precisará informar o tipo
   de servidor que deseja (**Internet Server**) bem como o hostname que será
   usado para o envio de emails;
4. Configuração do *PostgreSQL*;
5. Configuração do *nginx* e do *php-fpm*;
6. Download do Login Cidadão usando *git*;
7. Configuração mínima de parâmetros do Login Cidadão, instalação de dependências
   PHP, criação do banco de dados e geração dos assets (CSS e JavaScript).

Pós-instalação
--------------

Ao fim da execução do script, assumindo que nada de errado ocorra, você deverá
ter uma instalação básica do Login Cidadão com:

 - Um arquivo output.log contendo o resultado da execução;
 - Um certificado HTTPS válido para o domínio informado com renovação automática;
 - Um servidor *Postfix* para envio de emails;
 - Um servidor *PostgreSQL* com o banco de dados do Login Cidadão configurado;
 - Um servidor HTTPS *nginx* atuando como proxy reverso;
 - Um servidor PHP-FPM que executará Login Cidadão;
 - O Login Cidadão na pasta `/home/logincidadao/login-cidadao`;
 - Login Cidadão acessível via **https://hostname.informado.na.instalcao**.

Seus próximos passos são:

1. Acessar sua instância do Login Cidadão e criar sua conta de usuário;
2. Promover seu usuário a Super Admin [conforme instruções](admin_user.md);
3. Gerar um **`secret`** para sua instância e substituir o utilizado por
   padrão em `/home/logincidadao/login-cidadao/app/config/parameters.yml`;
4. Configurar seu arquivo `parameters.yml` para ajustar os detalhes de sua
   instância do Login Cidadão, tais como chaves de acesso do reCAPTCHA,
   Facebook, Google e Twitter bem como outras configurações particulares de
   sua instância.

Troubleshooting
---------------

O script de instalação é bastante simples e procura não utilizar nenhum recurso
muito avançado de programação bash para ser o mais legível possível em caso de
problemas, consistindo basicamente de uma série de comandos sequenciais, sem a
utilização de condicionais.

Dessa forma, em caso de erros na execução do script, o processo de instalação
será interrompido e você poderá verificar o output do script na sua tela ou no
arquivo `output.log`, dependendo do tipo de erro que ocorrer.

Tendo solucionado o problema, recomendamos que continue a instalação de forma
manual, executando um-a-um os comandos do script pois algumas partes dele não
são idempotentes e podem gerar novos erros se executadas duas vezes.

<a name="manual"></a> Instalação Manual
-----------------

Caso queira realizar uma instalação manual, você pode seguir as instruções a
seguir para replicar o que o script de instalação mencionado anteriormente faz.

Os comandos a seguir devem ser executados como usuário **root**.

O primeiro passo é instalar sudo pois será necessário nas etapas seguintes

    apt-get install -y sudo

Em seguida instalam-se os repositórios que serão usados:

    # Let's Encrypt
    echo "deb http://ftp.debian.org/debian jessie-backports main" > /etc/apt/sources.list.d/backports.list

    # nginx
    curl -O http://nginx.org/keys/nginx_signing.key \
      && apt-key add nginx_signing.key \
      && echo "deb http://nginx.org/packages/debian/ jessie nginx" > /etc/apt/sources.list.d/nginx.list \
      && echo "deb-src http://nginx.org/packages/debian/ jessie nginx" >> /etc/apt/sources.list.d/nginx.list

    # postgresql
    curl -O https://www.postgresql.org/media/keys/ACCC4CF8.asc \
     && apt-key add ACCC4CF8.asc \
     && echo "deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main" > /etc/apt/sources.list.d/postgresql.list

    # node.js
    curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -

    apt-get update

Agora instalaremos as dependências:

    # node.js, git, memcached e gettext-base
    apt-get install -y curl gettext-base git nodejs memcached

    # nginx
    apt-get install -y nginx

    # PostgreSQL
    apt-get install -y postgresql postgresql-client

    # PHP 5 e suas extensões
    apt-get install -y php5 php5-cli php5-fpm php5-curl php5-intl php5-pgsql php5-memcache

    # Let's Encrypt
    apt-get install -y certbot -t jessie-backports

    # Composer
    curl -o composer-setup.php https://getcomposer.org/installer
    php composer-setup.php --quiet && rm composer-setup.php && mv composer.phar /usr/local/bin/composer

Instaladas as dependências iniciais, vamos aproveitar que não há servidor HTTP
configurado e obteremos o certificado HTTPS de forma mais prática, utilizando um
servidor standalone do Let's Encrypt:

    # Devemos nos certificar de que o nginx não está em execução
    service nginx stop

    # certbot é o comando usado para obter um certificado do Let's Encrypt
    # Você deve informar seu domínio no comando a seguir
    certbot certonly --standalone -d SEU.DOMINIO.AQUI

    # Voltamos a ligar o nginx
    service nginx start

Os certificados do Let's Encrypt tem uma curta duração, sendo assim é importante
que seja criado uma entrada *crontab* para realizar a renovação automática do
certificado obtido no passo anterior:

    (crontab -l 2>/dev/null; echo '0 14 * * * certbot renew --post-hook "systemctl reload nginx"') | crontab -

Nos passos a seguir, seguiremos com a configuração do envio de emails utilizando
seu próprio servidor. Caso você pretenda utilizar um serviço de envio de terceiros
basta pular essa etapa e alterar o arquivo `app/config/parameters.yml` para
refletir a configuração desejada.

**Importante**: durante a instalação do *Postfix* serão solicitadas algumas
informações. Você deve configurar o seu servidor como **`Internet Server`**
e digitar o seu domínio nas configurações seguintes, quando solicitado.

    apt-get install -y postfix

    # Informe seu domínio nos comandos a seguir
    postconf -e "myorigin = SEU.DOMINIO.AQUI"
    postconf -e "myhostname=$(hostname)"
    postconf -e "relay_domains = SEU.DOMINIO.AQUI"
    postfix reload

Nosso próximo passo será configurar o PostgreSQL para atuar como servidor de
banco de dados do Login Cidadão. Para facilitar a configuração, utilizaremos a
autenticação do prórpio sistema operacional, bastando, para isso, criar o usuário
`logincidadao`:

    useradd -m logincidadao
    usermod -s /bin/bash logincidadao

Em seguida, realizamos a configuração do PostgreSQL em si:

    service postgresql start
    su - postgres -c "createuser -S -D -R logincidadao"
    su - postgres -c "createdb logincidadao"

Nesse momento já temos um servidor de banco de dados, um servidor para envio de
emails e um certificado HTTPS. Vamos continuar a configurar o *nginx* e o
*php-fpm* para que possamos unir todos esses componentes através do Login Cidadão.

Para termos uma instalação HTTPS segura devemos gerar um arquivo *dhparam.pem*. A
geração desse arquivo é bastante demorada, portanto tenha paciência. Aproveite e
faça uma pausa, beba água, prepare um café ou chá...

    openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048

**Nota:** aqui estamos utilizando a quantidade de bits de *2048*, que é o padrão
e o que recomendamos. Caso não tenha paciência para aguardar a geração você pode
reduzir o tamanho para 1024, entretanto recomenda-se que você pesquise sobre essa
escolha.

O próximo passo será copiar o arquivo [nginx.conf](templates/nginx.conf) para
`/etc/nginx/conf.d/logincidadao.conf` e substituir `${DOMAIN}` por seu domínio.
Você deverá encontrar e substituir 5 ocorrências de `${DOMAIN}` nesse arquivo.

A seguir, copie o arquivo [php-fpm.conf](templates/php-fpm.conf) para
`/etc/php5/fpm/pool.d/logincidadao.conf`. Nesse arquivo não será necessário
fazer modificações.

Por fim, reiniciaremos o *nginx* e o *php-fpm* para utilizar as configurações
criadas:

    service php5-fpm restart
    service nginx configtest && service nginx restart

Agora que instalamos os serviços que suportarão o funcionamento do Login Cidadão,
podemos iniciar a instalação e configuração do Login Cidadão em si.

A partir desse momento, executaremos os comandos usando o usuário `logincidadao`.

    sudo su logincidadao

Iniciaremos obtendo o código do Login Cidadão e instalando as bibliotecas PHP
necessárias para a execução do Login Cidadão. A instalação será feita na *home*
do usuário `logincidadao`, especificamente no diretório
`/home/logincidadao/login-cidadao`:

    git clone https://github.com/redelivre/login-cidadao.git /home/logincidadao/login-cidadao
    cd /home/logincidadao/login-cidadao
    composer install

Ao fim da instalação de dependências, você poderá configurar, interativamente,
os parâmetros do Login Cidadão. As opções serão salvas no arquivo
`app/config/parameters.yml` (caminho relativo ao diretório atual) e você pode
obter mais informações sobre ele [aqui](../parameters.md).

Para que a instalação que estamos realizando agora funcione corretamente você
deverá informar alguns valores específicos, quando solicitados. São eles:

  * `database_host`: `~`
  * `database_port`: `~`
  * `database_password`: `~`
  * `site_domain`: `SEU.DOMINIO.AQUI`

Para finalizar a instalação, devemos gerar os assets (JavaScripts e arquivos CSS),
limpar o cache de produção, gerar e popular a estrutura de banco de dados:

    rm -rf app/cache/prod
    ./app/console doctrine:schema:update --force
    ./app/console lc:database:populate batch/
    ./app/console assets:install
    rm -rf app/cache/prod
    ./app/console assetic:dump -e prod

**Pronto!** Agora basta acessar https://SEU.DOMINIO.AQUI, criar sua conta e seguir
as instruções para tornar-se um Super Admin [aqui](admin_user.md).
