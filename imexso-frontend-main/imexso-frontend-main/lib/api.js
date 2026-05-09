async function getCsrfCookie() {
    await fetch('/sanctum/csrf-cookie', {
        credentials: 'include',
    });
}

function getCookie(name) {
    if (typeof document === 'undefined') return null;
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

export async function apiRequest(endpoint, options = {}) {
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
    };

    const xsrfToken = getCookie('XSRF-TOKEN');
    if (xsrfToken) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
    }

    const response = await fetch(endpoint, {
        ...options,
        headers,
        credentials: 'include',
    });

    if (response.status === 419) {
        await getCsrfCookie();
        return apiRequest(endpoint, options);
    }

    return response;
}

export async function apiGet(endpoint) {
    return apiRequest(endpoint, { method: 'GET' });
}

export async function apiPost(endpoint, data) {
    return apiRequest(endpoint, {
        method: 'POST',
        body: JSON.stringify(data),
    });
}

export async function apiDelete(endpoint) {
    return apiRequest(endpoint, { method: 'DELETE' });
}

export async function apiPostFormData(endpoint, formData) {
    const headers = {
        'Accept': 'application/json',
    };

    const xsrfToken = getCookie('XSRF-TOKEN');
    if (xsrfToken) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
    }

    const response = await fetch(endpoint, {
        method: 'POST',
        headers,
        body: formData,
        credentials: 'include',
    });

    if (response.status === 419) {
        await getCsrfCookie();
        return apiPostFormData(endpoint, formData);
    }

    return response;
}

export { getCsrfCookie };
