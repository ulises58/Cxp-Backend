<?php

declare(strict_types=1);

/**
 * Solo se ejecuta vía composer post-create-project-cmd.
 * Quita carpetas de distribución del instalador (bin/, build/) del proyecto ya generado
 * y luego se borra a sí mismo. No afecta a quien clona el repo para trabajar en la base.
 */
$root = __DIR__;

$rrmdir = static function (string $dir) use (&$rrmdir): void {
    if (! is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.DIRECTORY_SEPARATOR.$item;
        is_dir($path) ? $rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
};

foreach (['bin', 'build'] as $name) {
    $path = $root.DIRECTORY_SEPARATOR.$name;
    $rrmdir($path);
}

$self = $root.DIRECTORY_SEPARATOR.basename(__FILE__);
if (is_file($self)) {
    @unlink($self);
}
