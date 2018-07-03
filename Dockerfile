FROM php:7.2-fpm-alpine

MAINTAINER "Nicolas Giraud" <nicolas.giraud.dev@gmail.com>

COPY helper /data/helper
RUN chmod +x /data/helper/bin/dashboard.sh && chmod +x /data/helper/dashboard/dashboard.php

VOLUME ["/data"]
WORKDIR /data/build

ENTRYPOINT ["/data/helper/bin/dashboard.sh"]
