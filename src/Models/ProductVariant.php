<?php
/**
 * ProductVariant Model
 * Handles product variants and variant combinations
 */

namespace PIM\Models;

use PIM\Utils\Database;

class ProductVariant {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create a new variant type for a product
     */
    public function createVariantType($productId, $data) {
        $slug = $this->slugify($data['name']);
        
        $result = $this->db->insert('product_variant_types', [
            'product_id' => $productId,
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $data['type'] ?? 'dropdown',
            'is_required' => $data['is_required'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        return $result;
    }

    /**
     * Get all variant types for a product
     */
    public function getVariantTypesByProduct($productId) {
        $result = $this->db->getRows(
            "SELECT * FROM product_variant_types WHERE product_id = ? ORDER BY sort_order ASC",
            'i',
            [$productId]
        );

        return $result ?: [];
    }

    /**
     * Get variant type with its values
     */
    public function getVariantTypeWithValues($variantTypeId) {
        $type = $this->db->getRow(
            "SELECT * FROM product_variant_types WHERE id = ?",
            'i',
            [$variantTypeId]
        );

        if (!$type) {
            return null;
        }

        $type['values'] = $this->getVariantValues($variantTypeId);
        return $type;
    }

    /**
     * Update a variant type
     */
    public function updateVariantType($variantTypeId, $data) {
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
            $updateData['slug'] = $this->slugify($data['name']);
        }
        if (isset($data['type'])) $updateData['type'] = $data['type'];
        if (isset($data['is_required'])) $updateData['is_required'] = $data['is_required'];
        if (isset($data['sort_order'])) $updateData['sort_order'] = $data['sort_order'];

        return $this->db->update('product_variant_types', $updateData, ['id' => $variantTypeId]);
    }

    /**
     * Delete a variant type
     */
    public function deleteVariantType($variantTypeId) {
        return $this->db->delete('product_variant_types', ['id' => $variantTypeId]);
    }

    /**
     * Add a value to a variant type
     */
    public function addVariantValue($variantTypeId, $data) {
        $result = $this->db->insert('product_variant_values', [
            'variant_type_id' => $variantTypeId,
            'value' => $data['value'],
            'display_value' => $data['display_value'] ?? $data['value'],
            'color_code' => $data['color_code'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        return $result;
    }

    /**
     * Get all values for a variant type
     */
    public function getVariantValues($variantTypeId) {
        $result = $this->db->getRows(
            "SELECT * FROM product_variant_values WHERE variant_type_id = ? ORDER BY sort_order ASC",
            'i',
            [$variantTypeId]
        );

        return $result ?: [];
    }

    /**
     * Update a variant value
     */
    public function updateVariantValue($variantValueId, $data) {
        $updateData = [];

        if (isset($data['value'])) $updateData['value'] = $data['value'];
        if (isset($data['display_value'])) $updateData['display_value'] = $data['display_value'];
        if (isset($data['color_code'])) $updateData['color_code'] = $data['color_code'];
        if (isset($data['sort_order'])) $updateData['sort_order'] = $data['sort_order'];

        return $this->db->update('product_variant_values', $updateData, ['id' => $variantValueId]);
    }

    /**
     * Delete a variant value
     */
    public function deleteVariantValue($variantValueId) {
        return $this->db->delete('product_variant_values', ['id' => $variantValueId]);
    }

    /**
     * Create a variant combination
     */
    public function createVariantCombination($productId, $data) {
        // Generate variant name from values
        $variantName = $this->generateVariantName($productId, $data['variant_values'] ?? []);

        $result = $this->db->insert('product_variant_combinations', [
            'product_id' => $productId,
            'variant_sku' => $data['variant_sku'],
            'variant_name' => $variantName,
            'price' => $data['price'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'image_url' => $data['image_url'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'variant_data' => json_encode($data['variant_values'] ?? [])
        ]);

        return $result;
    }

    /**
     * Get all variant combinations for a product
     */
    public function getVariantCombinations($productId) {
        $result = $this->db->getRows(
            "SELECT * FROM product_variant_combinations WHERE product_id = ? ORDER BY created_at DESC",
            'i',
            [$productId]
        );

        if ($result) {
            foreach ($result as &$combo) {
                $combo['variant_data'] = json_decode($combo['variant_data'], true);
            }
        }

        return $result ?: [];
    }

    /**
     * Get single variant combination
     */
    public function getVariantCombination($combinationId) {
        $result = $this->db->getRow(
            "SELECT * FROM product_variant_combinations WHERE id = ?",
            'i',
            [$combinationId]
        );

        if ($result) {
            $result['variant_data'] = json_decode($result['variant_data'], true);
        }

        return $result;
    }

    /**
     * Update variant combination
     */
    public function updateVariantCombination($combinationId, $data) {
        $updateData = [];

        if (isset($data['variant_sku'])) $updateData['variant_sku'] = $data['variant_sku'];
        if (isset($data['price'])) $updateData['price'] = $data['price'];
        if (isset($data['cost_price'])) $updateData['cost_price'] = $data['cost_price'];
        if (isset($data['stock_quantity'])) $updateData['stock_quantity'] = $data['stock_quantity'];
        if (isset($data['image_url'])) $updateData['image_url'] = $data['image_url'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];

        if (isset($data['variant_values'])) {
            $updateData['variant_data'] = json_encode($data['variant_values']);
            $updateData['variant_name'] = $data['variant_name'] ?? $this->generateVariantNameFromData($data['variant_values']);
        }

        return $this->db->update('product_variant_combinations', $updateData, ['id' => $combinationId]);
    }

    /**
     * Delete variant combination
     */
    public function deleteVariantCombination($combinationId) {
        return $this->db->delete('product_variant_combinations', ['id' => $combinationId]);
    }

    /**
     * Get variant combination by SKU
     */
    public function getVariantBySku($sku) {
        return $this->db->getRow(
            "SELECT * FROM product_variant_combinations WHERE variant_sku = ?",
            's',
            [$sku]
        );
    }

    /**
     * Bulk create variant combinations
     * Useful for creating all combinations from selected values
     */
    public function bulkCreateCombinations($productId, $variantTypes, $baseData = []) {
        // Get all variant type values
        $valuesByType = [];
        
        foreach ($variantTypes as $typeId) {
            $values = $this->getVariantValues($typeId);
            $valuesByType[$typeId] = array_map(function($v) { return $v['value']; }, $values);
        }

        // Generate all combinations
        $combinations = $this->cartesianProduct($valuesByType);
        $created = [];

        foreach ($combinations as $combo) {
            $variantName = implode(' - ', array_values($combo));
            
            $data = array_merge($baseData, [
                'variant_name' => $variantName,
                'variant_values' => $combo
            ]);

            // Generate SKU if not provided
            if (empty($data['variant_sku'])) {
                $baseSku = $this->db->getRow(
                    "SELECT sku FROM products WHERE id = ?",
                    'i',
                    [$productId]
                )['sku'];
                
                $suffix = substr(hash('md5', implode('-', $combo)), 0, 6);
                $data['variant_sku'] = "{$baseSku}-{$suffix}";
            }

            $id = $this->createVariantCombination($productId, $data);
            if ($id) {
                $created[] = $id;
            }
        }

        return $created;
    }

    /**
     * Check if variant SKU exists
     */
    public function variantSkuExists($sku, $excludeId = null) {
        $query = "SELECT id FROM product_variant_combinations WHERE variant_sku = ?";
        $types = 's';
        $params = [$sku];

        if ($excludeId) {
            $query .= " AND id != ?";
            $types .= 'i';
            $params[] = $excludeId;
        }

        return $this->db->getRow($query, $types, $params) !== null;
    }

    /**
     * Get product with all variants
     */
    public function getProductWithVariants($productId) {
        $variantTypes = $this->getVariantTypesByProduct($productId);
        
        foreach ($variantTypes as &$type) {
            $type['values'] = $this->getVariantValues($type['id']);
        }

        return [
            'variant_types' => $variantTypes,
            'variant_combinations' => $this->getVariantCombinations($productId)
        ];
    }

    /**
     * Generate variant name from values array
     */
    private function generateVariantName($productId, $values) {
        if (empty($values)) {
            return '';
        }

        // Get variant type names in order
        $types = $this->getVariantTypesByProduct($productId);
        $typeMap = [];
        foreach ($types as $type) {
            $typeMap[$type['id']] = $type['name'];
        }

        $parts = [];
        foreach ($values as $typeId => $value) {
            if (isset($typeMap[$typeId])) {
                $parts[] = "{$typeMap[$typeId]}: {$value}";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Generate variant name from data array
     */
    private function generateVariantNameFromData($variants) {
        if (empty($variants)) {
            return '';
        }

        $parts = [];
        foreach ($variants as $key => $value) {
            $parts[] = "{$key}: {$value}";
        }
        
        return implode(', ', $parts);
    }

    /**
     * Cartesian product for generating combinations
     */
    private function cartesianProduct($arrays) {
        if (empty($arrays)) {
            return [];
        }

        $keys = array_keys($arrays);
        $products = [[]];

        foreach ($keys as $key) {
            $new_products = [];
            foreach ($products as $product) {
                foreach ($arrays[$key] as $value) {
                    $new_products[] = array_merge($product, [$key => $value]);
                }
            }
            $products = $new_products;
        }

        return $products;
    }

    /**
     * Slugify text
     */
    private function slugify($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
