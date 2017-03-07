Gerenciamento de Usuários
=========================

Durante a operação de sua instância do Login Cidadão é normal que você precise
realizar algumas verificações em contas de usuário além de conceder permissões
para desenvolvedores ou, até mesmo, *beta testers*. Nesse capítulo conheceremos
as funcionalidades administrativas que facilitarão essa tarefa.

Visualização de Informações
---------------------------

Para verificar informações de usuários como, por exemplo, data de cadastrou,
data da verificação do endereço de email ou quais os dados pessoais de um usuário
você pode acessar a opção `Gerenciar Pessoas`, na `Área do Administrador`.

Nessa tela será possível pesquisar usuários por email ou CPF fazendo com que
seja exibida uma breve lista de usuários encontrados que possuem os termos
pesquisados. Ao identificar o usuário correto, basta acionar o botão de edição
para que as informações sejam exibidas.

Além de informações pessoais como nome, email, telefone e data de nascimento, é
possível verificar metadados tais como a data de criação da conta, a data da
verificação do email, o último login e o CPF cadastrado.

Também serão listados os serviços autorizados pelo usuário bem como a data de sua
última autorização.

**Nota:** é possível que administradores menos privilegiados tenham acesso
limitado ao CPF do usuário. Quando isso ocorre o número do CPF não é exibido,
apenas será informado se aquele usuário preencheu ou não o campo de CPF.
A permissão que controla esse tipo de acesso é `ROLE_VIEW_USERS_CPF`.
