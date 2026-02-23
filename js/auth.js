/**
 * Authentication JavaScript
 */

const AuthAPI = {
    /**
     * Login user
     */
    login: async function(email, password) {
        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }

            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Register user
     */
    register: async function(userData) {
        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }

            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Logout user
     */
    logout: async function() {
        try {
            const response = await fetch('/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Logout failed');
            }

            // Redirect to login
            window.location.href = '/login';
        } catch (error) {
            console.error('Logout error:', error);
        }
    },

    /**
     * Get current user
     */
    getCurrentUser: async function() {
        try {
            const response = await fetch('/api/auth/me', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch user');
            }

            const data = await response.json();
            return data.user;
        } catch (error) {
            console.error('Get user error:', error);
            return null;
        }
    },

    /**
     * Check if user is authenticated
     */
    isAuthenticated: async function() {
        const user = await this.getCurrentUser();
        return user !== null;
    }
};

// Export if using modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthAPI;
}
