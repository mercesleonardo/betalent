# Betalent – API de Pagamentos Multi-Gateway

API RESTful para gerenciamento de pagamentos com múltiplos gateways. Realiza cobrança na ordem de prioridade configurada; em caso de falha em um gateway, tenta o próximo. Respostas em JSON, autenticação via Laravel Sanctum e controle de acesso por roles.

## Requisitos

- PHP 8.5+
- Composer
- MySQL 8.x
- Docker e Docker Compose (para rodar com Sail e mocks dos gateways)
- Extensões PHP: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML

## Instalação e execução

### 1. Clonar e instalar dependências

```bash
git clone <url-do-repositorio> betalent
cd betalent
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configurar ambiente

Edite o `.env` com o banco de dados e, se for usar Docker, as URLs dos gateways:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=betalent
DB_USERNAME=sail
DB_PASSWORD=password

# Gateways (com Docker use o nome do serviço)
GATEWAY_ONE_URL=http://gateways-mock:3001
GATEWAY_ONE_EMAIL=dev@betalent.tech
GATEWAY_ONE_TOKEN=FEC9BB078BF338F464F96B48089EB498

GATEWAY_TWO_URL=http://gateways-mock:3002
GATEWAY_TWO_TOKEN=tk_f2198cc671b5289fa856
GATEWAY_TWO_SECRET=3d15e8ed6131446ea7e3456728b1211f
```

Para rodar **fora do Docker** (mocks na máquina local):

```bash
docker run -d -p 3001:3001 -p 3002:3002 matheusprotzen/gateways-mock
```

E no `.env`:

```env
DB_HOST=127.0.0.1
DB_USERNAME=root
DB_PASSWORD=

GATEWAY_ONE_URL=http://localhost:3001
GATEWAY_TWO_URL=http://localhost:3002
```

### 3. Rodar com Docker (Sail)

Recomendado: sobe aplicação, MySQL e mocks dos gateways.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

A API fica em `http://localhost` (porta 80). Gateway 1 em `http://localhost:3001` e Gateway 2 em `http://localhost:3002` (portas expostas pelo compose).

### 4. Rodar sem Docker

```bash
php artisan migrate
php artisan serve
```

A API fica em `http://localhost:8000`. Garanta que os mocks estejam acessíveis nas URLs configuradas em `GATEWAY_ONE_URL` e `GATEWAY_TWO_URL`.

### 5. Testes

```bash
# Com Sail
./vendor/bin/sail artisan test

# Sem Sail
php artisan test
```

---

## Variáveis de ambiente

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `APP_URL` | URL base da aplicação | `http://localhost` |
| `DB_*` | Conexão MySQL | — |
| `GATEWAY_ONE_URL` | Base URL do Gateway 1 | `http://gateways-mock:3001` |
| `GATEWAY_ONE_EMAIL` | E-mail para login no Gateway 1 | `dev@betalent.tech` |
| `GATEWAY_ONE_TOKEN` | Token para login no Gateway 1 | (ver especificação do mock) |
| `GATEWAY_TWO_URL` | Base URL do Gateway 2 | `http://gateways-mock:3002` |
| `GATEWAY_TWO_TOKEN` | Header `Gateway-Auth-Token` | (ver especificação do mock) |
| `GATEWAY_TWO_SECRET` | Header `Gateway-Auth-Secret` | (ver especificação do mock) |

---

## Rotas da API

Base URL: `{APP_URL}/api` (ex.: `http://localhost/api`).

### Rotas públicas (sem autenticação)

#### Login

- **POST** `/api/login`
- **Body (JSON):**
  ```json
  {
    "email": "usuario@exemplo.com",
    "password": "senha123"
  }
  ```
- **Resposta 200:** `{ "token": "...", "user": { "id", "name", "email", "role" } }`
- **Resposta 422:** Erro de validação ou credenciais inválidas (`errors.email`).

#### Realizar compra (checkout)

- **POST** `/api/checkout`
- **Body (JSON):**
  ```json
  {
    "products": [
      { "id": 1, "quantity": 2 },
      { "id": 2, "quantity": 1 }
    ],
    "card_name": "Nome no Cartão",
    "card_email": "comprador@email.com",
    "card_number": "5569000000006063",
    "card_cvv": "010"
  }
  ```
  - `products`: array de `{ id, quantity }`; o **valor total é calculado no backend** a partir dos produtos e quantidades.
  - `card_number`: 16 dígitos; `card_cvv`: 3 ou 4 dígitos.
- **Resposta 201:** Transação criada (recurso da transação).
- **Resposta 422:** Validação ou falha de pagamento em todos os gateways (mensagem no body).

---

### Rotas privadas (requerem `Authorization: Bearer {token}`)

Obtenha o token em `POST /api/login` e envie no header:

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Permissões por role (resumo):

- **ADMIN:** todas as rotas.
- **MANAGER:** CRUD usuários, CRUD produtos, listar/ver clientes e transações.
- **FINANCE:** CRUD produtos, CRUD gateways, ativar/desativar e prioridade de gateways, reembolso; listar/ver clientes e transações.
- **USER:** listar/ver produtos, clientes e transações (sem alterar gateways, usuários ou reembolsar).

#### Usuários (CRUD) – Gate: `manage-users` (ADMIN, MANAGER)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/users` | Listar usuários |
| POST | `/api/users` | Criar usuário. Body: `name`, `email`, `password`, `role` (`admin` \| `manager` \| `finance` \| `user`) |
| GET | `/api/users/{id}` | Detalhe do usuário |
| PUT/PATCH | `/api/users/{id}` | Atualizar usuário |
| DELETE | `/api/users/{id}` | Remover usuário |

#### Produtos (CRUD) – Gate: `manage-products` (ADMIN, MANAGER, FINANCE)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/products` | Listar produtos (qualquer autenticado) |
| POST | `/api/products` | Criar produto. Body: `name`, `amount` (inteiro, centavos) |
| GET | `/api/products/{id}` | Detalhe do produto |
| PUT/PATCH | `/api/products/{id}` | Atualizar produto |
| DELETE | `/api/products/{id}` | Remover produto |

#### Gateways – Gate: `manage-finances` (ADMIN, FINANCE)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/gateways` | Listar gateways (ativos; cache) |
| POST | `/api/gateways` | Criar gateway. Body: `name` (único, ex. `GATEWAY_1`), `is_active`, `priority` |
| GET | `/api/gateways/{id}` | Detalhe do gateway |
| PUT/PATCH | `/api/gateways/{id}` | Atualizar (incl. ativar/desativar e alterar prioridade) |
| DELETE | `/api/gateways/{id}` | Remover gateway |

#### Clientes (somente leitura)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/clients` | Listar clientes |
| GET | `/api/clients/{id}` | Detalhe do cliente e todas as compras (transações) |

#### Transações

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/transactions` | Listar transações |
| GET | `/api/transactions/{id}` | Detalhe da transação |
| POST | `/api/transactions/{transaction}/refund` | Reembolso (Gate: `manage-finances`) |

- **Reembolso:** envia a solicitação ao gateway que processou a transação. Resposta 200 em sucesso; 422 com mensagem em caso de erro.

---

## Roles e permissões

| Role | Descrição | Permissões |
|------|-----------|------------|
| **admin** | Administrador | Todas as ações |
| **manager** | Gerente | CRUD usuários, CRUD produtos, listar/ver clientes e transações |
| **finance** | Financeiro | CRUD produtos, CRUD gateways, ativar/desativar e prioridade, reembolso, listar/ver clientes e transações |
| **user** | Usuário | Listar/ver produtos, clientes e transações |

---

## Estrutura do banco (resumo)

- **users:** id, name, email, password, role, timestamps, soft deletes
- **gateways:** id, name, is_active, priority, timestamps
- **clients:** id, name, email, timestamps
- **products:** id, name, amount (centavos), timestamps
- **transactions:** id, client_id, gateway_id (nullable), external_id, status, amount, card_last_numbers, timestamps
- **product_transaction:** transaction_id, product_id, quantity, historical_amount (pivot)

O nome do gateway na tabela `gateways` deve corresponder à chave em `config/gateways.php` (ex.: `GATEWAY_1`, `GATEWAY_2`) para o sistema resolver a implementação correta.

---

## Multi-gateway

- Gateways ativos são ordenados por `priority` (menor número = maior prioridade).
- No checkout, a cobrança é tentada nessa ordem; se um gateway falhar, o próximo é tentado.
- Se algum gateway retornar sucesso, a API responde com sucesso (201). Só há 422 quando todos falham.
- Novos gateways: criar classe em `App\Services\Gateways`, implementar a interface do provider e registrar em `config/gateways.php` e na tabela `gateways`.

---

## Licença

Projeto de teste. Consulte o repositório para mais informações.
