# Serenovia on Bluehost

Clone the GitHub repository into `$HOME/serenovia`, outside the web root.
In cPanel Git Version Control use **Create → Clone a Repository**, or run
`git clone https://github.com/mtaitahd/serenovia.git "$HOME/serenovia"` from Terminal.

If Bluehost allows changing this domain's document root, use
`$HOME/serenovia/public` and skip the split-root instructions below. For the
primary domain with a fixed `public_html`, use the split-root instructions.

In cPanel, select a PHP version compatible with `composer.lock` and use that
same PHP version in Terminal. Confirm Composer, PDO SQLite, and GD are available.

## First installation

In `$HOME/serenovia`, create a new `.env` from `.env.example` and set
`APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` to the actual HTTPS
domain. Run `composer install --no-dev --prefer-dist --optimize-autoloader`
and `php artisan key:generate` once.

To retain the local tours, settings, and admin account, transfer a consistent
copy of the local SQLite database to `$HOME/serenovia/database/database.sqlite`
through an authenticated channel, and transfer the matching `public/uploads`
folder to the domain's document root. The database can contain accounts and
customer enquiries. Keep it outside the web root and GitHub. If starting
without local data, create an empty SQLite file instead and set up your admin
at `/admin/setup` after migration. Run `php artisan migrate --force` in either case.
Ensure PHP can write to `storage`, `bootstrap/cache`, the SQLite directory,
and the uploads directory. Verify the home page and `/admin/login`.

## Fixed public_html document root

Run these commands from cPanel Terminal after the clone. The copy excludes the
original front controller and preserves existing uploads. The PHP files below
assume the two folders are direct siblings.

```sh
cd "$HOME/serenovia"
rsync -a --exclude='/index.php' --exclude='/uploads/' public/ "$HOME/public_html/"
cp deploy/public-html-index.php "$HOME/public_html/index.php"
cp deploy/public-path.php "$HOME/serenovia/bootstrap/public-path.php"
```

## Subsequent updates

Back up the live database before migrations. In cPanel Git Version Control use
**Manage → Pull or Deploy → Update from Remote**, or run `git pull --ff-only`
inside `$HOME/serenovia`. Run Composer install again, then
`php artisan migrate --force` and `php artisan optimize:clear`. For the
fixed `public_html` layout, repeat the public file copy above after each pull.
Never copy a local database, local `.env`, or local uploads over the live
versions during routine updates.
