/**
 * Product Variants JavaScript Management
 */

class ProductVariantManager {
    constructor(productId) {
        this.productId = productId;
        this.variantTypes = [];
        this.variantCombinations = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadVariantTypes();
    }

    bindEvents() {
        // Variant type management
        document.getElementById('addVariantTypeBtn')?.addEventListener('click', () => this.showAddTypeModal());
        document.getElementById('saveVariantTypeBtn')?.addEventListener('click', () => this.saveVariantType());
        
        // Add value to type
        document.querySelector('.add-value-btn')?.addEventListener('click', () => this.showAddValueModal());
        document.getElementById('saveVariantValueBtn')?.addEventListener('click', () => this.saveVariantValue());

        // Variant combinations
        document.getElementById('bulkCreateCombinationsBtn')?.addEventListener('click', () => this.showBulkCreateModal());
        document.getElementById('confirmBulkCreateBtn')?.addEventListener('click', () => this.bulkCreateCombinations());
    }

    async loadVariantTypes() {
        try {
            const response = await fetch(`/api/product-variants/types/${this.productId}`);
            const data = await response.json();

            if (response.ok) {
                this.variantTypes = data.data;
                this.renderVariantTypes();
                this.loadVariantCombinations();
            }
        } catch (error) {
            console.error('Error loading variant types:', error);
            this.showError('Failed to load variant types');
        }
    }

    async loadVariantCombinations() {
        try {
            const response = await fetch(`/api/product-variants/combinations/${this.productId}`);
            const data = await response.json();

            if (response.ok) {
                this.variantCombinations = data.data;
                this.renderVariantCombinations();
            }
        } catch (error) {
            console.error('Error loading combinations:', error);
        }
    }

    renderVariantTypes() {
        const container = document.getElementById('variantTypesContainer');
        if (!container) return;

        if (this.variantTypes.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No variant types yet. Add one to get started.</p>';
            return;
        }

        container.innerHTML = this.variantTypes.map(type => `
            <div class="variant-type-card">
                <div class="type-header">
                    <h4>${this.escapeHtml(type.name)}</h4>
                    <span class="badge badge-${type.type}">${type.type}</span>
                    ${type.is_required ? '<span class="badge badge-required">Required</span>' : ''}
                </div>
                <div class="type-values">
                    <div class="values-header">
                        <h5>Values</h5>
                        <button class="btn btn-small btn-secondary" onclick="variantManager.showAddValueModal(${type.id})">+ Add Value</button>
                    </div>
                    <div class="values-list">
                        ${type.values && type.values.length > 0 ? 
                            type.values.map(val => `
                                <div class="value-item">
                                    ${type.type === 'color' ? `<div class="color-preview" style="background-color: ${val.color_code || '#ccc'}"></div>` : ''}
                                    <span>${this.escapeHtml(val.display_value || val.value)}</span>
                                    <button class="btn btn-tiny btn-danger" onclick="variantManager.deleteValue(${val.id})">×</button>
                                </div>
                            `).join('')
                            : '<p class="text-muted">No values added yet</p>'
                        }
                    </div>
                </div>
                <div class="type-actions">
                    <button class="btn btn-secondary btn-small" onclick="variantManager.editType(${type.id})">Edit</button>
                    <button class="btn btn-danger btn-small" onclick="variantManager.deleteType(${type.id})">Delete</button>
                </div>
            </div>
        `).join('');
    }

    renderVariantCombinations() {
        const container = document.getElementById('variantCombinationsContainer');
        if (!container) return;

        if (this.variantCombinations.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No variant combinations yet.</p>';
            return;
        }

        container.innerHTML = `
            <table class="combinations-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${this.variantCombinations.map(combo => `
                        <tr>
                            <td>${this.escapeHtml(combo.variant_name || '-')}</td>
                            <td><code>${this.escapeHtml(combo.variant_sku)}</code></td>
                            <td>${combo.price ? '$' + combo.price.toFixed(2) : '-'}</td>
                            <td>${combo.stock_quantity}</td>
                            <td>
                                <span class="status-badge status-${combo.is_active ? 'active' : 'inactive'}">
                                    ${combo.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-small btn-primary" onclick="variantManager.editCombination(${combo.id})">Edit</button>
                                <button class="btn btn-small btn-danger" onclick="variantManager.deleteCombination(${combo.id})">Delete</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    showAddTypeModal() {
        const modal = this.createModal('Add Variant Type', `
            <div class="form-group">
                <label>Type Name</label>
                <input type="text" id="typeNameInput" placeholder="e.g., Size, Color, Material" required>
            </div>
            <div class="form-group">
                <label>Type Kind</label>
                <select id="typeKindInput">
                    <option value="dropdown">Dropdown</option>
                    <option value="color">Color Picker</option>
                    <option value="text">Text</option>
                    <option value="multiselect">Multiple Selection</option>
                </select>
            </div>
            <div class="form-group checkbox">
                <input type="checkbox" id="typeRequiredInput">
                <label for="typeRequiredInput">Required</label>
            </div>
        `);

        document.getElementById('modalConfirmBtn').textContent = 'Create Type';
        document.getElementById('modalConfirmBtn').onclick = () => this.saveVariantType(modal);
    }

    async saveVariantType(modal) {
        const name = document.getElementById('typeNameInput')?.value;
        const type = document.getElementById('typeKindInput')?.value;
        const isRequired = document.getElementById('typeRequiredInput')?.checked;

        if (!name) {
            alert('Please enter a type name');
            return;
        }

        try {
            const response = await fetch('/api/product-variants/types', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: this.productId,
                    name: name,
                    type: type,
                    is_required: isRequired ? 1 : 0
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showSuccess('Variant type created');
                modal?.remove();
                this.loadVariantTypes();
            } else {
                this.showError(data.error || 'Failed to create type');
            }
        } catch (error) {
            this.showError('Error creating variant type');
        }
    }

    showAddValueModal(variantTypeId) {
        const type = this.variantTypes.find(t => t.id === variantTypeId);
        if (!type) return;

        const isColor = type.type === 'color';

        const modal = this.createModal(`Add Value to ${this.escapeHtml(type.name)}`, `
            <div class="form-group">
                <label>Value</label>
                <input type="text" id="valueInput" placeholder="e.g., XL, Red, Cotton" required>
            </div>
            <div class="form-group">
                <label>Display Name</label>
                <input type="text" id="displayValueInput" placeholder="Leave blank to use value as display name">
            </div>
            ${isColor ? `
                <div class="form-group">
                    <label>Color Code</label>
                    <input type="color" id="colorCodeInput">
                </div>
            ` : ''}
        `);

        document.getElementById('modalConfirmBtn').textContent = 'Add Value';
        document.getElementById('modalConfirmBtn').onclick = () => this.saveVariantValue(variantTypeId, modal);
    }

    async saveVariantValue(variantTypeId, modal) {
        const value = document.getElementById('valueInput')?.value;
        const displayValue = document.getElementById('displayValueInput')?.value || value;
        const colorCode = document.getElementById('colorCodeInput')?.value || null;

        if (!value) {
            alert('Please enter a value');
            return;
        }

        try {
            const response = await fetch('/api/product-variants/values', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    variant_type_id: variantTypeId,
                    value: value,
                    display_value: displayValue,
                    color_code: colorCode
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showSuccess('Value added');
                modal?.remove();
                this.loadVariantTypes();
            } else {
                this.showError(data.error || 'Failed to add value');
            }
        } catch (error) {
            this.showError('Error adding value');
        }
    }

    async deleteType(typeId) {
        if (!confirm('Delete this variant type? All associated combinations will be deleted.')) return;

        try {
            const response = await fetch(`/api/product-variants/types/${typeId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                this.showSuccess('Variant type deleted');
                this.loadVariantTypes();
            } else {
                this.showError('Failed to delete type');
            }
        } catch (error) {
            this.showError('Error deleting type');
        }
    }

    async deleteValue(valueId) {
        if (!confirm('Delete this value?')) return;

        try {
            const response = await fetch(`/api/product-variants/values/${valueId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                this.showSuccess('Value deleted');
                this.loadVariantTypes();
            } else {
                this.showError('Failed to delete value');
            }
        } catch (error) {
            this.showError('Error deleting value');
        }
    }

    showBulkCreateModal() {
        if (this.variantTypes.length === 0) {
            alert('Please create variant types first');
            return;
        }

        const typeCheckboxes = this.variantTypes.map(type => `
            <div class="form-group checkbox">
                <input type="checkbox" id="type${type.id}" value="${type.id}">
                <label for="type${type.id}">${this.escapeHtml(type.name)}</label>
            </div>
        `).join('');

        const modal = this.createModal('Create All Combinations', `
            <p>Select variant types to generate all possible combinations:</p>
            <div class="types-selection">
                ${typeCheckboxes}
            </div>
        `);

        document.getElementById('modalConfirmBtn').textContent = 'Generate Combinations';
        document.getElementById('modalConfirmBtn').onclick = () => this.bulkCreateCombinations(modal);
    }

    async bulkCreateCombinations(modal) {
        const checkedTypes = Array.from(
            document.querySelectorAll('[id^="type"]:checked')
        ).map(cb => parseInt(cb.value));

        if (checkedTypes.length === 0) {
            alert('Please select at least one variant type');
            return;
        }

        const count = this.calculateCombinationCount(checkedTypes);
        if (!confirm(`This will create ${count} variant combinations. Continue?`)) return;

        try {
            const response = await fetch('/api/product-variants/bulk-create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: this.productId,
                    variant_type_ids: checkedTypes,
                    base_data: {}
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showSuccess(`Created ${data.data.created_count} variant combinations`);
                modal?.remove();
                this.loadVariantCombinations();
            } else {
                this.showError(data.error || 'Failed to create combinations');
            }
        } catch (error) {
            this.showError('Error creating combinations');
        }
    }

    calculateCombinationCount(typeIds) {
        let count = 1;
        for (const typeId of typeIds) {
            const type = this.variantTypes.find(t => t.id === typeId);
            if (type && type.values) {
                count *= type.values.length;
            }
        }
        return count;
    }

    async editCombination(comboId) {
        const combo = this.variantCombinations.find(c => c.id === comboId);
        if (!combo) return;

        const modal = this.createModal('Edit Variant Combination', `
            <div class="form-group">
                <label>SKU</label>
                <input type="text" id="comboSkuInput" value="${this.escapeHtml(combo.variant_sku)}" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" id="comboPriceInput" step="0.01" value="${combo.price || ''}">
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" id="comboStockInput" value="${combo.stock_quantity}">
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" id="comboImageInput" value="${combo.image_url || ''}">
            </div>
            <div class="form-group checkbox">
                <input type="checkbox" id="comboActiveInput" ${combo.is_active ? 'checked' : ''}>
                <label for="comboActiveInput">Active</label>
            </div>
        `);

        document.getElementById('modalConfirmBtn').textContent = 'Update Combination';
        document.getElementById('modalConfirmBtn').onclick = async () => {
            const sku = document.getElementById('comboSkuInput').value;
            const price = document.getElementById('comboPriceInput').value;
            const stock = document.getElementById('comboStockInput').value;
            const image = document.getElementById('comboImageInput').value;
            const isActive = document.getElementById('comboActiveInput').checked;

            try {
                const response = await fetch(`/api/product-variants/combinations/${comboId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        variant_sku: sku,
                        price: price ? parseFloat(price) : null,
                        stock_quantity: parseInt(stock),
                        image_url: image || null,
                        is_active: isActive ? 1 : 0
                    })
                });

                if (response.ok) {
                    this.showSuccess('Combination updated');
                    modal?.remove();
                    this.loadVariantCombinations();
                } else {
                    this.showError('Failed to update');
                }
            } catch (error) {
                this.showError('Error updating combination');
            }
        };
    }

    async deleteCombination(comboId) {
        if (!confirm('Delete this variant combination?')) return;

        try {
            const response = await fetch(`/api/product-variants/combinations/${comboId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                this.showSuccess('Combination deleted');
                this.loadVariantCombinations();
            } else {
                this.showError('Failed to delete');
            }
        } catch (error) {
            this.showError('Error deleting combination');
        }
    }

    editType(typeId) {
        alert('Edit variant type - not yet implemented');
        // TODO: Implement edit functionality
    }

    createModal(title, content) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
                <div class="modal-footer">
                    <button id="modalConfirmBtn" class="btn btn-primary">Confirm</button>
                    <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modal;
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
