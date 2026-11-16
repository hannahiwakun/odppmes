# ODPPMES

Ondo State Development Partnerships and Project Monitoring & Evaluation System.

This repository is a Laravel 13 application (PHP 8.3) used for operational project monitoring, MDA coordination, CMS/public content, authentication and authorisation, audit logging, and private operational file storage.

## Requirements

- PHP 8.3 with extensions required by Laravel, including `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, and `gd`
- Composer
- Node.js (for Vite asset build on the host)
- MySQL for production

PHPUnit uses SQLite in-memory only (`phpunit.xml`). Do not use SQLite as the production database.

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a unique `APP_KEY` on the host. Never commit `.env` or real keys.
- Set `DB_CONNECTION=mysql` and Cloud/host MySQL credentials.
- Default filesystem disk is `local` (`storage/app/private`). Operational documents, photos, CMS media, and monitoring evidence are private. Do **not** run `php artisan storage:link`.
- Attach persistent storage for `storage/app/private` on Laravel Cloud so uploads survive deploys.
- Frontend assets are built with Vite (`npm ci` and `npm run build`). `public/build` is gitignored and must be produced on the host.
- Session, cache, and queue default to the `database` driver; run migrations on the host when approved.

## Local development

Copy `.env.example` to `.env`, generate an application key, point the database at a non-production MySQL instance, install PHP and Node dependencies, then serve the app. Do not copy production secrets into this repository.

## Licence

Application code in this repository is for the ODPPMES programme. The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
