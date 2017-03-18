Instalação em Debian
====================

A seguir demonstraremos os passos para instalação do Login Cidadão em ambiente
*Debian* utilizando *nginx*, *Postgres*, *Postfix* e *Let's Encrypt*. Recomendamos
que você não se baseie apenas nos passos apresentados aqui no caso de um servidor
de produção. Para servidores de produção ou para ambientes diferentes do exposto,
consulte as [instruções completas](docs.md) com explicações mais amplas.

Antes de começar
----------------

### Servidor

Para essa instalação estamos assumindo que o servidor utilizado foi recém
instalado e não possui outros softwares em execução, como, por exemplo, uma
máquina virtual.

### Domínio e Conectividade

Certifique-se de que o hostname desejado já está apontando para o(s) endereço(s)
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
 - Um certificado HTTPS válido para o domínio informado;
 - Um *cronjob* que fará a renovação automática do certificado obtido;
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
