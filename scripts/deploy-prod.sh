#!/usr/bin/env bash
set -euo pipefail

REPO_DIR="${REPO_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
BRANCH="${BRANCH:-main}"

echo "=== Deploy Producao ==="
cd "$REPO_DIR"

git pull origin "$BRANCH"

cd docker/prod
docker compose up -d --build
docker compose exec app-prod composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-parallel
docker compose exec app-prod composer dump-autoload --no-dev --optimize
docker compose exec app-prod php artisan migrate --force
docker compose exec app-prod php artisan config:clear
docker compose exec app-prod php artisan route:clear
docker compose exec app-prod php artisan view:clear
docker compose exec app-prod php artisan route:cache
docker compose exec app-prod php artisan view:cache

echo "=== Deploy Producao concluido ==="