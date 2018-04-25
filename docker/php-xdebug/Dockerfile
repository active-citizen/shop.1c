FROM php:7.0-fpm

RUN apt-get update && apt-get install -y \
libpq-dev \
libmemcached-dev \
curl \
wget \
git \
zip \
libjpeg-dev \
libpng12-dev \
libfreetype6-dev \
libssl-dev \
libmcrypt-dev \
--no-install-recommends \
&& rm -r /var/lib/apt/lists/*

# configure gd library
RUN docker-php-ext-configure gd \
--enable-gd-native-ttf \
--with-jpeg-dir=/usr/lib \
--with-freetype-dir=/usr/include/freetype2

# install mcrypt library
RUN docker-php-ext-install mcrypt \
opcache \
mysqli \
gd

RUN wget https://github.com/websupport-sk/pecl-memcache/archive/NON_BLOCKING_IO_php7.tar.gz \
&& tar -zxvf NON_BLOCKING_IO_php7.tar.gz \
&& cd pecl-memcache-NON_BLOCKING_IO_php7 \
&& phpize && ./configure --enable-memcache && make \
&& cp modules/memcache.so /usr/local/lib/php/extensions/no-debug-non-zts-20151012/ \
&& docker-php-ext-enable memcache \
&& rm -rf NON_BLOCKING_IO_php7.zip \
&& rm -rf pecl-memcache-NON_BLOCKING_IO_php7

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
#RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /bin/composer
RUN composer global require fxp/composer-asset-plugin

#ARG HOST_UID
#RUN usermod -u ${HOST_UID} www-data \
#&& chown www-data: /home/bitrix

#WORKDIR /home/bitrix
#RUN cd /home/bitrix \
#&& rm -fr * \
#&& chown -R www-data: /home/bitrix
#
#ADD ./b0/templates/data/{{common::redaction}}/html/ /home/bitrix/
#RUN cd /home/bitrix \
#&& tar xf www.tgz \
#&& rm www.tgz
#
#EXPOSE 9000
