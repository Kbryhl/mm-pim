# Product Pricing Tiers System

## Overview

The Product Pricing Tiers system allows you to create quantity-based and unit-based pricing strategies. Offer bulk discounts, different pricing for different units of measurement, and track profit margins with cost prices.

## Features

- ✅ **Bulk Pricing** - Different prices for different quantity ranges
- ✅ **Unit Types** - Support for pieces, kg, liters, meters, and more
- ✅ **Quantity Ranges** - Set minimum and maximum quantities for each tier
- ✅ **Cost Tracking** - Track cost price and profit margins
- ✅ **Discount Percentage** - Display discount % for each tier
- ✅ **Variant-Specific** - Set different pricing for product variants (optional)
- ✅ **Active/Inactive** - Disable tiers without deleting them
- ✅ **Bulk Import** - Import pricing tiers via JSON
- ✅ **Dynamic Calculation** - Auto-calculate price for any quantity

## Architecture

### Database Table

```sql
CREATE TABLE product_pricing_tiers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    variant_combination_id INT,
    unit_type VARCHAR(50) DEFAULT 'piece',
    min_quantity DECIMAL(10, 2) NOT NULL,
    max_quantity DECIMAL(10, 2),
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2),
    discount_percent DECIMAL(5, 2),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_combination_id) REFERENCES product_variant_combinations(id) ON DELETE CASCADE
);
```

### Available Unit Types

| Unit | Code | Usage |
|------|------|-------|
| Piece(s) | `piece` | Individual items |
| Kilogram(s) | `kg` | Weight-based |
| Gram(s) | `g` | Small weights |
| Liter(s) | `liter` | Liquid volume |
| Milliliter(s) | `ml` | Small liquid volume |
| Meter(s) | `meter` | Length |
| Centimeter(s) | `cm` | Small length |
| Pack(s) | `pack` | Packaged units |
| Box(es) | `box` | Boxed units |
| Bundle(s) | `bundle` | Bundled units |
| Dozen | `dozen` | 12 units |

## API Endpoints

All endpoints require authentication.

### Get Product Pricing Tiers

**GET `/api/product-pricing/product/:product_id`**

Get all pricing tiers for a product with summary.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/product-pricing/product/1"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "variant_combination_id": null,
      "unit_type": "piece",
      "min_quantity": "1.00",
      "max_quantity": "10.00",
      "price": "29.99",
      "cost_price": "15.00",
      "discount_percent": null,
      "is_active": 1,
      "sort_order": 0
    },
    {
      "id": 2,
      "product_id": 1,
      "variant_combination_id": null,
      "unit_type": "piece",
      "min_quantity": "11.00",
      "max_quantity": "50.00",
      "price": "24.99",
      "cost_price": "12.50",
      "discount_percent": "16.67",
      "is_active": 1,
      "sort_order": 1
    }
  ],
  "summary": {
    "base_price": 29.99,
    "min_price": 24.99,
    "max_price": 29.99,
    "tier_count": 2,
    "has_tiers": true
  }
}
```

### Get Variant Pricing Tiers

**GET `/api/product-pricing/variant/:variant_id`**

Get pricing tiers for a specific variant combination.

```bash
curl "http://localhost/api/product-pricing/variant/1"
```

### Get Single Tier

**GET `/api/product-pricing/:id`**

Get a single pricing tier.

```bash
curl "http://localhost/api/product-pricing/1"
```

### Create Pricing Tier

**POST `/api/product-pricing`**

Create a new pricing tier.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "unit_type": "piece",
    "min_quantity": 11,
    "max_quantity": 50,
    "price": 24.99,
    "cost_price": 12.50,
    "discount_percent": 16.67,
    "is_active": 1
  }' \
  "http://localhost/api/product-pricing"
```

### Update Pricing Tier

**PUT `/api/product-pricing/:id`**

Update an existing pricing tier.

```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -d '{
    "price": 23.99,
    "cost_price": 11.50
  }' \
  "http://localhost/api/product-pricing/2"
```

### Delete Pricing Tier

**DELETE `/api/product-pricing/:id`**

Delete a pricing tier.

```bash
curl -X DELETE \
  "http://localhost/api/product-pricing/2"
```

### Calculate Price for Quantity

**GET `/api/product-pricing/calculate/:product_id/:quantity[/:variant_id]`**

Calculate the applicable price for a given quantity.

```bash
curl "http://localhost/api/product-pricing/calculate/1/15"
```

Response:
```json
{
  "success": true,
  "data": {
    "quantity": 15,
    "price": 24.99,
    "total": 374.85,
    "tier": {
      "id": 2,
      "min_quantity": "11.00",
      "max_quantity": "50.00",
      "price": "24.99",
      "discount_percent": "16.67"
    },
    "discount_percent": "16.67"
  }
}
```

### Get Available Unit Types

**GET `/api/product-pricing/units`**

Get list of all available unit types.

```bash
curl "http://localhost/api/product-pricing/units"
```

Response:
```json
{
  "success": true,
  "data": {
    "piece": "Piece(s)",
    "kg": "Kilogram(s)",
    "g": "Gram(s)",
    "liter": "Liter(s)",
    "ml": "Milliliter(s)",
    "meter": "Meter(s)",
    "cm": "Centimeter(s)",
    "pack": "Pack(s)",
    "box": "Box(es)",
    "bundle": "Bundle(s)",
    "dozen": "Dozen"
  }
}
```

### Bulk Import Pricing Tiers

**POST `/api/product-pricing/bulk`**

Create or update multiple pricing tiers at once.

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "tiers": [
      {
        "unit_type": "piece",
        "min_quantity": 1,
        "max_quantity": 10,
        "price": 29.99,
        "cost_price": 15.00
      },
      {
        "unit_type": "piece",
        "min_quantity": 11,
        "max_quantity": 50,
        "price": 24.99,
        "cost_price": 12.50
      },
      {
        "unit_type": "piece",
        "min_quantity": 51,
        "price": 19.99,
        "cost_price": 10.00
      }
    ]
  }' \
  "http://localhost/api/product-pricing/bulk"
```

## Frontend Routes

| Route | File | Description |
|-------|------|-------------|
| `/products/pricing?product_id=X` | pricing.php | Manage pricing tiers for a product |

## JavaScript Class

### PricingTierManager

```javascript
class PricingTierManager {
  constructor(productId, basePrice = null)
  
  // Core Methods
  init()
  bindEvents()
  
  // Data Loading
  loadPricingTiers()
  loadUnitTypes()
  
  // Rendering
  renderPricingTiers()
  updatePricingSummary(summary)
  
  // Tier Management
  showAddTierModal(tierId = null)
  saveTier(tierId = null, modal = null)
  editTier(tierId)
  deleteTier(tierId)
  
  // Bulk Operations
  showBulkImportModal()
  bulkImportTiers(modal)
  
  // Utilities
  calculatePrice(quantity)
  calculateMargin(price, costPrice)
  createModal(title, content)
  showError(message)
  showSuccess(message)
  escapeHtml(text)
}
```

## Usage Examples

### Example 1: T-Shirt Bulk Pricing

**Scenario:** T-shirts with volume discounts

**Setup:**
```json
[
  {
    "unit_type": "piece",
    "min_quantity": 1,
    "max_quantity": 10,
    "price": 29.99,
    "cost_price": 15.00
  },
  {
    "unit_type": "piece",
    "min_quantity": 11,
    "max_quantity": 50,
    "price": 25.99,
    "cost_price": 15.00
  },
  {
    "unit_type": "piece",
    "min_quantity": 51,
    "max_quantity": 100,
    "price": 22.99,
    "cost_price": 15.00
  },
  {
    "unit_type": "piece",
    "min_quantity": 101,
    "price": 19.99,
    "cost_price": 15.00
  }
]
```

**Pricing:**
- 1-10 pieces: $29.99 each
- 11-50 pieces: $25.99 each (13% discount)
- 51-100 pieces: $22.99 each (23% discount)
- 100+ pieces: $19.99 each (33% discount)

### Example 2: Coffee Beans by Weight

**Scenario:** Coffee sold by kilogram with price breaks

**Setup:**
```json
[
  {
    "unit_type": "kg",
    "min_quantity": 0.5,
    "max_quantity": 1,
    "price": 12.99
  },
  {
    "unit_type": "kg",
    "min_quantity": 1.01,
    "max_quantity": 5,
    "price": 11.99
  },
  {
    "unit_type": "kg",
    "min_quantity": 5.01,
    "price": 10.99
  }
]
```

**Pricing:**
- 0.5-1 kg: $12.99/kg
- 1.01-5 kg: $11.99/kg (7.7% discount)
- 5+ kg: $10.99/kg (15.3% discount)

### Example 3: Custom Packaging (Variants)

**Scenario:** Product has variants with different unit pricing

```bash
# Add tier for variant combination 5 (small pack)
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 3,
    "variant_combination_id": 5,
    "unit_type": "pack",
    "min_quantity": 1,
    "price": 9.99
  }' \
  "http://localhost/api/product-pricing"
```

## Frontend Features

### Pricing Summary
Shows overview statistics:
- Base product price
- Minimum price across all tiers
- Maximum price across all tiers
- Number of active pricing tiers

### Pricing Table
Displays all tiers with:
- Unit type
- Quantity range (min-max)
- Price
- Cost price
- Profit margin %
- Active/Inactive status
- Edit/Delete actions

### Modal Forms
- Add new pricing tier
- Edit existing tier
- Bulk import from JSON

### Dynamic Price Calculator
JavaScript function to calculate price for any quantity:
```javascript
const price = pricingManager.calculatePrice(15); // Returns $24.99
```

## Validation

### Client-Side
- Required fields: product_id, min_quantity, price
- Numeric validation for quantities and prices
- Maximum quantity > minimum quantity
- Discount percent 0-100%

### Server-Side
- Duplicate quantity range detection
- Decimal validation for quantities
- Cost price cannot exceed price
- Product existence validation

## Examples: Real-World Scenarios

### Scenario 1: Bulk Discount Promotion
**Goal:** Encourage larger orders

| Quantity | Price | Savings |
|----------|-------|---------|
| 1-5 | $30 | - |
| 6-20 | $27 | 10% |
| 21-50 | $24 | 20% |
| 50+ | $21 | 30% |

### Scenario 2: Unit-Based Pricing
**Goal:** Different unit types offer different prices

| Unit | Price | Cost | Margin |
|------|-------|------|--------|
| Single piece | $10 | $5 | 100% |
| Pack (6 pieces) | $8/unit | $4 | 100% |
| Bulk (100+ pieces) | $6/unit | $3 | 100% |

### Scenario 3: Seasonal Pricing
**Goal:** Different prices for different quantities during season

```json
[
  {
    "unit_type": "kg",
    "min_quantity": 1,
    "max_quantity": 10,
    "price": 8.99,
    "discount_percent": 0
  },
  {
    "unit_type": "kg",
    "min_quantity": 11,
    "price": 6.99,
    "discount_percent": 22
  }
]
```

## Performance Considerations

- Indexes on product_id, variant_combination_id, min_quantity for fast lookups
- Quantity-based pricing calculated server-side
- Summary cached at product level
- JSON bulk import for efficient tier creation

## Security

- ✅ Authentication required for all operations
- ✅ Input validation on all numeric fields
- ✅ SQL injection prevention (parameterized queries)
- ✅ XSS prevention (HTML escaping)
- ✅ Decimal precision for monetary values (10,2 format)

## Troubleshooting

### Issue: "No pricing tier found"
- Solution: Create at least one pricing tier for the product
- Check min/max quantities are correct

### Issue: Price calculation returns null
- Solution: Ensure quantity falls within at least one tier's range
- Make tier is_active = 1

### Issue: Bulk import fails
- Solution: Validate JSON format
- Ensure all required fields (min_quantity, price) are present

### Issue: Margin shows negative
- Solution: Cost price exceeds sale price
- Review your cost_price values

## Database Relationships

```
Product (1) ──→ (Many) product_pricing_tiers
ProductVariantCombination (1) ──→ (Many) product_pricing_tiers
```

## Next Steps & Future Enhancements

- [ ] Time-based pricing (seasonal, promotional)
- [ ] Customer role-based pricing (wholesale different from retail)
- [ ] Currency support for international pricing
- [ ] Price history tracking
- [ ] Automated margin alerts
- [ ] Pricing rule templates
- [ ] A/B testing pricing strategies
- [ ] Integration with inventory for dynamic pricing

## Related Documentation

- [README.md](../README.md) - Project overview
- [PRODUCTS.md](../PRODUCTS.md) - Product management
- [VARIANTS.md](../VARIANTS.md) - Product variants
