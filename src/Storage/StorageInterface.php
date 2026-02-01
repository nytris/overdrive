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

namespace Nytris\Overdrive\Storage;

/**
 * Interface StorageInterface.
 *
 * Stores the class mapping cache for Overdrive.
 *
 * @phpstan-type ClassMap array<string, string|true|false|null>
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StorageInterface
{
    /**
     * Fetches the map cache from the backing store, if it has been stored yet.
     *
     * @return ClassMap|null
     */
    public function fetchMapCache(): ?array;

    /**
     * Determines whether the storage mechanism is supported.
     */
    public function isSupported(): bool;

    /**
     * Stores a new map cache to the backing store.
     *
     * @param ClassMap $mapCache
     */
    public function saveMapCache(array $mapCache): void;
}
