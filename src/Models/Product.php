<?php
/**
 * Product Model
 * Handles product data and operations
 */

namespace PIM\Models;

use PIM\Utils\Database;

class Product {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    /**
     * Create new product
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['sku'])) {
            throw new \Exception("Product name and SKU are required");
        }

        // Check if SKU is unique
        if ($this->skuExists($data['sku'])) {
            throw new \Exception("SKU already exists");
        }

        // Prepare product data
        $productData = [
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'price' => $data['price'] ?? 0,
            'status' => $data['status'] ?? 'draft',
            'image_url' => $data['image_url'] ?? null,
            'created_by' => $data['created_by'] ?? null
        ];

        $productId = $this->db->insert('products', $productData);

        // Add attributes if provided
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attrId => $value) {
                $this->addAttribute($productId, $attrId, $value);
            }
        }

        return $productId;
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        $sql = 'SELECT * FROM products WHERE id = ?';
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get all products with pagination
     */
    public function getAll($limit = 50, $offset = 0, $filters = []) {
        $sql = 'SELECT * FROM products WHERE 1=1';
        $params = [];

        // Apply filters
        if (!empty($filters['category_id'])) {
            $sql .= ' AND category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE ? OR sku LIKE ? OR description LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->getRows($sql, $params);
    }

    /**
     * Count products
     */
    public function count($filters = []) {
        $sql = 'SELECT COUNT(*) as total FROM products WHERE 1=1';
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= ' AND category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE ? OR sku LIKE ? OR description LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->getRow($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        // Remove sensitive fields
        unset($data['id']);
        unset($data['created_at']);
        unset($data['created_by']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('products', $data, 'id = ?', [$id]);
    }

    /**
     * Delete product
     */
    public function delete($id) {
        // Delete product attributes first
        $this->db->delete('product_attributes', 'product_id = ?', [$id]);
        
        // Delete product
        return $this->db->delete('products', 'id = ?', [$id]);
    }

    /**
     * Check if SKU exists
     */
    public function skuExists($sku, $excludeId = null) {
        $sql = 'SELECT id FROM products WHERE sku = ?';
        $params = [$sku];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $result = $this->db->getRow($sql, $params);
        return (bool) $result;
    }

    /**
     * Get product with attributes
     */
    public function getWithAttributes($id) {
        $product = $this->getById($id);

        if (!$product) {
            return null;
        }

        // Get product attributes
        $sql = 'SELECT pa.*, attr.name, attr.type FROM product_attributes pa
                 LEFT JOIN attributes attr ON pa.attribute_id = attr.id
                 WHERE pa.product_id = ?';
        $product['attributes'] = $this->db->getRows($sql, [$id]);

        return $product;
    }

    /**
     * Add attribute to product
     */
    public function addAttribute($productId, $attributeId, $value) {
        $data = [
            'product_id' => $productId,
            'attribute_id' => $attributeId,
            'value' => $value
        ];

        return $this->db->insert('product_attributes', $data);
    }

    /**
     * Update product attribute
     */
    public function updateAttribute($productId, $attributeId, $value) {
        $data = ['value' => $value];
        return $this->db->update(
            'product_attributes',
            $data,
            'product_id = ? AND attribute_id = ?',
            [$productId, $attributeId]
        );
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = 50, $offset = 0) {
        $sql = 'SELECT * FROM products WHERE category_id = ?
                 ORDER BY name ASC LIMIT ? OFFSET ?';
        return $this->db->getRows($sql, [$categoryId, $limit, $offset]);
    }

    /**
     * Search products
     */
    public function search($query, $limit = 50, $offset = 0) {
        $searchTerm = '%' . $query . '%';
        $sql = 'SELECT * FROM products 
                 WHERE name LIKE ? OR sku LIKE ? OR description LIKE ?
                 ORDER BY name ASC LIMIT ? OFFSET ?';
        return $this->db->getRows($sql, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    }

    /**
     * Get product statistics
     */
    public function getStats() {
        $stats = [];

        // Total products
        $result = $this->db->getRow('SELECT COUNT(*) as total FROM products');
        $stats['total'] = $result['total'] ?? 0;

        // Active products
        $result = $this->db->getRow('SELECT COUNT(*) as total FROM products WHERE status = "active"');
        $stats['active'] = $result['total'] ?? 0;

        // Draft products
        $result = $this->db->getRow('SELECT COUNT(*) as total FROM products WHERE status = "draft"');
        $stats['draft'] = $result['total'] ?? 0;

        // Products by category
        $result = $this->db->getRow('SELECT COUNT(DISTINCT category_id) as total FROM products WHERE category_id IS NOT NULL');
        $stats['categories'] = $result['total'] ?? 0;

        return $stats;
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus($ids, $status) {
        $validStatuses = ['active', 'inactive', 'draft'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid status: $status");
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE products SET status = ? WHERE id IN ($placeholders)";
        
        $params = array_merge([$status], $ids);
        $stmt = $this->db->query($sql, $params);
        
        return $stmt->affected_rows;
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete($ids) {
        foreach ($ids as $id) {
            $this->delete($id);
        }
        return count($ids);
    }
}

?>
