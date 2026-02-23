# Product Variants Management System

## Overview

The Product Variants system allows you to create and manage different variations of products with custom variant types (Size, Color, Material, etc.). Each variant combination can have its own SKU, price, stock quantity, and image.

## Features

- ✅ **Custom Variant Types** - Admin can define any variant type (Size, Color, Material, Brand, etc.)
- ✅ **Multiple Input Types** - Text, Color Picker, Dropdown, Multiple Selection
- ✅ **Variant Values** - Add unlimited values/options to each variant type
- ✅ **Variant Combinations** - Generate all combinations automatically
- ✅ **Individual SKU & Price** - Each variant has unique SKU and price
- ✅ **Stock Management** - Track stock for each variant separately
- ✅ **Variant Images** - Different images for different variants
- ✅ **Quick Generation** - Generate all combinations in one click
- ✅ **Manual Management** - Edit/delete individual combinations
- ✅ **Responsive Design** - Works on desktop, tablet, and mobile

## Architecture

### Database Tables

#### `product_variant_types`
Defines variant types for each product.

```sql
CREATE TABLE product_variant_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    type ENUM('text', 'color', 'dropdown', 'multiselect') DEFAULT 'dropdown',
    is_required BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_product_variant (product_id, slug),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

#### `product_variant_values`
Possible values for each variant type.

```sql
CREATE TABLE product_variant_values (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_type_id INT NOT NULL,
    value VARCHAR(255) NOT NULL,
    display_value VARCHAR(255),
    color_code VARCHAR(7),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_variant_value (variant_type_id, value),
    FOREIGN KEY (variant_type_id) REFERENCES product_variant_types(id) ON DELETE CASCADE
);
```

#### `product_variant_combinations`
Actual product variants (combinations of variant values).

```sql
CREATE TABLE product_variant_combinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    variant_sku VARCHAR(100) UNIQUE NOT NULL,
    variant_name VARCHAR(255),
    price DECIMAL(10, 2),
    cost_price DECIMAL(10, 2),
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    variant_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

## API Endpoints

All endpoints require authentication.

### Variant Types Management

**GET `/api/product-variants/types/:product_id`**
Get all variant types for a product.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/product-variants/types/1"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "name": "Size",
      "slug": "size",
      "type": "dropdown",
      "is_required": 1,
      "sort_order": 0,
      "values": [
        {"id": 1, "value": "S", "display_value": "Small", "sort_order": 0},
        {"id": 2, "value": "M", "display_value": "Medium", "sort_order": 1},
        {"id": 3, "value": "L", "display_value": "Large", "sort_order": 2}
      ]
    },
    {
      "id": 2,
      "product_id": 1,
      "name": "Color",
      "slug": "color",
      "type": "color",
      "is_required": 0,
      "sort_order": 1,
      "values": [
        {"id": 4, "value": "RED", "display_value": "Red", "color_code": "#ff0000"},
        {"id": 5, "value": "BLUE", "display_value": "Blue", "color_code": "#0000ff"}
      ]
    }
  ]
}
```

**POST `/api/product-variants/types`**
Create a new variant type.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "name": "Size",
    "type": "dropdown",
    "is_required": 1,
    "sort_order": 0
  }' \
  "http://localhost/api/product-variants/types"
```

**PUT `/api/product-variants/types/:id`**
Update variant type.

```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -d '{"name": "Clothing Size"}' \
  "http://localhost/api/product-variants/types/1"
```

**DELETE `/api/product-variants/types/:id`**
Delete variant type and all associated values and combinations.

```bash
curl -X DELETE \
  "http://localhost/api/product-variants/types/1"
```

### Variant Values Management

**POST `/api/product-variants/values`**
Add a value to a variant type.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "variant_type_id": 1,
    "value": "M",
    "display_value": "Medium",
    "color_code": null,
    "sort_order": 1
  }' \
  "http://localhost/api/product-variants/values"
```

**PUT `/api/product-variants/values/:id`**
Update a variant value.

```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -d '{"display_value": "Medium Size"}' \
  "http://localhost/api/product-variants/values/2"
```

**DELETE `/api/product-variants/values/:id`**
Delete a variant value.

```bash
curl -X DELETE \
  "http://localhost/api/product-variants/values/2"
```

### Variant Combinations Management

**GET `/api/product-variants/combinations/:product_id`**
Get all combinations for a product.

```bash
curl "http://localhost/api/product-variants/combinations/1"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "variant_sku": "SHIRT-S-RED",
      "variant_name": "Size: S, Color: Red",
      "price": "29.99",
      "cost_price": "15.00",
      "stock_quantity": 50,
      "image_url": "https://example.com/shirt-s-red.jpg",
      "is_active": 1,
      "variant_data": {
        "1": "S",
        "2": "RED"
      }
    }
  ]
}
```

**POST `/api/product-variants/combinations`**
Create a single variant combination.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "variant_sku": "SHIRT-S-RED",
    "variant_values": {"1": "S", "2": "RED"},
    "price": 29.99,
    "stock_quantity": 50,
    "is_active": 1
  }' \
  "http://localhost/api/product-variants/combinations"
```

**POST `/api/product-variants/bulk-create`**
Generate all combinations automatically.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "variant_type_ids": [1, 2],
    "base_data": {
      "price": 29.99,
      "stock_quantity": 0
    }
  }' \
  "http://localhost/api/product-variants/bulk-create"
```

Response:
```json
{
  "success": true,
  "message": "Variant combinations created",
  "data": {
    "created_count": 6,
    "ids": [1, 2, 3, 4, 5, 6]
  }
}
```

**PUT `/api/product-variants/combinations/:id`**
Update a variant combination.

```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -d '{
    "price": 34.99,
    "stock_quantity": 100,
    "is_active": 1
  }' \
  "http://localhost/api/product-variants/combinations/1"
```

**DELETE `/api/product-variants/combinations/:id`**
Delete a variant combination.

```bash
curl -X DELETE \
  "http://localhost/api/product-variants/combinations/1"
```

## Frontend Routes

| Route | File | Description |
|-------|------|-------------|
| `/products/variants?product_id=X` | variants.php | Manage variants for a product |

## JavaScript Classes

### ProductVariantManager

Main class for managing variants.

```javascript
class ProductVariantManager {
  constructor(productId)
  
  // Core methods
  init()
  bindEvents()
  loadVariantTypes()
  loadVariantCombinations()
  
  // Rendering
  renderVariantTypes()
  renderVariantCombinations()
  
  // Type management
  showAddTypeModal()
  saveVariantType(modal)
  editType(typeId)
  deleteType(typeId)
  
  // Value management
  showAddValueModal(variantTypeId)
  saveVariantValue(variantTypeId, modal)
  deleteValue(valueId)
  
  // Combination management
  showBulkCreateModal()
  bulkCreateCombinations(modal)
  editCombination(comboId)
  deleteCombination(comboId)
  
  // Utilities
  calculateCombinationCount(typeIds)
  createModal(title, content)
  showError(message)
  showSuccess(message)
  escapeHtml(text)
}
```

## PHP Model

### ProductVariant Model

```php
class ProductVariant {
  // Variant Types
  createVariantType(productId, data)
  getVariantTypesByProduct(productId)
  getVariantTypeWithValues(variantTypeId)
  updateVariantType(variantTypeId, data)
  deleteVariantType(variantTypeId)
  
  // Variant Values
  addVariantValue(variantTypeId, data)
  getVariantValues(variantTypeId)
  updateVariantValue(variantValueId, data)
  deleteVariantValue(variantValueId)
  
  // Variant Combinations
  createVariantCombination(productId, data)
  getVariantCombinations(productId)
  getVariantCombination(combinationId)
  updateVariantCombination(combinationId, data)
  deleteVariantCombination(combinationId)
  getVariantBySku(sku)
  bulkCreateCombinations(productId, variantTypes, baseData)
  
  // Utilities
  variantSkuExists(sku, excludeId)
  getProductWithVariants(productId)
}
```

## Usage Guide

### Step 1: Create Variant Types

1. Navigate to a product and click "Manage Variants"
2. Go to the "Variant Types" tab
3. Click "+ Add Variant Type"
4. Enter type name (e.g., "Size")
5. Select type kind (Dropdown, Color Picker, Text, etc.)
6. Mark as required if needed
7. Click "Create Type"

### Step 2: Add Values to Variant Type

1. After creating a type, click "+ Add Value"
2. Enter the value (e.g., "S", "M", "L")
3. For Color type, pick a color from the color picker
4. Click "Add Value"

### Step 3: Create Variant Combinations

**Option A: Bulk Create**
1. Go to "Variant Combinations" tab
2. Click "+ Generate All Combinations"
3. Select variant types to generate (e.g., Size + Color)
4. Click "Generate Combinations"
5. Confirm the number of combinations to create
6. All combinations will be created with auto-generated SKUs

**Option B: Manual Create**
1. Click the "Edit" button on a combination
2. Modify the SKU, price, stock, image
3. Click "Update Combination"

### Step 4: Edit Combinations

1. In "Variant Combinations" tab, click "Edit" on any combination
2. Update SKU, price, cost price, stock, or image
3. Click "Update Combination"

### Step 5: Activate/Deactivate

Use the status checkbox when editing a combination to activate or deactivate it.

## Example Scenarios

### Scenario 1: T-Shirt with Size and Color

**Variant Types:**
- Size (Dropdown): S, M, L, XL
- Color (Color Picker): Red, Blue, White

**Generated Combinations:**
- SHIRT-1001-S-RED-1
- SHIRT-1001-S-BLUE-1
- SHIRT-1001-L-WHITE-3
- ... (12 total combinations)

### Scenario 2: Shoe with Size Only

**Variant Types:**
- Size (Dropdown): 6, 7, 8, 9, 10, 11, 12

**Generated Combinations:**
- SHOE-2001-6
- SHOE-2001-7
- ... (7 total combinations)

### Scenario 3: Phone with Storage and Color

**Variant Types:**
- Storage (Dropdown): 64GB, 128GB, 256GB
- Color (Color Picker): Black, Silver, Gold

**Generated Combinations:**
- PHONE-3001-64-BLACK
- PHONE-3001-128-SILVER
- ... (9 total combinations)

## Variant Data Structure

Each variant stores a JSON object with variant type IDs as keys:

```json
{
  "1": "S",
  "2": "RED",
  "3": "COTTON"
}
```

This mapping allows quick lookup and generation of variant names.

## Database Relationships

```
Product (1) ──→ (Many) product_variant_types
                          ↓
                          (1) ──→ (Many) product_variant_values
                          
Product (1) ──→ (Many) product_variant_combinations
                        (Contains variant_data JSON with references to values)
```

## Performance Considerations

- Variant combinations are stored as complete records (denormalized) for easier querying
- Variant data stored as JSON for flexibility
- Indexes on product_id, variant_sku, and is_active for fast lookups
- Bulk creation with cartesian product calculation done in memory

## Validation

### Client-Side
- Variant type name is required
- Variant values are required
- SKU uniqueness check before submission
- Type selection validation for bulk creation

### Server-Side
- Duplicate SKU detection
- Product existence validation
- Variant type existence check
- JSON validation for variant_data
- Stock quantity validation (non-negative)

## Security

- ✅ Authentication required for all operations
- ✅ Role-based access control
- ✅ SQL injection prevention (parameterized queries)
- ✅ XSS prevention (HTML escaping)
- ✅ Request validation and sanitization

## Quirks & Tips

1. **Auto-Generated SKU**: Bulk creation generates SKUs by appending a hash of the variant values
2. **Variant Name**: Automatically generated from variant type names and values
3. **Color Code**: Optional for color variants, enables visual color picker
4. **JSON Storage**: Variant data stored as JSON for flexibility with future variant types
5. **Cascading Delete**: Deleting a variant type deletes all associated values and combinations

## Troubleshooting

**Issue: "Variant SKU already exists"**
- Solution: Each variant must have a unique SKU. Rename the SKU before saving.

**Issue: Bulk creation shows "0 combinations"**
- Solution: Ensure variant types have values added. At least one value per type is required.

**Issue: Combinations not appearing**
- Solution: Make sure combinations were created before navigating away. Check console for errors.

**Issue: Color picker not showing for color type**
- Solution: Use `type: 'color'` when creating the variant type.

## Next Steps

- [ ] File upload support for variant images
- [ ] Bulk price updates for all variants
- [ ] Variant import from CSV
- [ ] Variant hierarchy templates
- [ ] Stock synchronization with e-commerce platforms
- [ ] Barcode generation for variants
- [ ] Variant analytics and popularity tracking

## Related Documentation

- [README.md](../README.md) - Project overview
- [PRODUCTS.md](../PRODUCTS.md) - Product management
- [CATEGORIES.md](../CATEGORIES.md) - Category management
