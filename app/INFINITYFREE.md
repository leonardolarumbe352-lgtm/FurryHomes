# InfinityFree database setup

Target from the supplied hosting screenshot:

- Host: `sql308.infinityfree.com`
- Port: `3306`
- Database: `if0_43266899_FurryHomes`
- Username: `if0_43266899`

In the InfinityFree dashboard, open phpMyAdmin for this database. Select Import and choose `schema.infinityfree.sql`. This creates the module tables without dropping existing tables or records. It does not migrate local records or change existing table definitions.

The application connection in `storage/database.php` now targets this hosting account and uses the password supplied directly by the user, correcting the earlier screenshot transcription. Upload that protected configuration along with the app. A local configuration backup remains in `storage/database.local.php`; it is not loaded automatically.

InfinityFree blocks external MySQL clients; import and hosted connection verification must run through its phpMyAdmin or uploaded PHP application. The password is intentionally omitted from this document.
