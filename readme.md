# UserApp — Local Setup Guide (macOS + Homebrew)

## Stack
- HTML / CSS (Bootstrap 5) / JS (jQuery) — frontend
- PHP — backend API
- MySQL — user authentication (username, email, password)
- MongoDB Atlas — user profiles (name, age, dob, contact, address)
- Redis — session token storage

---

## 1. Install & start MySQL

```bash
brew install mysql
brew services start mysql

# Set root password (press Enter when asked for current password)
mysql_secure_installation
```

---

## 2. Create the MySQL database

```bash
mysql -u root -p < setup.sql
```

Or manually:
```bash
mysql -u root -p
# then paste the contents of setup.sql
```

Then open `php/config.php` and set:
```php
define('DB_PASS', 'your_mysql_password');
```

---

## 3. Install & start Redis

```bash
brew install redis
brew services start redis

# Test it
redis-cli ping   # should print: PONG
```

---

## 4. Fix PHP Redis extension (if pecl fails)

```bash
# Check your PHP version first
php -v

# Option A — try pecl with build tools
brew install autoconf
pecl install redis

# Option B — if Option A fails (M1/M2 Mac)
brew tap shivammathur/php
brew install shivammathur/php/php-redis
```

Find your php.ini:
```bash
php --ini | grep "Loaded Configuration"
```

Open that file and add at the very bottom:
```
extension=redis.so
```

Restart PHP:
```bash
brew services restart php
```

Verify:
```bash
php -r "echo class_exists('Redis') ? 'Redis: OK' : 'Redis: MISSING';"
```

---

## 5. Set up MongoDB Atlas

1. Go to https://cloud.mongodb.com and sign up (free)
2. Create a free cluster
3. Click **Connect** → **Connect your application** → **PHP**
4. Copy the connection string (looks like `mongodb+srv://user:pass@cluster.mongodb.net/`)
5. Paste it in `php/config.php`:
   ```php
   define('MONGO_URI', 'mongodb+srv://youruser:yourpass@yourcluster.mongodb.net/...');
   ```
6. In Atlas → Network Access → Add your IP (or 0.0.0.0/0 for local dev)

---

## 6. Install PHP MongoDB extension

```bash
pecl install mongodb
```

Add to php.ini:
```
extension=mongodb.so
```

---

## 7. Install Composer dependencies (MongoDB PHP library)

```bash
# Install Composer if you don't have it
brew install composer

# In the project folder
cd /path/to/user_app
composer install
```

---

## 8. Add the autoloader to PHP files

The `config.php` already includes this — just make sure `vendor/autoload.php` exists after step 7.

---

## 9. Run the project locally

```bash
cd /path/to/user_app
php -S localhost:8000
```

Open in browser: **http://localhost:8000**

Flow: Register → Login → Profile

---

## Folder structure

```
user_app/
├── index.html          ← redirects to login
├── register.html
├── login.html
├── profile.html
├── composer.json
├── setup.sql
├── css/
│   └── style.css
├── js/
│   ├── register.js
│   ├── login.js
│   └── profile.js
└── php/
    ├── config.php      ← set your credentials here
    ├── register.php
    ├── login.php
    └── profile.php
```