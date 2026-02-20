# GUVI-Task — User Registration & Profile Management

A full-stack web application with user authentication and profile management built with PHP, MySQL, MongoDB Atlas, and Redis.

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, Bootstrap 5, jQuery |
| Backend | PHP 8.x |
| Auth Database | MySQL (users, passwords) |
| Profile Database | MongoDB Atlas (profile details) |
| Session Storage | Redis (token-based sessions) |

---

## 📁 Project Structure

```
GUVI-Task/
├── index.html          # Redirects to login
├── register.html       # User registration page
├── login.html          # User login page
├── profile.html        # User profile page
├── composer.json       # PHP dependencies
├── composer.lock
├── setup.sql           # MySQL database schema
├── .env                # Environment variables (MongoDB credentials)
├── .gitignore
├── css/
│   └── style.css       # Custom styles
├── js/
│   ├── register.js     # Registration form logic
│   ├── login.js        # Login form logic
│   └── profile.js      # Profile page logic
├── php/
│   ├── config.php      # Database connections & helpers
│   ├── register.php    # Registration API endpoint
│   ├── login.php       # Login/Logout API endpoint
│   └── profile.php     # Profile GET/POST API endpoint
└── vendor/             # Composer dependencies
```

---

## ⚡ Quick Start (macOS)

### Prerequisites
- Homebrew installed
- PHP 8.x
- MySQL
- Redis
- MongoDB Atlas account (free)

### 1. Clone the Repository

```bash
git clone https://github.com/PiruthiviP/GUVI-Task.git
cd GUVI-Task
```

### 2. Start MySQL & Redis

```bash
brew services start mysql
brew services start redis
```

### 3. Create MySQL Database

```bash
mysql -u root -p < setup.sql
```

### 4. Set Up MongoDB Atlas

1. Go to [MongoDB Atlas](https://cloud.mongodb.com) and create a free cluster
2. Create a database user (Database Access → Add New Database User)
3. Whitelist your IP (Network Access → Add IP Address → Add Current IP)
4. Get your connection string (Connect → Connect your application → PHP)

### 5. Configure Environment Variables

Create a `.env` file in the project root:

```env
MONGO_URI=mongodb+srv://YOUR_USERNAME:YOUR_PASSWORD@YOUR_CLUSTER.mongodb.net/?retryWrites=true&w=majority
MONGO_DB=user_app
MONGO_COLL=profiles
```

> ⚠️ **Important**: URL-encode special characters in your password:
> - `@` → `%40`
> - `:` → `%3A`
> - `/` → `%2F`

### 6. Install PHP Extensions

```bash
# Install from source (recommended for PHP 8.5+)
cd /tmp && git clone --depth 1 https://github.com/phpredis/phpredis.git
cd phpredis && phpize && ./configure && make && sudo make install

cd /tmp && git clone --depth 1 https://github.com/mongodb/mongo-php-driver.git
cd mongo-php-driver && git submodule update --init
phpize && ./configure && make && sudo make install
```

Add to your `php.ini`:
```ini
extension=redis.so
extension=mongodb.so
```

Verify installation:
```bash
php -m | grep -E 'redis|mongodb'
```

### 7. Install Composer Dependencies

```bash
composer install
```

### 8. Run the Application

```bash
php -S localhost:8000
```

Open **http://localhost:8000** in your browser.

---

## 🔄 Application Flow

```
Register → Login → Profile (View/Edit) → Logout
```

1. **Register**: Create account (stored in MySQL)
2. **Login**: Authenticate & receive session token (stored in Redis)
3. **Profile**: View/update profile details (stored in MongoDB Atlas)
4. **Logout**: Invalidate session token

---

## ⚙️ Configuration

### MySQL (`php/config.php`)

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'user_app');
define('DB_USER', 'root');
define('DB_PASS', '');  // Set your MySQL password
```

### MongoDB (`.env` file)

```env
MONGO_URI=mongodb+srv://user:pass@cluster.mongodb.net/...
MONGO_DB=user_app
MONGO_COLL=profiles
```

### Redis (`php/config.php`)

```php
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('SESSION_TTL', 86400);  // 24 hours
```

---

## 🔧 Troubleshooting

### MongoDB Connection Issues

1. **Authentication failed**: Check username/password in `.env`, ensure special characters are URL-encoded
2. **Connection closed**: Add your IP to Network Access in MongoDB Atlas
3. **No suitable servers**: Wait 1-2 minutes after adding IP, or use `0.0.0.0/0` for testing

### Redis Connection Issues

```bash
# Check if Redis is running
redis-cli ping  # Should return: PONG

# Restart Redis
brew services restart redis
```

### PHP Extension Issues

```bash
# Check loaded extensions
php -m | grep -E 'redis|mongodb'

# Find php.ini location
php --ini | grep "Loaded Configuration"
```

---

## 📝 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/php/register.php` | POST | Create new user account |
| `/php/login.php` | POST | Login (action: login/logout) |
| `/php/profile.php` | GET | Get user profile |
| `/php/profile.php` | POST | Update user profile |

---

## 👤 Author

**PiruthiviP**

- GitHub: [@PiruthiviP](https://github.com/PiruthiviP)

---

## 📄 License

This project is for educational purposes (GUVI Task).
