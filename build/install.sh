#!/usr/bin/env bash
# Instalador estilo https://laravel.build/nombre-app — solo necesitas Docker (Composer va dentro del contenedor).
#
# Uso:
#   curl -fsSL https://raw.githubusercontent.com/ulises58/Cxp-Backend/main/build/install.sh | bash -s example-app
#
# Variables opcionales (antes del curl o en la misma línea):
#   CXP_REPO_URL       URL git (https o git@...), por defecto placeholder
#   CXP_VERSION        p.ej. dev-main
#   CXP_PACKAGIST=1    si ya publicaste cxp/cxp-backend en Packagist (no usa --repository)
#   CXP_COMPOSER_IMAGE Imagen Composer (por defecto laravelsail/php85-composer:latest)
#
set -euo pipefail

NAME="${1:-}"
if [[ -z "$NAME" ]]; then
  echo "Uso:  curl -fsSL .../build/install.sh | bash -s <nombre-carpeta>" >&2
  echo "Ej.:  curl -fsSL ... | bash -s mi-saas" >&2
  exit 1
fi

if ! [[ "$NAME" =~ ^[a-zA-Z0-9][a-zA-Z0-9_.-]*$ ]]; then
  echo "Nombre inválido: usa solo letras, números, guiones, punto o guion bajo (sin espacios)." >&2
  exit 1
fi

docker info > /dev/null 2>&1 || {
  echo "Docker no está en marcha. Arranca Docker Desktop (o el daemon) e inténtalo de nuevo." >&2
  exit 1
}

: "${CXP_REPO_URL:=https://github.com/ulises58/Cxp-Backend.git}"
: "${CXP_VERSION:=dev-main}"
COMPOSER_IMG="${CXP_COMPOSER_IMAGE:-laravelsail/php85-composer:latest}"

run_create_project() {
  if [[ -n "${CXP_PACKAGIST:-}" ]]; then
    docker run --rm --pull=always \
      -v "$(pwd)":/opt \
      -w /opt \
      -e COMPOSER_ALLOW_SUPERUSER=1 \
      "${COMPOSER_IMG}" \
      bash -ec "
        set -euo pipefail
        composer create-project 'cxp/cxp-backend:${CXP_VERSION}' '${NAME}' --remove-vcs --no-interaction
      "
  else
    docker run --rm --pull=always \
      -v "$(pwd)":/opt \
      -w /opt \
      -e COMPOSER_ALLOW_SUPERUSER=1 \
      "${COMPOSER_IMG}" \
      bash -ec "
        set -euo pipefail
        composer create-project 'cxp/cxp-backend:${CXP_VERSION}' '${NAME}' \
          --repository='{\"type\":\"vcs\",\"url\":\"${CXP_REPO_URL}\"}' \
          --remove-vcs --no-interaction
      "
  fi
}

echo "→ composer create-project (imagen ${COMPOSER_IMG})"
run_create_project

cd "${NAME}"

# El post-create deja .env con SQLite; para el mismo stack que laravel.build (MySQL en Sail) usamos la plantilla Sail.
if [[ -f .env.sail.example ]]; then
  echo "→ .env para Sail (MySQL, Redis, Mailpit…)"
  docker run --rm -v "$(pwd)":/opt -w /opt \
    -e COMPOSER_ALLOW_SUPERUSER=1 \
    "${COMPOSER_IMG}" \
    bash -ec "cp .env.sail.example .env && php artisan key:generate --force --ansi"
fi

echo "→ sail pull + build (imágenes de servicios)"
./vendor/bin/sail pull mysql redis meilisearch mailpit selenium
./vendor/bin/sail build

echo "→ sail up + migrate + seed (MySQL)"
./vendor/bin/sail up -d
# Breve espera a que MySQL acepte conexiones (mismo patrón que un primer sail artisan)
sleep 10
./vendor/bin/sail artisan migrate --force --ansi
./vendor/bin/sail artisan db:seed --force --ansi

CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

fix_perms() {
  if command -v doas &>/dev/null; then
    doas chown -R "$(id -un)": .
  elif command -v sudo &>/dev/null; then
    if sudo -n true 2>/dev/null; then
      sudo chown -R "$(id -un)": .
    else
      echo -e "${BOLD}Introduce tu contraseña para ajustar permisos de los ficheros creados por Docker.${NC}"
      sudo chown -R "$(id -un)": .
    fi
  else
    echo "Aviso: no hay sudo/doas; si ves permisos raros, ejecuta: chown -R \$(whoami) . dentro de ${NAME}" >&2
  fi
}

echo ""
fix_perms

echo ""
echo -e "${CYAN}${BOLD}Listo.${NC} El stack ya está arriba (${BOLD}sail up -d${NC})."
echo -e "Para parar: ${BOLD}cd ${NAME} && ./vendor/bin/sail down${NC}"
echo ""
echo "API (por defecto): http://localhost   ·   Mailpit: http://localhost:8025"
