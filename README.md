# Shonchoy

**Shonchoy** (সঞ্চয়, "savings") is a digital pocket-money wallet for children under 16, with parental supervision.
Laravel 12 · MySQL (XAMPP) · Bootstrap 5 · Chart.js

> Academic simulation project: no real money is transferred.

---

## 1. One-time setup (Windows)

### What you need
| Tool | Check it with | Need |
|---|---|---|
| XAMPP (PHP + MySQL) | `php -v` | PHP **8.2 or newer** |
| Composer | `composer -V` | any 2.x |
| Git | `git --version` | any |

**If `php -v` says "not recognized":** add `C:\xampp\php` to your PATH
(Windows search → *Edit the system environment variables* → Environment Variables →
User variables → **Path** → Edit → New → `C:\xampp\php` → OK), then restart VS Code completely.

**Enable two PHP extensions:** XAMPP Control Panel → Apache **Config** → **PHP (php.ini)**.
Remove the `;` at the start of these two lines and save:
```
extension=zip
extension=intl
```

**Composer:** install from https://getcomposer.org/download/ (Composer-Setup.exe).
When asked for PHP, pick `C:\xampp\php\php.exe`.

**Tell Git who you are** (use your GitHub email, so your commits count as yours):
```
git config --global user.name "Your Name"
git config --global user.email "your-github-email@example.com"
```

### Get the project running
1. Accept the GitHub collaborator invitation (check your email).
2. Open the **XAMPP Control Panel** and click **Start** next to **MySQL**.
3. Run these commands in a terminal (VS Code: Ctrl + `), one at a time:

```
mkdir C:\Projects
cd C:\Projects
git clone https://github.com/wahidint/kids_wallet.git kids-wallet
cd kids-wallet
composer install
copy .env.example .env
php artisan key:generate
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE kids_wallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate:fresh --seed
php artisan serve
```

4. Open **http://127.0.0.1:8000**. Done!

| Command | What it does |
|---|---|
| `git clone` | Downloads the code (a browser window may ask you to sign in to GitHub) |
| `composer install` | Downloads Laravel and its packages into `vendor/` |
| `copy .env.example .env` | Creates your local settings file |
| `php artisan key:generate` | Creates your app's secret encryption key |
| `mysql ... CREATE DATABASE` | Creates the empty `kids_wallet` database |
| `migrate:fresh --seed` | Builds all tables and fills them with demo data |
| `php artisan serve` | Starts the website (Ctrl + C to stop) |

### Demo accounts (password for all: `password`)
| Name | Email | Role |
|---|---|---|
| Rahim Ahmed | rahim@demo.com | Parent of Ayaan and Maya |
| Nasreen Ahmed | nasreen@demo.com | Parent of Ayaan and Maya |
| Karim Hossain | karim@demo.com | Parent of Tasnim |
| Ayaan Ahmed | ayaan@demo.com | Child (age 12, has spending limits) |
| Maya Ahmed | maya@demo.com | Child (age 9) |
| Tasnim Hossain | tasnim@demo.com | Child (wallet starts **locked**) |

---

## 2. Every time you work on the project

```
cd C:\Projects\kids-wallet
git pull
composer install
php artisan migrate:fresh --seed
php artisan serve
```
(Start MySQL in XAMPP first.) `migrate:fresh --seed` resets your local database to the
latest tables and demo data. It wipes your own test data, which is fine for development.

## 3. Making changes (so your contribution shows on GitHub)

Work on your own branch, never directly on `main`:
```
git checkout main
git pull
git checkout -b yourname/short-description
```
After your change works:
```
git add .
git commit -m "Clear description of what you changed"
git push -u origin yourname/short-description
```
Then on GitHub: **Compare & pull request** → **Create pull request**. The team lead reviews and merges it.

---

## 4. Team rules (keep the app consistent)
- **Never change a balance directly.** Always use `App\Services\WalletService`
  (`credit()`, `debit()`, `lock()`, `unlock()`). It records every transaction safely.
- Parent pages about one child go inside the `children/{child}` route group in `routes/web.php`
  (it already checks the `manage-child` permission).
- Use `<x-form-input>` for form fields and the existing CSS classes (`kw-card`, `btn-kw`, …).
- Never edit a migration that has already been pushed. Create a new migration instead.
- Never commit `.env`.

## 5. Troubleshooting
| Problem | Fix |
|---|---|
| `'php' is not recognized` | Add `C:\xampp\php` to PATH, restart VS Code |
| `No connection could be made… actively refused it` | MySQL is stopped: start it in XAMPP |
| `Unknown database 'kids_wallet'` | Run the `CREATE DATABASE` command above |
| `No application encryption key` | Run `php artisan key:generate` |
| `Class "…" not found` after `git pull` | Run `composer install`, then `php artisan optimize:clear` |
| Page looks unstyled | You're offline: Bootstrap and fonts load from the internet |
| MySQL won't start in XAMPP | Another MySQL may be using port 3306; close it or ask the team |