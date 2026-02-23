<?php
/**
 * Categories List View
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
    <title>Categories - <?php echo APP_NAME; ?></title>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1>Categories</h1>
                <a href="<?php echo BASE_URL; ?>categories/new" class="btn btn-primary">+ Add New Category</a>
            </div>

            <!-- View Toggle -->
            <div class="view-toggle">
                <button id="listViewBtn" class="view-btn active" title="List View">
                    <span>📋 List</span>
                </button>
                <button id="treeViewBtn" class="view-btn" title="Tree View">
                    <span>🌳 Tree</span>
                </button>
            </div>

            <!-- Filters & Search -->
            <div class="filters-section">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search categories by name..."
                        class="search-input"
                    >
                </div>

                <div class="filter-controls">
                    <label class="checkbox-label">
                        <input type="checkbox" id="activeOnlyFilter">
                        Active Only
                    </label>
                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </div>

            <!-- List View -->
            <div id="listView" class="view-content active">
                <div class="table-section">
                    <table id="categoriesTable" class="categories-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllCheckbox">
                                </th>
                                <th>Category Name</th>
                                <th>Slug</th>
                                <th>Parent</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <tr class="loading-row">
                                <td colspan="8" class="text-center">Loading categories...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tree View -->
            <div id="treeView" class="view-content">
                <div class="tree-section">
                    <div id="categoryTree" class="category-tree">
                        <p class="text-center">Loading category tree...</p>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-section" id="paginationSection">
                <div class="pagination-info">
                    <span>Showing <span id="paginationStart">0</span>-<span id="paginationEnd">0</span> of <span id="paginationTotal">0</span> categories</span>
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
    <script src="<?php echo BASE_URL; ?>js/categories.js"></script>
    <script>
        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });

        // Initialize categories manager
        document.addEventListener('DOMContentLoaded', function() {
            new CategoriesManager();
        });
    </script>
</body>
</html>
