<?php
/**
 * Category Model
 * Handles category data and operations
 */

namespace PIM\Models;

use PIM\Utils\Database;

class Category {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    /**
     * Create new category
     */
    public function create($data) {
        if (empty($data['name'])) {
            throw new \Exception("Category name is required");
        }

        // Check if category already exists
        if ($this->nameExists($data['name'])) {
            throw new \Exception("Category already exists");
        }

        // Generate slug
        $slug = $this->generateSlug($data['name']);

        $categoryData = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_active' => $data['is_active'] ?? 1
        ];

        return $this->db->insert('categories', $categoryData);
    }

    /**
     * Get category by ID
     */
    public function getById($id) {
        return $this->db->getRow('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    /**
     * Get all categories
     */
    public function getAll($limit = 100, $offset = 0, $activeOnly = false) {
        $sql = 'SELECT * FROM categories WHERE 1=1';
        $params = [];

        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }

        $sql .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->getRows($sql, $params);
    }

    /**
     * Count categories
     */
    public function count($activeOnly = false) {
        $sql = 'SELECT COUNT(*) as total FROM categories WHERE 1=1';
        $params = [];

        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }

        $result = $this->db->getRow($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Update category
     */
    public function update($id, $data) {
        unset($data['id']);
        unset($data['created_at']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('categories', $data, 'id = ?', [$id]);
    }

    /**
     * Delete category
     */
    public function delete($id) {
        // Move products to parent category if exists
        $category = $this->getById($id);
        if ($category && $category['parent_id']) {
            $this->db->update('categories', ['parent_id' => $category['parent_id']], 'parent_id = ?', [$id]);
        }

        return $this->db->delete('categories', 'id = ?', [$id]);
    }

    /**
     * Check if name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = 'SELECT id FROM categories WHERE name = ?';
        $params = [$name];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $result = $this->db->getRow($sql, $params);
        return (bool) $result;
    }

    /**
     * Get category tree (hierarchical)
     */
    public function getTree($parentId = null, $activeOnly = false) {
        $sql = 'SELECT * FROM categories WHERE parent_id ' . ($parentId ? '= ?' : 'IS NULL');
        $params = $parentId ? [$parentId] : [];

        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }

        $sql .= ' ORDER BY name ASC';

        $categories = $this->db->getRows($sql, $params);

        foreach ($categories as &$category) {
            $category['children'] = $this->getTree($category['id'], $activeOnly);
        }

        return $categories;
    }

    /**
     * Get category with product count
     */
    public function getWithProductCount($id) {
        $category = $this->getById($id);

        if (!$category) {
            return null;
        }

        $result = $this->db->getRow(
            'SELECT COUNT(*) as product_count FROM products WHERE category_id = ?',
            [$id]
        );

        $category['product_count'] = $result['product_count'] ?? 0;

        return $category;
    }

    /**
     * Generate slug from text
     */
    private function generateSlug($text) {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug is unique, if not add number
        if ($this->slugExists($slug)) {
            $slug = $slug . '-' . time();
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug) {
        $result = $this->db->getRow('SELECT id FROM categories WHERE slug = ?', [$slug]);
        return (bool) $result;
    }
}

?>
