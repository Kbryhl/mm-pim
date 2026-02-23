<?php
/**
 * Attribute Model
 * Handles product attributes
 */

namespace PIM\Models;

use PIM\Utils\Database;

class Attribute {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    /**
     * Create new attribute
     */
    public function create($data) {
        if (empty($data['name'])) {
            throw new \Exception("Attribute name is required");
        }

        if (empty($data['type'])) {
            throw new \Exception("Attribute type is required");
        }

        // Check if attribute already exists
        if ($this->nameExists($data['name'])) {
            throw new \Exception("Attribute already exists");
        }

        // Generate slug
        $slug = $this->generateSlug($data['name']);

        $attrData = [
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'is_required' => $data['is_required'] ?? 0,
            'is_filterable' => $data['is_filterable'] ?? 1,
            'is_searchable' => $data['is_searchable'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0
        ];

        return $this->db->insert('attributes', $attrData);
    }

    /**
     * Get attribute by ID
     */
    public function getById($id) {
        return $this->db->getRow('SELECT * FROM attributes WHERE id = ?', [$id]);
    }

    /**
     * Get all attributes
     */
    public function getAll($limit = 100, $offset = 0) {
        $sql = 'SELECT * FROM attributes ORDER BY sort_order ASC, name ASC LIMIT ? OFFSET ?';
        return $this->db->getRows($sql, [$limit, $offset]);
    }

    /**
     * Count attributes
     */
    public function count() {
        $result = $this->db->getRow('SELECT COUNT(*) as total FROM attributes');
        return $result['total'] ?? 0;
    }

    /**
     * Update attribute
     */
    public function update($id, $data) {
        unset($data['id']);
        unset($data['created_at']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('attributes', $data, 'id = ?', [$id]);
    }

    /**
     * Delete attribute
     */
    public function delete($id) {
        // Delete attribute options
        $this->db->delete('attribute_options', 'attribute_id = ?', [$id]);
        
        // Delete product attributes
        $this->db->delete('product_attributes', 'attribute_id = ?', [$id]);
        
        // Delete category attributes
        $this->db->delete('category_attributes', 'attribute_id = ?', [$id]);

        return $this->db->delete('attributes', 'id = ?', [$id]);
    }

    /**
     * Check if name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = 'SELECT id FROM attributes WHERE name = ?';
        $params = [$name];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $result = $this->db->getRow($sql, $params);
        return (bool) $result;
    }

    /**
     * Get attribute with options
     */
    public function getWithOptions($id) {
        $attribute = $this->getById($id);

        if (!$attribute) {
            return null;
        }

        $attribute['options'] = $this->getOptions($id);

        return $attribute;
    }

    /**
     * Get attribute options
     */
    public function getOptions($attributeId) {
        $sql = 'SELECT * FROM attribute_options WHERE attribute_id = ? ORDER BY sort_order ASC';
        return $this->db->getRows($sql, [$attributeId]);
    }

    /**
     * Add attribute option
     */
    public function addOption($attributeId, $label, $value, $sortOrder = 0) {
        $data = [
            'attribute_id' => $attributeId,
            'label' => $label,
            'value' => $value,
            'sort_order' => $sortOrder
        ];

        return $this->db->insert('attribute_options', $data);
    }

    /**
     * Update attribute option
     */
    public function updateOption($optionId, $label, $value) {
        $data = ['label' => $label, 'value' => $value];
        return $this->db->update('attribute_options', $data, 'id = ?', [$optionId]);
    }

    /**
     * Delete attribute option
     */
    public function deleteOption($optionId) {
        return $this->db->delete('attribute_options', 'id = ?', [$optionId]);
    }

    /**
     * Get filterable attributes
     */
    public function getFilterable() {
        $sql = 'SELECT * FROM attributes WHERE is_filterable = 1 ORDER BY name ASC';
        return $this->db->getRows($sql, []);
    }

    /**
     * Get searchable attributes
     */
    public function getSearchable() {
        $sql = 'SELECT * FROM attributes WHERE is_searchable = 1 ORDER BY name ASC';
        return $this->db->getRows($sql, []);
    }

    /**
     * Generate slug from text
     */
    private function generateSlug($text) {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');

        if ($this->slugExists($slug)) {
            $slug = $slug . '_' . time();
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug) {
        $result = $this->db->getRow('SELECT id FROM attributes WHERE slug = ?', [$slug]);
        return (bool) $result;
    }
}

?>
