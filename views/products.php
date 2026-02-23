<?php
/**
 * Products List View
 */

require_once '../src/Utils/Auth.php';
use PIM\Utils\Auth;

Auth::requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo APP_NAME; ?></title>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1>Products</h1>
                <a href="<?php echo BASE_URL; ?>products/new" class="btn btn-primary">+ Add New Product</a>
            </div>

            <!-- Filters & Search -->
            <div class="filters-section">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search products by name, SKU, or description..."
                        class="search-input"
                    >
                </div>

                <div class="filter-controls">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                    </select>

                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </div>

            <!-- Products Table -->
            <div class="table-section">
                <table id="productsTable" class="products-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <tr class="loading-row">
                            <td colspan="8" class="text-center">Loading products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-section">
                <div class="pagination-info">
                    <span>Showing <span id="paginationStart">0</span>-<span id="paginationEnd">0</span> of <span id="paginationTotal">0</span> products</span>
                </div>
                <div class="pagination-controls">
                    <button id="prevBtn" class="btn btn-secondary" disabled>Previous</button>
                    <span id="pageInfo">Page 1</span>
                    <button id="nextBtn" class="btn btn-secondary" disabled>Next</button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div id="bulkActions" class="bulk-actions hidden">
                <span id="selectedCount">0 selected</span>
                <select id="bulkActionSelect" class="bulk-action-select">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button id="bulkActionBtn" class="btn btn-primary">Apply</button>
                <button id="clearSelectionBtn" class="btn btn-secondary">Clear Selection</button>
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
        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });

        // Initialize products page
        document.addEventListener('DOMContentLoaded', function() {
            new ProductsManager();
        });
    </script>
</body>
</html>
