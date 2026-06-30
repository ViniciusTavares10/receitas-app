#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-$HOME/receitas-app}"
BRANCH="${BRANCH:-main}"
REPO_URL="${REPO_URL:-https://github.com/ViniciusTavares10/receitas-app.git}"
INSTALL_DOCKER="${INSTALL_DOCKER:-true}"
START_HOMOLOG="${START_HOMOLOG:-true}"
START_PROD="${START_PROD:-true}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"

log() {
    printf '\n==> %s\n' "$1"
}

warn() {
    printf '\nAVISO: %s\n' "$1" >&2
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

sudo_cmd() {
    if [ "$(id -u)" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

require_apt_linux() {
    if ! command_exists apt-get; then
        echo "Este bootstrap foi preparado para VMs Ubuntu/Debian com apt-get." >&2
        exit 1
    fi
}

resolve_repo_url() {
    if [ -n "$REPO_URL" ]; then
        return
    fi

    if [ -d .git ] && command_exists git; then
        REPO_URL="$(git remote get-url origin 2>/dev/null || true)"
    fi

    if [ -z "$REPO_URL" ]; then
        cat >&2 <<MSG
REPO_URL nao foi informado e nao consegui descobrir o remote git atual.

Execute assim em uma VM crua:
REPO_URL=https://github.com/seu-usuario/seu-repo.git bash scripts/bootstrap-vm.sh

Ou defina tambem o destino:
REPO_URL=https://github.com/seu-usuario/seu-repo.git PROJECT_DIR=/home/univates/receitas-app bash scripts/bootstrap-vm.sh
MSG
        exit 1
    fi
}

install_base_packages() {
    log "Instalando dependencias basicas"
    sudo_cmd apt-get update
    sudo_cmd apt-get install -y ca-certificates curl git
}

install_docker() {
    if command_exists docker && docker compose version >/dev/null 2>&1; then
        log "Docker e Docker Compose ja estao disponiveis"
        docker --version || true
        docker compose version || true
        return
    fi

    log "Instalando Docker Engine e Docker Compose plugin"
    sudo_cmd apt-get remove -y docker.io docker-compose docker-compose-v2 docker-doc podman-docker containerd runc >/dev/null 2>&1 || true
    sudo_cmd install -m 0755 -d /etc/apt/keyrings
    sudo_cmd curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    sudo_cmd chmod a+r /etc/apt/keyrings/docker.asc

    . /etc/os-release
    CODENAME="${UBUNTU_CODENAME:-$VERSION_CODENAME}"
    ARCH="$(dpkg --print-architecture)"

    printf 'Types: deb\nURIs: https://download.docker.com/linux/ubuntu\nSuites: %s\nComponents: stable\nArchitectures: %s\nSigned-By: /etc/apt/keyrings/docker.asc\n' "$CODENAME" "$ARCH" \
        | sudo_cmd tee /etc/apt/sources.list.d/docker.sources >/dev/null

    sudo_cmd apt-get update
    sudo_cmd apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    if command_exists systemctl; then
        sudo_cmd systemctl enable docker
        sudo_cmd systemctl start docker
    fi

    local target_user="${SUDO_USER:-${USER:-}}"
    if [ -n "$target_user" ] && [ "$target_user" != "root" ]; then
        sudo_cmd usermod -aG docker "$target_user" || true
        warn "Usuario '$target_user' adicionado ao grupo docker. Em um novo login, docker podera rodar sem sudo. Neste bootstrap uso sudo quando necessario."
    fi

    sudo_cmd docker --version
    sudo_cmd docker compose version
}

docker_cmd() {
    if docker ps >/dev/null 2>&1; then
        docker "$@"
    else
        sudo_cmd docker "$@"
    fi
}

compose_cmd() {
    docker_cmd compose "$@"
}

checkout_project() {
    resolve_repo_url

    if [ -d "$PROJECT_DIR/.git" ]; then
        log "Atualizando repositorio em $PROJECT_DIR"
        git -C "$PROJECT_DIR" fetch origin "$BRANCH"
        git -C "$PROJECT_DIR" checkout "$BRANCH"
        git -C "$PROJECT_DIR" pull --ff-only origin "$BRANCH"
        return
    fi

    if [ -e "$PROJECT_DIR" ] && [ -n "$(find "$PROJECT_DIR" -mindepth 1 -maxdepth 1 2>/dev/null || true)" ]; then
        echo "O diretorio $PROJECT_DIR ja existe e nao esta vazio, mas nao e um repositorio git." >&2
        exit 1
    fi

    log "Clonando repositorio em $PROJECT_DIR"
    mkdir -p "$(dirname "$PROJECT_DIR")"
    git clone --branch "$BRANCH" "$REPO_URL" "$PROJECT_DIR"
}

prepare_permissions() {
    log "Preparando permissoes de storage/cache/vendor"
    mkdir -p "$PROJECT_DIR/storage/framework/cache" "$PROJECT_DIR/storage/framework/sessions" "$PROJECT_DIR/storage/framework/views" "$PROJECT_DIR/bootstrap/cache" "$PROJECT_DIR/vendor"
    sudo_cmd chmod -R 777 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache" "$PROJECT_DIR/vendor"
    sudo_cmd chown -R www-data:www-data "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache" "$PROJECT_DIR/vendor"
}

start_environment() {
    local env_name="$1"
    local app_service="$2"
    local compose_dir="$PROJECT_DIR/docker/$env_name"

    log "Subindo ambiente $env_name"
    cd "$compose_dir"
    compose_cmd up -d --build

    log "Garantindo autoload otimizado em $app_service"
    compose_cmd exec -T "$app_service" composer dump-autoload --no-dev --optimize

    if [ "$RUN_MIGRATIONS" = "true" ]; then
        log "Executando migrations em $env_name"
        compose_cmd exec -T "$app_service" php artisan migrate --force
    fi

    log "Otimizando caches Laravel em $env_name"
    compose_cmd exec -T "$app_service" php artisan config:clear
    compose_cmd exec -T "$app_service" php artisan route:clear
    compose_cmd exec -T "$app_service" php artisan view:clear
    compose_cmd exec -T "$app_service" php artisan route:cache
    compose_cmd exec -T "$app_service" php artisan view:cache
}

show_status() {
    log "Status final dos containers"
    docker_cmd ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'

    cat <<MSG

Ambientes esperados:
- Homologacao: http://IP-DA-VM:8080
- Producao:    http://IP-DA-VM:8081

Comandos uteis:
cd $PROJECT_DIR/docker/homolog && docker compose logs -f
cd $PROJECT_DIR/docker/prod && docker compose logs -f
MSG
}

main() {
    require_apt_linux
    install_base_packages

    if [ "$INSTALL_DOCKER" = "true" ]; then
        install_docker
    fi

    checkout_project
    prepare_permissions

    if [ "$START_HOMOLOG" = "true" ]; then
        start_environment homolog app-homolog
    fi

    if [ "$START_PROD" = "true" ]; then
        start_environment prod app-prod
    fi

    show_status
}

main "$@"