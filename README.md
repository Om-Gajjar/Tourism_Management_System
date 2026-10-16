# Tourism Management System

A beginner-friendly, minimalistic PHP/MySQL web application for managing tourist packages and reservations.

## 🎯 Features

### Public Features
- **Package Browsing** - View all tour packages with images, prices, and descriptions
- **Package Details** - Detailed view with booking option
- **User Registration & Login** - Secure authentication with password hashing
- **Booking System** - Book packages with instant confirmation
- **My Bookings** - View and cancel personal bookings
- **Profile Management** - Update name, email, and password
- **Contact Form** - Send enquiries to admin

### Admin Features
- **Dashboard** - Statistics: packages, bookings, users, revenue
- **Package Management** - Add, edit, delete tour packages
- **Booking Management** - View all bookings, update status (pending/confirmed/cancelled)
- **Enquiry Management** - View enquiries with slide-out drawer for full details; mark as read/unread; reply via email

## 🛠 Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.x (Procedural) |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5.3 (CDN) |
| Icons | Bootstrap Icons 1.10 |
| Font | Inter (Google Fonts) |
| Security | PDO Prepared Statements, password_hash, CSRF Tokens |

## 📋 Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server
- PDO MySQL extension

## 🚀 Quick Start

### 1. Clone & Navigate
```bash
cd /home/kali/tourism-project
```

### 2. Database Setup
```bash
# Login to MySQL as root
sudo mysql

# In MySQL shell:
CREATE DATABASE tourism_db;
CREATE USER 'tourism_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tourism_db.* TO 'tourism_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
sudo mysql tourism_db < database/schema.sql
```

### 3. Configure Database
Edit `src/config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tourism_db');
define('DB_USER', 'tourism_user');
define('DB_PASS', 'your_secure_password');
```

### 4. Start Server

**Option A: Using the start script (recommended)**
```bash
# Make script executable (first time only)
chmod +x start.sh

# Start ALL services (MariaDB + Apache/phpMyAdmin + PHP server)
./start.sh start

# Check status of all services
./start.sh status

# Stop all services
./start.sh stop

# Restart all services
./start.sh restart
```

The start script manages:
- **MariaDB** - Database server (port 3306)
- **Apache** - Web server for phpMyAdmin (http://localhost/phpmyadmin)
- **PHP built-in server** - Your website (http://localhost:8000)

**Option B: Manual start**
```bash
# Start MariaDB
sudo service mysql start

# Start Apache (for phpMyAdmin)
sudo service apache2 start

# Start PHP built-in server with router (development)
php -S localhost:8000 router.php
```

**Option C: Apache / XAMPP / WAMP**

If you are running on Apache, XAMPP, or WAMP, two `.htaccess` files handle routing automatically — no configuration needed:

- `.htaccess` (root) → Redirects all traffic to `public/` and blocks access to `src/`, `database/`, `logs/`, etc.
- `public/.htaccess` → Handles clean URL routing and sets security headers inside the web root.

Just point your Apache `DocumentRoot` to either:
- The **project root folder** and the root `.htaccess` will forward traffic to `public/`, or
- Directly to the **`public/` subfolder** and `public/.htaccess` handles everything.


### 5. Access Application

| Service | URL | Credentials |
|---------|-----|-------------|
| **Public Website** | http://localhost:8000 | - |
| **Admin Panel** | http://localhost:8000/admin/login.php | `admin` / `admin123` |
| **phpMyAdmin** | http://localhost/phpmyadmin | `tourism_user` / `password` |

## ☁️ Production deployment

For a Render Docker web service backed by Aiven MySQL, follow
[docs/DEPLOYMENT_RENDER_AIVEN.md](docs/DEPLOYMENT_RENDER_AIVEN.md). It covers
Aiven TLS, schema import, Render environment variables, health checks, and the
fact that Render's local filesystem is not persistent for uploaded images.

For a traditional PHP/MySQL upload to InfinityFree, follow
[docs/DEPLOYMENT_INFINITYFREE.md](docs/DEPLOYMENT_INFINITYFREE.md). It uses a
server-only local configuration file while preserving the Render/Aiven
environment-variable deployment.

## 📁 Project Structure

```
tourism-project/
├── README.md                # 📖 Main documentation (GitHub landing)
├── LICENSE                  # ⚖️ MIT License
├── .gitignore               # 🚫 Git ignore rules
├── .htaccess                # 🛡️ Apache root routing (XAMPP/WAMP/shared hosting)
├── router.php               # 🛡️ PHP CLI server router & security firewall
├── start.sh                 # 🚀 Service control script (MariaDB, Apache, PHP)
├── public/                  # 🌐 Web Document Root (Exposed to web)
│   ├── index.php            # Homepage & tour listings
│   ├── register.php         # User registration with phone support
│   ├── login.php            # User authentication
│   ├── logout.php           # Secure session termination
│   ├── contact.php          # Contact form & package inquiry handler
│   ├── booking.php          # Dynamic booking with real-time price calculation
│   ├── my_bookings.php      # Customer bookings & review submission
│   ├── profile.php          # User profile & contact management
│   ├── package_details.php  # Tour details, reviews & inquiry trigger
│   ├── css/
│   │   └── style.css        # Inter design system, badges & drawer styling
│   ├── .htaccess            # 🛡️ Apache public webroot routing & security headers
│   └── admin/               # 🔒 Admin Management Panel
│       ├── index.php        # Admin dashboard & package manager
│       ├── login.php        # Standalone admin login with rate limiting
│       ├── enquiries.php    # Customer message desk & drawer triggers
│       ├── enquiry_detail.php # Slide-out AJAX enquiry detail panel
│       ├── edit_package.php # Package editor
│       ├── delete_package.php # Package deletion handler
│       └── update_booking.php # Dual status & payment management
├── src/                     # 🛡️ Private Application Logic (Protected)
│   ├── config/
│   │   ├── config.php       # Centralized application configuration
│   │   └── config.php.example # Configuration template
│   ├── db/
│   │   └── db.php           # PDO database connection layer
│   ├── helpers/
│   │   └── helpers.php      # Security, CSRF, formatting & badge helpers
│   └── views/               # Layout Templates
│       ├── public/          # Public header & footer includes
│       │   ├── header.php
│       │   └── footer.php
│       └── admin/           # Admin header & footer includes
│           ├── header.php
│           └── footer.php
├── database/
│   └── schema.sql           # Database schema, foreign keys, indexes & seed data
├── docs/                    # 📚 Project Documentation
│   ├── DEVELOPER.md         # Technical architecture & extension guide
│   ├── CHANGELOG.md         # Version release history
│   └── CONTRIBUTING.md      # Contribution standards & code review guide
├── logs/                    # 📋 Runtime logs (git-ignored)
│   └── .gitkeep
├── screenshots/             # 🖼️ Interface previews & screenshots
└── tests/                   # 🧪 Automated verification & test suites
```

> **Note:** Only `public/` is exposed to the web. Start the server with `php -S localhost:8000 -t public/`. All application code in `src/` and `database/` remains private.

## 🔐 Security Features

- **SQL Injection Prevention** - All queries use PDO prepared statements
- **Password Security** - `password_hash()` (bcrypt) and `password_verify()`
- **XSS Protection** - `htmlspecialchars()` via `e()` helper on all output
- **CSRF Protection** - Unique tokens on every form, validated server-side
- **Session Security** - HttpOnly, SameSite=Lax, strict mode, regeneration on login
- **Input Sanitization** - `sanitizeInput()` strips tags and trims whitespace

## 🎨 Design System

### Colors
```css
--primary: #2563eb;      /* Royal Blue */
--primary-hover: #1d4ed8;
--secondary: #64748b;    /* Slate */
--success: #059669;      /* Emerald */
--danger: #dc2626;       /* Red */
--warning: #d97706;      /* Amber */
--bg: #f8fafc;           /* Slate 50 */
--card-bg: #ffffff;      /* White */
--text: #1e293b;         /* Slate 800 */
--text-muted: #64748b;   /* Slate 500 */
```

### Typography
- **Font Family**: Inter (300-700 weights)
- **Base Size**: 1rem (16px)
- **Headings**: 600-700 weight

### Components
- **Cards**: 16px radius, subtle shadow, hover lift
- **Buttons**: Pill-shaped (50px radius), 600 weight
- **Badges**: Semantic colors for status states
- **Forms**: Clean inputs with focus states

## 📖 Code Organization

### Centralized Helpers (`src/helpers/helpers.php`)

| Category | Functions |
|----------|-----------|
| Database | `getDbConnection()` |
| Session | `initSession()` |
| Authentication | `isLoggedIn()`, `isAdminLoggedIn()`, `requireAuth()`, `requireAdminAuth()`, `getCurrentUserId()`, `getCurrentUserName()` |
| CSRF | `generateCsrfToken()`, `verifyCsrfToken()` |
| Output | `e()` - HTML escaping |
| Input | `sanitizeInput()`, `validateEmail()` |
| Flash Messages | `redirectWithMessage()`, `getFlashMessage()`, `displayFlashMessage()` |
| Utilities | `formatDate()`, `getStatusBadgeClass()`, `getStatusLabel()` |

### Usage Examples

**Database Query:**
```php
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();
```

**Secure Form with CSRF:**
```php
<form method="POST">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
    <!-- form fields -->
</form>
```

**Output Escaping:**
```php
<h1><?= e($package['name']) ?></h1>
```

**Flash Message Redirect:**
```php
redirectWithMessage('index.php', 'Package added!', 'success');
```

**Display Flash in Template:**
```php
<?php displayFlashMessage(); ?>
```

## 🗄 Database Schema

### Tables
1. **users** - User accounts (name, email, phone, password, timestamps)
2. **packages** - Tour packages (destination, price, duration, capacity, featured, active status)
3. **bookings** - User bookings (travel date, number of travelers, total price lock-in, booking status, payment status, special requests)
4. **reviews** - Verified customer star ratings & feedback (1-5 stars, comments)
5. **enquiries** - Contact inquiries & tour questions (linked user, linked tour package, phone, unread/read/replied status)

See `database/schema.sql` for complete structure and sample data.

## 🔧 Configuration

### `src/config/config.php`
```php
// Application
define('APP_NAME', 'Tourism MS');
define('APP_URL', 'http://localhost:8000');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'tourism_db');
define('DB_USER', 'tourism_user');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SESSION_LIFETIME', 7200);      // 2 hours
define('PASSWORD_MIN_LENGTH', 6);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Admin (change in production!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] Homepage loads with package cards
- [ ] Register new user → email validation → success
- [ ] Login with valid credentials → redirect to home
- [ ] Login with invalid credentials → error message
- [ ] Book a package → confirmation → appears in My Bookings
- [ ] Cancel booking → status updates
- [ ] Update profile → changes persist
- [ ] Change password → works correctly
- [ ] Contact form → enquiry saved
- [ ] Admin login → dashboard statistics
- [ ] Admin add/edit/delete packages
- [ ] Admin update booking status
- [ ] Admin view/mark enquiries read
- [ ] Admin enquiry drawer shows full message details

### Automated Tests (curl)
```bash
# Test all public pages
for page in index.php register.php login.php contact.php; do
    curl -s -o /dev/null -w "$page: %{http_code}\n" http://localhost:8000/$page
done

# Test auth protection
for page in my_bookings.php profile.php; do
    curl -s -o /dev/null -w "$page: %{http_code}\n" http://localhost:8000/$page
done
```

## 🚀 Deployment Checklist

- [ ] Change `APP_URL` to production domain
- [ ] Set `DB_PASS` to strong password
- [ ] Change `ADMIN_USERNAME` / `ADMIN_PASSWORD`
- [ ] Set `ini_set('display_errors', 0);` in config.php
- [ ] Enable HTTPS and set `session.cookie_secure = 1`
- [ ] Configure web server (Apache/Nginx)
- [ ] Set up SSL certificate
- [ ] Configure PHP-FPM (if using Nginx)
- [ ] Set proper file permissions (644 files, 755 dirs)
- [ ] Disable directory listing
- [ ] Set up database backups

## 📝 License

Educational project - free to use and modify.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Open Pull Request

## 📞 Support

For issues, check:
1. PHP error logs
2. MySQL error logs
3. Browser developer console
4. Network tab for failed requests