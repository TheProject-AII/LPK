FROM php:8.1-fpm
RUN sed -i 's/9000/9001/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/9000/9001/g' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    git \
    jpegoptim optipng pngquant gifsicle \
    locales \
    unzip \
    vim \
    zip


# Install zip extension and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install fileinfo


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions

# Graphics Draw
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmagic-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get update && apt-get install -y \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl
   
# Multibyte String
RUN apt-get update && apt-get install -y libonig-dev && docker-php-ext-install mbstring

# Miscellaneous
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install exif
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Update the listening port in zz-docker.conf
RUN sed -i 's/listen = .*/listen = 9001/' /usr/local/etc/php-fpm.d/zz-docker.conf


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install specific version of Node.js with npm through nvm
SHELL ["/bin/bash", "--login", "-c"]
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash
RUN nvm install v16.17.0

# Install Cron
RUN apt-get update && apt-get install -y cron
RUN echo "* * * * * root php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab
RUN touch /var/log/cron.log

CMD bash -c "cron && php-fpm"

# change value in php.ini
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i -e "s/^ *memory_limit.*/memory_limit = 1G/g" /usr/local/etc/php/php.ini
RUN sed -i -e "s/^ *upload_max_filesize.*/upload_max_filesize = 200M/g" /usr/local/etc/php/php.ini
RUN sed -i -e "s/^ *post_max_size.*/post_max_size = 200M/g" /usr/local/etc/php/php.ini
RUN sed -i -e "s/^ *max_execution_time.*/max_execution_time = 600/g" /usr/local/etc/php/php.ini
