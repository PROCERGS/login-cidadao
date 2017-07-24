Procedimentos de Manutenção
===========================

Atualizando o Login Cidadão
---------------------------

De forma a ter uma experiência cada vez melhor e mais estável, é altamente
recomendável que se atualize periodicamente sua instalação do Login Cidadão e para
lhe ajudar nessa tarefa, esse capítulo irá enumerar alguns pontos importantes que
devem ser observados para evitar problemas no seu ambiente de produção.

### Antes de atualizar

Antes de você atualizar o software em execução em produção, é importante verificar
seu funcionamento em um ambiente isolado pois uma nova versão pode trazer algumas
mudanças que exigem alguma ação de sua parte.

### Obtendo o código atualizado

Para atualizar o código instalado, basta utilizar o `git` para fazer download da
versão mais recente do código. Para isso, você deve acessar o diretório raiz de
sua instalação e digitar o seguinte comando:

    $ git pull

Após o download do código mais recente, você poderá proceder e realizar os testes
necessários para certificar-se do correto funcionamento da nova versão em seu
ambiente.

### Verificando Dependências

Antes de seguir com as demais verificações é fundamental que você esteja com todas
as dependências necessárias instaladas. Para isso utilize o `composer` executando
seu comando `install` que será responsável por atualizar as dependências conforme
exigidas pelo arquivo `composer.lock`.

    $ composer install

### Banco de Dados

O principal ponto a ser verificado em um momento de atualização é se houve mudança
da estrutura do banco de dados. Essa verificação pode ser realizada durante o
comando de deploy do Login Cidadão, onde um comando do `Doctrine` é executado
para verificar discrepâncias no banco de dados.

    $ ./app/console lc:deploy

Esse comando exibirá as queries SQL/DDL que devem ser executadas para deixar o
banco de dados com a estrutura correspondente à versão mais recente. Caso queira
executar os comandos exibidos, basta responder de acordo com o menu interativo
que será exibido.

Caso haja a criação de colunas `NOT NULL`, ou quaisquer outras constraints que não
sejam atendidas por sua base de dados atual, a execução dos comandos irá falhar e
você terá que providenciar as migrações necessárias correspondentes ao seu ambiente.

### Customizações

Caso sua instalação possua alterações em relação a versão da *comunidade*, não
esqueça de verificar se elas continuam funcionando corretamente após atualizar
para a versão mais recente.

### Limpeza de Cache

Principalmente quando há uma alteração no banco de dados ou, ainda, quando são
alterados parâmetros dos arquivos dentro da pasta `app/config` é importante que
os caches sejam limpos.

#### Cache de Aplicação

O cache de aplicação geralmente é limpo na execução do comando de deploy. Caso
ocorra algum problema, você pode fazer a limpeza manualmente, bastando remover
as pastas `app/cache/dev` e `app/cache/prod`

    $ rm -rf app/cache{dev,prod}

#### Cache no Memcached

Caso você tenha problemas relacionados a Entidades do Doctrine ou outras
dificuldades relacionadas a banco de dados após uma atualização, é provável que
a limpeza do cache de metadados do Doctrine tenha falhado. Esse é um problema
frequente em sistemas CentOS e RHEL 7 e a única forma conhecida de contorná-lo
é limpando totalmente o cache do `memcached` de forma manual.

**Importante**: ao executar este procedimento você fará com que todos usuários
tenham que autenticar-se novamente, uma vez que seus dados de sessão serão
invalidados.

    $ telnet <endereço do memcached> <porta do memcached>
    flush_all
    quit
    $ 

Caso prefira, você pode simplesmente reiniciar o processo do memcached para obter
o mesmo efeito. Para saber como reiniciar um serviço consulte a documentação
específica do `memcached` para seu sistema operacional.

Alterando Configurações
-----------------------

O Login Cidadão possui diversos parâmetros que permitem ajustar seu funcionamento
de acordo com as necessidades de sua organização. Cada um dos parâmetros está
brevemente descrito no arquivo `app/config/parameters.yml.dist` e você também
encontra uma descrição mais detalhada no capítulo sobre [parâmetros do Login
Cidadão](parameters.md).
