#Login Cidadão Docs

This is the official repository of Login Cidadão Documentation. Here, you can find docs about installation, maintenance mode and procedures of [Projeto Login Cidadão](https://github.com/PROCERGS/login-cidadao).

All documents here were generated with mkdocs.

## How to get this documentation?

1 - In a distro gnu/linux using apt package manager, install the package management system of python language (pip). Sudo user or root permissions can be required:
```
  # apt-get install python-pip
```

2 - After that, as sudo user too, run pip to install mkdocs:
```
  # pip install mkdocs
```

3 - Clone the repo using git (sudo or root permission is not necessary):
```
  $ git clone https://github.com/PROCERGS/login-cidadao-docs.git
```

4 - Done! All content files will be in docs directory.

## How to get a local installation?

1 - After clone the repo, get the diretory and run mkdocs:

```
$ cd login-cidadao-docs
$ mkdocs serve
```

2 - In localhost plus default door (http://127.0.0.1:8000) you can reach the docs site. If you want to make some changes, mkdocs will be able to show automaticaly.

## Building html/css/js

1 - To deploy the docs into a html struture, let's build the documentation. Is very simple. In the zup-docs directory, run mkdocs build:

```
$ mkdocs build
```

This will create a new directory, named site. Let's take a look inside the directory:

```
  user@Server:~/zup-docs$ ls -la site/
  total 80 files
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 api_configuration
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 css
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 fonts
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 images
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 img
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 implement
  -rw-r--r--  1 user user 7926 Nov 23 15:18 index.html
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 installation_docker
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 javascript
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 js
  drwxr-xr-x  3 user user 4096 Nov 23 15:18 license
  drwxr-xr-x  3 user user 4096 Nov 23 15:18 mkdocs
  -rw-r--r--  1 user user 4917 Nov 23 15:18 search.html
  -rw-r--r--  1 user user  990 Nov 23 15:18 sitemap.xml
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 updating_docker
  drwxr-xr-x  2 user user 4096 Nov 23 15:18 web_configuration
```

After some time, files may be removed from the documentation but they will still reside in the site directory. To remove those stale files, just run mkdocs with the --clean switch.

```
$ mkdocs build --clean
```

To more information about mkdocs, see http://www.mkdocs.org. 

