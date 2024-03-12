FROM php:8.3-fpm-alpine
COPY install_composer.sh /
RUN chmod +x /install_composer.sh
RUN /install_composer.sh
RUN rm /install_composer.sh
RUN mv composer.phar /usr/bin/composer