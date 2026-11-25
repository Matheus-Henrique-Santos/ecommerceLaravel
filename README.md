# Ecommerce Laravel

[![CI](https://github.com/Matheus-Henrique-Santos/ecommerceLaravel/actions/workflows/ci.yml/badge.svg)](https://github.com/Matheus-Henrique-Santos/ecommerceLaravel/actions/workflows/ci.yml)

Loja virtual construída com Laravel e Livewire, pensada como um projeto de referência:
domínio de e-commerce completo (catálogo, carrinho, checkout, pagamento, cupons e API),
com arquitetura simples de manter, testes automatizados e pipeline de CI.

O andamento está em [ROADMAP.md](ROADMAP.md).

## Stack

| Camada        | Tecnologia                         |
|---------------|------------------------------------|
| Linguagem     | PHP 8.3+                           |
| Framework     | Laravel 13                         |
| UI reativa    | Livewire + Blade                   |
| Banco         | PostgreSQL 16 (SQLite em memória nos testes locais) |
| Testes        | Pest (feature, unit e arquitetura) |
| Estilo        | Laravel Pint (preset `laravel`)    |
| CI            | GitHub Actions                     |

## Como rodar localmente

Pré-requisitos: PHP 8.3+ com `pdo_pgsql`, Composer, Node 20+ e um PostgreSQL acessível.

```bash
git clone https://github.com/Matheus-Henrique-Santos/ecommerceLaravel.git
cd ecommerceLaravel
composer install
cp .env.example .env          # ajuste DB_* para o seu Postgres
php artisan key:generate
php artisan migrate
php artisan serve
```

A aplicação fica disponível em `http://localhost:8000`.

## Testes e qualidade

```bash
php artisan test        # suíte Pest (usa SQLite em memória por padrão)
composer lint           # aplica o Pint
composer lint:check     # só verifica o estilo, como no CI
```

O CI roda em todo push e pull request para a `main`:

- **Pint** em modo `--test` (falha se houver arquivo fora do padrão);
- **Pest** contra um PostgreSQL 16 real, em PHP 8.3 e 8.4, após rodar as migrations.

## Estrutura

```
app/Http/Controllers   controllers finos (single action quando possível)
resources/views/site   layout e páginas públicas da loja
resources/views/errors página 404 no layout da loja
routes/web.php         rotas públicas (páginas estáticas via Route::view)
tests/Feature          testes HTTP das páginas
tests/Unit             testes unitários e regras de arquitetura (Pest arch)
```

## Decisões técnicas

- **Esqueleto novo em vez de upgrade incremental (10 → 13):** o projeto tinha pouco código de domínio,
  então recriar sobre o esqueleto atual (bootstrap enxuto em `bootstrap/app.php`, sem `Kernel`s e
  providers legados) foi mais barato e deixou a base sem resíduos das versões antigas.
- **PostgreSQL no CI, SQLite nos testes locais:** a suíte roda rápido sem dependências na máquina do
  desenvolvedor, enquanto o CI garante compatibilidade com o banco de produção.
- **Páginas estáticas com `Route::view`:** controllers que só retornavam uma view foram removidos;
  controllers voltam a existir quando houver lógica real.
- **Regras de arquitetura como teste:** o Pest `arch()` impede `dd()`/`dump()` esquecidos e mantém a
  convenção dos controllers.

## Licença

MIT
