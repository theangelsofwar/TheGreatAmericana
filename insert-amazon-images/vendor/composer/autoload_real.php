<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitbea23d0505793e8ae5dfb42a322cf26c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('AmazonImages\Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitbea23d0505793e8ae5dfb42a322cf26c', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \AmazonImages\Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInitbea23d0505793e8ae5dfb42a322cf26c', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require_once __DIR__ . '/autoload_static.php';

            call_user_func(\AmazonImages\Composer\Autoload\ComposerStaticInitbea23d0505793e8ae5dfb42a322cf26c::getInitializer($loader));
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        if ($useStaticLoader) {
            $includeFiles = AmazonImages\Composer\Autoload\ComposerStaticInitbea23d0505793e8ae5dfb42a322cf26c::$files;
        } else {
            $includeFiles = require __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequirebea23d0505793e8ae5dfb42a322cf26c($fileIdentifier, $file);
        }

        return $loader;
    }
}

function composerRequirebea23d0505793e8ae5dfb42a322cf26c($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}
