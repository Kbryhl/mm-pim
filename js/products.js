/**
 * Products JavaScript
 */

class ProductsManager {
    constructor() {
        this.currentPage = 1;
        this.limit = 50;
        this.selectedIds = new Set();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadProducts();
        this.loadCategories();
    }

    bindEvents() {
        // Search and filters
        document.getElementById('searchInput')?.addEventListener('input', () => this.loadProducts());
        document.getElementById('statusFilter')?.addEventListener('change', () => this.loadProducts());
        document.getElementById('categoryFilter')?.addEventListener('change', () => this.loadProducts());
        document.getElementById('clearFilters')?.addEventListener('click', () => this.clearFilters());

        // Pagination
        document.getElementById('prevBtn')?.addEventListener('click', () => this.previousPage());
        document.getElementById('nextBtn')?.addEventListener('click', () => this.nextPage());

        // Selection
        document.getElementById('selectAllCheckbox')?.addEventListener('change', (e) => this.selectAll(e.target.checked));
        document.getElementById('bulkActionBtn')?.addEventListener('click', () => this.performBulkAction());
        document.getElementById('clearSelectionBtn')?.addEventListener('click', () => this.clearSelection());
    }

    async loadProducts() {
        const searchQuery = document.getElementById('searchInput')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';

        const offset = (this.currentPage - 1) * this.limit;

        const params = new URLSearchParams({
            limit: this.limit,
            offset: offset,
            ...(searchQuery && { search: searchQuery }),
            ...(statusFilter && { status: statusFilter }),
            ...(categoryFilter && { category_id: categoryFilter })
        });

        try {
            const response = await fetch(`/api/products?${params}`);
            const data = await response.json();

            if (response.ok) {
                this.renderProducts(data.data);
                this.updatePagination(data.pagination);
                this.clearSelection();
            } else {
                throw new Error(data.error || 'Failed to load products');
            }
        } catch (error) {
            console.error('Load products error:', error);
            this.showError('Failed to load products: ' + error.message);
        }
    }

    renderProducts(products) {
        const tbody = document.getElementById('productsTableBody');
        
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No products found</td></tr>';
            return;
        }

        tbody.innerHTML = products.map(product => `
            <tr>
                <td>
                    <input type="checkbox" class="product-checkbox" value="${product.id}">
                </td>
                <td>
                    <a href="/products/edit?id=${product.id}" class="product-link">${this.escapeHtml(product.name)}</a>
                </td>
                <td>${this.escapeHtml(product.sku)}</td>
                <td>${product.category_id ? 'Category' : '-'}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>
                    <span class="badge badge-${product.status}">${this.capitalizeFirst(product.status)}</span>
                </td>
                <td>${new Date(product.created_at).toLocaleDateString()}</td>
                <td>
                    <a href="/products/edit?id=${product.id}" class="btn btn-small btn-primary">Edit</a>
                    <button class="btn btn-small btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                </td>
            </tr>
        `).join('');

        // Add checkbox event listeners
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateSelection());
        });
    }

    updatePagination(pagination) {
        const { limit, offset, total, pages } = pagination;

        document.getElementById('paginationStart').textContent = total === 0 ? 0 : offset + 1;
        document.getElementById('paginationEnd').textContent = Math.min(offset + limit, total);
        document.getElementById('paginationTotal').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${this.currentPage} of ${pages}`;

        document.getElementById('prevBtn').disabled = this.currentPage === 1;
        document.getElementById('nextBtn').disabled = this.currentPage >= pages;
    }

    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadProducts();
        }
    }

    nextPage() {
        this.currentPage++;
        this.loadProducts();
    }

    async loadCategories() {
        try {
            const response = await fetch('/api/categories');
            const data = await response.json();

            if (response.ok) {
                const select = document.getElementById('categoryFilter');
                if (select) {
                    select.innerHTML += data.data.map(cat => 
                        `<option value="${cat.id}">${this.escapeHtml(cat.name)}</option>`
                    ).join('');
                }
            }
        } catch (error) {
            console.error('Load categories error:', error);
        }
    }

    selectAll(checked) {
        this.selectedIds.clear();
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedIds.add(parseInt(checkbox.value));
            }
        });
        this.updateSelection();
    }

    updateSelection() {
        this.selectedIds.clear();
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            this.selectedIds.add(parseInt(checkbox.value));
        });

        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');

        if (this.selectedIds.size > 0) {
            bulkActions?.classList.remove('hidden');
            selectedCount.textContent = `${this.selectedIds.size} selected`;
            selectAllCheckbox.indeterminate = this.selectedIds.size > 0 && this.selectedIds.size < document.querySelectorAll('.product-checkbox').length;
        } else {
            bulkActions?.classList.add('hidden');
            selectAllCheckbox.checked = false;
        }
    }

    async performBulkAction() {
        const action = document.getElementById('bulkActionSelect')?.value;

        if (!action || this.selectedIds.size === 0) {
            this.showError('Please select products and an action');
            return;
        }

        const actionMap = {
            'activate': 'active',
            'deactivate': 'inactive',
            'delete': null
        };

        const status = actionMap[action];

        if (action === 'delete') {
            if (!confirm(`Delete ${this.selectedIds.size} product(s)?`)) return;
            
            try {
                for (const id of this.selectedIds) {
                    await fetch(`/api/products/${id}`, { method: 'DELETE' });
                }
                this.showSuccess(`Deleted ${this.selectedIds.size} product(s)`);
                this.loadProducts();
            } catch (error) {
                this.showError('Failed to delete products');
            }
        } else if (status) {
            try {
                const response = await fetch('/api/products/bulk/status', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ids: Array.from(this.selectedIds),
                        status: status
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    this.showSuccess(data.message);
                    this.loadProducts();
                } else {
                    throw new Error(data.error);
                }
            } catch (error) {
                this.showError('Failed to update products');
            }
        }
    }

    clearSelection() {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => checkbox.checked = false);
        this.selectedIds.clear();
        document.getElementById('bulkActions')?.classList.add('hidden');
    }

    clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('categoryFilter').value = '';
        this.currentPage = 1;
        this.loadProducts();
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

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

/**
 * Product Form Manager
 */
class ProductForm {
    constructor(productId, isEdit) {
        this.productId = productId;
        this.isEdit = isEdit;
        this.init();
    }

    init() {
        this.bindEvents();
        if (this.isEdit) {
            this.loadProduct();
        } else {
            this.loadAttributesAndCategories();
        }
    }

    bindEvents() {
        document.getElementById('productForm')?.addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('image_url')?.addEventListener('change', () => this.previewImage());
    }

    async loadProduct() {
        try {
            const response = await fetch(`/api/products/${this.productId}`);
            const data = await response.json();

            if (response.ok) {
                this.populateForm(data.data);
                this.loadAttributesAndCategories();
            } else {
                this.showError('Product not found');
                setTimeout(() => window.location.href = '/products', 2000);
            }
        } catch (error) {
            this.showError('Failed to load product');
        }
    }

    populateForm(product) {
        document.getElementById('name').value = product.name || '';
        document.getElementById('sku').value = product.sku || '';
        document.getElementById('description').value = product.description || '';
        document.getElementById('price').value = product.price || '';
        document.getElementById('category_id').value = product.category_id || '';
        document.getElementById('status').value = product.status || 'draft';
        document.getElementById('image_url').value = product.image_url || '';

        if (product.image_url) {
            this.previewImage();
        }
    }

    async loadAttributesAndCategories() {
        try {
            const [catResponse, attrResponse] = await Promise.all([
                fetch('/api/categories'),
                fetch('/api/attributes')
            ]);

            const categories = await catResponse.json();
            const attributes = await attrResponse.json();

            if (catResponse.ok) {
                const categorySelect = document.getElementById('category_id');
                categories.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);
                });
            }

            if (attrResponse.ok) {
                this.renderAttributes(attributes.data);
            }
        } catch (error) {
            console.error('Load data error:', error);
        }
    }

    renderAttributes(attributes) {
        const container = document.getElementById('attributesContainer');
        if (!attributes || attributes.length === 0) {
            container.innerHTML = '<p class="text-muted">No attributes available</p>';
            return;
        }

        container.innerHTML = attributes.map(attr => `
            <div class="form-group">
                <label for="attr_${attr.id}">${this.escapeHtml(attr.name)}</label>
                ${this.renderAttributeInput(attr)}
            </div>
        `).join('');
    }

    renderAttributeInput(attr) {
        const id = `attr_${attr.id}`;
        switch (attr.type) {
            case 'text':
                return `<input type="text" id="${id}" name="attributes[${attr.id}]">`;
            case 'number':
                return `<input type="number" id="${id}" name="attributes[${attr.id}]" step="0.01">`;
            case 'select':
                return `<select id="${id}" name="attributes[${attr.id}]"><option value="">Select</option></select>`;
            case 'date':
                return `<input type="date" id="${id}" name="attributes[${attr.id}]">`;
            case 'boolean':
                return `<input type="checkbox" id="${id}" name="attributes[${attr.id}]">`;
            default:
                return `<input type="text" id="${id}" name="attributes[${attr.id}]">`;
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

        const formData = new FormData(document.getElementById('productForm'));
        const data = {
            name: formData.get('name'),
            sku: formData.get('sku'),
            description: formData.get('description'),
            price: parseFloat(formData.get('price') || 0),
            category_id: formData.get('category_id') || null,
            status: formData.get('status'),
            image_url: formData.get('image_url')
        };

        try {
            const method = this.isEdit ? 'PUT' : 'POST';
            const url = this.isEdit ? `/api/products/${this.productId}` : '/api/products';
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                this.showSuccess(result.message);
                setTimeout(() => window.location.href = '/products', 1500);
            } else {
                this.showError(result.error || 'Failed to save product');
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
 * Delete product
 */
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        const response = await fetch(`/api/products/${productId}`, { method: 'DELETE' });
        const data = await response.json();

        if (response.ok) {
            UI.notify('Product deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            UI.notify(data.error || 'Failed to delete product', 'error');
        }
    } catch (error) {
        UI.notify('Error: ' + error.message, 'error');
    }
}
