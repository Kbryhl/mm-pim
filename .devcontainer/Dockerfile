FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP MySQLi extension
RUN docker-php-ext-install mysqli

WORKDIR /workspaces/mm-pim
