<?php

declare(strict_types=1);

// Point d'entree commun: charge config et autoload minimal des classes MVC.
const APP_ROOT = __DIR__;
const PROJECT_ROOT = __DIR__ . '/..';

require_once APP_ROOT . '/config/config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = APP_ROOT . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

