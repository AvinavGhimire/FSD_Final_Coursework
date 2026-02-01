// URL Helper for generating URLs with proper base path
function url(path) {
    const basePath = window.APP_BASE_PATH || '';
    // Ensure path starts with /
    if (!path.startsWith('/')) {
        path = '/' + path;
    }
    return basePath + path;
}

// AJAX helper function that uses proper URLs
function ajaxRequest(path, options = {}) {
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = { ...defaults, ...options };
    const fullUrl = url(path);
    
    return fetch(fullUrl, config);
}

// Navigation helper
function navigateTo(path) {
    window.location.href = url(path);
}

console.log('URL helpers loaded. Base path:', window.APP_BASE_PATH || 'none');