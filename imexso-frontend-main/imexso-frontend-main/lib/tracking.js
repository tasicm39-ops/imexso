import { apiPost } from '@/lib/api';

const TRACKING_ENABLED = true;

export async function trackEvent(eventType, payload = {}) {
    if (!TRACKING_ENABLED) return;

    try {
        await apiPost('/api/segment-events', {
            event_type: eventType,
            payload,
        });
    } catch {
        // Silently fail - tracking should never break the app
    }
}

export const EventTypes = {
    SEARCH: 'search',
    VIEW_CAR: 'view_car',
    FILTER: 'filter',
    PAGE_VIEW: 'page_view',
    LOGIN: 'login',
    REGISTER: 'register',
    FAVOURITE: 'favourite',
    SAVE_SEARCH: 'save_search',
    ADD_TO_CART: 'add_to_cart',
    REMOVE_FROM_CART: 'remove_from_cart',
};
