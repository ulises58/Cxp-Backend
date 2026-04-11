#!/usr/bin/env bash
# Arranque inicial con Laravel Sail (Docker).
# Requisitos: Docker Desktop (o Docker Engine + Compose), PHP 8.3+ y Composer en el host.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if ! command -v docker >/dev/null 2>&1; then
  echo "No se encontró 'docker'. Instala Docker Desktop o Docker Engine."
  exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
  echo "No se encontró Docker Compose v2 ('docker compose')."
  exit 1
fi

echo "→ composer install"
composer install --no-interaction

if [[ ! -f .env ]]; then
  echo "→ cp .env.sail.example .env"
  cp .env.sail.example .env
fi

echo "→ php artisan key:generate"
php artisan key:generate --ansi

echo "→ docker compose build (imagen PHP / Sail)"
docker compose build

echo "→ docker compose up -d (MySQL/Redis deben estar healthy antes del contenedor app)"
docker compose up -d

echo "→ migrate + seed (dentro del contenedor)"
./vendor/bin/sail artisan migrate --force --ansi
./vendor/bin/sail artisan db:seed --force --ansi

echo ""
echo "Listo. API: http://localhost (puerto \${APP_PORT:-80} en el host)"
echo "Mailpit (correo de prueba): http://localhost:8025"
echo "Comandos útiles: ./vendor/bin/sail artisan test | ./vendor/bin/sail down"
