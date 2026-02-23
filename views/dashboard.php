<?php
/**
 * Dashboard View
 * Main landing page of PIM System
 */

require_once '../src/Utils/Auth.php';
use PIM\Utils\Auth;

Auth::requireAuth();
$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
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
                <span class="user-name"><?php echo htmlspecialchars($user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['username']); ?></span>
                <span class="user-role"><?php echo ucfirst($user['role']); ?></span>
                <button id="logoutBtn" class="btn btn-danger" title="Logout">Logout</button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <h1>Welcome back, <strong><?php echo htmlspecialchars($user['first_name'] ?: $user['username']); ?></strong>!</h1>
            <p><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></p>
            
            <!-- Dashboard Cards -->
            <div class="dashboard">
                <div class="card">
                    <h2>📦 Products</h2>
                    <p>Manage all your products in one place.</p>
                    <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary">Manage Products</a>
                </div>

                <div class="card">
                    <h2>🏷️ Categories</h2>
                    <p>Organize products by categories.</p>
                    <a href="<?php echo BASE_URL; ?>categories" class="btn btn-primary">Manage Categories</a>
                </div>

                <div class="card">
                    <h2>⚙️ Attributes</h2>
                    <p>Define product attributes and specifications.</p>
                    <a href="<?php echo BASE_URL; ?>attributes" class="btn btn-primary">Manage Attributes</a>
                </div>

                <div class="card">
                    <h2>📊 Analytics</h2>
                    <p>View insights about your product data.</p>
                    <a href="<?php echo BASE_URL; ?>analytics" class="btn btn-primary">View Analytics</a>
                </div>

                <div class="card">
                    <h2>👥 Users</h2>
                    <p>Manage user accounts and permissions.</p>
                    <a href="<?php echo BASE_URL; ?>users" class="btn btn-primary">Manage Users</a>
                </div>

                <div class="card">
                    <h2>⚡ Settings</h2>
                    <p>Configure system settings.</p>
                    <a href="<?php echo BASE_URL; ?>settings" class="btn btn-primary">Go to Settings</a>
                </div>
            </div>

            <!-- Quick Stats Section -->
            <section class="stats mt-3">
                <h2>Quick Stats</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="statsTableBody">
                        <tr>
                            <td>Total Products</td>
                            <td id="totalProducts">Loading...</td>
                            <td><span class="text-warning">Loading</span></td>
                        </tr>
                        <tr>
                            <td>Active Products</td>
                            <td id="activeProducts">Loading...</td>
                            <td><span class="text-success">✓ Active</span></td>
                        </tr>
                        <tr>
                            <td>Draft Products</td>
                            <td id="draftProducts">Loading...</td>
                            <td><span class="text-warning">Draft</span></td>
                        </tr>
                        <tr>
                            <td>System Status</td>
                            <td>Operational</td>
                            <td><span class="text-success">✓ Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-3 mb-3">
        <p>&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>js/auth.js"></script>
    <script>
        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (confirm('Are you sure you want to logout?')) {
                await AuthAPI.logout();
            }
        });

        // Load product statistics
        async function loadProductStats() {
            try {
                const response = await fetch('/api/products/stats');
                const data = await response.json();

                if (response.ok) {
                    document.getElementById('totalProducts').textContent = data.data.total;
                    document.getElementById('activeProducts').textContent = data.data.active;
                    document.getElementById('draftProducts').textContent = data.data.draft;
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadProductStats);
    </script>
</body>
</html>
