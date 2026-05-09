<?php

/**
 * Laravel 12 framework dist omits Illuminate\Foundation\resources\server.php, but
 * ServeCommand still falls back to it when base_path('server.php') is missing.
 * Restore the Laravel 11-style router (cwd is public/ when artisan serve runs).
 */

$base = dirname(__DIR__);
$vendorRouter = $base.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
$rootRouter = $base.'/server.php';

$vendorContent = <<<'PHP'
<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
PHP;

$rootContent = <<<'PHP'
<?php

/**
 * Router for PHP's built-in development server (`php artisan serve`).
 * Used when this file is passed as the router script (project root).
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
PHP;

if (! is_dir(dirname($vendorRouter))) {
    fwrite(STDERR, "ensure-dev-server-router: vendor/laravel/framework not found, skip.\n");

    exit(0);
}

$needsVendor = ! is_file($vendorRouter)
    || ! str_contains((string) @file_get_contents($vendorRouter), '$publicPath = getcwd();');

if ($needsVendor) {
    if (! @file_put_contents($vendorRouter, $vendorContent)) {
        fwrite(STDERR, "ensure-dev-server-router: failed to write {$vendorRouter}\n");

        exit(1);
    }
}

if (! is_file($rootRouter)) {
    if (! @file_put_contents($rootRouter, $rootContent)) {
        fwrite(STDERR, "ensure-dev-server-router: could not write {$rootRouter} (permissions?). Using vendor router only — php artisan serve still works.\n");
        // Do not exit(1): breaks composer install; ServeCommand falls back to vendor server.php.
    }
}
