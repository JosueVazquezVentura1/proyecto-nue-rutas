FROM php:8.2-cli

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

WORKDIR /app

COPY . /app

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000"]
