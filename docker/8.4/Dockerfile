FROM ubuntu:24.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=24
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=18

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV LANG=C.UTF-8
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"
ENV PLAYWRIGHT_BROWSERS_PATH=0

# Configure timezone and apt caching
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom \
    && echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom \
    && echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

# Install base dependencies and configure repositories
RUN sed -i 's@http://.*archive.ubuntu.com@http://mirror.yandex.ru/ubuntu@g' /etc/apt/sources.list.d/ubuntu.sources \
    && sed -i 's@http://.*security.ubuntu.com@http://mirror.yandex.ru/ubuntu@g' /etc/apt/sources.list.d/ubuntu.sources \
    && apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends curl gnupg ca-certificates \
    && mkdir -p /etc/apt/keyrings

# Add PHP repository
RUN curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' \
        | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" \
        > /etc/apt/sources.list.d/ppa_ondrej_php.list

# Add Node.js repository
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list

# Add PostgreSQL repository
RUN curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list

# Install PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        gnupg gosu curl ca-certificates zip unzip git supervisor \
        sqlite3 libcap2-bin libpng-dev python3 dnsutils \
        librsvg2-bin fswatch ffmpeg nano libgd3 \
        php8.4-cli php8.4-dev php8.4-pgsql php8.4-sqlite3 php8.4-gd \
        php8.4-curl php8.4-mongodb php8.4-imap php8.4-mysql \
        php8.4-mbstring php8.4-xml php8.4-zip php8.4-bcmath \
        php8.4-soap php8.4-intl php8.4-readline php8.4-ldap \
        php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-swoole \
        php8.4-memcached php8.4-pcov php8.4-imagick

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Install Node.js and package managers
RUN apt-get update && apt-get install -y --no-install-recommends nodejs \
    && npm install -g npm pnpm \
    && corepack enable \
    && corepack prepare yarn@stable --activate \
    && npx playwright install-deps

# Install database clients
RUN apt-get update && apt-get install -y --no-install-recommends \
        $MYSQL_CLIENT postgresql-client-$POSTGRES_VERSION

# Cleanup and configure PHP
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /root/.cache /root/.npm

# Configure PHP capabilities and create user
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4 \
    && userdel -r ubuntu 2>/dev/null || true \
    && groupadd --force -g $WWWGROUP sail \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail \
    && git config --global --add safe.directory /var/www/html

# Copy configuration files
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
