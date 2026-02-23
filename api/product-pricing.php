<?php
/**
 * Product Pricing API
 */

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/ProductPricingTier.php';

use PIM\Utils\Auth;
use PIM\Models\ProductPricingTier;

Auth::startSession();

// Check authentication
if (!Auth::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$request = trim($_GET['q'] ?? '', '/');
$parts = explode('/', $request);

$response = ['success' => false, 'error' => 'Invalid request'];

try {
    $pricingTier = new ProductPricingTier();

    /**
     * GET /api/product-pricing/product/:product_id
     * Get all pricing tiers for a product
     */
    if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'product' && isset($parts[1])) {
        $productId = intval($parts[1]);
        $tiers = $pricingTier->getByProduct($productId, true);
        
        // Get pricing summary
        $summary = $pricingTier->getPricingSummary($productId);

        $response = [
            'success' => true,
            'data' => $tiers,
            'summary' => $summary
        ];
    }

    /**
     * GET /api/product-pricing/variant/:variant_id
     * Get pricing tiers for a variant combination
     */
    else if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'variant' && isset($parts[1])) {
        $variantId = intval($parts[1]);
        $tiers = $pricingTier->getByVariant($variantId, true);

        $response = [
            'success' => true,
            'data' => $tiers
        ];
    }

    /**
     * GET /api/product-pricing/:id
     * Get a single pricing tier
     */
    else if ($method === 'GET' && isset($parts[0]) && is_numeric($parts[0])) {
        $tierId = intval($parts[0]);
        $tier = $pricingTier->getById($tierId);

        if ($tier) {
            $response = [
                'success' => true,
                'data' => $tier
            ];
        } else {
            http_response_code(404);
            $response = ['success' => false, 'error' => 'Pricing tier not found'];
        }
    }

    /**
     * POST /api/product-pricing
     * Create a pricing tier
     */
    else if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate
        $errors = $pricingTier->validateTier($data);
        
        if (!empty($errors)) {
            http_response_code(400);
            $response = ['success' => false, 'error' => implode(', ', $errors)];
        } else {
            $id = $pricingTier->create($data);
            
            if ($id) {
                $tier = $pricingTier->getById($id);
                $response = [
                    'success' => true,
                    'message' => 'Pricing tier created',
                    'data' => $tier
                ];
            } else {
                http_response_code(500);
                $response = ['success' => false, 'error' => 'Failed to create pricing tier'];
            }
        }
    }

    /**
     * PUT /api/product-pricing/:id
     * Update a pricing tier
     */
    else if ($method === 'PUT' && isset($parts[0]) && is_numeric($parts[0])) {
        $data = json_decode(file_get_contents('php://input'), true);
        $tierId = intval($parts[0]);

        // Validate
        $errors = $pricingTier->validateTier($data);
        
        if (!empty($errors)) {
            http_response_code(400);
            $response = ['success' => false, 'error' => implode(', ', $errors)];
        } else {
            $pricingTier->update($tierId, $data);
            $updated = $pricingTier->getById($tierId);

            $response = [
                'success' => true,
                'message' => 'Pricing tier updated',
                'data' => $updated
            ];
        }
    }

    /**
     * DELETE /api/product-pricing/:id
     * Delete a pricing tier
     */
    else if ($method === 'DELETE' && isset($parts[0]) && is_numeric($parts[0])) {
        $tierId = intval($parts[0]);
        $pricingTier->delete($tierId);

        $response = [
            'success' => true,
            'message' => 'Pricing tier deleted'
        ];
    }

    /**
     * GET /api/product-pricing/calculate/:product_id/:quantity
     * Calculate price for a given quantity
     */
    else if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'calculate' && isset($parts[1]) && isset($parts[2])) {
        $productId = intval($parts[1]);
        $quantity = floatval($parts[2]);
        $variantId = isset($parts[3]) ? intval($parts[3]) : null;

        $applicableTier = $pricingTier->getTierForQuantity($productId, $quantity, $variantId);

        if ($applicableTier) {
            $price = floatval($applicableTier['price']);
            $response = [
                'success' => true,
                'data' => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                    'tier' => $applicableTier,
                    'discount_percent' => $applicableTier['discount_percent'] ?? null
                ]
            ];
        } else {
            http_response_code(404);
            $response = ['success' => false, 'error' => 'No pricing tier found for this quantity'];
        }
    }

    /**
     * GET /api/product-pricing/units
     * Get available unit types
     */
    else if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'units') {
        $units = $pricingTier->getUnitTypes();
        
        $response = [
            'success' => true,
            'data' => $units
        ];
    }

    /**
     * POST /api/product-pricing/bulk
     * Bulk create or update pricing tiers
     */
    else if ($method === 'POST' && isset($parts[0]) && $parts[0] === 'bulk') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['product_id']) || empty($data['tiers'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Missing required fields: product_id, tiers'];
        } else {
            $createdIds = $pricingTier->bulkUpdateTiers($data['product_id'], $data['tiers']);
            
            $response = [
                'success' => true,
                'message' => 'Pricing tiers updated',
                'data' => ['created_count' => count($createdIds), 'ids' => $createdIds]
            ];
        }
    }

    else {
        http_response_code(404);
        $response = ['success' => false, 'error' => 'Endpoint not found'];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
