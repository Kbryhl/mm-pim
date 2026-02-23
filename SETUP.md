# PIM System - Quick Start Guide

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Git (for GitHub integration)

## Installation Steps

### 1. Create Database

Open MySQL client and run:

```sql
CREATE DATABASE pim_system;
USE pim_system;
```

Then import the schema:
```bash
mysql -u root -p pim_system < database/schema.sql
```

### 2. Configure Database Connection

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'pim_system');
define('DB_PORT', 3306);
```

### 3. Run Development Server

```bash
cd c:\Users\KennethBryhl\VSCode\STAMDATA
php -S localhost:8000 -t public/
```

### 4. Access Application

Open your browser and navigate to:
```
http://localhost:8000
```

You will be redirected to the login page.

## Default User Account

After running the schema, create a test user via the registration page:
- Visit `http://localhost:8000/register`
- Fill in the form
- Click "Create Account"
- Login with your credentials

## Default Admin User (Optional)

To create an admin user directly in the database:

```sql
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
VALUES (
    'admin',
    'admin@example.com',
    '$2y$10$...',  -- bcrypt hash of your password
    'Admin',
    'User',
    'admin',
    1
);
```

To generate a bcrypt hash in PHP:
```php
<?php
echo password_hash('password', PASSWORD_DEFAULT);
?>
```

## Project Features Implemented

✅ **User Authentication**
- User registration
- User login
- Session management
- Password hashing with bcrypt
- CSRF protection tokens
- Role-based access control (admin, manager, editor, viewer)

## File Structure

```
├── public/              # Web root
│   └── index.php        # Entry point & routing
├── src/                 # Source code
│   ├── Models/          # Data models (User, Product, etc.)
│   ├── Controllers/     # Business logic controllers
│   └── Utils/           # Utility classes (Auth, Database, etc.)
├── config/              # Configuration files
├── database/            # Database schema
├── views/               # HTML templates
├── assets/              # Static assets (CSS, images)
├── js/                  # JavaScript files
├── api/                 # API endpoints
└── README.md            # Documentation
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login user
- `POST /api/auth/register` - Register new user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user

### Example: Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

Response:
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "username": "user",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "editor"
  }
}
```

## Authentication Classes

### Auth Utility (`src/Utils/Auth.php`)
```php
Auth::startSession();           // Start PHP session
Auth::isAuthenticated();        // Check if user is logged in
Auth::getCurrentUser();         // Get current user object
Auth::getCurrentUserId();       // Get current user ID
Auth::setUserSession($user);    // Set user session
Auth::logout();                 // Logout user
Auth::hashPassword($pass);      // Hash password
Auth::verifyPassword($pass, $hash);  // Verify password
Auth::requireAuth();            // Require authentication (redirect if not)
Auth::requireAdmin();           // Require admin role
Auth::generateCsrfToken();      // Generate CSRF token
Auth::verifyCsrfToken($token);  // Verify CSRF token
```

### User Model (`src/Models/User.php`)
```php
$user = new User($database);

$user->create($data);           // Create new user
$user->getById($id);            // Get user by ID
$user->getUserByEmail($email);  // Get user by email
$user->authenticate($email, $password);  // Authenticate user
$user->update($id, $data);      // Update user
$user->delete($id);             // Delete user
$user->getAll($limit, $offset); // Get all users
```

### Database Utility (`src/Utils/Database.php`)
```php
$db = new Database($connection);

$db->query($sql, $params);      // Execute query
$db->getRow($sql, $params);     // Get single row
$db->getRows($sql, $params);    // Get multiple rows
$db->insert($table, $data);     // Insert data
$db->update($table, $data, $where, $whereParams);  // Update data
$db->delete($table, $where, $params);  // Delete data
```

## Frontend Authentication

The `js/auth.js` provides client-side API for authentication:

```javascript
// Login
await AuthAPI.login('user@example.com', 'password');

// Register
await AuthAPI.register({
    username: 'newuser',
    email: 'user@example.com',
    password: 'password123',
    first_name: 'John',
    last_name: 'Doe'
});

// Logout
await AuthAPI.logout();

// Get current user
const user = await AuthAPI.getCurrentUser();

// Check authentication status
const isAuth = await AuthAPI.isAuthenticated();
```

## Troubleshooting

### "Connection failed" error
- Check database credentials in `config/database.php`
- Ensure MySQL server is running
- Verify database exists

### "Session error" when logging in
- Ensure PHP sessions are enabled
- Check file permissions on temporary directories
- Verify `session.save_path` in php.ini

### Password verification fails
- Ensure PHP has `ext-hash` extension enabled
- Check that password hashing uses PASSWORD_DEFAULT

## Next Steps

The next feature to implement can be:
1. **Product Management** - Create, read, update, delete products
2. **Category Management** - Organize products into categories
3. **Attribute System** - Define product attributes
4. **Advanced Search** - Search and filter products
5. **User Management** - Admin panel for managing users

## Git Integration

Initialize git and push to GitHub:

```bash
git init
git config user.name "Your Name"
git config user.email "your.email@example.com"
git add .
git commit -m "Initial commit: Authentication system"
git remote add origin https://github.com/username/pim-system.git
git branch -M main
git push -u origin main
```

## Support

For issues or questions, refer to the main README.md or check the code comments.
