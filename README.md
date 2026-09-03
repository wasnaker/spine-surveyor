# spine-surveyor

Modul Surveyor untuk platform **laravelspine** — data surveyor + kantor
cabang (branch). NPWP/VAT dipakai bersama module lain via
**[spine-vat](https://github.com/wasnaker/spine-vat)**.

## Entity

- **Surveyor** — entitas utama. Field: `code` (unique), `name`, `email`,
  `phone`, `parent_vat_number` (NPWP HO, nullable), `is_active`,
  `ulid`, soft delete. HasMany Branch + MorphMany Vat.
- **Branch** — kantor cabang / site / pabrik. Field: `surveyor_id` (FK),
  `code` (nullable), `name`, `address`, `phone`, `vat_id` (FK ke vats,
  nullable), `is_active`. BelongsTo Surveyor + BelongsTo Vat.

## Dependensi

- **wajib**: [`wasnaker/spine-vat`](https://github.com/wasnaker/spine-vat) — tabel `vats` di-own module itu; Surveyor & Branch.attach NPWP via `VatService::findOrCreate()`.

## API

```
GET    /api/v1/surveyors                  surveyor:view
POST   /api/v1/surveyors                  surveyor:create
GET    /api/v1/surveyors/{id}             surveyor:view
PUT    /api/v1/surveyors/{id}             surveyor:edit
DELETE /api/v1/surveyors/{id}             surveyor:delete
GET    /api/v1/surveyors/{id}/activity-logs   surveyor:view
GET    /api/v1/surveyors/{id}/branches        branch:view   (nested)

GET    /api/v1/branches                   branch:view
POST   /api/v1/branches                   branch:create
GET    /api/v1/branches/{id}              branch:view
PUT    /api/v1/branches/{id}              branch:edit
DELETE /api/v1/branches/{id}              branch:delete
GET    /api/v1/branches/{id}/activity-logs    branch:view
```

Endpoint Vat ada di module spine-vat (`/api/v1/vats`).

## RBAC

- **Permission (8)**: `surveyor:{view,create,edit,delete}` + `branch:{view,create,edit,delete}`
- **Role (3)**:
  - `surveyor`             → `surveyor:view` (read-only)
  - `surveyor-branch-admin`→ `surveyor:view` + `branch:*`
  - `surveyor-admin`       → `surveyor:*` + `branch:*`
- **Grants**: `staff` → `surveyor:view` + `branch:view`

Sync: `php artisan spine:rbac:sync`.

## Lifecycle

`Surveyor` & `Branch` pakai `HasLifecycleHooks` → listener log activity.
Vat lifecycle di-handle module `spine-vat`.

## Menu

Item menu **Surveyors** dengan `permission: surveyor:view` — auto-hidden
untuk user tanpa permission.

## Instalasi (konsumen laravelspine)

1. Install `spine-vat` dulu (module dependensi):
   ```bash
   # taruh di modules/Vat/ dan tambahkan 'Vat' ke modules_statuses.json
   ```
2. Install `spine-surveyor`:
   ```bash
   # taruh di modules/Surveyor/, tambahkan 'Surveyor' ke modules_statuses.json
   ```
3. `composer dump-autoload`
4. `php artisan migrate`  → membuat tabel `vats`, `surveyors`, `branches` berurutan
5. `php artisan spine:rbac:sync`

## v1 → v2 changelog

- Vat diekstrak ke module terpisah (wasnaker/spine-vat).
- `composer.json` & `module.json` declare dependensi `spine-vat`.
- `SurveyorController` auto-attach `parent_vat_number` ke Vat via `VatService`.
