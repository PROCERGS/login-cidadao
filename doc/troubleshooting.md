Problemas Comuns
================

Nesse capítulo abordaremos alguns problemas frequentes que são encontrados por
pessoas que instalam o Login Cidadão e como solucioná-los.

Alterei as configurações e nada mudou
-------------------------------------

Isso acontece, principalmente quando acessamos no modo Produção, pois o Symfony
faz um cache das configurações. Sempre que você alterar suas configurações, seja
em `parameters.yml`, `config.yml` ou outros arquivos de configuração é imperativo
que o cache seja limpo.

Para limpar o cache é recomendado usar o próprio comando do Symfony, entretanto,
em alguns casos, algumas alterações podem impedir seu correto funcionamento e
será necessário apagar, manualmente, as pastas de cache.

O comando do Symfony, recomendado, é:

    ./app/console cache:clear -e prod

Caso o comando não funcione corretamente você pode utilizar o comando a seguir
para limpar o cache do modo de produção e o do modo de desenvolvimento:

    rm -rf app/cache/{dev,prod}

Alterei um arquivo e nada mudou
-------------------------------

Assim como no problema anterior, isso ocorre com maior frequência quando é feito
acesso em modo de Produção. Quando um *asset* (CSS, JS, imagens...) é alterado,
é importante que sejam compilados novamente os *assets* através dos comandos do
Symfony, além de ser feita a limpeza do cache.

Para gerar os *assets*, você pode utilizar os comandos `assets:install` e
`assetic:dump`, entretanto recomendamos que você utilize o `lc:deploy` que
cuidará de outros aspectos da instalação:

    ./app/console lc:deploy

### Mas e em desenvolvimento?

Certamente é muitíssimo inconveniente ficar executando comandos a cada alteração,
pois mesmo que esses comandos sejam automáticos a cada vez que o arquivo é salvo
eles demoram alguns segundos para concluir o processamento e isso não lhe trará
uma boa experiência como desenvolvedor.

Desse modo, assumindo que você esteja desenvolvendo em ambiente *Linux*, é
recomendado que você instale os assets através de *symlinks* e que acesse o modo
de Desenvolvimento para que os *assets* compilados pelo componente *assetic*
sejam gerados em tempo de execução.

Para instalar os *assets* usando *symlinks* basta utilizar o comando apropriado:

    ./app/console assets:install --symlink

Para acessar o modo de Desenvolvimento basta utilizar `/app_dev.php` na frente
de suas URIs. Por exemplo: `https://seu.dominio/app_dev.php` ou
`https://seu.dominio/app_dev.php/login`. Entretanto para ter acesso seu IP/rede
deve estar listado no parâmetro `dev_allowed` do `parameters.yml`.

Os emails não estão sendo enviados
----------------------------------

Esse é um dos problemas mais frequentes quando uma nova instalação está sendo
configurada e, em 99.9% dos casos, o problema está na configuração do arquivo
`parameters.yml` ou do servidor de envio de emails em si. Algumas dicas que
podemos dar são:

### Configurações do `parameters.yml`

Verifique se informou o endereço correto de seu servidor SMTP no parâmetro
`mailer_host` bem como as credenciais em `mailer_user` e `mailer_password`.
Certifique-se, também, de limpar o cache do Symfony após essas alterações.

### Configurações do SMTP

Certifique-se de que o servidor está aceitando as conexões do Login Cidadão e
que está configurado corretamente para enviar emails. Caso esteja enviando para
servidores utilizando IPv6 você deve ter um PTR configurado corretamente ou as
suas mensagens serão recusadas. Esse problema é bastante frequente quando são
enviados emails a servidores do Google e há, inclusive,
[documentação sobre isso](https://support.google.com/mail/answer/81126?p=IPv6AuthError&visit_id=1-636253930059328048-1270189812&rd=1#authentication).

Caso tenha optado por enviar emails utilizando o *Gmail*, verifique se todos os
passos da [documentação do Symfony](http://symfony.com/doc/2.8/email/gmail.html)
foram seguidos, em especial a configuração de envio por "apps menos seguros",
conforme citado no fim do documento.

Alterações em entidades não funcionam
-------------------------------------

Para otimizar o processamento envolvendo bancos de dados, o *Doctrine* salva,
no *memcached*, um cache dos metadados gerados. Caso tenham sido feitas mudanças
em entidades do *Doctrine* ou quaisquer outras alterações no banco de dados e
isso esteja gerando erros, é possível que o cache do *Doctrine* no *memcached*
não esteja sendo limpo corretamente.

Normalmente esse cache é limpo quando executamos o comando `lc:deploy`, mas é
possível que, por motivos que desconhecemos, essa limpeza de cache não seja
eficaz, o que resulta em erros onde o *Doctrine* espera que o banco de dados
esteja de uma forma diferente da que ele se encontra.

Para sanar o problema você pode limpar o cache de metadados através do Symfony
utilizando o seguinte comando:

    ./app/console doctrine:cache:clear-metadata -e prod

Caso o problema persista, será necessário apagar todo o cache, o que fará com
que todas as sessões sejam perdidas, infelizmente. Isso pode ser feito
reiniciando o serviço `memcached` ou conectando-se a ele via `telnet` e
executando o comando `flush_all` (linhas começando com `>` representam os
comandos que você deverá digitar):

    > telnet <endereco.do.memcached> <porta_do_memcached>
    Trying [ip do memcached]...
    Connected to [ip do memcached].
    Escape character is '^]'.
    > flush_all
    OK
    > quit
    Connection closed by foreign host.

Erro no fluxo de Autorização (`redirect_uri_mismatch`)
------------------------------------------------------

    {
        error: "redirect_uri_mismatch",
        error_description: "The redirect URI provided is missing or does not match",
        error_uri: "#section-3.1.2"
    }

Caso você encontre o erro identificado como `redirect_uri_mismatch` durante o
processo de autorização de algum serviço, isso significa que a requisição de
autorização está sendo gerada com uma URI errada ou não cadastrada para o
serviço em questão. Acesse o menu do Desenvolvedor ou do Administrador e ajuste
as configurações do serviço para incluir a URI desejada.
