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
php artisan migrate --seed   # cria o catálogo e as contas de demonstração
php artisan storage:link     # expõe as imagens enviadas pelo painel em /storage
php artisan serve
```

A aplicação fica disponível em `http://localhost:8000`.

Contas de demonstração (senha `password`): `admin@example.com` (acessa `/admin`, onde fica o
cadastro de produtos em `/admin/produtos`) e `cliente@example.com`. Para dar acesso administrativo a uma conta existente:

```bash
php artisan user:promote email@exemplo.com
```

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
app/Enums              enums do domínio (ProductSort na query string, Role do usuário)
app/Http/Requests      validação e regras de entrada (login com rate limit, cadastro)
app/Policies           autorização do back office (ProductPolicy)
app/Livewire           componentes Livewire (Shop\ProductCatalog: vitrine; Admin\Products: tabela e editor)
app/Livewire/Forms     Form Objects do Livewire (ProductForm: validação e persistência do produto)
app/Support            conversões de entrada sem dependência de framework (PriceInput)
app/Models             Category e Product (+ concerns reutilizáveis em app/Models/Concerns)
database/seeders       CatalogSeeder idempotente com dados de demonstração
resources/views/site   layout e páginas públicas da loja (shop, página de produto)
resources/views/auth   login e cadastro
resources/views/admin  painel administrativo e páginas de produtos
resources/views/livewire views dos componentes Livewire
resources/views/errors página 404 no layout da loja
routes/web.php         rotas públicas, de autenticação e o grupo /admin
tests/Feature          testes HTTP, de domínio e de componentes Livewire
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
- **Dinheiro em centavos (`price_cents` inteiro):** evita erro de arredondamento de ponto flutuante;
  a formatação em BRL fica no model até o value object `Money` chegar com o carrinho.
- **Slug estável e único:** gerado a partir do nome na criação (com sufixo `-2`, `-3`… em colisões,
  considerando registros na lixeira, já que o índice único também os cobre) e não muda quando o nome
  é editado, para não quebrar URLs públicas. O model resolve route binding pelo slug.
- **Catálogo com soft delete e FK `restrict`:** produtos saem da vitrine sem perder histórico, e o
  banco impede apagar uma categoria que ainda tem produtos.
- **Scopes do domínio:** `Product::active()` já exclui produtos de categorias inativas, então a
  vitrine não precisa repetir essa regra; `inCategory()` aceita model, id ou slug.
- **Estado da vitrine na URL:** busca, categoria e ordenação ficam em `?busca=&categoria=&ordem=`
  (`#[Url]` do Livewire), então qualquer filtro pode ser compartilhado por link e funciona no primeiro
  render sem JavaScript. Valores desconhecidos de `ordem` caem no padrão (`ProductSort::fromQuery`) em
  vez de gerar erro, e mudar qualquer filtro volta para a página 1.
- **Busca portátil e segura:** `Product::search()` compara `lower(coluna) LIKE ? ESCAPE '!'` em nome e
  SKU, com os curingas `%` e `_` digitados pelo usuário escapados; o mesmo SQL vale para PostgreSQL e
  SQLite, e a ordenação sempre desempata por `id` para a paginação ser determinística.
- **Página de produto só para itens visíveis:** `/produto/{slug}` resolve pelo scope `active()`, então
  produto inativo, de categoria inativa ou na lixeira responde 404 em vez de vazar pela URL.
- **Autenticação própria em vez de starter kit:** login, cadastro e logout cabem em dois controllers e
  dois Form Requests, sem trazer scaffolding de UI que o projeto não usaria. O login regenera a sessão
  (contra session fixation), o logout invalida sessão e token CSRF, e as tentativas são limitadas a 5
  por combinação e-mail + IP; o cadastro também passa por `throttle`.
- **Papéis como enum, fora do mass assignment:** `users.role` guarda o backed value de `App\Enums\Role`
  (`admin`/`customer`, padrão `customer`). A coluna não é `fillable`, então um campo `role` injetado no
  formulário de cadastro é ignorado; promover alguém exige `User::promoteToAdmin()` ou o comando
  `user:promote`.
- **Autorização em duas camadas:** o Gate `access-admin` protege o grupo `/admin` via middleware `can`
  (visitante vai para o login e volta para a página pedida; cliente recebe 403), e a `ProductPolicy`
  define as permissões finas do catálogo que o CRUD administrativo vai usar.
- **CRUD do back office com Livewire Form Object:** criação e edição usam o mesmo `ProductForm`, que
  guarda o texto digitado (preço como `1.299,90`, estoque como string) e só converte para valores do
  domínio depois de validar. O SKU é normalizado antes da regra `unique` (que também considera itens na
  lixeira, como o índice do banco), e `image_path` fica fora do mass assignment.
- **Autorização em cada ação Livewire:** as rotas `/admin/produtos` usam `can` com a `ProductPolicy`, mas
  ações Livewire chegam em outra requisição; por isso `mount`, `save`, `toggleActive` e `delete`
  chamam `Gate::authorize` de novo (um admin rebaixado no meio da sessão recebe 403 na próxima ação).
- **Upload de imagem sem arquivos órfãos:** JPG, PNG ou WebP de até 2 MB, gravados no disco `public`
  em `products/`. Se salvar o produto falhar, o arquivo novo é apagado; a imagem anterior só é removida
  depois que a nova foi persistida. Produtos sem imagem usam um placeholder (`Product::image_url`).
- **Preço digitado em notação brasileira:** `App\Support\PriceInput` aceita `1299`, `1.299,90` e
  `1299.90`, rejeita formatos ambíguos (três casas decimais, separadores trocados) e limita o valor ao
  que cabe em `price_cents`. Vira parte do value object `Money` quando o carrinho chegar.
- **Desativar ≠ remover:** "Desativar" tira o produto da vitrine e mantém o cadastro editável;
  "Remover" faz soft delete, preservando histórico para pedidos futuros.
- **Regras de arquitetura como teste:** o Pest `arch()` impede `dd()`/`dump()` esquecidos e mantém a
  convenção dos controllers.

## Licença

MIT
