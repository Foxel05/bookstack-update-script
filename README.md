# BookStack PHP Update Script

> Update [BookStack](https://www.bookstackapp.com/) without SSH access — just a single PHP file.

Shared hosting providers like **all-inkl.com** don't give you SSH access, but BookStack's official update process requires running shell commands. This script works around that by executing all necessary commands via PHP's `exec()`, triggered from your browser.

---

## ⚠️ Security Warning

This script executes shell commands on your server. **Never leave it publicly accessible.** Upload it, run it once, then delete it immediately — or protect it with a password/IP restriction.

---

## Requirements

- PHP 8.2+ (script uses `/usr/bin/php83` — adjust to your host's binary)
- BookStack installed via Git
- `composer.phar` uploaded to your BookStack root directory (see setup below)
- `exec()` not disabled in your host's PHP configuration

---

## Setup

### 1. Get `composer.phar`

Composer is often not installed on shared hosts. Download it manually:

👉 https://getcomposer.org/composer-stable.phar

Upload the downloaded file as `composer.phar` into your BookStack root directory (the same folder that contains `artisan`).

You can verify it works with this quick test script:

```php
<?php
$projectPath = "/path/to/your/bookstack";
$phpBin = "/usr/bin/php83";

exec("$phpBin $projectPath/composer.phar --version 2>&1", $output);
echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
?>
```

It should print something like:
```
Composer version 2.x.x ...
PHP version 8.3.x (...)
```

### 2. Configure the update script

Open `bookstack-update.php` and adjust the two variables at the top:

```php
$projectPath = "/www/htdocs/youruser/yourdomain.de"; // absolute path to BookStack
$phpBin      = "/usr/bin/php83";                     // path to your PHP binary
```

To find your PHP binary path, you can run:
```php
<?php echo PHP_BINARY; ?>
```

### 3. Upload the script

Upload `bookstack-update.php` somewhere within your webroot — ideally with a non-obvious filename (e.g. `bs-update-2024.php`).

---

## 💾 Before You Update — Backup!

**Always back up before updating.** If a migration goes wrong, you need a way back.

### Database
Export your BookStack database via your host's control panel (e.g. phpMyAdmin on all-inkl.com):

1. Open **phpMyAdmin** in your hosting control panel
2. Select your BookStack database
3. Click **Export** → Quick → Format: SQL → **Go**
4. Save the `.sql` file somewhere safe

### Uploaded files & attachments
BookStack stores user-uploaded images and attachments on disk. Copy the following folders to a safe location:

```
/your/bookstack/public/uploads/
/your/bookstack/storage/uploads/
```

You can do this via FTP/SFTP or your host's file manager.

### `.env` file
Your `.env` file contains your database credentials, app key, and all configuration. Back it up too:

```
/your/bookstack/.env
```

> 💡 The official BookStack backup docs are here: https://www.bookstackapp.com/docs/admin/backup-restore/

---

## Usage

1. Open the script URL in your browser
2. Watch each step execute in sequence
3. If everything shows **Return code: 0**, the update was successful
4. **Delete the script from your server immediately after**

---

## What the script does

The script runs the following commands in order, stopping if any critical step fails:

| Step | Command | Purpose |
|---|---|---|
| `git reset` | `git reset --hard HEAD` | Discard any local file modifications |
| `git pull` | `git pull origin release` | Pull the latest BookStack release |
| `composer install` | `composer install --no-dev` | Install/update PHP dependencies |
| `migrate` | `php artisan migrate --force` | Apply database migrations |
| `cache:clear` | `php artisan cache:clear` | Clear application cache |
| `config:clear` | `php artisan config:clear` | Clear config cache |
| `view:clear` | `php artisan view:clear` | Clear compiled views |

The `git reset --hard` step is necessary because BookStack's release process updates files like `composer.lock`, `version`, and `readme.md` — which would otherwise block the pull on an existing installation.

---

## The Script

The full script is in [`bookstack-update.php`](./bookstack-update.php).

It features a dark-themed HTML output so you can clearly see which steps passed or failed, and stops automatically if any critical step returns a non-zero exit code.

---

## Tested on

- **Host:** all-inkl.com (KAS)
- **PHP:** 8.3
- **BookStack:** v25.x / v26.x

---

## References

- [BookStack official update docs](https://www.bookstackapp.com/docs/admin/updates/)
- [BookStack releases](https://codeberg.org/bookstack/bookstack/releases)
- [getcomposer.org](https://getcomposer.org/)

---

## License

MIT — do whatever you want with it.
