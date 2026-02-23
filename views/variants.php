<?php
/**
 * Product Variants Management View
 */

require_once '../src/Utils/Auth.php';
use PIM\Utils\Auth;

Auth::requireAuth();

// Get product ID from query
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;

if (!$productId) {
    header('Location: ' . BASE_URL . 'products');
    exit;
}

// Fetch product info
require_once '../src/Models/Product.php';
use PIM\Models\Product;

$productModel = new Product();
$product = $productModel->getById($productId);

if (!$product) {
    http_response_code(404);
    echo "Product not found";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Variants - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/variants.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo"><?php echo APP_NAME; ?></div>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>products">Products</a></li>
                <li><a href="<?php echo BASE_URL; ?>categories">Categories</a></li>
                <li><a href="<?php echo BASE_URL; ?>attributes">Attributes</a></li>
                <li><a href="<?php echo BASE_URL; ?>settings">Settings</a></li>
            </ul>
            <div class="user-menu">
                <span class="user-name"><?php echo htmlspecialchars(Auth::getCurrentUser()['first_name'] ?: Auth::getCurrentUser()['username']); ?></span>
                <span class="user-role"><?php echo ucfirst(Auth::getCurrentUser()['role']); ?></span>
                <button id="logoutBtn" class="btn btn-danger" title="Logout">Logout</button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="<?php echo BASE_URL; ?>">Dashboard</a> /
                <a href="<?php echo BASE_URL; ?>products">Products</a> /
                <a href="<?php echo BASE_URL; ?>products/edit?id=<?php echo $product['id']; ?>">
                    <?php echo htmlspecialchars($product['name']); ?>
                </a> /
                <span>Variants</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>Product Variants</h1>
                    <p class="subtitle">
                        Managing variants for: <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                    </p>
                </div>
                <div class="header-actions">
                    <a href="<?php echo BASE_URL; ?>products/edit?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Back to Product</a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="variant-tabs">
                <button class="tab-btn active" data-tab="types">Variant Types</button>
                <button class="tab-btn" data-tab="combinations">Variant Combinations</button>
            </div>

            <!-- Variant Types Tab -->
            <div id="types" class="tab-content active">
                <div class="section-header">
                    <h2>Define Variant Types</h2>
                    <p>Create the variant options for this product (e.g., Size, Color, Material)</p>
                    <button id="addVariantTypeBtn" class="btn btn-primary">+ Add Variant Type</button>
                </div>

                <div id="variantTypesContainer" class="variant-types-grid">
                    <p class="text-center text-muted">Loading...</p>
                </div>
            </div>

            <!-- Variant Combinations Tab -->
            <div id="combinations" class="tab-content">
                <div class="section-header">
                    <h2>Variant Combinations</h2>
                    <p>Create specific product variants with unique SKUs and prices</p>
                    <button id="bulkCreateCombinationsBtn" class="btn btn-primary">+ Generate All Combinations</button>
                </div>

                <div class="combinations-info">
                    <div class="info-box">
                        <h4>📊 Combination Generator</h4>
                        <p>Select your variant types and generate all possible combinations automatically. 
                           Each combination will get a unique SKU and can have its own price and stock levels.</p>
                    </div>
                </div>

                <div id="variantCombinationsContainer" class="variant-combinations-container">
                    <p class="text-center text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-3 mb-3">
        <p>&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>js/auth.js"></script>
    <script src="<?php echo BASE_URL; ?>js/variants.js"></script>
    <script>
        let variantManager;

        document.addEventListener('DOMContentLoaded', function() {
            variantManager = new ProductVariantManager(<?php echo $productId; ?>);

            // Tab switching
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tab = this.getAttribute('data-tab');
                    
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    document.getElementById(tab).classList.add('active');
                });
            });
        });

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });
    </script>
</body>
</html>
