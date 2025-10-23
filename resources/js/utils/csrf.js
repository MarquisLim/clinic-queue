// Утилита для работы с CSRF токеном
export function getCsrfToken() {
    // Пробуем получить токен из разных источников
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const windowToken = window.Laravel?.csrfToken;
    
    return metaToken || inputToken || windowToken || '';
}

export function getCsrfHeaders() {
    const token = getCsrfToken();
    
    if (!token) {
        console.warn('CSRF токен не найден');
        return {};
    }
    
    return {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
}

export function makeAuthenticatedRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            ...getCsrfHeaders()
        }
    };
    
    return fetch(url, {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    });
}
