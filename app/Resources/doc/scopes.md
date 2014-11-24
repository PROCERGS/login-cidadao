Scopes
======

This page will describe what scope does what. Bellow there's a list of the available scopes

 * [ public_profile ](#public_profile)
 * [ full_name ](#full_name)
 * [ cpf ](#cpf)
 * [ birthdate ](#birthdate)
 * [ email ](#email)
 * [ city ](#city)
 * ~~[ voter_registration ](#voter_registration)~~ (**deprecated**)
 * [ country ](#country)
 * [ uf ](#uf)
 * [ address ](#address)
 * ~~[ adress_number ](#adress_number)~~ (**deprecated**)
 * ~~[ adress_complement ](#adress_complement)~~ (**deprecated**)
 * ~~[ rgs ](#rgs)~~ (**deprecated**)
 * [ get_all_notifications ](#get_all_notifications)
 * [ notifications ](#notifications)
 * [ cep ](#cep)
 * [ mobile ](#mobile)
 * [ logout ](#logout)

## <a name="public_profile"></a>public_profile

The `public_profile` scope allows read access on the public information of a person. This includes:

 * Unique Identification
 * First Name
 * Username
 * Profile Picture
 * Last Updated Date
 * Badges
 * Age Range

## <a name="full_name"></a> full_name

Allows read access to a person's full name.

## <a name="cpf"></a> cpf

Allows read access to a person's CPF (Brazil's kinda equivalent of Social Security Number).

## <a name="birthdate"></a> birthdate

Allows read access to a person's full birth date (instead of only an age range given by the public_profile).

## <a name="email"></a> email

Allows read access to a person's email address.

## <a name="city"></a> city

Allows read access to a person's city.

## <a name="voter_registration"></a> ~~voter_registration~~ (deprecated)

**DO NOT USE**

Used to allow read access to a person's voter registration.

## <a name="country"></a> country

Allows read access to a person's country.

## <a name="uf"></a> uf

Allows read access to a person's state.

## <a name="address"></a> address

Allows read access to a person's addresses.

## <a name="adress_number"></a> ~~adress_number~~ (deprecated)

**DO NOT USE**

## <a name="adress_complement"></a> ~~adress_complement~~ (deprecated)

**DO NOT USE**

## <a name="rgs"></a> ~~rgs~~ (deprecated)

**DO NOT USE**

## <a name="id_cards"></a> id_cards

Allows read access to all ID Cards.

## <a name="get_all_notifications"></a> get_all_notifications

Allows read access to all notifications of a given person instead of being able to see only the OAuth Client's own notifications.

## <a name="notifications"></a> notifications

Allows read and **write** access to the OAuth Client's own notifications.

## <a name="cep"></a> cep (deprecated)

**DO NOT USE**

## <a name="mobile"></a> mobile

Allows read access to a person's mobile number(s).

## <a name="logout"></a> logout

Allows the generation of Logout Keys so that an OAuth Client can "deauth" a person. [Read More](remoteLogout.md)
