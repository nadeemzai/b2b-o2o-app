# B2B O2O Platform — API

Laravel 13 REST API for the Oz Tech / Huashu B2B wholesale marketplace.

---

## Quick setup

```bash
# 1. Install a fresh Laravel 13 project in the current folder
composer create-project laravel/laravel:^13.0 .

# 2. Drop all files from this zip into the project root
#    (overwrite when prompted — our versions contain project-specific wiring)

# 3. Install dependencies
composer install

# 4. Environment
cp .env.example .env
php artisan key:generate

# 5. Create the PostgreSQL database
#    psql -U postgres -c "CREATE DATABASE b2b_o2o;"
#    Then update DB_* values in .env

# 6. Migrate + seed
php artisan migrate --fresh
php artisan db:seed

# 7. Serve
php artisan serve
```

> **PostgreSQL is required.** The schema uses `GENERATED ALWAYS AS … STORED` computed columns (`qty_available`, `line_total_pkr`) and `ilike` for case-insensitive search. MySQL is not supported without rewriting the migrations.

---

## Seeded credentials

| Role      | Email                          | Password        |
|-----------|-------------------------------|-----------------|
| Admin     | admin@oztech.com              | Admin@12345     |
| Manager   | manager@lahore-ts.com         | Manager@12345   |
| Operator  | operator@lahore-ts.com        | Operator@12345  |
| Rider     | rider@lahore-ts.com           | Rider@12345     |
| Retailer 1| ahmed.general@retailer.com    | Retailer@12345  |
| Retailer 2| tariq.traders@retailer.com    | Retailer@12345  |

---

## API endpoints (23 total)

### Public
| Method | URI | Description |
|--------|-----|-------------|
| POST | `/api/auth/login` | Obtain Bearer token |
| POST | `/api/auth/register` | Retailer self-registration |

### Auth (any role, token required)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/auth/me` | Authenticated user details |
| POST | `/api/auth/logout` | Revoke current token |

### Retailer (`role:retailer`)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/retailer/profile` | View own profile |
| PATCH | `/api/retailer/profile` | Update phone / address / business name |
| POST | `/api/retailer/kyc/upload` | Upload CNIC + business docs |
| GET | `/api/retailer/catalogue` | Browse products (store-scoped) |
| GET | `/api/retailer/catalogue/{product}` | Product detail |
| GET | `/api/retailer/orders` | Own order history |
| POST | `/api/retailer/orders` | Place new order |
| GET | `/api/retailer/orders/{order}` | Order detail |
| POST | `/api/retailer/orders/{order}/cancel` | Cancel pending order |

### Admin (`role:admin`)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/admin/retailers` | KYC queue (filterable by status) |
| POST | `/api/admin/retailers/{retailer}/approve` | Approve KYC |
| POST | `/api/admin/retailers/{retailer}/reject` | Reject with reason |

### Store Staff (`role:store_staff`)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/store/orders` | Order queue |
| GET | `/api/store/orders/{order}` | Order detail |
| POST | `/api/store/orders/{order}/status` | Update status (confirmed → ready → dispatched) |
| POST | `/api/store/orders/{order}/deliver` | Mark delivered + collect COD amount |
| GET | `/api/store/stock` | Stock levels (filterable, low-stock flag) |
| POST | `/api/store/stock/inbound` | Record inbound stock |
| POST | `/api/store/stock/adjust` | Manual adjustment (damage / count correction) |

---

## Project structure

```
app/
├── Exceptions/
│   ├── Handler.php                        ← global JSON envelope { message, errors?, details? }
│   └── InsufficientStockException.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php                 ← base
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── RegisterController.php
│   │       ├── Admin/
│   │       │   └── AdminRetailerController.php
│   │       ├── Retailer/
│   │       │   ├── CatalogueController.php
│   │       │   ├── KycController.php
│   │       │   ├── OrderController.php
│   │       │   └── RetailerProfileController.php
│   │       └── Store/
│   │           ├── StoreOrderController.php
│   │           └── StoreStockController.php
│   ├── Middleware/
│   │   └── EnsureRole.php
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── PlaceOrderRequest.php          ← mergedItems() deduplication
│   │   ├── UpdateOrderStatusRequest.php
│   │   └── DeliverOrderRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── ProductResource.php
│       ├── RetailerResource.php
│       ├── OrderResource.php
│       └── OrderItemResource.php
├── Models/                                ← 13 Eloquent models
├── Policies/
│   ├── RetailerPolicy.php
│   ├── StoreStaffPolicy.php
│   └── AdminRetailerPolicy.php
├── Providers/
│   └── AppServiceProvider.php             ← policy registrations
└── Services/
    ├── OrderService.php                   ← atomic place / cancel / deliver
    └── OrderStatusTransitionService.php

bootstrap/
└── app.php                                ← role middleware + exception handler wired here

config/
└── filesystems.php                        ← 'private' disk for KYC uploads

database/
├── factories/   (4 factories)
├── migrations/  (8 migrations)
└── seeders/     (6 seeders)

routes/
└── api.php                                ← all 23 endpoints
```

---

## Key design decisions

| Decision | Detail |
|---|---|
| Stock locking | `SELECT FOR UPDATE` via `lockForUpdate()` in DB transaction |
| Stock accounting | `qty_available = qty_on_hand − qty_reserved` (PostgreSQL generated column) |
| Price freezing | `unit_price_pkr` copied into `order_items` at creation time |
| Line total | `line_total_pkr = qty × unit_price_pkr` (PostgreSQL generated column) |
| COD only | `payment_method` defaulted to `'cod'`; `collected_pkr` filled on delivery |
| Reservations | `stock_reservations` tracks soft-holds; consumed on delivery, released on cancel |
| Auth | Laravel Sanctum Bearer tokens; `role` enum enforced by `EnsureRole` middleware |
| KYC gate | `RetailerPolicy::placeOrder()` checks `kyc_status === 'approved'` |
| KYC storage | Private disk at `storage/app/private/kyc/{retailer_id}/` — never served publicly |
| Error envelope | All API errors: `{ message, errors?, details? }` via global `Handler` |

---

*Oz Tech — B2B O2O Platform v1 (Phase 1 → 3)*
