FROM docker.elastic.co/apm/apm-server:6.2.3

COPY docker/apm/apm-server.yml /usr/share/apm-server/apm-server.yml

USER root

RUN chown apm-server /usr/share/apm-server/apm-server.yml \
    && chmod 640 /usr/share/apm-server/apm-server.yml

USER apm-server
