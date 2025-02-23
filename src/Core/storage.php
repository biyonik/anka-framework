<?php

use Framework\Core\Container\Container;
use Framework\Core\Filesystem\Contracts\FilesystemInterface;
use Framework\Core\Filesystem\Storage\StorageManager;

if (!function_exists('storage_path')) {
    /**
     * Storage dizininde bir yolun tam adresini döndürür.
     *
     * @param string $path Alt yol
     * @return string Tam yol
     */
    function storage_path(string $path = ''): string
    {
        $container = new Container();
        $basePath = $container->get('config')->get('paths.storage');
        return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Public dizininde bir yolun tam adresini döndürür.
     *
     * @param string $path Alt yol
     * @return string Tam yol
     */
    function public_path(string $path = ''): string
    {
        $container = new Container();
        $basePath = $container->get('config')->get('paths.public');
        return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage')) {
    /**
     * Storage instance'ını ya da belirli bir diski döndürür.
     *
     * @param string|null $disk Disk adı
     * @return StorageManager|FilesystemInterface
     */
    function storage(?string $disk = null): FilesystemInterface|StorageManager
    {
        $container = new Container();
        $storage = $container->get('storage');

        if ($disk === null) {
            return $storage;
        }

        return $storage->disk($disk);
    }
}

if (!function_exists('asset')) {
    /**
     * Public URL için asset yolu oluşturur.
     *
     * @param string $path Asset yolu
     * @return string Asset URL'i
     */
    function asset(string $path): string
    {
        $container = new Container();
        return rtrim($container->get('config')->get('app.url', ''), '/')
            . '/' . ltrim($path, '/');
    }
}

if (!function_exists('base_path')) {
    /**
     * Uygulama kök dizininde bir yolun tam adresini döndürür.
     *
     * @param string $path Alt yol
     * @return string Tam yol
     */
    function base_path(string $path = ''): string
    {
        $container = new Container();
        $basePath = $container->get('config')->get('paths.base');
        return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}