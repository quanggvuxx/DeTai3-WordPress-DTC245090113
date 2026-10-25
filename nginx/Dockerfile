FROM nginxinc/nginx-unprivileged:stable-alpine
USER root
RUN mkdir -p /var/log/site && chown 101:101 /var/log/site
COPY nginx.conf /etc/nginx/nginx.conf
USER 101
EXPOSE 8080 8082
