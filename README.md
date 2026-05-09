# Imexso

Monorepo:

| Path | Stack |
|------|--------|
| `imexso-frontend-main/imexso-frontend-main/` | Next.js 14 (App Router) |
| `imexso-main/imexso-main/` | Laravel API + Sanctum |

---

## Handoff (for a mentor / reviewer)

**Prerequisites:** Node 18+ (or as in your `package.json`), PHP 8.2+, Composer, npm.

### 1. Backend (Laravel)

```bash
cd imexso-main/imexso-main
cp .env.example .env
php artisan key:generate
composer install
php artisan migrate
php artisan serve
```

Default `.env.example` uses **SQLite** (`DB_CONNECTION=sqlite`). Ensure `database/database.sqlite` exists if you keep SQLite, or switch to MySQL in `.env` and create the database.

API runs at **`http://127.0.0.1:8000`** (matches `APP_URL` in `.env.example`).

### 2. Frontend (Next.js)

```bash
cd imexso-frontend-main/imexso-frontend-main
cp .env.example .env.local
npm install
npm run dev
```

App runs at **`http://localhost:3000`**.  
`NEXT_PUBLIC_API_URL` in `.env.local` must match the Laravel URL above; Next rewrites `/api/*` and `/sanctum/*` to that host (`next.config.mjs`).

### 3. Smoke test

- Register / login (reCAPTCHA keys optional if disabled on backend).
- Home → hero search → **Inventory** with filters.
- Mobile width: header menu, inventory list.

### 4. Secrets

Do **not** commit real `.env` or `.env.local`. Share production-like values with the mentor over a **private channel** if needed.

---

## Repo layout reminder

- Wrapper scripts also live in `imexso-frontend-main/package.json` (`npm run dev` delegates into the inner app folder).

Remote: `origin` → GitHub (`imexso` under your account).
