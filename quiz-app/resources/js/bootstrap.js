import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Send cookies (session / csrf) on cross-origin requests. Vite proxies
// relative requests to the Laravel backend, so withCredentials=true ensures
// the browser sends and receives cookies for auth flows (Sanctum).
window.axios.defaults.withCredentials = true;

// Try to initialize the CSRF cookie used by Laravel Sanctum. This is a
// best-effort call — if it fails the form submission will still attempt
// to run and Laravel will return a 419 until the cookie is present.
// Using a relative path ensures the Vite proxy forwards to the backend.
if (typeof window !== 'undefined') {
	window.axios.get('/sanctum/csrf-cookie').catch(() => {
		// ignore errors here; the user can retry by reloading the page
	});
}
