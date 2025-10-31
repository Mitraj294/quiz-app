import axios from 'axios';

// Use globalThis instead of window for broader environment compatibility
globalThis.axios = axios;

globalThis.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
