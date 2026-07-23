FROM php:8.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    apt-utils \
    curl \
    unzip \
    supervisor \
    cron \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    pcntl \
    sockets \
    pgsql \
    zip \
    && docker-php-ext-enable pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html

COPY turk-ai-backend/ /var/www/html
COPY docker/supervisor/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]