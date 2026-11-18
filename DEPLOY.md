# Serenovia database and cPanel deployment

The application, local environment and test suite use MySQL. MariaDB is supported
through Laravel's MySQL driver (the local XAMPP server uses MariaDB).
SQLite is retained only as a migration source and a temporary rollback option.

## Existing live SQLite website: switch without Terminal

Do not replace the active `.env` before transferring the data.

1. In cPanel, create an **empty** MySQL database and a database user.
   Add the user to this database with **All Privileges**.
2. In File Manager, enable **Show Hidden Files**. In
   `/home2/mpmrejmy/serenovia/`, copy the existing live `.env` to
   `.env.mysql`. Keep the existing APP_KEY and all non-database settings.
3. In the copy only, set:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=mpmrejmy_Serenovia_db
   DB_USERNAME=mpmrejmy_Serenovia_user
   DB_PASSWORD="YOUR_DATABASE_USER_PASSWORD"
   ```

   Use the actual database host shown by the hosting provider if different.
   Remove DB_URL if present. Ensure APP_ENV=production and APP_DEBUG=false.
   Do not upload credentials or environment files to GitHub.
4. Keep the current live `database/database.sqlite` in place. Do not upload an
   older local database over it. Pause queue workers or other scheduled writers
   during the switch if they are configured.
5. In Git Version Control, **Update from Remote**, then **Deploy HEAD Commit**.

The task validates the staged environment, briefly puts the website in maintenance
mode, creates a consistent private SQLite backup, builds the MySQL schema, copies
the data, compares every imported value and row count, and checks foreign keys.
Only a completely empty MySQL database is accepted. IDs, password hashes, image
references, itinerary JSON, settings, enquiries and generator history are preserved.
The migration ledger is created by running the current application migrations.

After verification, the task backs up the old environment, moves `.env.mysql`
into place as `.env`, and completes deployment. APP_KEY is never rotated during
the switch. The original SQLite database is retained. All later backups are SQL
files in `storage/app/backups/`, outside the web root.

If import fails before the environment switch, the old SQLite website is brought
back online. MySQL may contain the newly created empty schema because MySQL DDL
is not transactional; use a **new empty database** for another attempt. Do not
delete tables from a live database to retry. Inspect the latest cPanel deployment
log. A later failure after the switch leaves the site in maintenance mode until
deployment succeeds; retain both database and environment backups for recovery.

## New MySQL installation

Clone into `$HOME/serenovia`, outside `public_html`. Create the MySQL database and
user through cPanel, then copy `.env.example` to `.env`. Set the MySQL credentials,
APP_ENV=production, APP_DEBUG=false, and APP_URL=https://www.serenoviaafrica.com.
Leave APP_KEY empty only for a genuinely new installation. A fresh deployment
runs migrations but does not invent or seed tours or admin users.

For the fixed public_html deployment layout, this repository and public_html
must be siblings. The task refuses to overwrite an unrelated index.php or
WordPress installation. Uploaded photos belong in `public_html/uploads/`;
they are not replaced by deployment.

## Requirements and later updates

PHP CLI 8.2+ (prefer PHP 8.3) with PDO MySQL, GD and the Composer-required
extensions must be available to cPanel deployment tasks. PDO SQLite is needed
only during the one-time transfer. Composer is downloaded from its official
installer if missing, with SHA-384 verification; outbound HTTPS must be allowed.
No interactive Terminal or mysqldump executable is required.

For later updates, use **Update from Remote → Deploy HEAD Commit**.
The task makes a private MySQL SQL backup before migrations. It preserves the
live environment and uploaded photos. Never overwrite the live database with
a local copy during routine updates.

SQL backups can be imported into a separate empty MySQL database through
phpMyAdmin. They contain sensitive site data: keep them outside public_html.
They cover this application's InnoDB tables; external views, routines or triggers
are not part of the application schema.

## Local development and tests

Create a local `serenovia` database with utf8mb4, configure `.env`, then run
`php artisan migrate`. For an existing SQLite installation, create an empty
MySQL destination and run:

```sh
php artisan serenovia:import-sqlite /absolute/path/to/database/database.sqlite
```

The command uses the configured MySQL connection and leaves SQLite intact.
`php artisan serenovia:backup-database` creates a backup without modifying data.

Create separate empty databases `serenovia_testing` and
`serenovia_transfer_testing` for tests. Both are disposable and must never contain
production data. The test account must have privileges on both. Set test
credentials in an ignored `.env.testing` if they differ from local development.
Run `php artisan test`. The suite uses MySQL; the transfer tests use SQLite only
to exercise the old-data import. Test guards reject an application test database
whose name does not end in `_testing`.
