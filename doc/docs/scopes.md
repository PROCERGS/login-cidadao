Escopos
=======

Esta página descreverá o que cada escopo faz. A seguir está a lista de todos os escopos disponíveis:

 * [ public_profile ](#public_profile)
 * [ full_name ](#full_name)
 * [ cpf ](#cpf)
 * [ birthdate ](#birthdate)
 * [ email ](#email)
 * [ city ](#city)
 * [ country ](#country)
 * [ state ](#state)
 * [ addresses ](#addresses)
 * ~~[ adress_number ](#adress_number)~~ (**deprecated**)
 * ~~[ adress_complement ](#adress_complement)~~ (**deprecated**)
 * ~~[ rgs ](#rgs)~~ (**deprecated**)
 * [ id_cards ](#id_cards)
 * [ get_all_notifications ](#get_all_notifications)
 * [ notifications ](#notifications)
 * ~~[ cep ](#cep)~~ (**deprecated**)
 * [ mobile ](#mobile)
 * [ logout ](#logout)

## <a name="public_profile"></a>public_profile

O escopo `public_profile` permite acesso de leitura às informações públicas de uma pessoa. São informações públicas:

 * Identificador Único (`id`)
 * Primeiro Nome (`first_name`)
 * Nome de Usuário (`username`)
 * Foto do Perfil (`profile_picture`)
 * Data da última atualização do cadastro (`updated_at`)
 * Badges (`badges`)
 * Faixa etária (`age_range`)

Veja um exemplo de um objeto JSON:

``` js
{
    id: 1,
    age_range: {
    	min: 21
    },
    first_name: "Guilherme",
    username: "gd",
    profile_picutre_url: "http://placehold.it/245x245",
    updated_at: "2014-11-25T16:22:28-0200",
    badges: {
        login-cidadao.has_cpf: true,
        login-cidadao.valid_email: true
    }
}
```

## <a name="full_name"></a> full_name

Permite acesso de leitura ao nome completo de uma pessoa, retornado nos seguintes atributos:

``` js
{
	// ...
    full_name: "Guilherme Donato",
    // ...
}
```

## <a name="cpf"></a> cpf

Permite acesso de leitura no CPF de uma pessoa, que será retornado como:

``` js
{
	// ...
    cpf: "12312312345",
    // ...
}
```

## <a name="birthdate"></a> birthdate

Permite acesso à data de nascimento completa de uma pessoa (em vez da faixa etária informada pelo public_profile). A data será formatada de acordo com o ISO 8601:

``` js
{
	// ...
    birthdate: "1989-08-21T00:00:00-0300",
    // ...
}
```

## <a name="email"></a> email

Permite acesso de leitura ao endereço de email de uma pessoa.

``` js
{
    // ...
    email: "user@example.com",
    // ...
}
```

## <a name="city"></a> city

Permite acesso de leitura à cidade de uma pessoa. Será retornado o objeto City completo, incluindo State e Country, como no exemplo:

``` js
{
	// ...
    city: {
        id: 4314902,
        name: "Porto Alegre",
        stat: "4314902",
        state: {
            id: 43,
            name: "Rio Grande do Sul",
            acronym: "RS",
            iso6: "BR-RS",
            country: {
                id: 36,
                name: "BRAZIL",
                iso2: "BR",
                iso3: "BRA"
            }
        }
    },
	// ...
}
```

## <a name="country"></a> country

Permite acesso de leitura do país de uma pessoa. Assim como em `city`, o objeto completo será retornado como no exemplo:

``` js
{
    // ...
    country: {
        id: 36,
        name: "BRAZIL",
        iso2: "BR",
        iso3: "BRA",
        iso_num: 76,
        postal_format: "99999-999",
        postal_name: "CEP"
    },
    // ...
}
```

## <a name="state"></a> state

Permite acesso de leitura do estado de uma pessoa. Assim como em `city` e `country`, o objeto completo será retornado como no exemplo:

``` js
{
    // ...
    state: {
        id: 43,
        name: "Rio Grande do Sul",
        acronym: "RS",
        iso6: "BR-RS",
        fips: "",
        stat: "43",
        class: "",
        country: {
            id: 36,
            name: "BRAZIL",
            iso2: "BR",
            iso3: "BRA"
        }
    },
    // ...
}
```

## <a name="addresses"></a> addresses

Permite acesso de leitura aos endereços de uma pessoa na forma de um array de PerssonAddress. O exemplo a seguir representa o endereço exemplificado abaixo.

```
Rua Fulano de Tal, 123
Prédio 3, Sala 321
Porto Alegre, RS, Brazil
90123-121
```

``` js
{
    // ...
    addresses: [
        {
            id: 6,
            name: "Casa",
            address: "Rua Fulano de Tal",
            complement: "Prédio 3, Sala 321",
            address_number: "123",
            city: {
                id: 4314902,
                name: "Porto Alegre",
                state: {
                    id: 43,
                    name: "Rio Grande do Sul",
                    acronym: "RS",
                    iso6: "BR-RS",
                    country: {
                        id: 36,
                        name: "BRAZIL",
                        iso2: "BR",
                        iso3: "BRA"
                    }
                }
            },
            postal_code: "90123-121"
        },
        // ...
    ]
    // ...
}
```

## <a name="adress_number"></a> ~~adress_number~~ (deprecated)

**NÃO USE**

## <a name="adress_complement"></a> ~~adress_complement~~ (deprecated)

**NÃO USE**

## <a name="rgs"></a> ~~rgs~~ (deprecated)

**NÃO USE**

## <a name="id_cards"></a> id_cards

Permite acesso de leitura a todos documentos de identidade de uma pessoa. Os documentos de identidade contém o órgão expedidor, o estado e o valor, que pode ser qualquer texto (números, letras, caracteres especiais...).

``` js
{
    // ...
    id_cards: [
        {
            state: {
                id: 43,
                name: "Rio Grande do Sul",
                acronym: "RS",
                iso6: "BR-RS",
                country: {
                    id: 36,
                    name: "BRAZIL",
                    iso2: "BR",
                    iso3: "BRA"
                }
            },
            issuer: "SJS/II RS",
            value: "1234567890"
        },
        // ...
    ]
    // ...
}
```

## <a name="get_all_notifications"></a> get_all_notifications

Permite acesso de leitura a todas as notificações de uma pessoa em vez de permitir acesso apenas às notificações do Client OAuth.

Esse escopo não altera o conteúdo do objeto Person.

## <a name="notifications"></a> notifications

Permite acesso de leitura e **escrite** às notificações do Client OAuth.

Esse escopo não altera o conteúdo do objeto Person.

## <a name="cep"></a> ~~cep~~ (deprecated)

**NÃO USE**

## <a name="mobile"></a> mobile

Permite acesso de leitura ao telefone celular de uma pessoa. Apenas números serão retornados, até mesmo o sinal "+" será removido da entrada do usuário.

``` js
{
    // ...
    mobile: "555196668555",
    // ...
}
```

## <a name="logout"></a> logout

Permite que sejam geradas Logou Keys de forma a habilitar um Client OAuth a deslogar um determinado usuário. [ Leia mais aqui ](remoteLogout.md)

[ Voltar ao Índice ](index.md)
