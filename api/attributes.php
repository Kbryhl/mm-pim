<?php
/**
 * Attribute API Endpoints
 */

require_once '../config/app.php';
require_once '../config/database.php';

require_once '../src/Utils/Database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/Attribute.php';

use PIM\Utils\Database as DB;
use PIM\Utils\Auth;
use PIM\Models\Attribute;

Auth::startSession();
Auth::requireAuth();

header('Content-Type: application/json');

try {
    $db = new DB($conn);
    $attrModel = new Attribute($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($path === '/api/attributes' && $method === 'GET') {
        handleGetAttributes($attrModel);
    } elseif ($path === '/api/attributes' && $method === 'POST') {
        handleCreateAttribute($attrModel);
    } elseif (preg_match('/^\/api\/attributes\/(\d+)$/', $path, $matches) && $method === 'GET') {
        handleGetAttribute($attrModel, $matches[1]);
    } elseif (preg_match('/^\/api\/attributes\/(\d+)$/', $path, $matches) && $method === 'PUT') {
        handleUpdateAttribute($attrModel, $matches[1]);
    } elseif (preg_match('/^\/api\/attributes\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
        handleDeleteAttribute($attrModel, $matches[1]);
    } elseif ($path === '/api/attributes/filterable' && $method === 'GET') {
        handleGetFilterableAttributes($attrModel);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGetAttributes($attrModel) {
    $attributes = $attrModel->getAll();
    
    http_response_code(200);
    echo json_encode(['data' => $attributes]);
}

function handleGetAttribute($attrModel, $id) {
    $attribute = $attrModel->getWithOptions($id);

    if (!$attribute) {
        http_response_code(404);
        echo json_encode(['error' => 'Attribute not found']);
        return;
    }

    http_response_code(200);
    echo json_encode(['data' => $attribute]);
}

function handleCreateAttribute($attrModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $attrId = $attrModel->create($input);
        http_response_code(201);
        echo json_encode([
            'message' => 'Attribute created successfully',
            'attribute_id' => $attrId
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateAttribute($attrModel, $id) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $attribute = $attrModel->getById($id);

        if (!$attribute) {
            http_response_code(404);
            echo json_encode(['error' => 'Attribute not found']);
            return;
        }

        $attrModel->update($id, $input);
        http_response_code(200);
        echo json_encode(['message' => 'Attribute updated successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteAttribute($attrModel, $id) {
    try {
        $attribute = $attrModel->getById($id);

        if (!$attribute) {
            http_response_code(404);
            echo json_encode(['error' => 'Attribute not found']);
            return;
        }

        $attrModel->delete($id);
        http_response_code(200);
        echo json_encode(['message' => 'Attribute deleted successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetFilterableAttributes($attrModel) {
    $attributes = $attrModel->getFilterable();
    
    http_response_code(200);
    echo json_encode(['data' => $attributes]);
}

?>
