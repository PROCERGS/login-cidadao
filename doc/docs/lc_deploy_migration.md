## Login Cidadadão > Migração da aplicação

Para migrar o Login Cidadadão de servidor, esteja atento aos seguintes pontos:

1 - Certifique-se de que o servidor de origem e destino correspondem as especificações da aplicação

Para verificar a versão de uma determinada versão gnu/linux, o comando abaixo pode ajudar: 

```
$ cat /etc/*-release 
```

2 - Faça instalação dos [requisitos mínimos de software](lc_deploy.md) a partir das orientações de deploy.


3 - Compacte a aplicação em produção

```
$ tar -czvf 2016_09_06_logincidadao.tgz /var/www/logincidadao
```

4 - Faça um dump da base de dados

```
$ pg_dump logincidadao > 2016_09_06_logincidadao.sql
```

5 - Transfira os arquivos para o novo servidor (recomendamos usar scp)

```
$ scp 2016_09_08_logincidadao.sql USUARIO-DA-MAQUINA-DE-DESTINO@IP-MAQUINA-DE-DESTINO:/srv/mapas/
$ scp 2016_09_06_logincidadao.tgz USUARIO-DA-MAQUINA-DE-DESTINO@IP-MAQUINA-DE-DESTINO:/srv/mapas/
```

6 - Reinicie os serviços

```
# service php5-fpm restart
# service nginx 
```
