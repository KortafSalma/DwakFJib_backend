<?php
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $prefixes = [
        'Tests\\' => __DIR__ . '/../tests/',
        'PHPUnit\\' => __DIR__ . '/../vendor/phpunit/phpunit/src/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) { require $file; return; }
    }

    $sebastianDirs = [
        'SebastianBergmann\\CodeCoverage\\' => __DIR__ . '/../vendor/phpunit/php-code-coverage/src/',
        'SebastianBergmann\\' => [
            __DIR__ . '/../vendor/sebastian/cli-parser/src/',
            __DIR__ . '/../vendor/sebastian/code-unit/src/',
            __DIR__ . '/../vendor/sebastian/code-unit-reverse-lookup/src/',
            __DIR__ . '/../vendor/sebastian/comparator/src/',
            __DIR__ . '/../vendor/sebastian/complexity/src/',
            __DIR__ . '/../vendor/sebastian/diff/src/',
            __DIR__ . '/../vendor/sebastian/environment/src/',
            __DIR__ . '/../vendor/sebastian/exporter/src/',
            __DIR__ . '/../vendor/sebastian/global-state/src/',
            __DIR__ . '/../vendor/sebastian/lines-of-code/src/',
            __DIR__ . '/../vendor/sebastian/object-enumerator/src/',
            __DIR__ . '/../vendor/sebastian/object-reflector/src/',
            __DIR__ . '/../vendor/sebastian/recursion-context/src/',
            __DIR__ . '/../vendor/sebastian/type/src/',
            __DIR__ . '/../vendor/sebastian/version/src/',
        ],
    ];
    foreach ($sebastianDirs as $prefix => $baseDirs) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;
        $relativeClass = substr($class, $len);
        $dirs = is_array($baseDirs) ? $baseDirs : [$baseDirs];
        foreach ($dirs as $baseDir) {
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) { require $file; return; }
        }
    }
});
