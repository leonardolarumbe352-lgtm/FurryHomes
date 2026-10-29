# Code organization

FurryHomes uses plain PHP modules. No framework, build step, or package manager is required.

```text
index.php                       Request entry point
router.php                      Private-path protection for PHP's dev server
app/
  bootstrap.php                 Database connection and shared components
  config/
    routes.php                  Page registry, titles, and permitted roles
    actions.php                 Form actions, handlers, and permitted roles
  database/
    schema.mysql.sql            Local MariaDB schema
    schema.infinityfree.sql     Hosting import schema
    schema.sqlite.sql           Isolated test schema
  layouts/
    main.php                    Shared page shell and navigation
  modules/
    auth/                       Registration, login, logout
    dashboard/                  Summary and live notification endpoint
    pets/                       Pet profiles and photo uploads
    applications/               Adoption submission and staff review
    medical/                    Health and vaccination records
    adopters/                   Approved adopter directory and contacts
    contracts/                  Adoption finalization and payment records
    fosters/                    Caregivers, placements, and returns
    analytics/                  Reports, audit history, and CSV export
    users/                      Admin account permissions
    profile/                    Signed-in user's contact details
  shared/                       Sessions, CSRF, database helpers, validation,
                                authentication, uploads, forms, dispatch
public/
  css/style.css                 Responsive stylesheet
  js/app.js                     Notification and application polling
  uploads/                      Runtime pet photos, ignored by Git
storage/
  database.example.php          Safe connection template
  database.php                  Active credentials, ignored by Git
  database.local.php            Localhost/CLI credentials, ignored by Git
tests/
  workflow.php                  Schema constraints and transaction checks
  modules.php                   Actual handlers, permissions, CSRF, views
docs/
  INFINITYFREE.md               Hosting/import instructions
```

## Module responsibilities

Each feature has a `view.php`. Modules that display stored data have a `repository.php` containing named read queries. Modules that change records have an `actions.php` containing their write handlers, input validation, and transaction boundaries. Read-only modules do not need action files, and profile uses the shared current-user lookup instead of an extra repository.

Views render escaped values and forms using the shared helpers. They call named repository functions rather than embedding SQL. Shared code contains cross-module primitives; business workflows stay in their feature module.

`index.php` resolves only registered page names. The action dispatcher checks CSRF before loading a registered handler and checks action roles separately from page roles. Module names never come directly from a request path. The existing `?page=...` URLs and form action values remain compatible.

## Sessions and transactions

Session initialization and secure cookie settings live in `app/shared/session.php`. Session rotation remains in the authentication actions. The rollback-on-failure transaction helper lives in `app/shared/database.php`.

The primary transaction workflows are:

1. `modules/applications/actions.php`: submission with notification and audit.
2. `modules/applications/actions.php`: review with notification and audit.
3. `modules/contracts/actions.php`: agreement, ownership status, competing applications, foster closure, and notifications.
4. `modules/fosters/actions.php`: placement and pet housing status.

Registration and foster returns also use transactions.

## Making changes

For an existing feature, edit its action, repository, or view file. For a new page, add a module view and register the module and its roles in `app/config/routes.php`. Register new write handlers and their roles in `app/config/actions.php`. Keep credentials in the ignored storage configuration.

Run both test scripts and PHP lint after changes. Integration tests use an in-memory database, production handlers, and rendered templates; they do not connect to or change the local or hosted production database.
