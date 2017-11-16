Usuário Super Admin
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

Para saber mais sobre os comandos do Symfony, você pode consultar a
[Introdução à CLI do Symfony](../commands.md).

**Lembrete**: Após realizar a promoção do usuário, é importante que essa pessoa
saia e faça login novamente para que as permissões sejam atualizadas na sessão.


Gerenciamento de Usuários
=========================

Durante a operação de sua instância do Login Cidadão é normal que você precise
realizar algumas verificações em contas de usuário além de conceder permissões
para desenvolvedores ou, até mesmo, *beta testers*. Nesse capítulo conheceremos
as funcionalidades administrativas que facilitarão essa tarefa.

Visualização de Informações
---------------------------

Para verificar informações de usuários como, por exemplo, data de cadastro,
data da verificação do endereço de email ou quais os dados pessoais de um usuário
você pode acessar a opção `Gerenciar Pessoas`, na `Área do Administrador`.

Nessa tela será possível pesquisar usuários por email ou CPF, fazendo com que
seja exibida uma breve lista de usuários encontrados que possuem os termos
pesquisados. Ao identificar o usuário correto, basta acionar o botão de edição
para que as informações sejam exibidas.

Além de informações pessoais como nome, email, telefone e data de nascimento, é
possível verificar metadados tais como a data de criação da conta, a data da
verificação do email, o último login e o CPF cadastrado.

Também serão listados os serviços autorizados pelo usuário bem como a data de sua
última autorização.

**Nota:** o acesso à informações do usuário gera um "*Registro de Atividade*"
visível ao usuário ao clicar na opção "*+*", na tela inicial, informando que
alguém acessou seus dados. Essa informação só está visível quando se consulta a
lista mais detalhada para evitar que usuários leigos fiquem confusos.

**Nota:** é possível que administradores menos privilegiados tenham acesso
limitado ao CPF do usuário. Quando isso ocorre o número do CPF não é exibido,
apenas será informado se aquele usuário preencheu ou não o campo de CPF.
A permissão que controla esse tipo de acesso é `ROLE_VIEW_USERS_CPF` e, por
padrão, só está disponível para Super Admins. Você pode concedê-la para usuários
específicos na própria tela do gerenciamento de pessoas ou através do comando
`lc:user:promote`.

    ./app/console lc:user:promote fulano@detal.com ROLE_VIEW_USERS_CPF

Alterando informações de usuários
---------------------------------

No Login Cidadão adotamos a filosofia de que apenas o usuário dono da conta pode
editar suas informações, portanto não há uma interface administrativa que permita
isso. A única coisa que pode ser alterada por um administrador são as permissões
de um usuário por meio do campo "*Perfis*" no painel "*Segurança*" da tela de
visualização de informações de usuários.

Uma forma de se "contornar" essa restrição é utilizando-se de um Super Admin para
acessar a conta do usuário desejado e fazer as alterações, entretanto esse tipo
de ação é desencorajado, uma vez que pode passar uma sensação de insegurança ao
usuário.

Acessando a conta de um usuário
-------------------------------

O Login Cidadão permite que os Super Administradores consigam se passar por
outros usuários. Para fazer isso, basta digitar o *username* do usuário desejado
na barra superior, no campo "*Impersonate*" e apertar \[ENTER\]. Para voltar ao
normal, basta utilizar o botão "*Back to Normal*" que aparecerá no lugar do campo
"*Impersonate*".

Ao passar-se por outro usuário o Super Administrador tem acesso total à conta
desse usuário, entretanto essa ação fica registrada e visível para o "alvo" e,
além disso, o Super Administrador deverá preencher um relatório quando voltar
para sua conta normal justificando o acesso e esse relatório poderá ser enviado
ao usuário caso o acesso seja questionado.

Esses relatórios não são disponibilizados automaticamente pois podem conter
informações internas sensíveis ou que o usuário não vá compreender, então se
optou por disponibilizar sob demanda para evitar confusão por parte de usuários
leigos.
