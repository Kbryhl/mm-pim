/**
 * PIM System - Main JavaScript
 */

console.log('PIM System initialized');

// Utility Functions
const API = {
    baseUrl: window.location.origin,
    
    /**
     * Make API request
     */
    request: async function(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseUrl}/api${endpoint}`, options);
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    get: function(endpoint) {
        return this.request(endpoint, 'GET');
    },

    /**
     * POST request
     */
    post: function(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    },

    /**
     * PUT request
     */
    put: function(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    },

    /**
     * DELETE request
     */
    delete: function(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
};

// UI Helpers
const UI = {
    /**
     * Show notification
     */
    notify: function(message, type = 'info') {
        const notificationId = 'notification-' + Date.now();
        const notificationDiv = document.createElement('div');
        notificationDiv.id = notificationId;
        notificationDiv.className = `notification notification-${type}`;
        notificationDiv.innerHTML = `<strong>${type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Info'}:</strong> ${this.escapeHtml(message)}`;
        
        document.body.appendChild(notificationDiv);
        
        // Auto-remove after 4 seconds
        setTimeout(() => {
            const elem = document.getElementById(notificationId);
            if (elem) {
                elem.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => elem.remove(), 300);
            }
        }, 4000);
    },

    /**
     * Show loading state
     */
    loading: function(element, show = true) {
        if (show) {
            element.classList.add('loading');
            element.disabled = true;
        } else {
            element.classList.remove('loading');
            element.disabled = false;
        }
    },

    /**
     * Format date
     */
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    /**
     * Escape HTML entities
     */
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Show modal/dialog
     */
    showModal: function(title, message) {
        return confirm(`${title}\n\n${message}`);
    },

    /**
     * Format currency
     */
    formatCurrency: function(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
};

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Initialize event listeners
    initializeEventListeners();
});

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Add event listeners here as features are built
    console.log('Event listeners initialized');
}
