<?php
/**
 * Product API Endpoints
 */

// Load configuration
require_once '../config/app.php';
require_once '../config/database.php';

// Load utility classes
require_once '../src/Utils/Database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/Product.php';

use PIM\Utils\Database as DB;
use PIM\Utils\Auth;
use PIM\Models\Product;

// Start session and require authentication
Auth::startSession();
Auth::requireAuth();

// Set response header
header('Content-Type: application/json');

try {
    // Initialize database and models
    $db = new DB($conn);
    $productModel = new Product($db);

    // Get request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Route to appropriate handler
    if ($path === '/api/products' && $method === 'GET') {
        handleGetProducts($productModel);
    } elseif ($path === '/api/products' && $method === 'POST') {
        handleCreateProduct($productModel);
    } elseif (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches) && $method === 'GET') {
        handleGetProduct($productModel, $matches[1]);
    } elseif (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches) && $method === 'PUT') {
        handleUpdateProduct($productModel, $matches[1]);
    } elseif (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
        handleDeleteProduct($productModel, $matches[1]);
    } elseif ($path === '/api/products/bulk/status' && $method === 'PUT') {
        handleBulkUpdateStatus($productModel);
    } elseif ($path === '/api/products/stats' && $method === 'GET') {
        handleGetStats($productModel);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Get all products
 */
function handleGetProducts($productModel) {
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $filters = [];
    if (!empty($_GET['category_id'])) {
        $filters['category_id'] = intval($_GET['category_id']);
    }
    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    $products = $productModel->getAll($limit, $offset, $filters);
    $total = $productModel->count($filters);

    http_response_code(200);
    echo json_encode([
        'data' => $products,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single product
 */
function handleGetProduct($productModel, $id) {
    $product = $productModel->getWithAttributes($id);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }

    http_response_code(200);
    echo json_encode(['data' => $product]);
}

/**
 * Create product
 */
function handleCreateProduct($productModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $productId = $productModel->create($input);

        http_response_code(201);
        echo json_encode([
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Update product
 */
function handleUpdateProduct($productModel, $id) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $product = $productModel->getById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        // Check SKU uniqueness if changed
        if (isset($input['sku']) && $input['sku'] !== $product['sku']) {
            if ($productModel->skuExists($input['sku'])) {
                http_response_code(400);
                echo json_encode(['error' => 'SKU already exists']);
                return;
            }
        }

        $productModel->update($id, $input);

        // Update attributes if provided
        if (isset($input['attributes']) && is_array($input['attributes'])) {
            foreach ($input['attributes'] as $attrId => $value) {
                $productModel->updateAttribute($id, $attrId, $value);
            }
        }

        http_response_code(200);
        echo json_encode([
            'message' => 'Product updated successfully',
            'product_id' => $id
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Delete product
 */
function handleDeleteProduct($productModel, $id) {
    try {
        $product = $productModel->getById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        $productModel->delete($id);

        http_response_code(200);
        echo json_encode(['message' => 'Product deleted successfully']);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Bulk update status
 */
function handleBulkUpdateStatus($productModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['ids']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: ids, status']);
        return;
    }

    try {
        $affected = $productModel->bulkUpdateStatus($input['ids'], $input['status']);

        http_response_code(200);
        echo json_encode([
            'message' => "Updated $affected products",
            'affected' => $affected
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Get product statistics
 */
function handleGetStats($productModel) {
    $stats = $productModel->getStats();

    http_response_code(200);
    echo json_encode(['data' => $stats]);
}

?>
