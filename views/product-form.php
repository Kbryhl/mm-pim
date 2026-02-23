<?php
/**
 * Product Form View (Add/Edit)
 */

require_once '../src/Utils/Auth.php';
use PIM\Utils\Auth;

Auth::requireAuth();

// Determine if we're editing or creating
$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$productId = $isEdit ? intval($_GET['id']) : null;
$pageTitle = $isEdit ? 'Edit Product' : 'Add New Product';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/products.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo"><?php echo APP_NAME; ?></div>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>products" class="active">Products</a></li>
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
                <span><?php echo $pageTitle; ?></span>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <div class="form-header">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p class="form-subtitle">Fill in the product details below</p>
                </div>

                <form id="productForm" class="product-form">
                    <!-- Basic Information -->
                    <fieldset class="form-section">
                        <legend>Basic Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Product Name *</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required 
                                    placeholder="Enter product name"
                                >
                                <small class="error-message"></small>
                            </div>

                            <div class="form-group">
                                <label for="sku">SKU *</label>
                                <input 
                                    type="text" 
                                    id="sku" 
                                    name="sku" 
                                    required 
                                    placeholder="Product SKU (unique)"
                                >
                                <small class="error-message"></small>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                placeholder="Enter detailed product description..."
                                rows="4"
                            ></textarea>
                        </div>
                    </fieldset>

                    <!-- Pricing & Organization -->
                    <fieldset class="form-section">
                        <legend>Pricing & Organization</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input 
                                    type="number" 
                                    id="price" 
                                    name="price" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                >
                            </div>

                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id">
                                    <option value="">Select a category</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Image -->
                    <fieldset class="form-section">
                        <legend>Product Image</legend>

                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input 
                                type="url" 
                                id="image_url" 
                                name="image_url" 
                                placeholder="https://example.com/image.jpg"
                            >
                            <div id="imagePreview" class="image-preview hidden">
                                <img id="previewImg" src="" alt="Product preview">
                                <button type="button" class="btn btn-small btn-danger" onclick="clearImage()">Remove Image</button>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Attributes -->
                    <fieldset class="form-section">
                        <legend>Product Attributes</legend>
                        <div id="attributesContainer" class="attributes-container">
                            <p class="text-muted">Loading attributes...</p>
                        </div>
                    </fieldset>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <?php echo $isEdit ? 'Update Product' : 'Create Product'; ?>
                        </button>
                        <?php if ($isEdit): ?>
                        <a href="<?php echo BASE_URL; ?>products/variants?product_id=<?php echo $productId; ?>" class="btn btn-secondary btn-large" title="Manage product variants">⚙️ Manage Variants</a>
                        <a href="<?php echo BASE_URL; ?>products/pricing?product_id=<?php echo $productId; ?>" class="btn btn-secondary btn-large" title="Manage pricing tiers">💰 Pricing</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>products" class="btn btn-secondary btn-large">Cancel</a>
                    </div>

                    <div id="formMessage" class="error-message hidden"></div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-3 mb-3">
        <p>&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>js/auth.js"></script>
    <script src="<?php echo BASE_URL; ?>js/products.js"></script>
    <script>
        const productId = <?php echo $productId ? $productId : 'null'; ?>;
        const isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            new ProductForm(productId, isEdit);
        });

        function clearImage() {
            document.getElementById('image_url').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
        }
    </script>
</body>
</html>
