#!/usr/bin/env bash
#
# Cyonima LMS - test environment bootstrap.
#
# Starts Joomla 5 + MariaDB via Docker Compose, installs Joomla (CLI) if
# needed, installs com_cyonima and prints the access URLs.
#
# Usage:
#   ./docker/up.sh
#
# Requires Docker. If your user is not in the "docker" group, run once:
#   sudo usermod -aG docker $USER   # then log out and back in
# or prefix the command with sudo:
#   sudo ./docker/up.sh
#

set -euo pipefail

cd "$(dirname "$0")"

if docker info >/dev/null 2>&1; then
    DC=(docker compose)
else
    echo "Cannot reach the Docker daemon." >&2
    echo "Either add your user to the docker group (sudo usermod -aG docker \$USER, then re-login)" >&2
    echo "or run this script with sudo:  sudo ./docker/up.sh" >&2
    exit 1
fi

echo "==> Pulling images and starting services..."
"${DC[@]}" up -d

echo "==> Waiting for the database..."
for i in $(seq 1 60); do
    if "${DC[@]}" exec -T db healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

echo "==> Waiting for the web server..."
for i in $(seq 1 60); do
    if curl -fsS -o /dev/null "http://localhost:8080/administrator/" 2>/dev/null; then
        break
    fi
    sleep 2
done

echo "==> Installing Joomla (if needed)..."
"${DC[@]}" exec -T joomla sh -c '
if [ ! -f /var/www/html/configuration.php ]; then
    php /var/www/html/installation/joomla.php install \
        --site-name="Cyonima LMS - Test" \
        --admin-user="Admin" \
        --admin-username="admin" \
        --admin-password="Cyonima2026!" \
        --admin-email="admin@example.com" \
        --db-type="mysqli" \
        --db-host="db" \
        --db-user="cyonima" \
        --db-pass="cyonima" \
        --db-name="joomla" \
        --db-prefix="cyn_"
else
    echo "Joomla already installed."
fi
'

echo "==> Installing com_cyonima..."
"${DC[@]}" exec -T joomla php /cyonima/docker/cleanup.php
"${DC[@]}" exec -T joomla php /cyonima/docker/install-component.php

echo
echo "=============================================================="
echo " Frontend:  http://localhost:8080/"
echo " Admin:     http://localhost:8080/administrator"
echo "   user:     admin"
echo "   password: Cyonima2026!"
echo "=============================================================="
echo
echo "Logs:   docker compose -f docker/docker-compose.yml logs -f joomla"
echo "Stop:   docker compose -f docker/docker-compose.yml down"
echo "Reset:  docker compose -f docker/docker-compose.yml down -v"
