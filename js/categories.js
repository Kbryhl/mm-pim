/**
 * Categories JavaScript
 */

class CategoriesManager {
    constructor() {
        this.currentPage = 1;
        this.limit = 50;
        this.selectedIds = new Set();
        this.currentView = 'list';
        this.allCategories = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCategories();
    }

    bindEvents() {
        // Search and filters
        document.getElementById('searchInput')?.addEventListener('input', () => this.loadCategories());
        document.getElementById('activeOnlyFilter')?.addEventListener('change', () => this.loadCategories());
        document.getElementById('clearFilters')?.addEventListener('click', () => this.clearFilters());

        // View toggle
        document.getElementById('listViewBtn')?.addEventListener('click', () => this.switchView('list'));
        document.getElementById('treeViewBtn')?.addEventListener('click', () => this.switchView('tree'));

        // Pagination
        document.getElementById('prevBtn')?.addEventListener('click', () => this.previousPage());
        document.getElementById('nextBtn')?.addEventListener('click', () => this.nextPage());

        // Selection
        document.getElementById('selectAllCheckbox')?.addEventListener('change', (e) => this.selectAll(e.target.checked));
        document.getElementById('bulkActionBtn')?.addEventListener('click', () => this.performBulkAction());
        document.getElementById('clearSelectionBtn')?.addEventListener('click', () => this.clearSelection());
    }

    switchView(view) {
        this.currentView = view;

        // Update button states
        document.getElementById('listViewBtn')?.classList.toggle('active', view === 'list');
        document.getElementById('treeViewBtn')?.classList.toggle('active', view === 'tree');

        // Update view visibility
        document.getElementById('listView')?.classList.toggle('active', view === 'list');
        document.getElementById('treeView')?.classList.toggle('active', view === 'tree');
        document.getElementById('paginationSection')?.style.display = view === 'list' ? 'flex' : 'none';

        if (view === 'tree') {
            this.loadCategoryTree();
        }
    }

    async loadCategories() {
        const searchQuery = document.getElementById('searchInput')?.value || '';
        const activeOnly = document.getElementById('activeOnlyFilter')?.checked || false;

        const offset = (this.currentPage - 1) * this.limit;

        const params = new URLSearchParams({
            limit: this.limit,
            offset: offset,
            ...(searchQuery && { search: searchQuery }),
            ...(activeOnly && { active: 'true' })
        });

        try {
            const response = await fetch(`/api/categories?${params}`);
            const data = await response.json();

            if (response.ok) {
                this.allCategories = data.data;
                
                // Filter by search locally for better UX
                let filtered = data.data;
                if (searchQuery) {
                    filtered = data.data.filter(cat => 
                        cat.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                        cat.slug?.toLowerCase().includes(searchQuery.toLowerCase())
                    );
                }

                this.renderCategories(filtered);
                this.updatePagination(data.data.length);
                this.clearSelection();
            } else {
                throw new Error(data.error || 'Failed to load categories');
            }
        } catch (error) {
            console.error('Load categories error:', error);
            this.showError('Failed to load categories: ' + error.message);
        }
    }

    async loadCategoryTree() {
        try {
            const response = await fetch('/api/categories/tree');
            const data = await response.json();

            if (response.ok) {
                const tree = this.buildTreeHtml(data.data);
                document.getElementById('categoryTree').innerHTML = tree;
            }
        } catch (error) {
            console.error('Load tree error:', error);
        }
    }

    buildTreeHtml(categories, level = 0) {
        if (!categories || categories.length === 0) {
            return '<p class="text-center text-muted">No categories yet</p>';
        }

        let html = '<ul class="category-tree-list">';
        
        categories.forEach(cat => {
            html += `
                <li class="category-tree-item level-${level}">
                    <div class="category-tree-content">
                        <div class="category-info">
                            <strong class="category-name">${this.escapeHtml(cat.name)}</strong>
                            <span class="category-meta">
                                <span class="status-badge status-${cat.is_active ? 'active' : 'inactive'}">
                                    ${cat.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </span>
                        </div>
                        <div class="tree-actions">
                            <a href="/categories/edit?id=${cat.id}" class="btn btn-small btn-primary">Edit</a>
                            <button class="btn btn-small btn-danger" onclick="deleteCategory(${cat.id})">Delete</button>
                        </div>
                    </div>
                    ${cat.children && cat.children.length > 0 ? this.buildTreeHtml(cat.children, level + 1) : ''}
                </li>
            `;
        });

        html += '</ul>';
        return html;
    }

    renderCategories(categories) {
        const tbody = document.getElementById('categoriesTableBody');
        
        if (categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No categories found</td></tr>';
            return;
        }

        tbody.innerHTML = categories.map(category => `
            <tr>
                <td>
                    <input type="checkbox" class="category-checkbox" value="${category.id}">
                </td>
                <td>
                    <a href="/categories/edit?id=${category.id}" class="category-link">${this.escapeHtml(category.name)}</a>
                </td>
                <td><code>${this.escapeHtml(category.slug)}</code></td>
                <td>${category.parent_id ? 'Has Parent' : 'Top-level'}</td>
                <td>${category.product_count || 0}</td>
                <td>
                    <span class="badge badge-${category.is_active ? 'active' : 'inactive'}">
                        ${category.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>${new Date(category.created_at).toLocaleDateString()}</td>
                <td>
                    <a href="/categories/edit?id=${category.id}" class="btn btn-small btn-primary">Edit</a>
                    <button class="btn btn-small btn-danger" onclick="deleteCategory(${category.id})">Delete</button>
                </td>
            </tr>
        `).join('');

        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateSelection());
        });
    }

    updatePagination(total) {
        const pages = Math.ceil(total / this.limit);
        const start = total === 0 ? 0 : (this.currentPage - 1) * this.limit + 1;
        const end = Math.min(this.currentPage * this.limit, total);

        document.getElementById('paginationStart').textContent = start;
        document.getElementById('paginationEnd').textContent = end;
        document.getElementById('paginationTotal').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${this.currentPage} of ${pages}`;

        document.getElementById('prevBtn').disabled = this.currentPage === 1;
        document.getElementById('nextBtn').disabled = this.currentPage >= pages;
    }

    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadCategories();
        }
    }

    nextPage() {
        this.currentPage++;
        this.loadCategories();
    }

    selectAll(checked) {
        this.selectedIds.clear();
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedIds.add(parseInt(checkbox.value));
            }
        });
        this.updateSelection();
    }

    updateSelection() {
        this.selectedIds.clear();
        document.querySelectorAll('.category-checkbox:checked').forEach(checkbox => {
            this.selectedIds.add(parseInt(checkbox.value));
        });

        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');

        if (this.selectedIds.size > 0) {
            bulkActions?.classList.remove('hidden');
            selectedCount.textContent = `${this.selectedIds.size} selected`;
        } else {
            bulkActions?.classList.add('hidden');
            selectAllCheckbox.checked = false;
        }
    }

    async performBulkAction() {
        const action = document.getElementById('bulkActionSelect')?.value;

        if (!action || this.selectedIds.size === 0) {
            this.showError('Please select categories and an action');
            return;
        }

        if (action === 'delete') {
            if (!confirm(`Delete ${this.selectedIds.size} category(ies) and all their subcategories?`)) return;
            
            try {
                for (const id of this.selectedIds) {
                    await fetch(`/api/categories/${id}`, { method: 'DELETE' });
                }
                this.showSuccess(`Deleted ${this.selectedIds.size} category(ies)`);
                this.loadCategories();
            } catch (error) {
                this.showError('Failed to delete categories');
            }
        }
    }

    clearSelection() {
        document.querySelectorAll('.category-checkbox').forEach(checkbox => checkbox.checked = false);
        this.selectedIds.clear();
        document.getElementById('bulkActions')?.classList.add('hidden');
    }

    clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('activeOnlyFilter').checked = false;
        this.currentPage = 1;
        this.loadCategories();
    }

    showError(message) {
        UI.notify(message, 'error');
    }

    showSuccess(message) {
        UI.notify(message, 'success');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Category Form Manager
 */
class CategoryForm {
    constructor(categoryId, isEdit) {
        this.categoryId = categoryId;
        this.isEdit = isEdit;
        this.allCategories = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadParentCategories();
        if (this.isEdit) {
            this.loadCategory();
        }
    }

    bindEvents() {
        document.getElementById('categoryForm')?.addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('image_url')?.addEventListener('change', () => this.previewImage());
    }

    async loadCategory() {
        try {
            const response = await fetch(`/api/categories/${this.categoryId}`);
            const data = await response.json();

            if (response.ok) {
                this.populateForm(data.data);
                document.getElementById('infoSection').style.display = 'block';
            } else {
                this.showError('Category not found');
                setTimeout(() => window.location.href = '/categories', 2000);
            }
        } catch (error) {
            this.showError('Failed to load category');
        }
    }

    populateForm(category) {
        document.getElementById('name').value = category.name || '';
        document.getElementById('parent_id').value = category.parent_id || '';
        document.getElementById('description').value = category.description || '';
        document.getElementById('image_url').value = category.image_url || '';
        document.getElementById('is_active').checked = category.is_active || false;
        document.getElementById('slug').value = category.slug || '';
        document.getElementById('productCount').value = category.product_count || 0;
        document.getElementById('createdAt').value = new Date(category.created_at).toLocaleString();

        if (category.image_url) {
            this.previewImage();
        }
    }

    async loadParentCategories() {
        try {
            const response = await fetch('/api/categories');
            const data = await response.json();

            if (response.ok) {
                this.allCategories = data.data;
                const parentSelect = document.getElementById('parent_id');
                
                data.data.forEach(cat => {
                    // Don't show current category as parent if editing
                    if (!this.isEdit || cat.id !== this.categoryId) {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        parentSelect.appendChild(option);
                    }
                });
            }
        } catch (error) {
            console.error('Load parent categories error:', error);
        }
    }

    previewImage() {
        const url = document.getElementById('image_url').value;
        const preview = document.getElementById('imagePreview');

        if (url) {
            document.getElementById('previewImg').src = url;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    async handleSubmit(e) {
        e.preventDefault();

        const formData = new FormData(document.getElementById('categoryForm'));
        const data = {
            name: formData.get('name'),
            description: formData.get('description'),
            parent_id: formData.get('parent_id') || null,
            image_url: formData.get('image_url') || null,
            is_active: formData.get('is_active') ? 1 : 0
        };

        try {
            const method = this.isEdit ? 'PUT' : 'POST';
            const url = this.isEdit ? `/api/categories/${this.categoryId}` : '/api/categories';
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                this.showSuccess(result.message);
                setTimeout(() => window.location.href = '/categories', 1500);
            } else {
                this.showError(result.error || 'Failed to save category');
            }
        } catch (error) {
            this.showError('Error: ' + error.message);
        }
    }

    showError(message) {
        const messageDiv = document.getElementById('formMessage');
        messageDiv.textContent = message;
        messageDiv.classList.remove('hidden');
        messageDiv.className = 'error-message';
    }

    showSuccess(message) {
        const messageDiv = document.getElementById('formMessage');
        messageDiv.textContent = message;
        messageDiv.classList.remove('hidden');
        messageDiv.className = 'success-message';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Delete category
 */
async function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category? Subcategories will be reorganized.')) return;

    try {
        const response = await fetch(`/api/categories/${categoryId}`, { method: 'DELETE' });
        const data = await response.json();

        if (response.ok) {
            UI.notify('Category deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            UI.notify(data.error || 'Failed to delete category', 'error');
        }
    } catch (error) {
        UI.notify('Error: ' + error.message, 'error');
    }
}
