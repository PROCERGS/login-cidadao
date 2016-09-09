Para a configuração dos serviços descritos abaixo é necessário criar uma imagem (png/jpg) representativa da aplicação para o facebook, twitter e google. Aconselho criá-la com tamanho 1024x1024 pois é o tamanho obrigatório no facebook e poderá ser usada nos outros serviços.

## Facebook

O primeiro passo para criação de um novo aplicativo e ter acesso as chaves utilizadas pelo LC para integrar com o facebook é acessar o endereço [https://developers.facebook.com/](https://developers.facebook.com/) e informar alguns detalhes como descrito abaixo.

No primeiro formulário as informações abaixo são obrigatórias:

* Display Name
* Namespace
* App Domains
* Contact Email

A seguir é necessário adicionar a plataforma web e configurar o a url do site onde será usado o login.

Na aba "App Details" é obrigatório preencher:

* Short Description
* Publisher
* Category
* Tagline
* Long Description
* Privacy Policy URL
* User Support Email

Na área gráfica, é obrigatório inserir um ícone e 4 screenshots de exemplo da aplicação:

* App Icon
* Small App Icon

Por último, será necessário incluir as permissões "user_location" e "user_birthday", com isso algumas outras informações serão necessárias mas o Facebook irá destacar o que é obrigatório preencher.

## Twitter

O endereço para ter acesso as chaves e construção do aplicativo no twitter é [https://apps.twitter.com/](https://apps.twitter.com/).

O cadastro não necessita de nenhuma configuração mais avançada.

## Google

É importante não confundir login utilizando google sign-in, com o login através do google plus, esse último não está mais sendo aconselhado a ser usado e por isso não vamos utilizá-lo.

Ao acessar o endereço [https://console.developers.google.com/apis/credentials](https://console.developers.google.com/apis/credentials) você vai ter acesso ao console do Google e será necessário criar um projeto.

A partir do projeto você terá que escolher a criação de uma credencial que deve ser do tipo oAuth.

Nas configurações você deverá informas as seguintes URL (Authorized redirect URIs):

* https://LC_DOMAIN_URL/connect/service/google
* https://LC_DOMAIN_URL/login/check-google

## Recaptcha

O endereço para ter acesso as chaves é [https://www.google.com/recaptcha/](https://www.google.com/recaptcha/)

O processo é bem simples e os detalhes a serem preenchidos são auto explicativos.
