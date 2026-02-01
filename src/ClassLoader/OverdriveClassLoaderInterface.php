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
 * Interface OverdriveClassLoaderInterface.
 *
 * Handles class autoloading when in Overdrive.
 *
 * @phpstan-import-type ClassMap from StorageInterface
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface OverdriveClassLoaderInterface
{
    /**
     * Adds a secondary autoloader.
     */
    public function appendAutoloader(callable $autoloader): void;

    /**
     * Used by Symfony DebugClassLoader.
     */
    public function findFile(string $class): string|false;

    /**
     * Fetches all registered autoloaders, both Composer's (if present) and secondary ones.
     *
     * @return callable[]
     */
    public function getAutoloaders(): array;

    /**
     * Fetches the class map.
     *
     * @return ClassMap
     */
    public function getClassMap(): array;

    /**
     * Hooks the given Composer autoloader.
     */
    public function hook(ClassLoader $composerLoader): void;

    /**
     * Autoloads the given class.
     */
    public function loadClass(string $class): void;
}
