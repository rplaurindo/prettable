<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

$setIncludePath = function (array $paths) {
    set_include_path(get_include_path() . PATH_SEPARATOR . implode(DIRECTORY_SEPARATOR, $paths));
};

$setIncludePath([$rootPath]);

$setIncludePath([$rootPath, 'lib']);

$setIncludePath([$rootPath, 'src']);

$setIncludePath([$rootPath, 'tests']);
