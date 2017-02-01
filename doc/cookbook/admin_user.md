Definição de usuário Super Admin
================================

Após instalar sua instância do Login Cidadão, você já pode acessá-la e criar sua
conta de usuário. Diferentemente de alguns tipos de software onde o primeiro
usuário a se cadastrar é considerado administrador ou onde há um usuário/senha
padrão para essa função, no Login Cidadão é necessário que alguém, devidamente
autorizado e com acesso ao console do servidor, execute um comando para definir
um usuário como *Super Administrador*.

Dessa forma impede-se que seja possível promover um usuário comum de forma remota
além de evitar que algum administrador de sistemas desavisado tenha em execução
uma instância do Login Cidadão onde o usuário administrador possui uma senha
padrão.

O comando para promover um usuário qualquer a *Super Admin* é bem simples e para
executá-lo basta saber o email desse usuário.

No exemplo a seguir vamos supor que o email seja `fulano@detal.com`

    $ ./app/console lc:user:promote fulano@detal.com --super

**Lembrete**: Após realizar a promoção do usuário, é importante que essa pessoa
saia e faça login novamente para que as permissões sejam atualizadas na sessão.
