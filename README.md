# LFT Dumaguete Curated Workspaces

A PHP + SQLite website for LFT Dumaguete with separate customer, staff, and administrator workflows.

## Requirements

- PHP 8.1 or newer
- PDO SQLite enabled
- A writable `storage/` directory

## Run in a Codespace or terminal

From the repository root:

```bash
php -S 0.0.0.0:8080 -t .
```

Open the forwarded port or `http://127.0.0.1:8080/` when running locally.

The SQLite database is created automatically at `storage/lft.sqlite` the first time the application connects. The database file is ignored by Git so account and booking data are not committed to the repository.

## Run with XAMPP on Windows

1. Place the repository inside your XAMPP `htdocs` directory.
2. Make sure your PHP installation has `pdo_sqlite` and `sqlite3` enabled in `php.ini`.
3. Start Apache from XAMPP.
4. Open the project through `http://localhost/<project-folder>/`.
5. Ensure PHP can write to the project's `storage` directory.

## Accounts and roles

Customers register from the public Login page. Customer accounts can reserve active spaces, submit walk-in check-ins, review upcoming visits and history, and cancel eligible Pending or Confirmed requests.

Staff accounts use `/Staff/` to review the booking queue, view booking details, manage arrivals, and update booking statuses.

Administrators use `/Admin/` to manage bookings, customers, staff roles, memberships, spaces, amenities, events, messages, and public website settings.

### Create the first administrator

Use the CLI helper with your own email and a strong password:

```bash
php scripts/create-role-user.php admin your-email@example.com "choose-a-strong-password"
```

After the first administrator exists, other users can register normally. Admin can promote an existing customer through **Admin → Staff accounts**.

A staff account can also be created from the CLI if needed:

```bash
php scripts/create-role-user.php staff staff@example.com "choose-a-strong-password"
```

## Main workflow

1. A visitor registers or logs in.
2. A customer chooses an active space and submits a booking or walk-in request.
3. The request appears in the Staff booking queue and Admin bookings area.
4. Staff/Admin update the request through Pending, Confirmed, Checked in, Completed, or Cancelled.
5. The customer sees the latest status in the customer dashboard.
6. Contact-form messages appear in the Admin Messages inbox.
7. Admin Settings controls the public contact details and footer information.

## Automated checks

GitHub Actions runs a PHP syntax check on every push to `main` and on pull requests using `.github/workflows/php-lint.yml`.

For a local syntax check:

```bash
find . -type f -name '*.php' -not -path './storage/*' -print0 | xargs -0 -n1 php -l
```

On Windows PowerShell, you can run individual files with:

```powershell
php -l .\index.php
```

## Storage and security notes

- Passwords use PHP `password_hash()` / `password_verify()`.
- Operational pages require customer/staff/admin role checks as appropriate.
- Database files are excluded through `.gitignore`.
- Do not commit real passwords, database files, or private customer information.
