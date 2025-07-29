import axios from 'axios';

window.axios = axios;

// Required by Laravel to detect AJAX requests
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Required to send session cookies (for Sanctum's web-based auth)
window.axios.defaults.withCredentials = true;
