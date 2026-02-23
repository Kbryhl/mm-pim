<?php
/**
 * Product Variants API
 */

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/ProductVariant.php';

use PIM\Utils\Auth;
use PIM\Models\ProductVariant;

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
    $variant = new ProductVariant();

    /**
     * GET /api/product-variants/types/:product_id
     * Get all variant types for a product
     */
    if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'types' && isset($parts[1])) {
        $productId = intval($parts[1]);
        $types = $variant->getVariantTypesByProduct($productId);
        
        // Load values for each type
        foreach ($types as &$type) {
            $type['values'] = $variant->getVariantValues($type['id']);
        }

        $response = [
            'success' => true,
            'data' => $types
        ];
    }

    /**
     * POST /api/product-variants/types
     * Create variant type
     */
    else if ($method === 'POST' && isset($parts[0]) && $parts[0] === 'types') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['product_id']) || empty($data['name'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Missing required fields: product_id, name'];
        } else {
            $id = $variant->createVariantType($data['product_id'], $data);
            $newType = $variant->getVariantTypeWithValues($id);
            
            $response = [
                'success' => true,
                'message' => 'Variant type created',
                'data' => $newType
            ];
        }
    }

    /**
     * PUT /api/product-variants/types/:id
     * Update variant type
     */
    else if ($method === 'PUT' && isset($parts[0]) && $parts[0] === 'types' && isset($parts[1])) {
        $data = json_decode(file_get_contents('php://input'), true);
        $typeId = intval($parts[1]);

        $variant->updateVariantType($typeId, $data);
        $updated = $variant->getVariantTypeWithValues($typeId);

        $response = [
            'success' => true,
            'message' => 'Variant type updated',
            'data' => $updated
        ];
    }

    /**
     * DELETE /api/product-variants/types/:id
     * Delete variant type
     */
    else if ($method === 'DELETE' && isset($parts[0]) && $parts[0] === 'types' && isset($parts[1])) {
        $typeId = intval($parts[1]);
        $variant->deleteVariantType($typeId);

        $response = [
            'success' => true,
            'message' => 'Variant type deleted'
        ];
    }

    /**
     * POST /api/product-variants/values
     * Add variant value
     */
    else if ($method === 'POST' && isset($parts[0]) && $parts[0] === 'values') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['variant_type_id']) || empty($data['value'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Missing required fields'];
        } else {
            $id = $variant->addVariantValue($data['variant_type_id'], $data);
            
            $response = [
                'success' => true,
                'message' => 'Variant value added',
                'data' => ['id' => $id]
            ];
        }
    }

    /**
     * PUT /api/product-variants/values/:id
     * Update variant value
     */
    else if ($method === 'PUT' && isset($parts[0]) && $parts[0] === 'values' && isset($parts[1])) {
        $data = json_decode(file_get_contents('php://input'), true);
        $valueId = intval($parts[1]);

        $variant->updateVariantValue($valueId, $data);

        $response = [
            'success' => true,
            'message' => 'Variant value updated'
        ];
    }

    /**
     * DELETE /api/product-variants/values/:id
     * Delete variant value
     */
    else if ($method === 'DELETE' && isset($parts[0]) && $parts[0] === 'values' && isset($parts[1])) {
        $valueId = intval($parts[1]);
        $variant->deleteVariantValue($valueId);

        $response = [
            'success' => true,
            'message' => 'Variant value deleted'
        ];
    }

    /**
     * GET /api/product-variants/combinations/:product_id
     * Get all variant combinations for a product
     */
    else if ($method === 'GET' && isset($parts[0]) && $parts[0] === 'combinations' && isset($parts[1])) {
        $productId = intval($parts[1]);
        $combinations = $variant->getVariantCombinations($productId);

        $response = [
            'success' => true,
            'data' => $combinations
        ];
    }

    /**
     * POST /api/product-variants/combinations
     * Create variant combination
     */
    else if ($method === 'POST' && isset($parts[0]) && $parts[0] === 'combinations') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['product_id']) || empty($data['variant_sku'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Missing required fields'];
        } else if ($variant->variantSkuExists($data['variant_sku'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Variant SKU already exists'];
        } else {
            $id = $variant->createVariantCombination($data['product_id'], $data);
            $combo = $variant->getVariantCombination($id);

            $response = [
                'success' => true,
                'message' => 'Variant combination created',
                'data' => $combo
            ];
        }
    }

    /**
     * PUT /api/product-variants/combinations/:id
     * Update variant combination
     */
    else if ($method === 'PUT' && isset($parts[0]) && $parts[0] === 'combinations' && isset($parts[1])) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($parts[1]);

        // Check SKU uniqueness if changed
        if (isset($data['variant_sku']) && $variant->variantSkuExists($data['variant_sku'], $id)) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Variant SKU already exists'];
        } else {
            $variant->updateVariantCombination($id, $data);
            $updated = $variant->getVariantCombination($id);

            $response = [
                'success' => true,
                'message' => 'Variant combination updated',
                'data' => $updated
            ];
        }
    }

    /**
     * DELETE /api/product-variants/combinations/:id
     * Delete variant combination
     */
    else if ($method === 'DELETE' && isset($parts[0]) && $parts[0] === 'combinations' && isset($parts[1])) {
        $id = intval($parts[1]);
        $variant->deleteVariantCombination($id);

        $response = [
            'success' => true,
            'message' => 'Variant combination deleted'
        ];
    }

    /**
     * POST /api/product-variants/bulk-create
     * Create multiple variant combinations at once
     */
    else if ($method === 'POST' && isset($parts[0]) && $parts[0] === 'bulk-create') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['product_id']) || empty($data['variant_type_ids'])) {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Missing required fields'];
        } else {
            $created = $variant->bulkCreateCombinations(
                $data['product_id'],
                $data['variant_type_ids'],
                $data['base_data'] ?? []
            );

            $response = [
                'success' => true,
                'message' => 'Variant combinations created',
                'data' => ['created_count' => count($created), 'ids' => $created]
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
