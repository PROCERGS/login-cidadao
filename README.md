Login Cidadão
=============

This is the source code for the 'Login Cidadão' (Citizen's Login) project.

This project's main objective is to provide a way for citizens to authenticate against official online services, eliminating the need to create and maintain several credentials on several services.

It also allows government agencies to better understand its citizen's needs and learn how to interact more effectively with them.

*Note*: Since this project is just on it's initial stages, it's not recommended to fork it just yet.

Dependencies
============

* composer
* node.js

Setup - Development
==========

Setting up Permissions
----------------------

Add the path 'web/uploads' on permission commands

More info: http://symfony.com/doc/current/book/installation.html

Generate assets
---------------

php app/console assets:install --symlink

Vagrant
=======

```
vagrant box add centos65-x86_64-20140116 https://github.com/2creatives/vagrant-centos/releases/download/v6.5.3/centos65-x86_64-20140116.box

vagrant init
```

Add entry to /etc/hosts

```
192.168.33.10 lc.local
```
