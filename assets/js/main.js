/**
 * Traveloop - Global JavaScript Helpers
 */

const App = {
    /**
     * Reusable Fetch wrapper for API calls
     * @param {string} url - The API endpoint
     * @param {string} method - HTTP method (GET, POST, etc.)
     * @param {object} data - Payload for POST/PUT
     * @returns {Promise<any>}
     */
    async apiRequest(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (method !== 'GET') {
            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
            if (csrfToken) {
                options.headers['X-CSRF-Token'] = csrfToken;
            }
        }

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Something went wrong');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Show a toast notification
     * @param {string} message 
     * @param {string} type - 'success', 'error', 'info'
     */
    showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        toast.innerHTML = `
            <span>${message}</span>
        `;

        container.appendChild(toast);

        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
};

// Expose App to window
window.App = App;
