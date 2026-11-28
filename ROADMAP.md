# Roadmap

Entregas pequenas e incrementais; cada item fecha com testes e CI verde.

- [x] 1. Modernização: Laravel 13 + PHP 8.3, PostgreSQL, Pest, Pint, CI, README/ROADMAP, remoção de código morto
- [x] 2. Catálogo: `Category`/`Product` (preço em centavos, slug, SKU, estoque), factories e seeders
- [x] 3. Vitrine Livewire: listagem paginada, filtro por categoria, busca, ordenação e página de produto
- [x] 4. Autenticação + papéis (admin/cliente) com Policies
- [x] 5. Admin: CRUD de produtos (Livewire Form Objects, upload de imagem)
- [x] 6. Carrinho: `CartService` (sessão para visitante, banco para logado, merge no login) e value object `Money`
- [ ] 7. Checkout: `Order`/`OrderItem` em transação, snapshot de preço, lock de estoque, status como enum
- [ ] 8. Pagamento: contrato `PaymentGateway` + driver fake + webhook idempotente (HMAC) processado em fila
- [ ] 9. Cupons (percentual/fixo, validade, valor mínimo)
- [ ] 10. Eventos e notificações: `OrderPlaced` → e-mail em fila
- [ ] 11. API REST v1 (Sanctum + API Resources + documentação OpenAPI)
- [ ] 12. Larastan nível 6+, Docker Compose e README final com arquitetura e screenshots
