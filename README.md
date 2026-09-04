# WEB-DEV-LFT
Web Dev LFT Curated Workspace

## Run locally

Use PHP 8.1 or newer with the SQLite PDO extension enabled:

```bash
php -S 127.0.0.1:8080 -t .
```

Open `http://127.0.0.1:8080/`. The database is created automatically at `storage/lft.sqlite` when the first page is loaded. Customers can create an account, log in, book a space, or submit a walk-in check-in from the Contact page. Passwords are stored with PHP's `password_hash()` API.

## Staff and admin accounts

Create protected operational accounts from the terminal:

```bash
php scripts/create-role-user.php staff staff@example.com "your-password"
php scripts/create-role-user.php admin admin@example.com "LFT-Admin-2026!"
```

Staff accounts use `/Staff/` to manage daily requests. Admin accounts use `/Admin/` to view totals and manage requests. Unauthorized users are redirected to login.
