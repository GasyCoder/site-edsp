# EDSP — CMS institutionnel

Application Laravel 12, Inertia 3, Vue 3/TypeScript, Tailwind CSS 4 et Filament 4. La maquette Claude Design originale est archivée dans `resources/design-reference` et n'est jamais chargée en production.

## Installation

Prérequis : PHP 8.2+, extensions `intl`, `pdo_mysql`, `mbstring`, `xml`, `gd`, Node 22+, MariaDB/MySQL.

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Configurer `DB_CONNECTION=mysql`, les paramètres `DB_*`, la messagerie et une queue persistante dans `.env`. Le compte super administrateur initial n’est créé que si `EDSP_ADMIN_EMAIL` et `EDSP_ADMIN_PASSWORD` (12 caractères minimum) sont fournis au moment du seeding ; aucun mot de passe n’est versionné.

## Processus permanents

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

Pour régénérer les variantes WebP de médias déjà présents : `php artisan media:optimize`.

En production, exécuter plutôt `php artisan schedule:run` chaque minute via cron et superviser `queue:work`. L’extension `intl` doit être activée pour PHP CLI, PHP-FPM et les workers Filament. Après déploiement :

```bash
php artisan migrate --force
php artisan optimize
php artisan storage:link
npm ci
npm run build
```
