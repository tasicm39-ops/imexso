"use client";

import { usePageTracking } from '@/hooks/useTracking';

export default function TrackingProvider({ children }) {
    usePageTracking();
    return children;
}
