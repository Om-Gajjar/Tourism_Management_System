# InfinityFree deployment

This deployment keeps the Render + Aiven path working. InfinityFree uses the
same PHP source code but has its own local MySQL configuration and does not use
the Dockerfile, Render port, or Aiven TLS settings.

## 1. Create the InfinityFree site and database

1. Create an InfinityFree account and a site or custom domain.
2. Enable HTTPS for the site before testing login and booking sessions.
3. In the InfinityFree control panel, create a MySQL database.
4. Copy the exact database host, database name, username, and password shown
   by InfinityFree. The host is normally not `localhost`.
5. Open the database's phpMyAdmin from the InfinityFree control panel.

## 2. Import the schema

Select the InfinityFree-created database in phpMyAdmin and import
`database/schema.sql`. Before importing, remove these local-server statements:

```sql
CREATE DATABASE IF NOT EXISTS tourism_db;
USE tourism_db;
```

The remaining tables and seed data can be imported into the selected database.
An explicit import is preferred to relying on first-request initialization on a
shared host.

## 3. Create the local configuration

On a local copy, create:

```text
src/config/config.local.php
```

using [`config.local.php.example`](../src/config/config.local.php.example) as
the template. Set:

```php
'APP_URL' => 'https://your-infinityfree-domain',
'DB_HOST' => 'the-host-from-infinityfree',
'DB_PORT' => '3306',
'DB_NAME' => 'the-database-from-infinityfree',
'DB_USER' => 'the-user-from-infinityfree',
'DB_PASS' => 'the-database-password',
'DB_SSL_CA' => '',
'DB_SSL_VERIFY' => 'false',
```

Use a unique admin username and password. Do not commit
`src/config/config.local.php`; it is ignored by Git. Environment variables
still take precedence, so Render continues to use its existing Aiven values.

## 4. Upload files

Upload the project through FTP or the InfinityFree file manager into the
domain's `htdocs` directory. Keep the project structure intact:

```text
htdocs/
├── .htaccess
├── public/
├── src/
├── database/
└── logs/
```

The root `.htaccess` forwards public requests into `public/` and blocks private
directories. If the host rejects a rewrite directive, check the hosting error
log before changing routing rules.

Do not upload the following as executable deployment components:

- `Dockerfile`
- `docker-entrypoint.sh`
- `render.yaml`
- `start.sh`

They may remain in the Git repository for Render/local use.

## 5. Verify the site

Test in this order:

1. `https://your-infinityfree-domain/`
2. package details
3. registration
4. login
5. **Book Now**
6. booking submission
7. admin login
8. package management

If a login redirect points to `localhost`, `APP_URL` is missing or incorrect.
If a database connection fails, re-copy the exact InfinityFree host; do not
assume `localhost`.

## Dual-deployment behavior

Render uses environment variables:

```text
APP_URL, DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS,
DB_SSL_CA, DB_SSL_VERIFY, ADMIN_USERNAME, ADMIN_PASSWORD
```

InfinityFree uses `src/config/config.local.php` when those environment
variables are unavailable. The configuration loader gives environment
variables priority, so adding the local file does not replace the Render/Aiven
deployment.

InfinityFree is shared hosting, not a Docker runtime. Its storage, PHP
execution, MySQL, request, and CPU limits apply independently of Render.
