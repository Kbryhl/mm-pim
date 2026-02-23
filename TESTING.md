# Testing Guide - Product Management System

## Prerequisites

1. Database initialized with schema (`schema.sql`)
2. User account created via registration page
3. Logged into the system
4. Server running at `http://localhost:8000`

## Test Scenarios

### Test 1: Product List Page

**Steps:**
1. Navigate to `http://localhost:8000/products`
2. Verify page loads with empty table (or existing products)
3. Check page title shows "Products"
4. Verify "Add New Product" button is visible and clickable

**Expected Result:**
- Products table displays correctly
- Search bar is functional
- Filter dropdowns exist for Status and Category

---

### Test 2: Create New Product

**Steps:**
1. Click "Add New Product" button
2. Fill in form with:
   - **Name**: "Test Laptop"
   - **SKU**: "TEST-LAPTOP-001"
   - **Description**: "This is a test product"
   - **Price**: "999.99"
   - **Status**: "draft"
3. Leave Category empty for now
4. Click "Create Product"

**Expected Result:**
- Form submits successfully
- Redirects to products list
- New product appears in table
- Success notification shows

**Error Handling Test:**
- Try submitting with empty name → should show error
- Try using duplicate SKU → should show error

---

### Test 3: Edit Product

**Steps:**
1. From products list, click "Edit" on the test product
2. Change values:
   - Name: "Updated Test Laptop"
   - Price: "1299.99"
   - Status: "active"
3. Click "Update Product"

**Expected Result:**
- Product updates successfully
- Redirects to products list
- Changes are visible in table
- Status badge shows "Active"

---

### Test 4: Search Products

**Steps:**
1. On products list, enter "laptop" in search box
2. Wait for results to filter
3. Clear search box

**Expected Result:**
- Products filter in real-time
- Only matching products show
- Search works for: name, SKU, and description

---

### Test 5: Filter By Status

**Steps:**
1. In Status dropdown, select "active"
2. Verify only active products show
3. Select "draft"
4. Select "All Statuses"

**Expected Result:**
- List filters correctly
- Count updates appropriately

---

### Test 6: Pagination

**Steps:**
1. Create multiple test products (10+)
2. Go to products list
3. Check pagination controls
4. Click "Next" button
5. Verify page 2 shows different products
6. Click "Previous"

**Expected Result:**
- Pagination works correctly
- Page info updates
- Next/Prev buttons enable/disable appropriately

---

### Test 7: Select & Bulk Delete

**Steps:**
1. On products list, select 2+ products with checkboxes
2. Select bulk action: "Delete"
3. Click "Apply"
4. Confirm deletion

**Expected Result:**
- Selected products are deleted
- List updates
- Bulk actions bar disappears

---

### Test 8: Bulk Status Update

**Steps:**
1. Select 2+ products
2. Select bulk action: "Activate"
3. Click "Apply"
4. Verify status changed

**Expected Result:**
- All selected products become "Active"
- Status badges update immediately

---

### Test 9: Dashboard Stats

**Steps:**
1. Navigate to Dashboard (`/`)
2. Scroll to "Quick Stats" section
3. Verify stats show:
   - Total Products: count of all products
   - Active Products: count of active products
   - Draft Products: count of draft products

**Expected Result:**
- Stats load correctly
- Numbers are accurate
- Updates when products are created/updated

---

### Test 10: Product Image Preview

**Steps:**
1. Go to Add New Product
2. Enter image URL: `https://via.placeholder.com/300x300`
3. Press Tab or change focus
4. Verify preview appears below URL field

**Expected Result:**
- Image preview shows
- Image is clickable
- Remove button appears
- Image preview can be cleared

---

### Test 11: API Testing (Using cURL or Postman)

**Get All Products:**
```bash
curl "http://localhost:8000/api/products?limit=10&offset=0"
```

**Create Product:**
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"API Product","sku":"API-001","price":99.99,"status":"active"}'
```

**Get Single Product:**
```bash
curl "http://localhost:8000/api/products/1"
```

**Update Product:**
```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated","price":149.99}'
```

**Delete Product:**
```bash
curl -X DELETE "http://localhost:8000/api/products/1"
```

**Get Stats:**
```bash
curl "http://localhost:8000/api/products/stats"
```

---

### Test 12: Categories API (Preparation for UI)

**Get All Categories:**
```bash
curl "http://localhost:8000/api/categories"
```

**Create Category:**
```bash
curl -X POST http://localhost:8000/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Electronics","description":"Electronic devices"}'
```

---

### Test 13: Attributes API (Preparation for UI)

**Get All Attributes:**
```bash
curl "http://localhost:8000/api/attributes"
```

**Create Attribute:**
```bash
curl -X POST http://localhost:8000/api/attributes \
  -H "Content-Type: application/json" \
  -d '{"name":"Color","type":"select"}'
```

---

## Responsive Design Testing

### Mobile (375px width)
- Open products page on mobile device or use browser dev tools
- Verify:
  - Table scales appropriately
  - Filters stack vertically
  - Buttons are accessible
  - Search bar is usable

### Tablet (768px width)
- Verify filters display in 2-column grid
- Table columns adjust
- Pagination controls work

### Desktop (1024px+ width)
- Verify full layout displays
- All features visible
- Smooth interactions

---

## Performance Testing

### Load Time
- Open products list with 100+ products
- Check browser DevTools
- Network requests should complete < 2 seconds

### Search Performance
- Search should be instant (with debouncing)
- No lag when typing in search box

### Pagination
- Switching pages should be smooth
- No full page reloads needed

---

## Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

Expected: All features work smoothly in all browsers

---

## Common Test Cases

### ✅ Success Scenarios
1. Create product → appears in list
2. Edit product → changes save
3. Delete product → removed from list
4. Filter by status → correct products shown
5. Search → returns matching results
6. Pagination → next/previous works
7. Bulk select → all checks selected
8. Bulk delete → selected deleted
9. Bulk status → all status updated
10. Dashboard stats → numbers accurate

### ❌ Error Scenarios
1. Submit empty name → error message
2. Duplicate SKU → error message
3. Invalid price → error handling
4. Delete without confirm → cancelled
5. Network error → graceful handling
6. Invalid image URL → preview fails gracefully

---

## Automation Testing (Optional)

For automated testing, you can create tests using:
- PHP Unit for backend APIs
- Selenium/Cypress for frontend UI
- cURL scripts for API endpoints

Example test template:
```php
<?php
class ProductTest {
    public function testCreateProduct() {
        $data = ['name' => 'Test', 'sku' => 'TEST-001'];
        $result = API::post('/api/products', $data);
        assert($result->status === 201);
        assert($result->product_id > 0);
    }
}
?>
```

---

## Troubleshooting

### Issue: Products not loading
- Check browser console for errors
- Verify database connection
- Check if server is running

### Issue: Add product form not working
- Check JavaScript console for errors
- Verify form input validation
- Check network request in DevTools

### Issue: Bulk select not working
- Check browser console
- Try refreshing page
- Clear browser cache

### Issue: API returning 404
- Check URL spelling
- Verify authentication (must be logged in)
- Check routing in public/index.php

### Issue: Slow performance
- Check number of products in database
- Verify database indexes exist
- Check server resources

---

## Test Data Seeds

To populate test data, use these API calls:

```bash
# Create categories
curl -X POST http://localhost:8000/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Electronics"}'

curl -X POST http://localhost:8000/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Computers"}'

# Create products
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Product 1","sku":"P001","price":99.99,"status":"active"}'

curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Product 2","sku":"P002","price":149.99,"status":"draft"}'

# Create attributes
curl -X POST http://localhost:8000/api/attributes \
  -H "Content-Type: application/json" \
  -d '{"name":"Size","type":"select"}'
```

---

## Sign-Off

After testing, verify all tests pass:
- [ ] Product list page loads
- [ ] Create product works
- [ ] Edit product works
- [ ] Delete product works
- [ ] Search functionality works
- [ ] Filter functionality works
- [ ] Pagination works
- [ ] Bulk operations work
- [ ] Dashboard stats work
- [ ] API endpoints work
- [ ] No JavaScript errors
- [ ] Responsive design works
- [ ] Performance acceptable

**Status**: Ready for production ✅
