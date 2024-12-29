# ベースイメージはPHP 8.2（Apache付き）
FROM php:8.2-apache

# 作業ディレクトリを設定 (ここが「/var/www/html」になる)
WORKDIR /var/www/html

# 必要なツール（unzip, curl）をインストール
RUN apt-get update && \
    apt-get install -y unzip curl && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# PHPの拡張機能 (pdo_mysql) をインストール
RUN docker-php-ext-install pdo_mysql

# プロジェクトファイルをコンテナ内にコピー
COPY ./src /var/www/html
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini


# Apacheをフォアグラウンドで起動
CMD ["apache2-foreground"]
