<?php

/*
 * Nytris Overdrive
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/nytris/overdrive/
 *
 * Released under the MIT license.
 * https://github.com/nytris/overdrive/raw/main/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Nytris\Overdrive;

use Composer\Autoload\ClassLoader;
use Nytris\Overdrive\ClassLoader\OverdriveClassLoader;
use Nytris\Overdrive\ClassLoader\OverdriveClassLoaderInterface;
use Nytris\Overdrive\Storage\ApcuStorage;
use Nytris\Overdrive\Storage\StorageInterface;
use Symfony\Component\ErrorHandler\DebugClassLoader;

/**
 * Class Overdrive.
 *
 * Overdrive library entrypoint.
 *
 * @phpstan-import-type ClassMap from StorageInterface
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Overdrive
{
    private static ?OverdriveClassLoaderInterface $autoloader = null;
    /**
     * @var array{OverdriveClassLoaderInterface, string}|null
     */
    private static ?array $autoloaderCallable = null;

    /**
     * Fetches the current class map.
     *
     * @return ClassMap
     */
    public static function getClassMap(): array
    {
        return self::$autoloader ? self::$autoloader->getClassMap() : [];
    }

    /**
     * Captures any newly installed autoloaders, replacing them with just Overdrive's.
     */
    public static function hookAutoloaders(): void
    {
        spl_autoload_unregister(self::$autoloaderCallable);
        $autoloaders = spl_autoload_functions() ?: [];

        foreach ($autoloaders as $autoloader) {
            spl_autoload_unregister($autoloader);

            $autoloader = is_array($autoloader) && $autoloader[0] instanceof DebugClassLoader ?
                $autoloader[0]->getClassLoader() :
                $autoloader;

            if (is_array($autoloader) && $autoloader[0] instanceof ClassLoader) {
                self::$autoloader->hook($autoloader[0]);
                break;
            }

            if ($autoloader !== self::$autoloaderCallable) {
                self::$autoloader->appendAutoloader($autoloader);
            }
        }

        spl_autoload_register(self::$autoloaderCallable);
    }

    /**
     * Installs Nytris Overdrive.
     *
     * @param StorageInterface $storage
     * @param (callable(StorageInterface): OverdriveClassLoaderInterface)|null $classLoaderProvider
     */
    public static function install(
        StorageInterface $storage = new ApcuStorage(),
        ?callable $classLoaderProvider = null
    ): void {
        if (!$storage->isSupported()) {
            return;
        }

        $loader = $classLoaderProvider ?
            ($classLoaderProvider)($storage) :
            new OverdriveClassLoader($storage);
        self::$autoloader = $loader;

        // Needs to use array-callable syntax as expected by DebugClassLoader.
        self::$autoloaderCallable = [$loader, 'loadClass'];

        self::hookAutoloaders();

        require_once __DIR__ . '/hook_autoload_installation.php';
    }

    /**
     * Uninstalls Nytris Overdrive.
     */
    public static function uninstall(): void
    {
        if (self::$autoloader !== null) {
            // Re-register any wrapped autoloaders.

            foreach (self::$autoloader->getAutoloaders() as $secondaryAutoloader) {
                spl_autoload_register($secondaryAutoloader);
            }
        }

        if (self::$autoloaderCallable !== null) {
            // Remove Overdrive's own wrapping autoloader.
            spl_autoload_unregister(self::$autoloaderCallable);
        }

        self::$autoloader = null;
        self::$autoloaderCallable = null;
    }
}
