Instalação em Debian
====================

A seguir demonstraremos os passos para instalação do Login Cidadão em ambiente
*Debian* utilizando *nginx*, *Postgres*, *Postfix* e *Let's Encrypt*. Recomendamos
que você não se baseie apenas nos passos apresentados aqui no caso de um servidor
de produção. Para servidores de produção ou para ambientes diferentes do exposto,
consulte as [instruções completas](docs.md) com explicações mais amplas.

Antes de começar
----------------

Como iremos configurar um servidor de envio de emails é importante que você
informe-se das políticas de envio de email de seu provedor de infraestrutura
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

Você deve **copiar os três arquivos** para a pasta na qual deseja instalar o
Login Cidadão e digitar o seguinte comando:

    chmod +x install ; sudo ./install
