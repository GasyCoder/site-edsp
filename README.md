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

Configurer `APP_URL` avec l’URL HTTPS publique exacte, `DB_CONNECTION=mysql`, les paramètres `DB_*`, la messagerie et une queue persistante dans `.env`. Le compte super administrateur initial n’est créé que si `EDSP_ADMIN_EMAIL` et `EDSP_ADMIN_PASSWORD` (12 caractères minimum) sont fournis au moment du seeding ; aucun mot de passe n’est versionné.

Pour que l’édition visuelle reste authentifiée en production :

```dotenv
APP_ENV=production
APP_URL=https://votre-domaine.mg
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
MEDIA_OPTIMIZE_AFTER_RESPONSE=true
```

Le serveur web doit transmettre PHP les cookies et les en-têtes `X-XSRF-TOKEN`, `X-Forwarded-Proto` et `Authorization`. L’éditeur utilise volontairement `POST` afin de rester compatible avec les reverse proxies et pare-feux qui refusent `PATCH`.

## Processus permanents

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

Les nouvelles images sont optimisées en WebP après la réponse HTTP sans dépendre du worker. Sur une infrastructure possédant un worker fiable, `MEDIA_OPTIMIZE_AFTER_RESPONSE=false` permet de déléguer ce traitement à la queue.

Pour générer les variantes WebP manquantes de médias déjà présents :

```bash
php artisan media:optimize
```

Utiliser `--force` pour tout régénérer et `--queue` uniquement si le worker est actif. Les listes du back-office et les cartes publiques utilisent les miniatures de 640 px ; les visuels principaux utilisent la variante optimisée de 1920 px.

En production, exécuter plutôt `php artisan schedule:run` chaque minute via cron et superviser `queue:work`. L’extension `intl` doit être activée pour PHP CLI, PHP-FPM et les workers Filament. Après déploiement :

```bash
php artisan migrate --force
php artisan storage:link
npm ci
npm run build
php artisan media:optimize
php artisan optimize
```

Avec Nginx, ajouter une politique de cache pour les fichiers générés (Apache utilise déjà `public/.htaccess`) :

```nginx
location ^~ /storage/ {
    try_files $uri =404;
    expires 30d;
    add_header Cache-Control "public, max-age=2592000";
}
```
