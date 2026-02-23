<?php
/**
 * Category API Endpoints
 */

require_once '../config/app.php';
require_once '../config/database.php';

require_once '../src/Utils/Database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/Category.php';

use PIM\Utils\Database as DB;
use PIM\Utils\Auth;
use PIM\Models\Category;

Auth::startSession();
Auth::requireAuth();

header('Content-Type: application/json');

try {
    $db = new DB($conn);
    $categoryModel = new Category($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($path === '/api/categories' && $method === 'GET') {
        handleGetCategories($categoryModel);
    } elseif ($path === '/api/categories' && $method === 'POST') {
        handleCreateCategory($categoryModel);
    } elseif (preg_match('/^\/api\/categories\/(\d+)$/', $path, $matches) && $method === 'GET') {
        handleGetCategory($categoryModel, $matches[1]);
    } elseif (preg_match('/^\/api\/categories\/(\d+)$/', $path, $matches) && $method === 'PUT') {
        handleUpdateCategory($categoryModel, $matches[1]);
    } elseif (preg_match('/^\/api\/categories\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
        handleDeleteCategory($categoryModel, $matches[1]);
    } elseif ($path === '/api/categories/tree' && $method === 'GET') {
        handleGetCategoryTree($categoryModel);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGetCategories($categoryModel) {
    $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
    $categories = $categoryModel->getAll(100, 0, $activeOnly);
    
    http_response_code(200);
    echo json_encode(['data' => $categories]);
}

function handleGetCategory($categoryModel, $id) {
    $category = $categoryModel->getWithProductCount($id);

    if (!$category) {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found']);
        return;
    }

    http_response_code(200);
    echo json_encode(['data' => $category]);
}

function handleCreateCategory($categoryModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $categoryId = $categoryModel->create($input);
        http_response_code(201);
        echo json_encode([
            'message' => 'Category created successfully',
            'category_id' => $categoryId
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateCategory($categoryModel, $id) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    try {
        $category = $categoryModel->getById($id);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }

        $categoryModel->update($id, $input);
        http_response_code(200);
        echo json_encode(['message' => 'Category updated successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteCategory($categoryModel, $id) {
    try {
        $category = $categoryModel->getById($id);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }

        $categoryModel->delete($id);
        http_response_code(200);
        echo json_encode(['message' => 'Category deleted successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetCategoryTree($categoryModel) {
    $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
    $tree = $categoryModel->getTree(null, $activeOnly);
    
    http_response_code(200);
    echo json_encode(['data' => $tree]);
}

?>
