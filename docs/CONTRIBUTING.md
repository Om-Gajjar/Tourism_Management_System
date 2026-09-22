# Contributing to Tourism Management System

Thank you for your interest in contributing! This document provides guidelines for contributing to this project.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Keep discussions on-topic

## How to Contribute

### Reporting Bugs

1. Check existing issues first
2. Create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - Environment (PHP version, MySQL version, browser)
   - Screenshots if applicable

### Suggesting Features

1. Open an issue with "Feature Request" label
2. Describe the feature and its benefits
3. Consider implementation complexity
4. Discuss with maintainers before implementing

### Pull Requests

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes
4. Test thoroughly
5. Commit with clear messages
6. Push to your fork
7. Open a Pull Request

## Development Setup

```bash
# 1. Fork and clone
git clone https://github.com/your-username/tourism-project.git
cd tourism-project

# 2. Set up database
sudo mysql < database/schema.sql

# 3. Configure
cp src/config/config.php.example src/config/config.php
# Edit src/config/config.php with your database credentials

# 4. Start server
php -S localhost:8000 -t public/
```

## Coding Standards

### PHP Style Guide

- **Paradigm**: Procedural (no classes)
- **Indentation**: 4 spaces
- **Braces**: Opening brace on same line
- **Naming**: 
  - Functions: `camelCase` (e.g., `getDbConnection()`)
  - Variables: `snake_case` (e.g., `$user_id`)
  - Constants: `UPPER_SNAKE_CASE` (e.g., `DB_HOST`)
- **Type Hints**: Use for function parameters and return types
- **Comments**: DocBlock for functions, inline for complex logic

```php
/**
 * Get database connection instance
 * @return PDO
 */
function getDbConnection(): PDO { ... }

// Good variable names
$user_id = getCurrentUserId();
$package_name = sanitizeInput($_POST['name']);

// Prepared statements ALWAYS
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### Security Requirements

Every PR must:
- [ ] Use prepared statements for ALL database queries
- [ ] Escape ALL output with `e()` function
- [ ] Sanitize ALL input with `sanitizeInput()`
- [ ] Include CSRF token on ALL forms
- [ ] Check authentication on protected pages
- [ ] Never log or expose passwords
- [ ] Validate file uploads (if any)

### HTML/CSS Standards

- Use Bootstrap 5 utility classes first
- Custom CSS in `public/css/style.css` only
- Use CSS variables from design system
- Mobile-first responsive design
- Semantic HTML5 elements
- Accessible markup (labels, alt text, ARIA when needed)

### Git Commit Messages

Format: `type(scope): description`

Types:
- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code restructuring
- `docs`: Documentation
- `style`: Formatting, no logic change
- `test`: Adding tests
- `chore`: Maintenance

Examples:
```
feat(auth): add password reset functionality
fix(booking): prevent duplicate bookings for same package
refactor(helpers): extract date formatting to utility function
docs(readme): add deployment checklist
```

## Testing Checklist

Before submitting PR, verify:

### Functional
- [ ] All existing features work
- [ ] New feature works as expected
- [ ] Edge cases handled (empty inputs, invalid data)
- [ ] Error messages user-friendly
- [ ] Admin enquiry drawer shows full message details
- [ ] Admin enquiry mark read/unread toggles status correctly

### Security
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] CSRF protection on new forms
- [ ] Auth checks on new protected pages
- [ ] Input validation and sanitization

### Design
- [ ] Consistent with design system
- [ ] Responsive on mobile/tablet/desktop
- [ ] No console errors
- [ ] Accessible (keyboard nav, screen readers)

### Code Quality
- [ ] No duplicate code (use helpers)
- [ ] Functions have single responsibility
- [ ] Clear variable/function names
- [ ] Comments for complex logic
- [ ] No debug code left behind

## Project Structure Guidelines

```
tourism-project/
├── README.md              # 📖 Main repository overview & quickstart
├── LICENSE                # ⚖️ MIT License
├── .gitignore             # 🚫 Git ignore rules
├── router.php             # 🛡️ Built-in server router & directory guard
├── start.sh               # 🚀 All-in-one start/stop service control script
├── public/                # 🌐 Web Document Root (only public/ is reachable)
│   ├── *.php              # Public customer pages
│   ├── css/
│   │   └── style.css      # Custom design system & component styles
│   └── admin/
│       └── *.php          # Admin management portal
├── src/                   # 🔒 Private code layer (never web-accessible)
│   ├── config/
│   │   ├── config.php            # Configuration ONLY
│   │   └── config.php.example
│   ├── db/
│   │   └── db.php                # DB connection
│   ├── helpers/
│   │   └── helpers.php           # Security & utility functions
│   └── views/
│       ├── public/
│       │   ├── header.php        # Public header/navbar
│       │   └── footer.php        # Public footer
│       └── admin/
│           ├── header.php        # Admin header/navbar
│           └── footer.php        # Admin footer
├── database/
│   └── schema.sql         # Database schema, relationships & seed data
├── docs/                  # 📚 Project documentation
│   ├── DEVELOPER.md
│   ├── CHANGELOG.md
│   └── CONTRIBUTING.md
├── logs/                  # 📋 Server logs (.gitkeep)
├── screenshots/           # 🖼️ Interface previews
└── tests/                 # 🧪 Automated test suites
```

### Where to Put Code

| Code Type | Location |
|-----------|----------|
| Configuration constants | `src/config/config.php` |
| Utility functions | `src/helpers/helpers.php` |
| Database connection | `src/db/db.php` |
| Public layout | `src/views/public/header.php`, `src/views/public/footer.php` |
| Admin layout | `src/views/admin/header.php`, `src/views/admin/footer.php` |
| Custom styles | `public/css/style.css` |
| Public pages | `public/*.php` |
| Admin pages | `public/admin/*.php` |
| Database changes | `database/schema.sql` |

## Review Process

1. Automated checks (if configured)
2. Maintainer reviews code
3. Security review for sensitive changes
4. Design review for UI changes
5. Approval and merge

## Questions?

Open an issue with "Question" label or start a discussion.

---

**Thank you for contributing!** 🎉