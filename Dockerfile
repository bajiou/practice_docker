FROM php:8.2-apache

WORKDIR /code

RUN docker-php-ext-install mysqli
