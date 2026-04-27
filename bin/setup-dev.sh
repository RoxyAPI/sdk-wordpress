#!/usr/bin/env bash
#
# Install PHP 8.4 + Composer 2 for RoxyAPI WordPress plugin development.
#
# Usage:
#   sudo bin/setup-dev.sh
#
# Tested on Ubuntu 24.04 LTS (noble); should also work on Ubuntu 22.04 and Debian 12+.
#
# Why PHP 8.4: WordPress 6.8 is fully tested through PHP 8.3; PHP 8.4 is the
# current stable production target (LTS through Dec 2028). PHP 8.1 is EOL.
# The plugin still declares "Requires PHP: 7.4" for end-user breadth, but
# local dev runs on the latest supported version.
#
# After this script:
#   composer install
#   npm install && npm run build:all
#   npx wp-env start

set -euo pipefail

PHP_VERSION="8.4"

if [[ $EUID -ne 0 ]]; then
  echo "ERROR: run with sudo" >&2
  exit 1
fi

if [[ ! -f /etc/os-release ]]; then
  echo "ERROR: /etc/os-release missing; only Debian/Ubuntu supported" >&2
  exit 1
fi
. /etc/os-release

case "${ID:-}" in
  ubuntu|debian) ;;
  *) echo "ERROR: only Ubuntu/Debian supported (got: ${ID:-unknown})" >&2; exit 1 ;;
esac

apt-get update -y
apt-get install -y --no-install-recommends \
  software-properties-common ca-certificates curl gnupg lsb-release unzip

if [[ "$ID" == "ubuntu" ]]; then
  add-apt-repository -y ppa:ondrej/php
else
  curl -sSLo /etc/apt/trusted.gpg.d/sury-php.gpg https://packages.sury.org/php/apt.gpg
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" \
    > /etc/apt/sources.list.d/sury-php.list
fi

apt-get update -y
apt-get install -y --no-install-recommends \
  "php${PHP_VERSION}-cli" \
  "php${PHP_VERSION}-common" \
  "php${PHP_VERSION}-mbstring" \
  "php${PHP_VERSION}-xml" \
  "php${PHP_VERSION}-curl" \
  "php${PHP_VERSION}-mysql" \
  "php${PHP_VERSION}-intl" \
  "php${PHP_VERSION}-bcmath" \
  "php${PHP_VERSION}-zip" \
  "php${PHP_VERSION}-gd"

update-alternatives --set php "/usr/bin/php${PHP_VERSION}" 2>/dev/null || true

if ! command -v composer >/dev/null 2>&1; then
  EXPECTED_SIG=$(curl -sS https://composer.github.io/installer.sig)
  curl -sSLo /tmp/composer-setup.php https://getcomposer.org/installer
  ACTUAL_SIG=$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")
  if [[ "$EXPECTED_SIG" != "$ACTUAL_SIG" ]]; then
    rm -f /tmp/composer-setup.php
    echo "ERROR: composer installer signature mismatch" >&2
    exit 1
  fi
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --2 --quiet
  rm -f /tmp/composer-setup.php
fi

echo
echo "--- versions ---"
php --version | head -1
composer --version
echo
echo "Next:"
echo "  composer install"
echo "  npm install && npm run build:all"
echo "  npx wp-env start"
