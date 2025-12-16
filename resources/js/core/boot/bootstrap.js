import axios from 'axios';

// -----------------------------
// Axios Config
// -----------------------------
window.axios = axios;
// Required by Laravel to detect AJAX requests
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Required to send session cookies (for Sanctum's web-based auth)
window.axios.defaults.withCredentials = true;
// Initialize the CSRF token cookie used by Sanctum
await window.axios.get('/sanctum/csrf-cookie');

// IMPORTANT: Do NOT enable withXSRFToken when using Sanctum
// axios.defaults.withXSRFToken = true;

// -----------------------------
// CSRF Auto-refresh Interceptor
// -----------------------------
axios.interceptors.response.use(
    response => response,
    async error => {
        if (error.response && error.response.status === 419) {
            console.warn('CSRF token expired, refreshing...');

            try {
                // Refresh CSRF cookie (Sanctum or session-based)
                await axios.get('/sanctum/csrf-cookie');

                // Retry the original request
                return axios(error.config);
            } catch (e) {
                console.error('Failed to refresh CSRF token', e);
            }
        }

        return Promise.reject(error);
    }
);
