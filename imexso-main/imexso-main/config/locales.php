<?php

return [
    /**
     * Public UI locales (Next.js storefront + Laravel admin/API).
     * Order matches the language selector: FR, NL, DE, IT, EN.
     */
    'supported' => ['fr', 'nl', 'de', 'it', 'en'],

    'default' => 'fr',

    /**
     * When a locale has no announcement text, try these in order after the requested locale.
     */
    'announcement_fallback' => ['fr', 'en'],
];
