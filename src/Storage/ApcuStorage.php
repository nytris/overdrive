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
 * Class ApcuStorage.
 *
 * Stores the class mapping cache for Overdrive in APCu.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ApcuStorage implements StorageInterface
{
    public function __construct(
        private readonly string $apcuNamespace = 'nytris.overdrive.map'
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fetchMapCache(): ?array
    {
        $mapCache = apcu_fetch($this->apcuNamespace, success: $success);

        return $success ? $mapCache : null;
    }

    /**
     * @inheritDoc
     */
    public function isSupported(): bool
    {
        return function_exists('apcu_enabled') && apcu_enabled();
    }

    /**
     * @inheritDoc
     */
    public function saveMapCache(array $mapCache): void
    {
        if (apcu_store($this->apcuNamespace, $mapCache) === false) {
            trigger_error('Failed to save Nytris Overdrive map cache to APCu', E_USER_ERROR);
        }
    }
}
