<?php
/**
 * ProductPricingTier Model
 * Handles quantity-based and unit-based pricing tiers
 */

namespace PIM\Models;

use PIM\Utils\Database;

class ProductPricingTier {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create a pricing tier
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['product_id']) || empty($data['min_quantity']) || empty($data['price'])) {
            return null;
        }

        $result = $this->db->insert('product_pricing_tiers', [
            'product_id' => $data['product_id'],
            'variant_combination_id' => $data['variant_combination_id'] ?? null,
            'unit_type' => $data['unit_type'] ?? 'piece',
            'min_quantity' => $data['min_quantity'],
            'max_quantity' => $data['max_quantity'] ?? null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        return $result;
    }

    /**
     * Get all pricing tiers for a product
     */
    public function getByProduct($productId, $includeInactive = false) {
        $query = "SELECT * FROM product_pricing_tiers WHERE product_id = ?";
        
        if (!$includeInactive) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order ASC, min_quantity ASC";

        $result = $this->db->getRows($query, 'i', [$productId]);
        return $result ?: [];
    }

    /**
     * Get pricing tiers for a variant combination
     */
    public function getByVariant($variantCombinationId, $includeInactive = false) {
        $query = "SELECT * FROM product_pricing_tiers WHERE variant_combination_id = ?";
        
        if (!$includeInactive) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order ASC, min_quantity ASC";

        $result = $this->db->getRows($query, 'i', [$variantCombinationId]);
        return $result ?: [];
    }

    /**
     * Get a single pricing tier
     */
    public function getById($tierId) {
        return $this->db->getRow(
            "SELECT * FROM product_pricing_tiers WHERE id = ?",
            'i',
            [$tierId]
        );
    }

    /**
     * Update a pricing tier
     */
    public function update($tierId, $data) {
        $updateData = [];

        if (isset($data['min_quantity'])) $updateData['min_quantity'] = $data['min_quantity'];
        if (isset($data['max_quantity'])) $updateData['max_quantity'] = $data['max_quantity'];
        if (isset($data['price'])) $updateData['price'] = $data['price'];
        if (isset($data['cost_price'])) $updateData['cost_price'] = $data['cost_price'];
        if (isset($data['discount_percent'])) $updateData['discount_percent'] = $data['discount_percent'];
        if (isset($data['unit_type'])) $updateData['unit_type'] = $data['unit_type'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
        if (isset($data['sort_order'])) $updateData['sort_order'] = $data['sort_order'];

        return $this->db->update('product_pricing_tiers', $updateData, ['id' => $tierId]);
    }

    /**
     * Delete a pricing tier
     */
    public function delete($tierId) {
        return $this->db->delete('product_pricing_tiers', ['id' => $tierId]);
    }

    /**
     * Get applicable price for a quantity
     *
     * Returns price from the first tier that matches the quantity
     */
    public function getPriceForQuantity($productId, $quantity, $variantCombinationId = null) {
        $query = "
            SELECT price FROM product_pricing_tiers 
            WHERE product_id = ? 
            AND is_active = 1
            AND min_quantity <= ?
            AND (max_quantity IS NULL OR max_quantity >= ?)
        ";
        
        $types = 'idd';
        $params = [$productId, $quantity, $quantity];

        if ($variantCombinationId) {
            $query .= " AND (variant_combination_id = ? OR variant_combination_id IS NULL)";
            $types .= 'i';
            $params[] = $variantCombinationId;
            $query .= " ORDER BY variant_combination_id DESC, min_quantity DESC LIMIT 1";
        } else {
            $query .= " AND variant_combination_id IS NULL ORDER BY min_quantity DESC LIMIT 1";
        }

        $result = $this->db->getRow($query, $types, $params);
        return $result ? floatval($result['price']) : null;
    }

    /**
     * Get applicable tier for a quantity
     * Returns complete tier record
     */
    public function getTierForQuantity($productId, $quantity, $variantCombinationId = null) {
        $query = "
            SELECT * FROM product_pricing_tiers 
            WHERE product_id = ? 
            AND is_active = 1
            AND min_quantity <= ?
            AND (max_quantity IS NULL OR max_quantity >= ?)
        ";
        
        $types = 'idd';
        $params = [$productId, $quantity, $quantity];

        if ($variantCombinationId) {
            $query .= " AND (variant_combination_id = ? OR variant_combination_id IS NULL)";
            $types .= 'i';
            $params[] = $variantCombinationId;
            $query .= " ORDER BY variant_combination_id DESC, min_quantity DESC LIMIT 1";
        } else {
            $query .= " AND variant_combination_id IS NULL ORDER BY min_quantity DESC LIMIT 1";
        }

        return $this->db->getRow($query, $types, $params);
    }

    /**
     * Get pricing summary for product (all tiers)
     */
    public function getPricingSummary($productId) {
        $tiers = $this->getByProduct($productId, false);
        
        if (empty($tiers)) {
            return null;
        }

        // Get base price
        $basePrice = $this->db->getRow(
            "SELECT price FROM products WHERE id = ?",
            'i',
            [$productId]
        )['price'] ?? null;

        // Get min and max prices from tiers
        $prices = array_map(function($tier) {
            return floatval($tier['price']);
        }, $tiers);

        return [
            'base_price' => $basePrice ? floatval($basePrice) : null,
            'min_price' => min($prices),
            'max_price' => max($prices),
            'tier_count' => count($tiers),
            'has_tiers' => count($tiers) > 0
        ];
    }

    /**
     * Check if pricing tiers exist for product
     */
    public function hasTiers($productId) {
        $result = $this->db->getRow(
            "SELECT COUNT(*) as count FROM product_pricing_tiers WHERE product_id = ? AND is_active = 1",
            'i',
            [$productId]
        );
        return $result && $result['count'] > 0;
    }

    /**
     * Calculate discount percentage between two prices
     */
    public function calculateDiscount($originalPrice, $tierPrice) {
        if ($originalPrice <= 0) return 0;
        return round((($originalPrice - $tierPrice) / $originalPrice) * 100, 2);
    }

    /**
     * Validate pricing tier data
     */
    public function validateTier($data) {
        $errors = [];

        if (empty($data['product_id'])) {
            $errors[] = 'Product ID is required';
        }

        if (empty($data['min_quantity'])) {
            $errors[] = 'Minimum quantity is required';
        } elseif (!is_numeric($data['min_quantity']) || $data['min_quantity'] < 0) {
            $errors[] = 'Minimum quantity must be a positive number';
        }

        if (isset($data['max_quantity']) && $data['max_quantity'] !== null) {
            if (!is_numeric($data['max_quantity']) || $data['max_quantity'] < 0) {
                $errors[] = 'Maximum quantity must be a positive number';
            } elseif ($data['max_quantity'] <= $data['min_quantity']) {
                $errors[] = 'Maximum quantity must be greater than minimum quantity';
            }
        }

        if (empty($data['price'])) {
            $errors[] = 'Price is required';
        } elseif (!is_numeric($data['price']) || $data['price'] < 0) {
            $errors[] = 'Price must be a positive number';
        }

        if (isset($data['cost_price']) && $data['cost_price'] !== null) {
            if (!is_numeric($data['cost_price']) || $data['cost_price'] < 0) {
                $errors[] = 'Cost price must be a positive number';
            }
        }

        if (isset($data['discount_percent']) && $data['discount_percent'] !== null) {
            if (!is_numeric($data['discount_percent']) || $data['discount_percent'] < 0 || $data['discount_percent'] > 100) {
                $errors[] = 'Discount percent must be between 0 and 100';
            }
        }

        return $errors;
    }

    /**
     * Get unit types (pieces, kg, liters, etc.)
     */
    public function getUnitTypes() {
        return [
            'piece' => 'Piece(s)',
            'kg' => 'Kilogram(s)',
            'g' => 'Gram(s)',
            'liter' => 'Liter(s)',
            'ml' => 'Milliliter(s)',
            'meter' => 'Meter(s)',
            'cm' => 'Centimeter(s)',
            'pack' => 'Pack(s)',
            'box' => 'Box(es)',
            'bundle' => 'Bundle(s)',
            'dozen' => 'Dozen'
        ];
    }

    /**
     * Get pricing tier with applied discount calculation
     */
    public function getTierWithDiscount($tierId, $basePrice = null) {
        $tier = $this->getById($tierId);
        
        if (!$tier) {
            return null;
        }

        if ($basePrice && $basePrice > $tier['price']) {
            $tier['discount_amount'] = $basePrice - $tier['price'];
            $tier['calculated_discount_percent'] = $this->calculateDiscount($basePrice, $tier['price']);
        }

        return $tier;
    }

    /**
     * Bulk update pricing tiers
     */
    public function bulkUpdateTiers($productId, $tiersData) {
        // Delete existing tiers for this product
        $this->db->delete('product_pricing_tiers', ['product_id' => $productId]);

        // Create new tiers
        $createdIds = [];
        foreach ($tiersData as $tierData) {
            $tierData['product_id'] = $productId;
            $id = $this->create($tierData);
            if ($id) {
                $createdIds[] = $id;
            }
        }

        return $createdIds;
    }

    /**
     * Export pricing tiers as array
     */
    public function exportTiers($productId) {
        return $this->getByProduct($productId, true); // Include inactive
    }

    /**
     * Import pricing tiers from array
     */
    public function importTiers($productId, $tiersData) {
        $imported = 0;
        
        foreach ($tiersData as $tierData) {
            // Validate
            $errors = $this->validateTier(array_merge(['product_id' => $productId], $tierData));
            
            if (empty($errors)) {
                $tierData['product_id'] = $productId;
                if ($this->create($tierData)) {
                    $imported++;
                }
            }
        }

        return $imported;
    }
}
