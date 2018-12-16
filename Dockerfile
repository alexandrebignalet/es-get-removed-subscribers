# See https://github.com/docker-library/php/blob/master/7.1/fpm/Dockerfile
FROM php:7.1-fpm
ARG TIMEZONE

MAINTAINER Alexandre BIGNALET

RUN apt-get update && apt-get install -y cron unzip

# Setup cron
COPY crontab /var/spool/cron/crontabs/root
RUN chmod 0600 /var/spool/cron/crontabs/root
RUN cron

# DL Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini
RUN "date"

# Install app and deps
WORKDIR /home/expertsender/get-removed-subscribers
COPY composer.json .
COPY composer.lock .
RUN composer install --no-scripts --no-autoloader --no-dev --no-interaction

# add the rest of the code
COPY . .
RUN composer dump-autoload --optimize && \
	composer run-script post-install-cmd
