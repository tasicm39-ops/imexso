"use client";

import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';

export default function ProtectedRoute({ children, requireValidation = false }) {
    const { user, loading, isAuthenticated, isValidated } = useAuth();
    const router = useRouter();

    useEffect(() => {
        if (!loading) {
            if (!isAuthenticated) {
                router.push('/login');
            } else if (requireValidation && !isValidated) {
                router.push('/pending-approval');
            }
        }
    }, [loading, isAuthenticated, isValidated, requireValidation, router]);

    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '100vh' }}>
                <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (!isAuthenticated) return null;
    if (requireValidation && !isValidated) return null;

    return children;
}
