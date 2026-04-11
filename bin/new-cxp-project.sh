#!/usr/bin/env bash
# Crea un proyecto nuevo a partir de esta base (equivalente a `composer create-project laravel/laravel`).
#
# Uso local (desde la raíz del repo):
#   ./bin/new-cxp-project.sh mi-saas
#
# Para el flujo tipo `curl https://laravel.build/app | bash` (solo Docker + Composer en contenedor), usa:
#   build/install.sh
#
# Uso con curl (Composer en el host):
#   curl -fsSL https://raw.githubusercontent.com/ulises58/Cxp-Backend/main/bin/new-cxp-project.sh | bash -s mi-saas
#
# Variables opcionales:
#   CXP_REPO_URL   URL git del paquete (https o git@...)
#   CXP_VERSION    Restricción de versión Composer (p. ej. dev-main, 1.x-dev, ^1.0)
#
set -euo pipefail

NAME="${1:-}"
if [[ -z "$NAME" ]]; then
  echo "Uso:  $0 <nombre-carpeta>" >&2
  echo "Ej.:  $0 mi-saas" >&2
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "Composer no está en el PATH. Instálalo: https://getcomposer.org" >&2
  exit 1
fi

# Sin Packagist: instala desde el repositorio Git (ajusta a tu fork o monorepo).
# Cuando publiques en Packagist, puedes omitir --repository y usar solo:
#   composer create-project cxp/cxp-backend mi-saas
: "${CXP_REPO_URL:=https://github.com/ulises58/Cxp-Backend.git}"
: "${CXP_VERSION:=dev-main}"

echo "→ composer create-project cxp/cxp-backend:${CXP_VERSION} ${NAME}"
composer create-project "cxp/cxp-backend:${CXP_VERSION}" "${NAME}" \
  --repository="{\"type\":\"vcs\",\"url\":\"${CXP_REPO_URL}\"}" \
  --remove-vcs \
  --no-interaction

echo "→ quitando bin/ y build/ (solo plantilla de instalador)"
rm -rf "${NAME}/bin" "${NAME}/build"

echo ""
echo "Proyecto creado en ./${NAME}"
echo "Sail (cuando quieras):"
echo "  cd ${NAME} && cp .env.sail.example .env && php artisan key:generate && ./vendor/bin/sail up -d && ./vendor/bin/sail artisan migrate"
echo "Atajo con migrate/seed: cd ${NAME} && composer run docker-setup"
echo ""
echo "SQLite / serve: cd ${NAME} && php artisan migrate && php artisan serve"
