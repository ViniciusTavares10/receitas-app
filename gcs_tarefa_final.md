# Gerência de Configuração de Software — Tarefa Final 2026/A

## Stack Escolhida

| Camada | Tecnologia |
|---|---|
| Linguagem / Framework | PHP 8.x + Laravel |
| Banco de Dados | PostgreSQL |
| Versionamento de Código | Git + GitHub |
| Controle de Mudança | GitHub Issues + GitHub Projects |
| CI/CD | GitHub Actions |
| Análise de Qualidade | Laravel Pint + PHPStan |
| Testes Automatizados | PHPUnit (embutido no Laravel) |
| Versionamento de BD | Laravel Migrations + Flyway (opcional) |
| Contêineres | Docker + Docker Compose |
| Ambientes | Homologação e Produção via Docker na VM Linux |

---

## Arquitetura Geral

```
[GitHub Issues]         ← Controle de Mudança (A)
      |
[GitHub Repositório]    ← Versionamento (C) + Implementação (B)
      |
[GitHub Actions]        ← CI: Testes (D) + Qualidade (E) + Build (F)
      |
      |──→ [Container Homolog]   ← Atualização Homologação (F) + (H)
      |──→ [Container Prod]      ← Atualização Produção (G) + (H)

VM Linux (Univates)
  └── Docker
        ├── app-homolog   (Laravel + PHP-FPM)
        ├── db-homolog    (PostgreSQL)
        ├── app-prod      (Laravel + PHP-FPM)
        └── db-prod       (PostgreSQL)
```

---

## Pré-requisitos na VM Linux

```bash
# Atualizar o sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER
newgrp docker

# Verificar
docker --version
docker compose version
```

---

## Estrutura de Diretórios do Projeto

```
minha-app/
├── app/
├── database/
│   └── migrations/        ← Versionamento do banco (Laravel Migrations)
├── tests/
│   ├── Unit/              ← Testes unitários
│   └── Feature/           ← Testes de feature (mínimo 20 testes no total)
├── docker/
│   ├── homolog/
│   │   ├── docker-compose.yml
│   │   └── .env.homolog
│   └── prod/
│       ├── docker-compose.yml
│       └── .env.prod
├── Dockerfile
├── phpstan.neon           ← Configuração do PHPStan
├── .github/
│   └── workflows/
│       └── ci.yml         ← Pipeline GitHub Actions
└── .env.example
```

---

## A) Controle de Mudança — GitHub Issues

1. No repositório GitHub, acesse **Issues → New Issue**
2. Crie um template de issue em `.github/ISSUE_TEMPLATE/mudanca.md`:

```markdown
---
name: Registro de Mudança
about: Registrar uma nova mudança no sistema
---

## Descrição da Mudança
<!-- Descreva o que será alterado -->

## Motivação
<!-- Por que essa mudança é necessária? -->

## Impacto esperado
- [ ] Frontend
- [ ] Backend
- [ ] Banco de Dados

## Critérios de aceite
- [ ] Testes passando
- [ ] Qualidade de código aprovada
- [ ] Deploy em Homologação validado
```

3. Use **GitHub Projects** (kanban) para rastrear: `Backlog → Em andamento → Em revisão → Concluído`

---

## B) Implementação

- Desenvolva localmente em uma branch criada a partir da issue:

```bash
# Criar branch vinculada à issue (ex: issue #5)
git checkout -b feature/5-nome-da-mudanca
```

- Ao concluir, abra um **Pull Request** referenciando a issue (`Closes #5` na descrição).

---

## C) Versionamento — Git + GitHub

```bash
# Clonar o repositório na VM
git clone https://github.com/seu-usuario/seu-repo.git
cd seu-repo

# Fluxo básico de versionamento
git checkout -b feature/nome-da-feature
git add .
git commit -m "feat: descrição da mudança (#numero-da-issue)"
git push origin feature/nome-da-feature
# → Abrir Pull Request no GitHub
```

### Versionamento do Banco de Dados — Laravel Migrations

```bash
# Criar uma migration
php artisan make:migration add_coluna_tabela --table=nome_tabela

# Exemplo de migration
# database/migrations/2026_06_01_000000_add_status_to_orders_table.php
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('status')->default('pending')->after('id');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
```

> Toda alteração de banco **obrigatoriamente** deve ser feita via migration, nunca manualmente.

---

## D) Testes Automatizados — PHPUnit

### Estrutura mínima (20 testes)

Distribua os testes entre `Unit` e `Feature`. Exemplo de organização:

```
tests/
├── Unit/
│   ├── UserTest.php           (5 testes)
│   ├── OrderTest.php          (5 testes)
│   └── ProductTest.php        (3 testes)
└── Feature/
    ├── AuthTest.php           (4 testes)
    └── OrderFlowTest.php      (3 testes)
```

### Exemplo de teste unitário

```php
// tests/Unit/UserTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_user_has_name(): void
    {
        $user = new User(['name' => 'João']);
        $this->assertEquals('João', $user->name);
    }

    public function test_user_email_is_required(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        // lógica de validação
    }
}
```

### Executar testes com estatísticas

```bash
# Rodar todos os testes com relatório detalhado
php artisan test --coverage

# Ou via PHPUnit diretamente com estatísticas
./vendor/bin/phpunit --testdox --coverage-text --coverage-html=coverage-report/
```

O relatório HTML fica em `coverage-report/index.html` com estatísticas completas de cobertura.

### Configuração no `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory>app</directory>
        </include>
        <report>
            <html outputDirectory="coverage-report"/>
            <text outputFile="php://stdout" showUncoveredFiles="false"/>
        </report>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

---

## E) Análise de Qualidade de Código

### Laravel Pint (formatação)

```bash
composer require laravel/pint --dev

# Executar
./vendor/bin/pint --test   # apenas verifica (não altera)
./vendor/bin/pint          # corrige automaticamente
```

### PHPStan (análise estática)

```bash
composer require nunomaduro/larastan --dev

# phpstan.neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon
parameters:
    paths:
        - app
    level: 5

# Executar
./vendor/bin/phpstan analyse
```

---

## H) Criação dos Ambientes com Docker

### Dockerfile da aplicação

```dockerfile
# Dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### Docker Compose — Homologação

```yaml
# docker/homolog/docker-compose.yml
services:
  app-homolog:
    build:
      context: ../../
      dockerfile: Dockerfile
    container_name: app-homolog
    env_file: .env.homolog
    volumes:
      - ../../:/var/www/html
    depends_on:
      - db-homolog
    networks:
      - homolog-net

  nginx-homolog:
    image: nginx:alpine
    container_name: nginx-homolog
    ports:
      - "8080:80"
    volumes:
      - ../../:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app-homolog
    networks:
      - homolog-net

  db-homolog:
    image: postgres:15
    container_name: db-homolog
    environment:
      POSTGRES_DB: app_homolog
      POSTGRES_USER: homolog_user
      POSTGRES_PASSWORD: homolog_pass
    volumes:
      - db-homolog-data:/var/lib/postgresql/data
    networks:
      - homolog-net

volumes:
  db-homolog-data:

networks:
  homolog-net:
    driver: bridge
```

### Docker Compose — Produção

```yaml
# docker/prod/docker-compose.yml
services:
  app-prod:
    build:
      context: ../../
      dockerfile: Dockerfile
    container_name: app-prod
    env_file: .env.prod
    depends_on:
      - db-prod
    networks:
      - prod-net

  nginx-prod:
    image: nginx:alpine
    container_name: nginx-prod
    ports:
      - "80:80"
    volumes:
      - ../../:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app-prod
    networks:
      - prod-net

  db-prod:
    image: postgres:15
    container_name: db-prod
    environment:
      POSTGRES_DB: app_prod
      POSTGRES_USER: prod_user
      POSTGRES_PASSWORD: prod_pass_segura
    volumes:
      - db-prod-data:/var/lib/postgresql/data
    networks:
      - prod-net

volumes:
  db-prod-data:

networks:
  prod-net:
    driver: bridge
```

### Subir os ambientes (primeira vez)

```bash
# Homologação
cd docker/homolog
docker compose up -d --build
docker compose exec app-homolog php artisan migrate --seed

# Produção
cd docker/prod
docker compose up -d --build
docker compose exec app-prod php artisan migrate --seed
```

---

## F e G) Atualização dos Ambientes (Semi-automatizado)

### Script de atualização — Homologação

```bash
#!/bin/bash
# scripts/deploy-homolog.sh

echo "=== Deploy Homologação ==="
cd /caminho/para/seu-repo

git pull origin main

cd docker/homolog
docker compose exec app-homolog composer install --no-dev --optimize-autoloader
docker compose exec app-homolog php artisan migrate --force
docker compose exec app-homolog php artisan config:cache
docker compose exec app-homolog php artisan route:cache
docker compose exec app-homolog php artisan view:cache

echo "=== Deploy Homologação concluído ==="
```

### Script de atualização — Produção

```bash
#!/bin/bash
# scripts/deploy-prod.sh

echo "=== Deploy Produção ==="
cd /caminho/para/seu-repo

git pull origin main

cd docker/prod
docker compose exec app-prod composer install --no-dev --optimize-autoloader
docker compose exec app-prod php artisan migrate --force
docker compose exec app-prod php artisan config:cache
docker compose exec app-prod php artisan route:cache
docker compose exec app-prod php artisan view:cache

echo "=== Deploy Produção concluído ==="
```

```bash
# Tornar executável
chmod +x scripts/deploy-homolog.sh
chmod +x scripts/deploy-prod.sh

# Executar quando necessário
./scripts/deploy-homolog.sh
./scripts/deploy-prod.sh
```

---

## Pipeline CI/CD — GitHub Actions

```yaml
# .github/workflows/ci.yml
name: CI Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  testes:
    name: Testes Automatizados
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: app_test
          POSTGRES_USER: test_user
          POSTGRES_PASSWORD: test_pass
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, pdo_pgsql, zip
          coverage: xdebug

      - name: Instalar dependências
        run: composer install --no-interaction --prefer-dist

      - name: Copiar .env
        run: cp .env.example .env.testing

      - name: Gerar chave da aplicação
        run: php artisan key:generate --env=testing

      - name: Executar migrations
        run: php artisan migrate --env=testing --force
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: app_test
          DB_USERNAME: test_user
          DB_PASSWORD: test_pass

      - name: Executar testes com cobertura
        run: ./vendor/bin/phpunit --testdox --coverage-text
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: app_test
          DB_USERNAME: test_user
          DB_PASSWORD: test_pass

  qualidade:
    name: Análise de Qualidade
    runs-on: ubuntu-latest
    needs: testes

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Instalar dependências
        run: composer install --no-interaction --prefer-dist

      - name: Laravel Pint (formatação)
        run: ./vendor/bin/pint --test

      - name: PHPStan (análise estática)
        run: ./vendor/bin/phpstan analyse --no-progress
```

---

## Roteiro de Validação (Apresentação)

Siga esta sequência durante a apresentação ao professor:

| # | Etapa | Comando / Ação |
|---|---|---|
| 1 | Mostrar ambientes vazios | `docker ps` — nenhum container rodando |
| 2 | Criar ambiente Homologação | `cd docker/homolog && docker compose up -d --build` |
| 3 | Criar ambiente Produção | `cd docker/prod && docker compose up -d --build` |
| 4 | App funcionando em Homolog | Acessar `http://IP-VM:8080` |
| 5 | App funcionando em Prod | Acessar `http://IP-VM:80` |
| 6 | Registrar mudança | Criar Issue no GitHub com template |
| 7 | Implementar mudança | Código + nova Migration |
| 8 | Versionar | `git add . && git commit && git push` |
| 9 | Executar CI | Pipeline automático no GitHub Actions (testes + qualidade) |
| 10 | Atualizar Homologação | `./scripts/deploy-homolog.sh` |
| 11 | Validar Homolog + BD | Acessar app e verificar mudança no banco |
| 12 | Atualizar Produção | `./scripts/deploy-prod.sh` |
| 13 | Validar Prod + BD | Acessar app e verificar mudança no banco |

---

## Checklist Final

- [ ] Repositório no GitHub configurado
- [ ] Issues e Projects ativos (controle de mudança)
- [ ] Dockerfile criado e funcionando
- [ ] Docker Compose de Homologação funcional
- [ ] Docker Compose de Produção funcional
- [ ] Mínimo de 20 testes implementados
- [ ] Relatório de cobertura de testes gerado
- [ ] Laravel Pint configurado
- [ ] PHPStan configurado (nível ≥ 5)
- [ ] GitHub Actions pipeline passando
- [ ] Scripts de deploy criados e testados
- [ ] Migrations cobrindo todas as alterações de banco
- [ ] Documento de arquitetura (diagrama) elaborado

---

## Documento de Arquitetura (entregável)

O diagrama deve conter:

- **VM Linux** (Univates) hospedando todos os containers
- **Docker** como runtime de containers
- **Containers**: app-homolog, db-homolog, app-prod, db-prod, nginx-homolog, nginx-prod
- **GitHub**: repositório, Issues/Projects, Actions
- **Tecnologias**: PHP 8.2, Laravel, PostgreSQL 15, Nginx
- **Fluxo**: Issue → Branch → Commit → PR → GitHub Actions (PHPUnit + PHPStan + Pint) → Deploy script → Homolog → Prod

