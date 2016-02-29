Scopes
======

This page will describe what scope does what. Bellow there's a list of the available scopes

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

The `public_profile` scope allows read access on the public information of a person. This includes:

 * Unique Identification (`id`)
 * First Name (`first_name`)
 * Username (`username`)
 * Profile Picture (`profile_picture`)
 * Last Updated Date (`updated_at`)
 * Badges (`badges`)
 * Age Range (`age_range`)

Bellow you can see an example JSON object:

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

Allows read access to a person's full name, returning the following attributes:

``` js
{
	// ...
    full_name: "Guilherme Donato",
    // ...
}
```

## <a name="cpf"></a> cpf

Allows read access to a person's CPF (Brazil's kinda equivalent of the Social Security Number). It'll be returned as:

``` js
{
	// ...
    cpf: "12312312345",
    // ...
}
```

## <a name="birthdate"></a> birthdate

Allows read access to a person's full birth date (instead of only an age range given by the public_profile). The date will be ISO 8601 formatted as follows:

``` js
{
	// ...
    birthdate: "1989-08-21T00:00:00-0300",
    // ...
}
```

## <a name="email"></a> email

Allows read access to a person's email address.

``` js
{
    // ...
    email: "user@example.com",
    // ...
}
```

## <a name="city"></a> city

Allows read access to a person's city. The full City object will be returned, including State and Country as in the example:

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

Allows read access to a person's country. The full object will be returned as shown in the example:

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

Allows read access to a person's state. The full object will be returned as shown in the example:

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

Allows read access to a person's addresses as an array of PersonAddress objects. The following example represents the address bellow:

```
123 Something St.
Building 3, room 321
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
            address: "Something St.",
            complement: "Building 3, room 321",
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

**DO NOT USE**

## <a name="adress_complement"></a> ~~adress_complement~~ (deprecated)

**DO NOT USE**

## <a name="rgs"></a> ~~rgs~~ (deprecated)

**DO NOT USE**

## <a name="id_cards"></a> id_cards

Allows read access to all ID Cards. ID Cards will contain the issuer agency, state and the value, that can be anything (numbers, letters, special characters...).

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

Allows read access to all notifications of a given person instead of being able to see only the OAuth Client's own notifications.

This won't change the contents of the Person object.

## <a name="notifications"></a> notifications

Allows read and **write** access to the OAuth Client's own notifications.

This won't change the contents of the Person object.

## <a name="cep"></a> ~~cep~~ (deprecated)

**DO NOT USE**

## <a name="mobile"></a> mobile

Allows read access to a person's mobile number(s). Only numbers will be returned. Even the "+" sign will be removed from the user's input.

``` js
{
    // ...
    mobile: "555196668555",
    // ...
}
```

## <a name="logout"></a> logout

Allows the generation of Logout Keys so that an OAuth Client can "deauth" a person. [Read More](remoteLogout.md)
