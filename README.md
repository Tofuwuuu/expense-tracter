# Expense Tracker

Personal single-user expense ledger. Vanilla PHP, MySQL, Bootstrap 5, and vanilla JavaScript. Runs in Docker (Apache + PHP + MySQL) instead of a local XAMPP install.

## Run locally

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/).
2. From this folder:

```bash
docker compose up --build -d
```

3. Open [http://localhost:8080](http://localhost:8080).

MySQL is on `localhost:3306` (database `expense_tracker`, user `root`, password `secret`). The schema and category seed run automatically on first start.

Stop with `docker compose down`. Data persists in the `mysql_data` volume.
