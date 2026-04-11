#!/usr/bin/env bash
# Instalador tipo laravel.build: solo crea el proyecto con Composer dentro de Docker (sin migraciones ni Sail automático).
#
# Uso:
#   curl -fsSL https://raw.githubusercontent.com/ulises58/Cxp-Backend/main/build/install.sh | bash -s example-app
#
# Variables opcionales:
#   CXP_REPO_URL, CXP_VERSION, CXP_PACKAGIST=1, CXP_COMPOSER_IMAGE
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
COMPOSER_IMG="${CXP_COMPOSER_IMAGE:-laravelsail/php84-composer:latest}"

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
    echo "Aviso: no hay sudo/doas; si ves permisos raros: chown -R \$(whoami) . dentro de ${NAME}" >&2
  fi
}

echo ""
fix_perms

echo ""
echo -e "${CYAN}${BOLD}Proyecto creado.${NC} Las migraciones y el seed no se ejecutan solos."
echo ""
echo -e "Con ${BOLD}Sail${NC} (MySQL, etc.):"
echo -e "  ${BOLD}cp .env.sail.example .env && php artisan key:generate${NC}"
echo -e "  ${BOLD}./vendor/bin/sail up -d${NC}"
echo -e "  ${BOLD}./vendor/bin/sail artisan migrate${NC}  y si quieres  ${BOLD}./vendor/bin/sail artisan db:seed${NC}"
echo ""
echo -e "Solo ${BOLD}SQLite${NC} en local: ${BOLD}php artisan migrate${NC} y ${BOLD}php artisan db:seed${NC} cuando quieras."
echo ""
echo -e "Atajo Docker completo (sube stack + migrate + seed): ${BOLD}composer run docker-setup${NC}"
