"use client";

import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { apiGet, apiPost, getCsrfCookie } from '@/lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchUser = useCallback(async () => {
        try {
            const res = await apiGet('/api/user');
            if (res.ok) {
                const data = await res.json();
                setUser(data);
            } else {
                setUser(null);
            }
        } catch {
            setUser(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchUser();
    }, [fetchUser]);

    const login = async (email, password) => {
        setError(null);
        await getCsrfCookie();
        const res = await apiPost('/_auth/login', { email, password });
        if (res.ok) {
            await fetchUser();
            return { success: true };
        }
        const data = await res.json().catch(() => ({}));
        setError(data.message || 'Login failed');
        return { success: false, error: data.message || 'Login failed', errors: data.errors };
    };

    const register = async (data) => {
        setError(null);
        await getCsrfCookie();
        const res = await apiPost('/_auth/register', data);
        if (res.ok || res.status === 201) {
            await fetchUser();
            return { success: true };
        }
        const responseData = await res.json().catch(() => ({}));
        setError(responseData.message || 'Registration failed');
        return { success: false, error: responseData.message, errors: responseData.errors };
    };

    const validateVat = async (countryCode, vatNumber) => {
        const res = await apiPost('/api/vat/validate', {
            country_code: countryCode,
            vat_number: vatNumber,
        });
        if (res.ok) {
            return await res.json();
        }
        return { valid: false };
    };

    const logout = async () => {
        await apiPost('/_auth/logout', {});
        setUser(null);
    };

    const forgotPassword = async (email) => {
        await getCsrfCookie();
        const res = await apiPost('/_auth/forgot-password', { email });
        return res.ok;
    };

    const resetPassword = async (data) => {
        await getCsrfCookie();
        const res = await apiPost('/_auth/reset-password', data);
        return res.ok;
    };

    return (
        <AuthContext.Provider value={{
            user,
            loading,
            error,
            login,
            register,
            validateVat,
            logout,
            forgotPassword,
            resetPassword,
            fetchUser,
            isAuthenticated: !!user,
            isValidated: user?.is_validated === true,
        }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
