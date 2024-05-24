FROM ucr.idc.ufpa.br/ocs_inventory-ufpa/2.8:base-image

LABEL maintainer="contact@ocsinventory-ng.org" \
      version="2.8" \
      description="OCS Inventory docker image"

ARG RELEASE_VERSION="v2.0"

ENV APACHE_RUN_USER=apache APACHE_RUN_GROUP=apache \
    APACHE_LOG_DIR=/var/log/httpd APACHE_PID_FILE=/var/run/httpd.pid APACHE_RUN_DIR=/var/run/httpd APACHE_LOCK_DIR=/var/lock/httpd \
    OCS_DB_SERVER=dbsrv OCS_DB_PORT=3306 OCS_DB_USER=ocs OCS_DB_PASS=ocs OCS_DB_NAME=ocsweb \
    OCS_LOG_DIR=/var/log/ocsinventory-server OCS_VARLIB_DIR=/var/lib/ocsinventory-reports/ OCS_WEBCONSOLE_DIR=/usr/share/ocsinventory-reports \
    OCS_PERLEXT_DIR=/etc/ocsinventory-server/perl/ OCS_PLUGINSEXT_DIR=/etc/ocsinventory-server/plugins/ \
    OCS_SSL_ENABLED=0 OCS_SSL_WEB_MODE=DISABLED OCS_SSL_COM_MODE=DISABLED OCS_SSL_KEY=/path/to/key OCS_SSL_CERT=/path/to/cert OCS_SSL_CA=/path/to/ca \
    TZ=America/Belem

VOLUME /var/lib/ocsinventory-reports /etc/ocsinventory-server /usr/share/ocsinventory-reports

WORKDIR /tmp

RUN wget https://gl.idc.ufpa.br/ocs_inventory-ufpa/2.8/-/archive/${RELEASE_VERSION}/2.8-${RELEASE_VERSION}.tar.gz && \
    tar -xzf 2.8-${RELEASE_VERSION}.tar.gz;

RUN mv /tmp/2.8-${RELEASE_VERSION} /tmp/OCSNG_UNIX_SERVER_2.8;

WORKDIR /tmp/OCSNG_UNIX_SERVER_2.8

RUN cd Apache/ && \
    perl Makefile.PL && \ 
    make && \
    make install ;

RUN ln -sf /proc/self/fd/1 /var/log/httpd/access_log && \
    ln -sf /proc/self/fd/1 /var/log/httpd/error_log && \
    ln -sf /proc/self/fd/1 /var/log/httpd/ssl_access_log && \
    ln -sf /proc/self/fd/1 /var/log/httpd/ssl_error_log && \
    ln -sf /proc/self/fd/1 /var/log/httpd/ssl_request_log ;

WORKDIR /tmp

COPY conf/ /tmp/conf
COPY ./scripts/docker-entrypoint.sh /usr/bin/docker-entrypoint.sh

EXPOSE 80 443

ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]
