FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev
RUN docker-php-ext-install curl
COPY . /var/www/html/
WORKDIR /var/www/html/
EXPOSE 80
