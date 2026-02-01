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

namespace Nytris\Overdrive\ClassLoader;

use Composer\Autoload\ClassLoader;
use Nytris\Overdrive\Storage\StorageInterface;

/**
 * Class OverdriveClassLoader.
 *
 * Handles class autoloading when in Overdrive.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class OverdriveClassLoader implements OverdriveClassLoaderInterface
{
    /**
     * @var callable[]
     */
    private array $autoloaders = [];
    private ?ClassLoader $composerLoader = null;
    /**
     * @var array<string, string|true|false|null>
     */
    private array $map;
    private bool $mapDirty = false;
    /**
     * @var callable|null
     */
    private mixed $scopeIsolatedInclude;

    public function __construct(
        private readonly StorageInterface $storage,
        ?callable $scopeIsolatedInclude = null
    ) {
        $this->map = $storage->fetchMapCache() ?? [];

        $this->scopeIsolatedInclude = $scopeIsolatedInclude ?? (static function ($path) {
            include $path;
        })->bindTo(newThis: null, newScope: null);
    }

    public function __destruct()
    {
        if ($this->mapDirty) {
            $this->storage->saveMapCache($this->map);
        }
    }

    /**
     * @inheritDoc
     */
    public function appendAutoloader(callable $autoloader): void
    {
        $this->autoloaders[] = $autoloader;
    }

    /**
     * @inheritDoc
     */
    public function findFile(string $class): string|false
    {
        $path = $this->map[$class] ?? null;

        if (
            // Cached as having never been loaded by any autoloader previously - treat as non-existent.
            $path === false ||
            // Cached as having been loaded by a secondary autoloader previously, we have no path to return.
            $path === true
        ) {
            return false;
        }

        if ($path === null) {
            $path = $this->composerLoader ? $this->composerLoader->findFile($class) : false;

            if ($path !== false) {
                $this->map[$class] = $path;
                $this->mapDirty = true;
            }

            // Don't add a cache entry if not found, as a secondary autoloader may still resolve it in the future.
            // However, there's no point in trying the secondary autoloaders here
            // as we cannot accurately resolve a path (reflection may not tell the whole story).
        }

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getAutoloaders(): array
    {
        return $this->composerLoader ?
            [[$this->composerLoader, 'loadClass'], ...$this->autoloaders] :
            $this->autoloaders;
    }

    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return $this->map;
    }

    /**
     * @inheritDoc
     */
    public function hook(ClassLoader $composerLoader): void
    {
        $this->composerLoader = $composerLoader;
    }

    /**
     * @inheritDoc
     */
    public function loadClass(string $class): void
    {
        $path = $this->map[$class] ?? null;

        if ($path === false) {
            // Cached as having never been loaded by any autoloader previously - treat as non-existent.
            return;
        }

        if ($path === true) {
            // Cached as having been loaded by a secondary autoloader previously.
            // Avoid looking up via Composer's autoloader, as we know it will fail to resolve.

            foreach ($this->autoloaders as $autoloader) {
                $autoloader($class);

                if (
                    class_exists($class, autoload: false) ||
                    interface_exists($class, autoload: false) ||
                    trait_exists($class, autoload: false) ||
                    enum_exists($class, autoload: false)
                ) {
                    return;
                }
            }
        }

        if ($path === null) {
            $path = $this->composerLoader ? $this->composerLoader->findFile($class) : false;

            if ($path === false) {
                foreach ($this->autoloaders as $autoloader) {
                    $autoloader($class);

                    if (
                        class_exists($class, autoload: false) ||
                        interface_exists($class, autoload: false) ||
                        trait_exists($class, autoload: false) ||
                        enum_exists($class, autoload: false)
                    ) {
                        // Cache as having been loaded by a secondary autoloader.
                        $path = true;
                        break;
                    }
                }
            }

            // Cache class as:
            // - true: having been loaded by a secondary autoloader.
            // - false: having never been loaded by any autoloader to avoid future lookups.
            // - string: the discovered class path, to avoid future lookups.
            $this->map[$class] = $path;

            $this->mapDirty = true;
        }

        if ($path !== false) {
            ($this->scopeIsolatedInclude)($path);
        }
    }
}
