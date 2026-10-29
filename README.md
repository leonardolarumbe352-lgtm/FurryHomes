# FurryHomes

A simple, modular PHP 8.1+ rescue shelter management application. Uses PDO MySQL with XAMPP MariaDB on port 3307 and database `furryhomes`. No package installation is needed.

## Run

Start Apache and MySQL in XAMPP and open http://localhost/FurryHomes/. Register the first account to become Admin. Subsequent accounts are Adopters; Admin can promote accounts to Staff using Team & access. There are no default passwords or preloaded personal records.

For PHP's development server, use the protected router:

```powershell
C:\xampp\php\php.exe -S localhost:8080 router.php
```

Hosted connections use `storage/database.php`; localhost and CLI runs automatically use `storage/database.local.php`. Both are excluded from Git and protected by Apache access rules. Local XAMPP uses `127.0.0.1:3307` and database `furryhomes`. See [hosting instructions](docs/INFINITYFREE.md). For another installation, copy `storage/database.example.php` to the appropriate configuration file, fill in its DSN, username and password, and import `app/database/schema.mysql.sql` or the InfinityFree schema. Apache must honor the included `.htaccess` files; the development router also blocks private paths. Back up MariaDB and uploaded photos together. The SQLite schema remains for isolated tests.

## Project structure

Feature code lives in `app/modules/`, with separate action handlers, read repositories, and views. Page and action permissions live in `app/config/`; common helpers live in `app/shared/`. The entry point only coordinates routing and rendering. See [the architecture guide](docs/ARCHITECTURE.md) for the full folder map and instructions for adding modules.

## Modules

1. Authentication: hashed passwords, server-side sessions, session ID rotation, CSRF protection, login throttling, and server-side role checks.
2. Pets: photo uploads (validated JPG/PNG/WebP, 4 MB maximum), profiles, search, availability, and editing.
3. Applications: pet-specific adopter submissions and duplicate prevention.
4. Reviews: staff decisions and private notes, with adopter notifications.
5. Medical: editable dated health records, vaccines, next-due dates, and spay/neuter records.
6. Adopters: searchable approved adopter directory and contact histories.
7. Agreements: signed agreement and ownership-document references, fees, and editable payment status. Records document references/text rather than accepting legal-document uploads or electronic signatures. Fees are recorded in the shelter's local currency; this app does not process payments.
8. Fosters: caregiver registry, locations, placement dates, and return history.
9. Dashboard: account-specific application status and notifications refreshed every 10 seconds while visible.
10. Analytics: Admin-only monthly intake/adoption reporting, CSV export, and printable PDF via the browser's Save as PDF dialog.

## Transactions

Module action handlers preserve four primary database transactions: application submission, application review, adoption finalization, and foster assignment. Finalization atomically creates the contract, updates the pet, closes competing applications, ends foster placement, and notifies adopters. All transactions roll back on failure through `app/shared/database.php`. Additional transactions protect registration and foster returns.

## Validation

Run `C:\xampp\php\php.exe tests/workflow.php` and `C:\xampp\php\php.exe tests/modules.php`. Both use isolated in-memory databases and do not modify the app database. Module integration checks exercise production handlers, permissions, CSRF protection, transaction failure paths, and page rendering for each role. PHP lint can be run against each PHP source file.

Before public deployment, configure HTTPS and Apache access controls, use a production PHP configuration with display_errors disabled, arrange backups, and add operational account recovery/email delivery as needed. This version uses in-app notifications.
