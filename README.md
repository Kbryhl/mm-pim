# PIM System - Product Information Management

A comprehensive Product Information Management (PIM) system built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### Phase 1 (Current)
- [x] Project structure setup
- [x] Basic dashboard
- [ ] Product management (CRUD operations)
- [ ] Category management
- [ ] Product attributes
- [ ] User authentication

### Future Phases
- Multi-language support
- Advanced filtering and search
- Import/Export functionality (CSV, XML)
- API for third-party integrations
- Analytics and reporting
- Bulk operations
- Workflow management
- Version control for products

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Package Manager**: Composer (for PHP dependencies)

## Project Structure

```
pim-system/
├── public/              # Web root
│   └── index.php        # Main entry point
├── src/                 # Source code
│   ├── Controllers/     # Application controllers
│   ├── Models/          # Data models
│   └── Utils/           # Utility classes
├── config/              # Configuration files
│   ├── app.php          # App settings
│   └── database.php     # Database configuration
├── database/            # Database related
│   └── schema.sql       # Database schema
├── views/               # HTML templates
├── assets/              # Static assets
│   ├── css/             # Stylesheets
│   └── images/          # Images and icons
├── js/                  # JavaScript files
├── api/                 # API endpoints
└── README.md            # This file
```

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependencies)
- A web server (Apache, Nginx, or PHP built-in)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pim-system
   ```

2. **Create database**
   ```sql
   CREATE DATABASE pim_system;
   ```

3. **Update database configuration**
   Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'pim_system');
   ```

4. **Install dependencies** (if using Composer)
   ```bash
   composer install
   ```

5. **Run development server**
   ```bash
   php -S localhost:8000 -t public/
   ```

6. **Access the application**
   Open your browser and go to `http://localhost:8000`

## Usage

### Basic Operations

#### 1. Products Management
- Add new products
- Edit product details
- Delete products
- Bulk operations

#### 2. Categories
- Create product categories
- Organize hierarchies
- Set category attributes

#### 3. Attributes
- Define product attributes
- Set attribute types and values
- Apply attributes to products

## API Endpoints

The system includes RESTful API endpoints for programmatic access:

### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get specific product
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Categories
- `GET /api/categories` - List all categories
- `POST /api/categories` - Create category
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category

### Attributes
- `GET /api/attributes` - List all attributes
- `POST /api/attributes` - Create attribute
- `PUT /api/attributes/{id}` - Update attribute
- `DELETE /api/attributes/{id}` - Delete attribute

## Development Workflow

### Adding a Feature

1. Create appropriate files in `src/` directory
2. Add API endpoint in `api/` directory
3. Update version number in `config/app.php`
4. Test thoroughly
5. Commit changes with descriptive messages
6. Push to GitHub

### Code Standards

- Follow PSR-12 PHP code style
- Use meaningful variable and function names
- Add comments for complex logic
- Write reusable, DRY code

## Database Schema

The application uses the following main tables:

```sql
-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE,
    description TEXT,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attributes table
CREATE TABLE attributes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product attributes join table
CREATE TABLE product_attributes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    attribute_id INT,
    value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id)
);
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues, questions, or suggestions, please open an issue on GitHub.

## Roadmap

- [ ] User authentication and authorization
- [ ] Advanced search and filtering
- [ ] Product variants support
- [ ] Multi-language support
- [ ] API documentation (Swagger)
- [ ] Performance optimization
- [ ] Mobile app
- [ ] Webhook support
- [ ] GraphQL API
- [ ] Caching layer

## Changelog

### v1.0.0 (Initial Release)
- Project structure setup
- Basic dashboard
- Initial configuration
