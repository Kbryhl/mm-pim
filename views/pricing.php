<?php
/**
 * Product Pricing Tiers Management View
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
    <title>Product Pricing - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pricing.css">
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
                <span>Pricing</span>
            </div>

            <!-- Page Header -->
            <div class="pricing-page-header">
                <div class="header-content">
                    <h1>💰 Pricing Tiers</h1>
                    <p class="subtitle">
                        Manage bulk discounts and quantity-based pricing for:
                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                        <span class="base-price-badge">Base: $<?php echo number_format($product['price'], 2); ?></span>
                    </p>
                </div>
                <div class="header-actions">
                    <button id="addTierBtn" class="btn btn-primary">+ Add Pricing Tier</button>
                    <button id="bulkImportBtn" class="btn btn-secondary">📥 Import JSON</button>
                    <a href="<?php echo BASE_URL; ?>products/edit?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Back to Product</a>
                </div>
            </div>

            <!-- Pricing Summary -->
            <div id="pricingSummary" class="pricing-summary">
                <p class="text-center text-muted">Loading pricing summary...</p>
            </div>

            <!-- Information Box -->
            <div class="info-section">
                <div class="info-box">
                    <h3>📊 How Pricing Tiers Work</h3>
                    <ul>
                        <li><strong>Unit Type:</strong> Define how you measure (pieces, kg, liters, etc.)</li>
                        <li><strong>Quantity Range:</strong> Set minimum and optional maximum quantities</li>
                        <li><strong>Tiered Pricing:</strong> Different prices for different quantity brackets</li>
                        <li><strong>Bulk Discounts:</strong> Automatically offer lower prices for larger orders</li>
                        <li><strong>Cost Tracking:</strong> Track profit margins with cost price and discount %</li>
                    </ul>
                </div>

                <div class="example-box">
                    <h4>💡 Example: T-Shirt Pricing</h4>
                    <table class="example-table">
                        <tr>
                            <th>Quantity (pieces)</th>
                            <th>Unit Price</th>
                            <th>Discount</th>
                        </tr>
                        <tr>
                            <td>1 - 10</td>
                            <td>$29.99</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>11 - 50</td>
                            <td>$25.99</td>
                            <td>13.3%</td>
                        </tr>
                        <tr>
                            <td>51 - 100</td>
                            <td>$22.99</td>
                            <td>23.3%</td>
                        </tr>
                        <tr>
                            <td>100+</td>
                            <td>$19.99</td>
                            <td>33.3%</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Pricing Tiers Container -->
            <div class="tiers-section">
                <h2>Active Pricing Tiers</h2>
                <div id="pricingTiersContainer" class="pricing-tiers-container">
                    <p class="text-center text-muted">Loading pricing tiers...</p>
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
    <script src="<?php echo BASE_URL; ?>js/pricing.js"></script>
    <script>
        let pricingManager;

        document.addEventListener('DOMContentLoaded', function() {
            pricingManager = new PricingTierManager(
                <?php echo $productId; ?>,
                <?php echo $product['price']; ?>
            );
        });

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });
    </script>
</body>
</html>
