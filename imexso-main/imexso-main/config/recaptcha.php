<?php

return [
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
    'enabled' => env('RECAPTCHA_ENABLED', true),
];
