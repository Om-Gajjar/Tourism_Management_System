# Changelog

All notable changes to the Tourism Management System will be documented in this file.

## [1.2.0] - 2026-09-21

### Added
- **Database Schema Modernization**
  - Added `phone` and `updated_at` to `users` table
  - Added `travel_date`, `number_of_travelers`, `total_price` (price lock-in), `payment_status` (`pending`, `paid`, `refunded`), `special_requests`, and `updated_at` to `bookings` table
  - Added `is_active`, `featured`, `max_travelers`, and `updated_at` to `packages` table
  - Created new `reviews` table with 1-5 star ratings, comments, and customer attribution
  - Added comprehensive performance indexes across all tables (`destination`, `price`, `active`, `travel_date`, `status`, `payment_status`)
- **Booking Flow Enhancements**
  - Date picker for departure date (min date validation)
  - Interactive traveler count selector with real-time price calculation
  - Support for special customer requests
  - Multi-status lifecycle tracking (Booking status & Payment status)
- **Reviews & Ratings System**
  - Star ratings on homepage package cards and package detail pages
  - Customer review submission and edit modal from "My Bookings" and "Package Details"
- **Admin Dashboard Expansion**
  - Customer phone, travel dates, guests, and payment statuses displayed in bookings table
  - Quick action status dropdowns for both Booking Status and Payment Status
  - Accurate confirmed revenue tracking based on `total_price`

## [1.1.1] - 2026-09-20

### Added
- **Enquiry Detail Drawer** - Slide-out panel showing full enquiry details (sender, email, date, status, full message)
- **Mark Read/Unread in Drawer** - Toggle status directly from drawer without page reload
- **Reply via Email** - One-click reply button in drawer opens default email client
- **AJAX Loading** - Drawer loads enquiry details via fetch API without page refresh
- **Visual Feedback** - Status badge and action button in main table update instantly when toggling

### Changed
- Enquiry rows now clickable to open drawer
- Removed auto-mark-as-read (user now controls when to mark status)

### Changed
- **Foldering restructure**: `public/` is now the sole web root; server docroot is `public/` (`php -S localhost:8000 -t public/`)
- Moved `admin/` inside `public/` → `public/admin/`
- Moved all application code into private `src/` layer (no longer web-accessible):
  - `includes/config.php` → `src/config/config.php`
  - `includes/helpers.php` → `src/helpers/helpers.php`
  - `includes/db.php` → `src/db/db.php`
  - `includes/header.php` / `includes/footer.php` → `src/views/public/`
  - `admin/includes/header.php` / `footer.php` → `src/views/admin/`
- Moved schema to `database/schema.sql`
- Removed stale root-level `css/`, `js/`, `images/`, and unreferenced `includes/navbar.php`
- Updated all include paths in public pages (`../src/...`) and admin pages (`../../src/...`)

### Added
- End-to-end test suite `test_e2e.py` (18 checks: pages, auth, booking, admin flows)

## [1.1.0] - 2026-09-12

### Added
- **Core Application Structure**
  - Public pages: Homepage, Register, Login, Contact, Booking, My Bookings, Profile, Package Details
  - Admin pages: Login, Dashboard, Package Management, Booking Management, Enquiry Management
  - Database schema with users, packages, bookings, enquiries tables

- **Security Implementation**
  - PDO prepared statements for all database queries
  - Password hashing with `password_hash()` (bcrypt)
  - CSRF protection on all forms
  - XSS prevention with `htmlspecialchars()` wrapper
  - Secure session configuration (HttpOnly, SameSite=Lax, strict mode)
  - Session regeneration on login

- **Code Quality Improvements**
  - Centralized configuration in `src/config/config.php`
  - Shared helper functions in `src/helpers/helpers.php`
  - Unified header/footer includes for public and admin sections
  - DRY principles - eliminated code duplication

- **Design System**
  - Modern minimalistic UI with Inter font
  - CSS custom properties for consistent theming
  - Bootstrap 5.3 via CDN
  - Consistent color palette: Primary (#2563eb), Success (#059669), etc.
  - Card-based layout with subtle shadows and hover animations
  - Pill-shaped buttons with lift hover effect
  - Semantic status badges

- **Functionality**
  - User registration with validation
  - Secure login/logout
  - Package browsing and details
  - Booking system with status tracking
  - User profile management (name, email, password)
  - Contact form with enquiry storage
  - Admin dashboard with statistics
  - Admin CRUD for packages
  - Admin booking status updates
  - Admin enquiry read/unread toggle

### Changed
- Refactored all pages to use centralized helpers
- Replaced inline database connections with `getDbConnection()`
- Standardized auth checks with `requireAuth()` / `requireAdminAuth()`
- Unified flash message system with `redirectWithMessage()` / `displayFlashMessage()`
- Consolidated navbar.php into header.php/footer.php pattern
- Updated admin pages to use shared admin header/footer

### Fixed
- Database connection path issues in admin includes
- CSRF token handling across all forms
- Session persistence across requests
- Admin login redirect loop
- Package deletion with foreign key constraints

### Documentation
- Added comprehensive README.md
- Added DEVELOPER.md for technical details
- Updated database/schema.sql with sample data and all required tables

## [0.1.0] - Initial Prototype

### Added
- Basic PHP/MySQL tourism system
- Simple package listing
- Basic user authentication
- Admin package management