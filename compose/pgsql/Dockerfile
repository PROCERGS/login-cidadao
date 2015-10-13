# Image based in Zaiste.net
# Source: https://github.com/zaiste/docker-postgresql

FROM ubuntu:14.04
MAINTAINER Gilson Filho <me@gilsondev.in>

RUN apt-get update
RUN apt-get -y install wget

RUN locale-gen en_US.UTF-8
RUN dpkg-reconfigure locales
ENV LANG en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN echo "deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main" > /etc/apt/sources.list.d/pgdg.list
RUN wget --quiet --no-check-certificate -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
RUN apt-get update
RUN LC_ALL=en_US.UTF-8 DEBIAN_FRONTEND=noninteractive apt-get -y install sudo postgresql-9.3

RUN sed -i -e"s/data_directory =.*$/data_directory = '\/data'/" /etc/postgresql/9.3/main/postgresql.conf
RUN echo "host    all    all    0.0.0.0/0    md5" >> /etc/postgresql/9.3/main/pg_hba.conf

ADD run /bin/run
RUN chmod +x /bin/run

VOLUME ["/data"]
EXPOSE 5432
CMD ["/bin/run"]
