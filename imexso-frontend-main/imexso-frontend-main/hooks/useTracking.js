"use client";

import { useCallback, useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';
import { trackEvent, EventTypes } from '@/lib/tracking';
import { useAuth } from '@/context/AuthContext';

export function usePageTracking() {
    const pathname = usePathname();
    const { isAuthenticated } = useAuth();
    const lastTrackedPath = useRef(null);

    useEffect(() => {
        if (isAuthenticated && pathname !== lastTrackedPath.current) {
            lastTrackedPath.current = pathname;
            trackEvent(EventTypes.PAGE_VIEW, { path: pathname });
        }
    }, [pathname, isAuthenticated]);
}

export function useTrackSearch() {
    return useCallback((searchParams) => {
        trackEvent(EventTypes.SEARCH, searchParams);
    }, []);
}

export function useTrackCarView() {
    return useCallback((carId, carMake, carModel) => {
        trackEvent(EventTypes.VIEW_CAR, { car_id: carId, make: carMake, model: carModel });
    }, []);
}

export function useTrackFilter() {
    return useCallback((filters) => {
        trackEvent(EventTypes.FILTER, filters);
    }, []);
}

export function useTrackLogin() {
    return useCallback(() => {
        trackEvent(EventTypes.LOGIN, {});
    }, []);
}

export function useTrackRegister() {
    return useCallback(() => {
        trackEvent(EventTypes.REGISTER, {});
    }, []);
}
