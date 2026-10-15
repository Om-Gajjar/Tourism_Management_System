# Render + Aiven deployment

This application is deployed as a Docker web service on Render and uses an
Aiven for MySQL service. Render does not provide a MySQL database for this
deployment; create the Aiven service separately.

## 1. Create the Aiven MySQL service

1. Create an Aiven for MySQL service in a region close to the Render service.
2. Wait until the service status is `Running`.
3. In Aiven, open **Service overview** and copy the host, port, database name,
   username, and password from **Connection information**.
4. Download the Aiven CA certificate. The application must use the CA
   certificate when `DB_SSL_VERIFY=true`.
5. Create the application database if it is not already present. The default
   Aiven database is commonly named `defaultdb`; either use that name or create
   `tourism_db`.

Import the schema from a machine that can reach Aiven:

```bash
mysql --host=YOUR_AIVEN_HOST --port=YOUR_AIVEN_PORT \
  --user=YOUR_AIVEN_USER --password \
  --ssl-mode=VERIFY_CA --ssl-ca=ca.pem \
  YOUR_AIVEN_DATABASE < database/schema.sql
```

The schema contains `CREATE DATABASE` and `USE` statements for local MySQL.
When using Aiven, import it into the selected database and skip those two
statements if the Aiven user cannot create databases. The application also
attempts to initialize missing tables on first connection, but an explicit
import is recommended for predictable deployments.

## 2. Configure Render

1. Push this repository to GitHub.
2. In Render, choose **New > Blueprint** and select the repository. Render will
   read [`render.yaml`](../render.yaml) and create the Docker web service.
3. If creating the service manually, choose **Web Service**, runtime
   **Docker**, and keep the Dockerfile in the repository root.
4. Add these environment variables in the Render service:

| Variable | Value |
| --- | --- |
| `APP_URL` | The final HTTPS Render URL, for example `https://tourism-management.onrender.com` |
| `DB_HOST` | Aiven host |
| `DB_PORT` | Aiven port (do not assume `3306`) |
| `DB_NAME` | The selected Aiven database |
| `DB_USER` | Aiven username |
| `DB_PASS` | Aiven password |
| `DB_SSL_CA` | `/etc/ssl/certs/aiven-ca.pem` |
| `DB_SSL_CA_CONTENT` | The complete contents of Aiven's downloaded `ca.pem` file |
| `DB_SSL_VERIFY` | `true` |
| `ADMIN_USERNAME` | A non-default admin username |
| `ADMIN_PASSWORD` | A long, unique admin password |

The `render.yaml` file defines the variable names, but secrets marked
`sync: false` must be entered in the Render dashboard. Do not commit passwords,
`.env` files, or the CA private material.

### CA certificate note

Paste the complete Aiven `ca.pem` contents into the secret
`DB_SSL_CA_CONTENT`. The Docker entrypoint writes it to `DB_SSL_CA` before
Apache starts, so the certificate is not committed to Git. Do not solve a
certificate problem by setting `DB_SSL_VERIFY=false` for production.

## 3. Verify the deployment

After the first deploy:

1. Open the Render URL and confirm the service health check is passing.
2. Register a test user and log in.
3. Open the admin login and verify package data loads.
4. Check Render logs for database errors.
5. Confirm `APP_URL` uses `https://`; Render terminates TLS and redirects are
   generated from this value.

## Important production limitations

- Render's local filesystem is ephemeral. Uploaded package images under
  `public/uploads` can disappear on redeploy or restart. Use object storage
  (S3-compatible storage, Cloudinary, or similar) before relying on persistent
  uploads.
- The free Render service may sleep when idle and has resource limitations.
- Aiven network access, service status, and credentials must be valid before
  Render can connect. Render and Aiven services do not communicate over a
  private Render network.
- Back up the Aiven database and use Aiven's TLS connection details rather
  than hard-coding credentials.
