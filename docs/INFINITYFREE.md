# InfinityFree database setup

Target from the supplied hosting screenshot:

- Host: `sql308.infinityfree.com`
- Port: `3306`
- Database: `if0_43266899_FurryHomes`
- Username: `if0_43266899`

In the InfinityFree dashboard, open phpMyAdmin for this database. Select Import and choose `app/database/schema.infinityfree.sql`. This creates the module tables without dropping existing tables or records. It does not migrate local records or change existing table definitions.

The hosted application uses `storage/database.php`. Local Apache requests on loopback addresses, PHP's development server, and CLI runs automatically use `storage/database.local.php`. That local file connects to XAMPP MariaDB at `127.0.0.1:3307`, database `furryhomes`, using a restricted app account. Both credential files are excluded from Git. Upload the hosted configuration with the app; the local configuration is only needed for development.

InfinityFree blocks external MySQL clients; import and hosted connection verification must run through its phpMyAdmin or uploaded PHP application. The password is intentionally omitted from this document.
