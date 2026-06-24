# bifrost-admin-ui

Administrasjon av cuper, sesonger, domener og roller.

Bygget med samme MVC-mønster som `jaktfeltnamdalen` (se `bifrost-shared/reference/mvc-standard-from-jaktfeltnamdalen.md`).

## Lokal URL

| Miljø | URL |
|-------|-----|
| XAMPP Apache (anbefalt) | http://admin.bifrost.local |
| PHP innebygd server | http://localhost:8083 |

## Avhengigheter

- PHP 8.1+
- Composer
- **`bifrost-backend`** kjørende på http://api.bifrost.local
- Database seed fra **`bifrost-shared`** — se `database/seeds/README.md` (`001_local_tenants.sql` + `002_local_admin_user.sql`)
- Backend auth-migreringer kjørt (`php bin/console migrate` i bifrost-backend)

## Oppsett

```bash
cd C:\xampp\htdocs\bifrost\bifrost-admin-ui
composer install
copy .env.example .env
```

Konfigurer Apache virtual host med document root `bifrost-admin-ui/public` og host `admin.bifrost.local`.

`BACKEND_API_URL` i `.env` peker til backend (standard: `http://api.bifrost.local`).

## Produksjon (bifrostevents.no)

1. **Document root** i ProISP må peke på `.../bifrostevents/public/` (samme mappe som `index.php` og `.htaccess`).
2. **FTP deploy** (`FTP_PATH` i GitHub) skal være prosjektroten `.../bifrostevents/` — ikke bare `public/`.
3. Opprett `.env` på serveren (beskyttet av deploy, overskrives ikke):
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_BASE_URL=https://bifrostevents.no`
   - `BACKEND_API_URL=https://api.bifrostevents.no` (eller faktisk backend-URL)

Uten `.htaccess` i `public/` gir `/login` Apache 404 — front controller kjøres bare for `/` via `DirectoryIndex`.

## Teste

1. Kjør migrering og seed i backend (se `bifrost-backend/README.md`)
2. Start backend på http://api.bifrost.local
3. Åpne http://admin.bifrost.local — du blir sendt til `/login` uten session

### Lokal testbruker

| Felt | Verdi |
|------|-------|
| E-post | `admin@bifrost.local` |
| Passord | `local-admin-change-me` |

Etter innlogging vises admin-oversikten med API health og tenant-liste.

Med PHP innebygd server:

```bash
composer serve
```

Admin-ui kaller backend server-side:

- `POST /api/auth/login` (ved innlogging — lagrer `BIFROSTSESSID` for admin-kall)
- `POST /api/auth/logout`
- `GET /api/auth/me` (session-sjekk)
- `GET /api/health`, `GET /api/tenants` (oversikt)
- `GET/POST/PUT/DELETE /api/admin/*` (plattform-CRUD)

## Auth og tilgang

Kun brukere med minst én av disse rollene får logge inn og se admin:

| Rolle | Tabell | Beskrivelse |
|-------|--------|-------------|
| `SystemAdmin` | `auth_system_roles` | Full plattformtilgang (Bifrost) |
| `CupAdmin` | `auth_tenant_admin_access` | Cup-/tenant-admin (f.eks. Namdal) |

Deltakerprofiler (`event_participant_profiles`) er ikke adminroller og vises ikke som tilgang.

## Funksjonalitet (nå)

- Felles admin-layout med venstremeny, toppfelt og cup-kontekst-velger
- Innlogging / utlogging mot backend session (cookie videresendes til `/api/admin/*`)
- **Oversikt** med API health og tenant-liste
- **Plattform CRUD** (server-side skjema, flash-meldinger):
  - Cuper / tenants — liste, detaljer, opprett, rediger, deaktiver
  - Domener — per cup, legg til/rediger/fjern (`public`, `admin`, `api`, `organizer`)
  - Brukere — liste, detaljer, opprett, rediger, deaktiver, `first_registered_tenant`
  - Roller og tilganger — SystemAdmin, CupAdmin, planlagt Organizer
- Placeholder-sider for øvrige menypunkter (se `config/admin-menu.php`)

### Plattform-sider

| Side | URL |
|------|-----|
| Cuper / tenants | http://admin.bifrost.local/platform/tenants |
| Domener | http://admin.bifrost.local/platform/domains |
| Brukere | http://admin.bifrost.local/platform/users |
| Roller | http://admin.bifrost.local/platform/roles |
