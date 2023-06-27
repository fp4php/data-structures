FROM php:8.1-cli

# Install dependencies
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libfreetype6-dev \
        libssl-dev \
        zip \
        unzip \
        git \
        autoconf \
        automake \
        gcc \
        make \
        pkg-config

# Install xdebug
RUN curl -L https://xdebug.org/files/xdebug-3.0.4.tgz | tar xvz -C /tmp/

RUN cd /tmp/xdebug-3.0.4 && \
    phpize && \
    ./configure --enable-xdebug && \
    make && \
    make install

COPY ./docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user
ARG HOST_UID=1000
ARG HOST_GID=1000
ARG HOST_USER=docker-user
ARG HOST_GROUP=docker-group

RUN echo '%sudonopswd ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && groupadd -g $HOST_GID $HOST_GROUP \
    && groupadd sudonopswd \
    && useradd -m -l -g $HOST_GROUP -u $HOST_UID $HOST_USER \
    && usermod -aG sudo $HOST_USER \
    && usermod -aG sudonopswd $HOST_USER \
    && chown -R $HOST_USER:$HOST_GROUP /opt \
    && chmod 755 /opt

USER $HOST_USER

WORKDIR /app
