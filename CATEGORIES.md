# Categories Management System

## Overview

The Categories Management System provides a complete interface for managing product categories with hierarchical organization, status management, and product counting. Categories can be organized in a parent-child hierarchy to create flexible category structures.

## Features

- ✅ Create, Read, Update, Delete (CRUD) categories
- ✅ Hierarchical category structure (parent-child relationships)
- ✅ Product count per category
- ✅ Search and filter functionality
- ✅ List view with pagination
- ✅ Tree view for hierarchical visualization
- ✅ Bulk operations (activate, deactivate, delete)
- ✅ Active/inactive status management
- ✅ Image URL support for categories
- ✅ Responsive design for mobile, tablet, desktop

## API Endpoints

All endpoints return JSON responses.

### List All Categories

**GET `/api/categories`**

Returns all categories with pagination and filtering options.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/categories?limit=50&offset=0"
```

**Query Parameters:**
- `limit`: Items per page (default: 50)
- `offset`: Pagination offset (default: 0)
- `search`: Search by name or slug
- `active`: Filter active only ('true'/'false')

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Electronic products",
      "image_url": "https://example.com/electronics.jpg",
      "parent_id": null,
      "is_active": 1,
      "created_at": "2026-01-15 10:30:00",
      "product_count": 45
    },
    {
      "id": 2,
      "name": "Smartphones",
      "slug": "smartphones",
      "description": "Mobile phones",
      "image_url": "https://example.com/phones.jpg",
      "parent_id": 1,
      "is_active": 1,
      "created_at": "2026-01-15 11:00:00",
      "product_count": 12
    }
  ],
  "meta": {
    "total": 2,
    "limit": 50,
    "offset": 0
  }
}
```

### Get Category Tree (Hierarchical)

**GET `/api/categories/tree`**

Returns all categories in a hierarchical tree structure.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/categories/tree"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "is_active": 1,
      "children": [
        {
          "id": 2,
          "name": "Smartphones",
          "slug": "smartphones",
          "is_active": 1,
          "children": []
        },
        {
          "id": 3,
          "name": "Laptops",
          "slug": "laptops",
          "is_active": 1,
          "children": []
        }
      ]
    }
  ]
}
```

### Get Single Category

**GET `/api/categories/:id`**

Returns a specific category with product count.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/categories/1"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic products",
    "image_url": "https://example.com/electronics.jpg",
    "parent_id": null,
    "is_active": 1,
    "created_at": "2026-01-15 10:30:00",
    "product_count": 45
  }
}
```

### Create Category

**POST `/api/categories`**

Creates a new category.

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Cameras",
    "description": "Digital cameras and accessories",
    "image_url": "https://example.com/cameras.jpg",
    "parent_id": 1,
    "is_active": 1
  }' \
  "http://localhost/api/categories"
```

**Request Body:**
```json
{
  "name": "Cameras",
  "description": "Digital cameras and accessories",
  "image_url": "https://example.com/cameras.jpg",
  "parent_id": null,
  "is_active": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 4,
    "name": "Cameras",
    "slug": "cameras",
    "description": "Digital cameras and accessories",
    "image_url": "https://example.com/cameras.jpg",
    "parent_id": null,
    "is_active": 1,
    "created_at": "2026-01-20 14:30:00"
  }
}
```

### Update Category

**PUT `/api/categories/:id`**

Updates an existing category.

```bash
curl -X PUT \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Digital Cameras",
    "description": "Updated description",
    "is_active": 1
  }' \
  "http://localhost/api/categories/4"
```

**Response:**
```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "id": 4,
    "name": "Digital Cameras",
    "slug": "digital-cameras",
    "description": "Updated description",
    "is_active": 1,
    "updated_at": "2026-01-20 15:00:00"
  }
}
```

### Delete Category

**DELETE `/api/categories/:id`**

Deletes a category. Subcategories are reorganized to parent's parent.

```bash
curl -X DELETE \
  -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/categories/4"
```

**Response:**
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

## Frontend Routes

| Route | View | Description |
|-------|------|-------------|
| `/categories` | categories.php | Category list with search, filter, pagination |
| `/categories/new` | category-form.php | Create new category form |
| `/categories/edit?id=X` | category-form.php | Edit existing category |

## JavaScript Classes

### CategoriesManager

Manages the category list view.

```javascript
class CategoriesManager {
  constructor()
  
  // Core methods
  init()
  bindEvents()
  loadCategories()
  loadCategoryTree()
  buildTreeHtml(categories, level)
  renderCategories(categories)
  updatePagination(total)
  
  // View switching
  switchView(view)
  
  // Navigation
  previousPage()
  nextPage()
  
  // Filtering
  clearFilters()
  
  // Selection
  selectAll(checked)
  updateSelection()
  clearSelection()
  
  // Bulk operations
  performBulkAction()
  
  // Utilities
  escapeHtml(text)
  showError(message)
  showSuccess(message)
}
```

**Usage Example:**
```javascript
const manager = new CategoriesManager();

// Load categories with filters
manager.loadCategories();

// Switch to tree view
manager.switchView('tree');

// Clear selections
manager.clearSelection();
```

### CategoryForm

Manages the category add/edit form.

```javascript
class CategoryForm {
  constructor(categoryId, isEdit)
  
  // Core methods
  init()
  bindEvents()
  loadCategory()
  loadParentCategories()
  populateForm(category)
  
  // Form handling
  handleSubmit(e)
  previewImage()
  
  // Utilities
  escapeHtml(text)
  showError(message)
  showSuccess(message)
}
```

**Usage Example:**
```javascript
// Create new category
const form = new CategoryForm(null, false);

// Edit existing category
const form = new CategoryForm(5, true);

// Manual form submission
form.handleSubmit(event);
```

## Category Model Methods

The `Category` model provides these methods:

```php
// CRUD Operations
create(array $data): int          // Returns category ID
getById(int $id): array
getAll(array $filters = []): array
update(int $id, array $data): bool
delete(int $id): bool

// Query Methods
getTree(): array                   // Get hierarchical structure
getWithProductCount(): array       // Include product counts
getByParent(int $parentId): array
getActive(): array

// Validation
nameExists(string $name, int $excludeId = null): bool

// Utilities
slugify(string $text): string
moveChildren(int $fromParentId, int $toParentId): void
```

## Usage Examples

### PHP Backend

```php
<?php
require_once '../src/Models/Category.php';
use PIM\Models\Category;

$category = new Category();

// Create category
$categoryData = [
    'name' => 'Cameras',
    'description' => 'Digital cameras',
    'parent_id' => 1,
    'image_url' => 'https://...',
    'is_active' => 1
];
$id = $category->create($categoryData);

// Get all with hierarchy
$tree = $category->getTree();

// Update
$category->update($id, ['name' => 'Updated Name']);

// Delete
$category->delete($id);
?>
```

### JavaScript Frontend

**List View:**
```javascript
// Initialize manager
const manager = new CategoriesManager();

// Search categories
document.getElementById('searchInput').addEventListener('input', () => {
  manager.loadCategories();
});

// Bulk delete
const selected = [...document.querySelectorAll('.category-checkbox:checked')]
  .map(cb => cb.value);
```

**Form View:**
```javascript
// Initialize form for new category
const form = new CategoryForm(null, false);

// Or for editing
const form = new CategoryForm(categoryId, true);

// Form auto-loads category data and parent categories
```

## View Features

### List View

- **Search Box**: Filter categories by name or slug (real-time)
- **Status Filter**: Show only active categories
- **Pagination**: Navigate through pages (50 items per page)
- **Bulk Actions**: Activate, deactivate, or delete multiple categories
- **Table Columns**: Name, Slug, Parent, Products, Status, Created, Actions
- **Row Actions**: Edit and delete buttons for each category

### Tree View

- **Hierarchical Display**: Shows parent-child relationships visually
- **Indentation**: Increases with nesting level
- **Status Badges**: Shows active/inactive status
- **Per-Item Actions**: Edit and delete for each category node

### Form View

- **Basic Information**: Category name, parent selection, status
- **Description**: Full text description field
- **Image**: URL-based image preview
- **Read-only Info**: Slug, product count, creation date (when editing)
- **Validation**: Client-side and server-side validation

## Data Validation

### Client-Side
- Category name is required and non-empty
- Image URL must be valid URL format if provided
- Parent category cannot be self-reference

### Server-Side
- Name uniqueness validation
- Parent ID existence check
- Status field validation (0 or 1)
- SQL injection prevention with parameterized queries

## Database Schema

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(500),
    parent_id INT,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX (slug),
    INDEX (parent_id),
    INDEX (is_active)
);
```

## Testing Guide

### Test Scenario 1: Create Category
1. Navigate to `/categories`
2. Click "Add New Category"
3. Fill in form: Name "Tech Gadgets", Parent "Electronics"
4. Submit form
5. Verify category appears in list

### Test Scenario 2: Hierarchical Display
1. Click "Tree View" button
2. Verify categories display with proper indentation
3. Verify parent-child relationships are correct

### Test Scenario 3: Search Functionality
1. Enter search term in search box
2. Results filter in real-time
3. Pagination updates accordingly

### Test Scenario 4: Bulk Operations
1. Select multiple categories using checkboxes
2. Select action from dropdown (Delete)
3. Click Apply
4. Confirm deletion
5. Verify categories are removed

### Test Scenario 5: Edit Category
1. Click Edit on any category
2. Modify fields (name, description, status)
3. Submit
4. Verify changes appear in list view

### Test Scenario 6: Status Toggle
1. Create category with active status
2. Edit category and toggle status to inactive
3. Verify status badge changes in list view
4. Test "Active Only" filter shows/hides correctly

### Test Scenario 7: Image Preview
1. In form view, enter image URL
2. Verify preview displays immediately
3. Delete image URL
4. Verify preview is hidden

### Test Scenario 8: Parent Category Selection
1. Create category with parent
2. In edit form, verify parent options exclude self
3. Change parent to different category
4. Verify tree view updates

## Responsive Design

### Desktop (1024px+)
- Full table with all columns visible
- Side-by-side layout for filters
- Bulk actions bar at bottom
- Full pagination controls

### Tablet (768px - 1023px)
- Condensed table columns
- Stack filter controls vertically
- Adjusted padding and spacing
- Responsive bulk actions

### Mobile (480px - 767px)
- Stacked layouts
- Full-width buttons
- Touch-friendly button sizes
- Simplified table display

### Extra Small (< 480px)
- Maximum simplification
- Single column layouts
- Minimal padding
- Large touch targets

## Security Features

- ✅ Authentication required for all category operations
- ✅ Role-based access control (enforced at controller level)
- ✅ CSRF token validation
- ✅ SQL injection prevention (parameterized queries)
- ✅ XSS prevention (HTML escaping on output)
- ✅ Input validation (client and server)
- ✅ Secure password handling

## Performance Optimizations

- ✅ Pagination to limit data transfer
- ✅ Database indexes on frequently queried fields (slug, parent_id, is_active)
- ✅ Lazy loading for tree expansions
- ✅ Efficient AJAX requests with debouncing
- ✅ CSS and JavaScript minification ready
- ✅ Request caching headers enabled

## Troubleshooting

### Categories not loading
- Check API server is running
- Verify authentication token is valid
- Check browser console for errors

### Parent category not appearing
- Refresh page
- Verify parent category is active
- Check for circular references

### Bulk operations failing
- Verify at least one category is selected
- Check request is being sent correctly
- Review server error logs

### Image preview not showing
- Verify URL is correct and public
- Check CORS if loading cross-domain images
- Try different image URL format

## Next Steps

After category management is complete, consider implementing:
1. **Attribute Management UI** - Similar feature for product attributes
2. **Category Images** - File upload support instead of URL-only
3. **Bulk Category Import** - Import from CSV file
4. **Analytics** - View category statistics and performance

## Related Documentation

- [README.md](../README.md) - Project overview
- [SETUP.md](../SETUP.md) - Installation and setup guide
- [PRODUCTS.md](../PRODUCTS.md) - Product management documentation
