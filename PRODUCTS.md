# Product Management System - Quick Reference

## Features Implemented

### ✅ Product Management (CRUD Operations)

1. **List Products** (`/products`)
   - Displays all products in a table format
   - Search by name, SKU, or description
   - Filter by status (active, inactive, draft)
   - Filter by category
   - Pagination with next/previous navigation
   - Display: Name, SKU, Category, Price, Status, Created Date

2. **Add New Product** (`/products/new`)
   - Create products with basic information
   - Set name, SKU, description, price
   - Assign category
   - Set status (draft, active, inactive)
   - Add product image via URL
   - Image preview before saving
   - Auto-slug generation for categories

3. **Edit Product** (`/products/edit?id=X`)
   - Modify all product details
   - Edit basic information
   - Update pricing and organization
   - Change product image
   - Manage product attributes
   - Real-time validation

4. **Delete Product**
   - Bulk delete or individual delete
   - Confirmation dialog
   - Cascading delete of related attributes

5. **Bulk Operations**
   - Select multiple products
   - Bulk status update (activate/deactivate)
   - Bulk delete
   - Select all / clear selection
   - Action counter

### ✅ Category Management

Key features:
- Hierarchical category structure (parent/child relationships)
- Slug auto-generation
- Active/Inactive status
- Product count per category
- Category tree view
- Unique names per category

### ✅ Attribute Management

Key features:
- Multiple attribute types:
  - Text
  - Number
  - Select (dropdown)
  - Multiselect
  - Date
  - Boolean
  - Image
- Attribute options (for select/multiselect)
- Filterable/Searchable flags
- Sort order control
- Product attribute values

## File Structure - New Files Created

```
src/Models/
├── Product.php          # Product data and operations
├── Category.php         # Category data and operations
└── Attribute.php        # Attribute data and operations

api/
├── products.php         # Product API endpoints
├── categories.php       # Category API endpoints
└── attributes.php       # Attribute API endpoints

views/
├── products.php         # Products list page
└── product-form.php     # Add/Edit product form

js/
└── products.js          # Product UI management

assets/css/
└── products.css         # Product page styling
```

## API Endpoints Reference

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | Get all products (with pagination & filters) |
| GET | `/api/products/:id` | Get single product with attributes |
| POST | `/api/products` | Create new product |
| PUT | `/api/products/:id` | Update product |
| DELETE | `/api/products/:id` | Delete product |
| PUT | `/api/products/bulk/status` | Bulk update status |
| GET | `/api/products/stats` | Get product statistics |

**Query Parameters for GET /api/products:**
- `limit` - Results per page (default: 50)
- `offset` - Pagination offset (default: 0)
- `search` - Search by name, SKU, description
- `status` - Filter by status (active, inactive, draft)
- `category_id` - Filter by category

### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | Get all categories |
| GET | `/api/categories/tree` | Get hierarchical category tree |
| GET | `/api/categories/:id` | Get single category with product count |
| POST | `/api/categories` | Create new category |
| PUT | `/api/categories/:id` | Update category |
| DELETE | `/api/categories/:id` | Delete category |

### Attributes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/attributes` | Get all attributes |
| GET | `/api/attributes/filterable` | Get filterable attributes only |
| GET | `/api/attributes/:id` | Get attribute with options |
| POST | `/api/attributes` | Create new attribute |
| PUT | `/api/attributes/:id` | Update attribute |
| DELETE | `/api/attributes/:id` | Delete attribute |

## Example API Calls

### Create a Product
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop",
    "sku": "LAPTOP-001",
    "description": "High-performance laptop",
    "price": 1299.99,
    "category_id": 1,
    "status": "active",
    "image_url": "https://example.com/laptop.jpg"
  }'
```

### Get Products with Filters
```bash
curl "http://localhost:8000/api/products?search=laptop&status=active&limit=10"
```

### Create a Category
```bash
curl -X POST http://localhost:8000/api/categories \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic devices",
    "parent_id": null
  }'
```

### Create an Attribute
```bash
curl -X POST http://localhost:8000/api/attributes \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Color",
    "type": "select",
    "is_filterable": true,
    "is_searchable": false
  }'
```

### Bulk Update Product Status
```bash
curl -X PUT http://localhost:8000/api/products/bulk/status \
  -H "Content-Type: application/json" \
  -d '{
    "ids": [1, 2, 3],
    "status": "active"
  }'
```

## Models & Classes Reference

### Product Model Methods
```php
$product = new Product($database);

// CRUD Operations
$product->create($data);              // Create product
$product->getById($id);               // Get by ID
$product->getAll($limit, $offset, $filters);  // Get all with filters
$product->update($id, $data);         // Update product
$product->delete($id);                // Delete product
$product->count($filters);            // Count products

// Advanced Operations
$product->getWithAttributes($id);     // Get product + attributes
$product->search($query, $limit, $offset);  // Search products
$product->getByCategory($id);         // Get by category
$product->getStats();                 // Get statistics
$product->bulkUpdateStatus($ids, $status);  // Bulk update
$product->bulkDelete($ids);           // Bulk delete
```

### Category Model Methods
```php
$category = new Category($database);

$category->create($data);             // Create category
$category->getById($id);              // Get by ID
$category->getAll($limit, $offset, $activeOnly);   // Get all
$category->update($id, $data);        // Update
$category->delete($id);               // Delete
$category->getTree($parentId, $activeOnly);  // Get tree
$category->getWithProductCount($id);  // Get + count
```

### Attribute Model Methods
```php
$attribute = new Attribute($database);

$attribute->create($data);            // Create attribute
$attribute->getById($id);             // Get by ID
$attribute->getAll($limit, $offset);  // Get all
$attribute->update($id, $data);       // Update
$attribute->delete($id);              // Delete
$attribute->getWithOptions($id);      // Get + options
$attribute->addOption($attrId, $label, $value);  // Add option
```

## Frontend JavaScript API

### ProductsManager Class
Handles product list page functionality:
```javascript
const manager = new ProductsManager();
// Automatically initializes:
// - Event listeners
// - Product loading
// - Filtering & searching
// - Pagination
// - Selection & bulk actions
```

### ProductForm Class
Handles product form (add/edit):
```javascript
const form = new ProductForm(productId, isEdit);
// Automatically initializes:
// - Form validation
// - Product loading
// - Attribute rendering
// - Image preview
// - Form submission
```

## Validation & Error Handling

### Product Validation
- Name and SKU are required
- SKU must be unique
- Price must be decimal
- Category ID must exist if provided
- Status must be: active, inactive, or draft

### Category Validation
- Name is required
- Names must be unique
- Auto-generates URL-friendly slug

### Attribute Validation
- Name and type are required
- Valid types: text, number, select, multiselect, date, boolean, image

## Security Features

✅ **Authentication Required**
- All endpoints require user authentication
- Session-based access control
- Redirect to login if not authenticated

✅ **Input Validation**
- Server-side validation on all inputs
- Parameterized queries to prevent SQL injection
- HTML escaping to prevent XSS

✅ **CSRF Protection**
- Token generation for forms
- Token verification on POST/PUT/DELETE

## Performance Considerations

- Pagination (default 50 items per page)
- Database indexes on frequently queried columns
- Efficient filtering with proper WHERE clauses
- Lazy loading of related data
- No N+1 query problems

## Testing the Product Management System

### 1. Create a Category
- Navigate to Categories page (pending feature)
- Or use API: `POST /api/categories`

### 2. Create a Product
- Go to Products → Add New Product
- Fill in details and submit
- Verify it appears in product list

### 3. Filter Products
- Use search box to find by name/SKU
- Use status dropdown to filter
- Use category dropdown to filter

### 4. Edit a Product
- Click "Edit" button on any product
- Modify details and save
- Verify changes appear in list

### 5. Bulk Operations
- Select multiple products (checkboxes)
- Choose bulk action (activate, deactivate, delete)
- Verify changes

### 6. Dashboard Stats
- Check dashboard for product statistics
- Stats should update in real-time

## Next Steps / Future Enhancements

1. **Category Management UI** - Create visual category editor
2. **Attribute Management UI** - Create attribute editor
3. **Product Variants** - Support product variants
4. **Import/Export** - CSV/XML import and export
5. **Advanced Search** - Elasticsearch integration
6. **Images** - File upload support
7. **Notifications** - Email notifications for changes
8. **Audit Trail** - Product change history
9. **Workflow** - Approval workflows
10. **Analytics** - Product performance metrics

## Common Issues & Solutions

### Problem: Products list shows "No products found"
- **Solution**: Create a product using the add form or API
- Check if database connection is working

### Problem: Image preview not showing
- **Solution**: Ensure image URL is valid and accessible
- Check CORS settings if image is from different domain

### Problem: SKU already exists error
- **Solution**: Use a unique SKU value
- Check if product with same SKU already exists

### Problem: Category dropdown is empty
- **Solution**: Create categories first via API or category page
- Or create product without category first

## Support & Documentation

- Main README: [README.md](./README.md)
- Setup Guide: [SETUP.md](./SETUP.md)
- Database Schema: [database/schema.sql](./database/schema.sql)
- API Endpoints: This document

For issues or questions, check the code comments or refer to the API endpoint handlers.
