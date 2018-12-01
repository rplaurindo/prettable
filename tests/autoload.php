<?php

spl_autoload_register(function ($className) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $className)  . '.php';
});

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';
