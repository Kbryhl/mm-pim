/**
 * Product Pricing Tiers JavaScript Management
 */

class PricingTierManager {
    constructor(productId, basePrice = null) {
        this.productId = productId;
        this.basePrice = basePrice;
        this.pricingTiers = [];
        this.unitTypes = {};
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadUnitTypes();
        this.loadPricingTiers();
    }

    bindEvents() {
        document.getElementById('addTierBtn')?.addEventListener('click', () => this.showAddTierModal());
        document.getElementById('saveTierBtn')?.addEventListener('click', () => this.saveTier());
        document.getElementById('bulkImportBtn')?.addEventListener('click', () => this.showBulkImportModal());
    }

    async loadUnitTypes() {
        try {
            const response = await fetch('/api/product-pricing/units');
            const data = await response.json();

            if (response.ok) {
                this.unitTypes = data.data;
            }
        } catch (error) {
            console.error('Error loading unit types:', error);
        }
    }

    async loadPricingTiers() {
        try {
            const response = await fetch(`/api/product-pricing/product/${this.productId}`);
            const data = await response.json();

            if (response.ok) {
                this.pricingTiers = data.data;
                this.renderPricingTiers();
                this.updatePricingSummary(data.summary);
            }
        } catch (error) {
            console.error('Error loading pricing tiers:', error);
            this.showError('Failed to load pricing tiers');
        }
    }

    renderPricingTiers() {
        const container = document.getElementById('pricingTiersContainer');
        if (!container) return;

        if (this.pricingTiers.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>📊 No pricing tiers yet</p>
                    <p class="text-muted">Create pricing tiers to offer bulk discounts or different unit-based pricing</p>
                </div>
            `;
            return;
        }

        const rows = this.pricingTiers.map((tier, index) => {
            const unitLabel = this.unitTypes[tier.unit_type] || tier.unit_type;
            const discount = tier.discount_percent ? `${tier.discount_percent}%` : '-';
            const margin = this.calculateMargin(tier.price, tier.cost_price);
            const status = tier.is_active ? '✓ Active' : '✗ Inactive';
            
            return `
                <tr class="tier-row ${tier.is_active ? '' : 'inactive'}">
                    <td><strong>${index + 1}</strong></td>
                    <td>
                        <span class="unit-badge">${this.escapeHtml(unitLabel)}</span>
                    </td>
                    <td>
                        <span class="quantity-range">
                            ${tier.min_quantity}
                            ${tier.max_quantity ? ` to ${tier.max_quantity}` : '+'}
                        </span>
                    </td>
                    <td>
                        <span class="price-display">$${parseFloat(tier.price).toFixed(2)}</span>
                        ${this.basePrice ? `<small class="discount-label">${discount}</small>` : ''}
                    </td>
                    <td>
                        ${tier.cost_price ? `$${parseFloat(tier.cost_price).toFixed(2)}` : '-'}
                    </td>
                    <td>${margin > 0 ? `<span class="margin-positive">${margin}%</span>` : '-'}</td>
                    <td><span class="status-badge">${status}</span></td>
                    <td>
                        <button class="btn btn-small btn-primary" onclick="pricingManager.editTier(${tier.id})">Edit</button>
                        <button class="btn btn-small btn-danger" onclick="pricingManager.deleteTier(${tier.id})">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');

        container.innerHTML = `
            <table class="pricing-tiers-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Unit</th>
                        <th>Quantity Range</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Margin</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        `;
    }

    updatePricingSummary(summary) {
        const summaryDiv = document.getElementById('pricingSummary');
        if (!summaryDiv || !summary) return;

        if (summary.has_tiers) {
            summaryDiv.innerHTML = `
                <div class="summary-grid">
                    <div class="summary-card">
                        <label>Base Price</label>
                        <span class="summary-value">${summary.base_price ? '$' + summary.base_price.toFixed(2) : 'N/A'}</span>
                    </div>
                    <div class="summary-card">
                        <label>Min Price (Bulk)</label>
                        <span class="summary-value price-low">$${summary.min_price.toFixed(2)}</span>
                    </div>
                    <div class="summary-card">
                        <label>Max Price</label>
                        <span class="summary-value price-high">$${summary.max_price.toFixed(2)}</span>
                    </div>
                    <div class="summary-card">
                        <label>Pricing Tiers</label>
                        <span class="summary-value">${summary.tier_count}</span>
                    </div>
                </div>
            `;
        }
    }

    showAddTierModal(tierId = null) {
        const isEdit = tierId !== null;
        const title = isEdit ? 'Edit Pricing Tier' : 'Add Pricing Tier';
        
        let content = `
            <div class="form-group">
                <label>Unit Type</label>
                <select id="unitTypeInput">
                    ${Object.entries(this.unitTypes).map(([key, label]) => 
                        `<option value="${key}">${label}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Minimum Quantity *</label>
                    <input type="number" id="minQuantityInput" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Maximum Quantity</label>
                    <input type="number" id="maxQuantityInput" step="0.01" placeholder="Leave empty for unlimited">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" id="priceInput" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Cost Price</label>
                    <input type="number" id="costPriceInput" step="0.01">
                </div>
            </div>
            <div class="form-group">
                <label>Discount %</label>
                <input type="number" id="discountInput" step="0.01" min="0" max="100">
            </div>
            <div class="form-group checkbox">
                <input type="checkbox" id="isActiveInput" checked>
                <label for="isActiveInput">Active</label>
            </div>
        `;

        const modal = this.createModal(title, content);
        
        // Pre-fill if editing
        if (isEdit) {
            const tier = this.pricingTiers.find(t => t.id === tierId);
            if (tier) {
                document.getElementById('unitTypeInput').value = tier.unit_type;
                document.getElementById('minQuantityInput').value = tier.min_quantity;
                document.getElementById('maxQuantityInput').value = tier.max_quantity || '';
                document.getElementById('priceInput').value = tier.price;
                document.getElementById('costPriceInput').value = tier.cost_price || '';
                document.getElementById('discountInput').value = tier.discount_percent || '';
                document.getElementById('isActiveInput').checked = tier.is_active;
            }
        }

        document.getElementById('modalConfirmBtn').textContent = isEdit ? 'Update Tier' : 'Create Tier';
        document.getElementById('modalConfirmBtn').onclick = () => this.saveTier(tierId, modal);
    }

    async saveTier(tierId = null, modal = null) {
        const unitType = document.getElementById('unitTypeInput').value;
        const minQuantity = parseFloat(document.getElementById('minQuantityInput').value);
        const maxQuantity = document.getElementById('maxQuantityInput').value ? 
            parseFloat(document.getElementById('maxQuantityInput').value) : null;
        const price = parseFloat(document.getElementById('priceInput').value);
        const costPrice = document.getElementById('costPriceInput').value ? 
            parseFloat(document.getElementById('costPriceInput').value) : null;
        const discountPercent = document.getElementById('discountInput').value ? 
            parseFloat(document.getElementById('discountInput').value) : null;
        const isActive = document.getElementById('isActiveInput').checked ? 1 : 0;

        // Client-side validation
        if (!minQuantity || !price) {
            alert('Please fill in all required fields');
            return;
        }

        if (maxQuantity && maxQuantity <= minQuantity) {
            alert('Maximum quantity must be greater than minimum quantity');
            return;
        }

        const data = {
            product_id: this.productId,
            unit_type: unitType,
            min_quantity: minQuantity,
            max_quantity: maxQuantity,
            price: price,
            cost_price: costPrice,
            discount_percent: discountPercent,
            is_active: isActive
        };

        try {
            const method = tierId ? 'PUT' : 'POST';
            const url = tierId ? `/api/product-pricing/${tierId}` : '/api/product-pricing';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                this.showSuccess(result.message);
                modal?.remove();
                this.loadPricingTiers();
            } else {
                this.showError(result.error || 'Failed to save tier');
            }
        } catch (error) {
            this.showError('Error saving tier: ' + error.message);
        }
    }

    async editTier(tierId) {
        this.showAddTierModal(tierId);
    }

    async deleteTier(tierId) {
        if (!confirm('Delete this pricing tier?')) return;

        try {
            const response = await fetch(`/api/product-pricing/${tierId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                this.showSuccess('Pricing tier deleted');
                this.loadPricingTiers();
            } else {
                this.showError('Failed to delete tier');
            }
        } catch (error) {
            this.showError('Error deleting tier');
        }
    }

    showBulkImportModal() {
        const modal = this.createModal('Import Pricing Tiers', `
            <div class="form-group">
                <label>JSON Data</label>
                <textarea id="bulkJsonInput" placeholder='[{"min_quantity": 1, "max_quantity": 10, "price": 29.99}, ...]' rows="8"></textarea>
                <small class="form-hint">Enter an array of tier objects with min_quantity, max_quantity, price, etc.</small>
            </div>
        `);

        document.getElementById('modalConfirmBtn').textContent = 'Import Tiers';
        document.getElementById('modalConfirmBtn').onclick = () => this.bulkImportTiers(modal);
    }

    async bulkImportTiers(modal) {
        const jsonText = document.getElementById('bulkJsonInput').value;

        if (!jsonText.trim()) {
            alert('Please enter JSON data');
            return;
        }

        try {
            const tiers = JSON.parse(jsonText);

            if (!Array.isArray(tiers)) {
                alert('JSON must be an array of tier objects');
                return;
            }

            const response = await fetch('/api/product-pricing/bulk', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: this.productId,
                    tiers: tiers
                })
            });

            const result = await response.json();

            if (response.ok) {
                this.showSuccess(`Imported ${result.data.created_count} pricing tiers`);
                modal?.remove();
                this.loadPricingTiers();
            } else {
                this.showError(result.error || 'Failed to import tiers');
            }
        } catch (error) {
            alert('Invalid JSON format: ' + error.message);
        }
    }

    calculateMargin(price, costPrice) {
        if (!costPrice || costPrice <= 0) return 0;
        return Math.round(((price - costPrice) / costPrice) * 100);
    }

    calculatePrice(quantity) {
        if (!this.pricingTiers.length) return this.basePrice;

        // Find applicable tier
        const applicableTier = this.pricingTiers.find(tier => 
            tier.is_active && 
            quantity >= tier.min_quantity && 
            (!tier.max_quantity || quantity <= tier.max_quantity)
        );

        return applicableTier ? applicableTier.price : this.basePrice;
    }

    createModal(title, content) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content pricing-modal">
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
