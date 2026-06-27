# Arquitetura de Configuracao e Deploy

```text
GitHub Issues/Projects
  -> Branch + Commits + Pull Request
  -> GitHub Actions
       - PHPUnit com PostgreSQL 15
       - Laravel Pint
       - PHPStan/Larastan nivel 5
  -> VM Linux Univates
       -> Docker
            -> Homologacao
                 - nginx-homolog :8080
                 - app-homolog PHP 8.3 FPM + Laravel
                 - db-homolog PostgreSQL 15
            -> Producao
                 - nginx-prod :8081
                 - app-prod PHP 8.3 FPM + Laravel
                 - db-prod PostgreSQL 15
```

## Fluxo de mudanca

1. Registrar a demanda usando o template `Registro de Mudanca`.
2. Criar uma branch a partir da issue.
3. Implementar a alteracao, incluindo migrations para qualquer mudanca de banco.
4. Abrir Pull Request referenciando a issue.
5. Validar o pipeline de CI com testes, Pint e PHPStan.
6. Atualizar homologacao com `scripts/deploy-homolog.sh`.
7. Validar em homologacao.
8. Atualizar producao com `scripts/deploy-prod.sh`.

## Ambientes

Homologacao fica exposta em `http://IP-VM:8080` e usa os containers
`app-homolog`, `nginx-homolog` e `db-homolog`.

Producao fica exposta em `http://IP-VM:8081` e usa os containers
`app-prod`, `nginx-prod` e `db-prod`.

As senhas e chaves dos arquivos `docker/**/.env.*` sao valores de exemplo para
a apresentacao. Antes de usar em producao real, substitua `APP_KEY`,
`DB_PASSWORD` e qualquer credencial sensivel.
