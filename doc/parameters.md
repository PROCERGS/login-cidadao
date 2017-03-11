Parâmetros do Login Cidadão
===========================

Nesse capítulo serão apresentadas as características e funcionalidades de cada um
dos parâmetros disponíveis no Login Cidadão.

Parâmetros de Banco de Dados
----------------------------

Os parâmetros para configuração da conexão com bancos de dados são:

  - **database_driver**: determina o tipo de banco de dados. São exemplos de valores
  válidos: `pdo_pgsql` e `pdo_mysql`;
  - **database_host**: endereço (host ou IP) do banco de dados. Caso você vá
  conectar-se via sockets, utilize o valor `~`;
  - **database_port**: porta usada para conexão com o banco de dados. Para usar
  sockets preencha com `~`;
  - **database_name**: nome/schema do banco de dados;
  - **database_user**: usuário/login para autenticar-se no banco de dados;
  - **database_password**: senha do usuário informado em `database_user` ou `~`
  no caso de conexão via socket;
  
Permissões de acesso por IP
---------------------------

  - **trusted_proxies**: IPs dos seus load balancers ou outro tipo de proxy
  reverso. Veja a [documentação do Symfony](http://symfony.com/doc/current/cookbook/request/load_balancer_reverse_proxy.html)
  para maiores informações;
  
  - **dev_allowed**: IPs autorizados a acessar em modo desenvolvimento
  (`/app_dev.php`);
  
  - **allowed_monitors**: IPs autorizados a acessar os endpoints de monitoria;

Memcached e Sessões
-------------------

  - **memcached_host**: endereço do servidor `memcached`;
  - **memcached_port**: porta do servidor `memcached`;
  - **memcached_prefix**: prefixo das chaves do `memcached`;

  - **session_prefix**: prefixo para as chaves relativas às sessões;
  - **session_lifetime**: tempo de vida das sessões;
  - **session.remember_me.name**: nome do cookie onde a funcionalidade "Remember
  me" é armazenada;

Envio de Emails
---------------

  - **mailer_transport**: verifique as configurações na [documentação oficial](http://symfony.com/doc/2.8/cookbook/email/email.html)
  - **mailer_host**: verifique as configurações na [documentação oficial](http://symfony.com/doc/2.8/cookbook/email/email.html)
  - **mailer_user**: verifique as configurações na [documentação oficial](http://symfony.com/doc/2.8/cookbook/email/email.html)
  - **mailer_password**: verifique as configurações na [documentação oficial](http://symfony.com/doc/2.8/cookbook/email/email.html)

  - **mailer_sender_mail**: endereço de email do remetente de emails. Geralmente
  será algo como nao-responda@seu.dominio.tld;
  - **mailer_sender_name**: nome do remetente de emails. Exemplo: 'Login Cidadão';

  - **mailer_receiver_mail**: endereço para onde todos emails serão enviados quando
  se estiver usando o modo de desenvolvimento. Mais informações na
  [documentação oficial](http://symfony.com/doc/2.8/email/dev_environment.html);

  - **contact_form.email**: email para onde as mensagens do formulário de contato
  serão enviadas;
  - **logs.email**: email para onde os logs de erros serão enviados;

Idioma
------

  - **locale**: esse parâmetro configura o idioma padrão que será utilizado;

  - **default_country_iso2**: sigla no padrão ISO2 do país padrão a ser
  considerado;

  - **log_translator**: configura se traduções não encontradas devem ser
  registradas no log no modo desenvolvimento;

Segurança
---------

  - **secret**: string secreta utilizada para funções criptográficas. Para mais
  informações verifique a [documentação oficial](http://symfony.com/doc/current/reference/configuration/framework.html#secret)

  - **brute_force_threshold**: determina a quantidade de vezes que um usuário pode
  errar sua senha antes de ser solicitado que responda um CAPTCHA;

  - **contact_form.captcha**: determina se os usuários deverão ser submetidos a um
  desafio de CAPTCHA no formulário de contato;

  - **warn_untrusted**: esse parâmetro determina se seus usuários deverão ser
  alertados ao tentar autorizar um serviço pertencente a uma organização não
  confiada por você;

  - **default_password_encoder**: define o algoritmo de codificação de senhas que
  será usado por padrão. Os encoders disponíveis podem ser configurados no arquivo
  `security.yml`;

  - **revalidate_sector_identifier_uri_on_auth**: essa opção permite que o
  `sector_identifier_uri` seja verificado novamente a cada tentativa de autorização.
  Observe que essa opção pode provocar alto uso de I/O, portanto use-a com cuidado;

  - **check_pathwell_topologies**: configura se as senhas serão testadas para
  detectar [PathWell Topologies](https://blog.korelogic.com/blog/2014/04/04/pathwell_topologies);

Modo de Desenvolvimento
-----------------------

  - **web_profiler_toolbar**: determina se a dev toolbar deve ser exibida no modo de
  desenvolvimento;

Login com Serviços de Terceiros
-------------------------------

  - **facebook_app_id**: ID da sua instância do Login Cidadão fornecido pelo
  Facebook para uso no login via OAuth 2.0 (`third_party_login`);
  - **facebook_app_secret**: secret da sua instância do Login Cidadão fornecido
  pelo Facebook para uso no login via OAuth 2.0 (`third_party_login`);

  - **twitter_app_key**: ID da sua instância do Login Cidadão fornecido pelo
  Twitter para uso no login via OAuth 2.0 (`third_party_login`);
  - **twitter_app_secret**: secret da sua instância do Login Cidadão fornecido
  pelo Twitter para uso no login via OAuth 2.0 (`third_party_login`);

  - **google_app_key**: ID da sua instância do Login Cidadão fornecido pelo Google
  para uso no login via OAuth 2.0 (`third_party_login`);
  - **google_app_secret**: secret da sua instância do Login Cidadão fornecido pelo
  Google para uso no login via OAuth 2.0 (`third_party_login`);

  - **third_party_login**: esse parâmetro configura quais serviços de terceiros
  podem ser usados para que seus usuários se autentiquem na sua instância do
  Login Cidadão. O valor deve ser um map com as chaves `facebook`, `twitter` ou
  `google` e valores booleanos;

reCAPTCHA:
----------

  - **recaptcha_public_key**: ID da sua instância do Login Cidadão fornecida pelo
  reCAPTCHA. Fundamental para o funcionamento dos mecanismos de CAPTCHA;
  - **recaptcha_private_key**: secret da sua instância do Login Cidadão fornecida
  pelo reCAPTCHA. Fundamental para o funcionamento dos mecanismos de CAPTCHA;

Configurações de Ambiente
-------------------------

  - **site_domain**: domínio oficial da sua instância. Esse domínio também será
  usado para exibição em aplicativos geradores de One Time Passwords (OTP) quando
  Autenticação de 2 Fatores está ativa;

  - **user_profile_upload_dir**: diretório para onde as fotos de perfil dos
  usuários serão enviadas;
  - **client_image_upload_dir**: diretório para onde as fotos de RPs serão
  enviadas;

  - **uri_root**: prefixo adicionado às URIs públicas das imagens enviadas;

  - **jwks_dir**: determina onde seu JSON Web Key Set (JWKS) será armazenado;
  - **jwks_private_key_file**: nome da sua chave privada do JWKS;

  - **two_factor_issuer**: nome da sua instância do Login Cidadão que será
  exibido em aplicativos geradores de OTP;

RP-Initiated Logout
-------------------

  - **rp_initiated_logout.logout.always_get_consent**: determina se o usuário
  deverá sempre consentir em ser deslogado por um RP;

  - **rp_initiated_logout.redirect.always_get_consent**: determina se o usuário
  deverá sempre consentir em ser redirecionado após um logout iniciado por um RP;

Formulário Proativo
-------------------

  - **pre_authorization.complete_information_task.skip_if_authorized**: somente
  solicita que o usuário preencha informações faltantes caso seja um novo pedido
  de autorização.

Regras de Negócio
-----------------

  - **require_email_validation**: condiciona o uso do Login Cidadão à confirmação
  do endereço de email cadastrado;
