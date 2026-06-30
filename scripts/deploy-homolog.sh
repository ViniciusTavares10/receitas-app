#!/usr/bin/env bash
set -euo pipefail

REPO_DIR="${REPO_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
BRANCH="${BRANCH:-main}"

echo "=== Deploy Homologacao ==="
cd "$REPO_DIR"

git pull origin "$BRANCH"

cd docker/homolog
docker compose up -d --build
docker compose exec app-homolog composer dump-autoload --no-dev --optimize
docker compose exec app-homolog php artisan migrate --force
docker compose exec app-homolog php artisan config:clear
docker compose exec app-homolog php artisan route:clear
docker compose exec app-homolog php artisan view:clear
docker compose exec app-homolog php artisan route:cache
docker compose exec app-homolog php artisan view:cache

echo "=== Deploy Homologacao concluido ==="