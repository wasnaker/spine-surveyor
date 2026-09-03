# spine-customer

Modul Customer untuk platform **laravelspine** — data customer + kantor
cabang (branch). NPWP/VAT dipakai bersama module lain via
**[spine-vat](https://github.com/wasnaker/spine-vat)**.

## Entity

- **Customer** — entitas utama. Field: `code` (unique), `name`, `email`,
  `phone`, `parent_vat_number` (NPWP HO, nullable), `is_active`,
  `ulid`, soft delete. HasMany Branch + MorphMany Vat.
- **Branch** — kantor cabang / site / pabrik. Field: `customer_id` (FK),
  `code` (nullable), `name`, `address`, `phone`, `vat_id` (FK ke vats,
  nullable), `is_active`. BelongsTo Customer + BelongsTo Vat.

## Dependensi

- **wajib**: [`wasnaker/spine-vat`](https://github.com/wasnaker/spine-vat) — tabel `vats` di-own module itu; Customer & Branch.attach NPWP via `VatService::findOrCreate()`.

## API

```
GET    /api/v1/customers                  customer:view
POST   /api/v1/customers                  customer:create
GET    /api/v1/customers/{id}             customer:view
PUT    /api/v1/customers/{id}             customer:edit
DELETE /api/v1/customers/{id}             customer:delete
GET    /api/v1/customers/{id}/activity-logs   customer:view
GET    /api/v1/customers/{id}/branches        branch:view   (nested)

GET    /api/v1/branches                   branch:view
POST   /api/v1/branches                   branch:create
GET    /api/v1/branches/{id}              branch:view
PUT    /api/v1/branches/{id}              branch:edit
DELETE /api/v1/branches/{id}              branch:delete
GET    /api/v1/branches/{id}/activity-logs    branch:view
```

Endpoint Vat ada di module spine-vat (`/api/v1/vats`).

## RBAC

- **Permission (8)**: `customer:{view,create,edit,delete}` + `branch:{view,create,edit,delete}`
- **Role (3)**:
  - `customer`             → `customer:view` (read-only)
  - `customer-branch-admin`→ `customer:view` + `branch:*`
  - `customer-admin`       → `customer:*` + `branch:*`
- **Grants**: `staff` → `customer:view` + `branch:view`

Sync: `php artisan spine:rbac:sync`.

## Lifecycle

`Customer` & `Branch` pakai `HasLifecycleHooks` → listener log activity.
Vat lifecycle di-handle module `spine-vat`.

## Menu

Item menu **Customers** dengan `permission: customer:view` — auto-hidden
untuk user tanpa permission.

## Instalasi (konsumen laravelspine)

1. Install `spine-vat` dulu (module dependensi):
   ```bash
   # taruh di modules/Vat/ dan tambahkan 'Vat' ke modules_statuses.json
   ```
2. Install `spine-customer`:
   ```bash
   # taruh di modules/Customer/, tambahkan 'Customer' ke modules_statuses.json
   ```
3. `composer dump-autoload`
4. `php artisan migrate`  → membuat tabel `vats`, `customers`, `branches` berurutan
5. `php artisan spine:rbac:sync`

## v1 → v2 changelog

- Vat diekstrak ke module terpisah (wasnaker/spine-vat).
- `composer.json` & `module.json` declare dependensi `spine-vat`.
- `CustomerController` auto-attach `parent_vat_number` ke Vat via `VatService`.
