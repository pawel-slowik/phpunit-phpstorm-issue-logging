# syntax=docker/dockerfile:1

FROM php:8.3-cli-bookworm

RUN apt-get update

RUN apt-get install --no-install-recommends -y unzip git

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/

WORKDIR /app

COPY tests/PHPUnit_10.5/PHP_8.3/composer.json .
COPY tests/PHPUnit_10.5/PHP_8.3/composer.lock .
COPY tests/PHPUnit_10.5/PHP_8.3/expected_output_template.txt .
COPY src lib
COPY tests/src src
COPY tests/PHPUnit_10.5/tests tests
COPY tests/PHPUnit_10.5/phpunit.xml .
COPY tests/match.sh tests/test.sh /usr/local/bin/

RUN composer install

ENTRYPOINT ["test.sh"]
