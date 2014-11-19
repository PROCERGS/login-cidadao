# Vagrant setup for Login Cidadão

## Vagrant Setup

To install using Vagrant first clone this branch:
```bash
$ git clone -b vagrant-php5.4 https://github.com/PROCERGS/login-cidadao.git`
```

Then just start the vagrant box using the command:
```bash
$ vagrant up
```

Please take note of the box's Samba share address that will be printed by the end of the setup. It will look something like `smb://172.28.128.3/login-cidadao` which is the equivalent of `\\172.28.128.3\login-cidadao` on Windows.

## Workspace Setup
After provisioning CentOS 7 box with the dependencies needed by `login-cidadao` in the previous steps, the project will have been cloned into `/var/www/login-cidadao`.

To work on it directly through your host OS, mount the Samba share printed in the end of the Vagrant Setup.
You can access the application by pointing your browser to `http://localhost:8080/`.
