<?php

spl_autoload_register(function ($className) {
    $filePath = getFilePathOfClassNameWithPSR0(
        $className,
        'coc',
        __DIR__,
        '.php'
    );
    if ($filePath) {
        /** @noinspection PhpIncludeInspection */
        require_once $filePath;
    }
});

function getFilePathOfClassNameWithPSR0($className, $baseNameSpace, $basePath, $extension = '.php')
{
    if (strpos($className, $baseNameSpace) === 0) {
        $classFile = str_replace($baseNameSpace, $basePath, $className);
        $classFile .= $extension;
        $classFile = str_replace('\\', '/', $classFile);

        return $classFile;
    }
    return null;
}