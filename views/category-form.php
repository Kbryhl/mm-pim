<?php
/**
 * Category Form View (Add/Edit)
 */

require_once '../src/Utils/Auth.php';
use PIM\Utils\Auth;

Auth::requireAuth();

// Determine if we're editing or creating
$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$categoryId = $isEdit ? intval($_GET['id']) : null;
$pageTitle = $isEdit ? 'Edit Category' : 'Add New Category';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/categories.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo"><?php echo APP_NAME; ?></div>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>products">Products</a></li>
                <li><a href="<?php echo BASE_URL; ?>categories" class="active">Categories</a></li>
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
                <a href="<?php echo BASE_URL; ?>categories">Categories</a> /
                <span><?php echo $pageTitle; ?></span>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <div class="form-header">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p class="form-subtitle">Fill in the category details below</p>
                </div>

                <form id="categoryForm" class="category-form">
                    <!-- Basic Information -->
                    <fieldset class="form-section">
                        <legend>Basic Information</legend>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="name">Category Name *</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required 
                                    placeholder="Enter category name"
                                >
                                <small class="error-message"></small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_id">Parent Category</label>
                                <select id="parent_id" name="parent_id">
                                    <option value="">No Parent (Top-level)</option>
                                </select>
                                <small class="form-hint">Leave empty for top-level categories</small>
                            </div>

                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="is_active" name="is_active" checked>
                                    <label for="is_active" class="toggle-label">
                                        <span class="toggle-on">Active</span>
                                        <span class="toggle-off">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Description -->
                    <fieldset class="form-section">
                        <legend>Additional Information</legend>

                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                placeholder="Enter category description..."
                                rows="4"
                            ></textarea>
                            <small class="form-hint">Optional description for this category</small>
                        </div>
                    </fieldset>

                    <!-- Category Image -->
                    <fieldset class="form-section">
                        <legend>Category Image</legend>

                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input 
                                type="url" 
                                id="image_url" 
                                name="image_url" 
                                placeholder="https://example.com/image.jpg"
                            >
                            <small class="form-hint">Display image for this category</small>
                            <div id="imagePreview" class="image-preview hidden">
                                <img id="previewImg" src="" alt="Category preview">
                                <button type="button" class="btn btn-small btn-danger" onclick="clearImage()">Remove Image</button>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Category Info (Read-only) -->
                    <fieldset class="form-section" id="infoSection" style="display: none;">
                        <legend>Category Information</legend>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" id="slug" readonly value="">
                                <small class="form-hint">Auto-generated</small>
                            </div>

                            <div class="form-group">
                                <label>Products in Category</label>
                                <input type="text" id="productCount" readonly value="0">
                                <small class="form-hint">Number of products</small>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label>Created</label>
                            <input type="text" id="createdAt" readonly value="">
                        </div>
                    </fieldset>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <?php echo $isEdit ? 'Update Category' : 'Create Category'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>categories" class="btn btn-secondary btn-large">Cancel</a>
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
    <script src="<?php echo BASE_URL; ?>js/categories.js"></script>
    <script>
        const categoryId = <?php echo $categoryId ? $categoryId : 'null'; ?>;
        const isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            new CategoryForm(categoryId, isEdit);
        });

        function clearImage() {
            document.getElementById('image_url').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
        }
    </script>
</body>
</html>
