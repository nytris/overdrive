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

use Closure;
use Nytris\Ignition\Preflight\PreflightInterface;
use Nytris\Overdrive\Storage\ApcuStorage;
use Nytris\Overdrive\Storage\StorageInterface;

/**
 * Class OverdrivePreflight.
 *
 * Overdrive Ignition preflight configuration.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class OverdrivePreflight implements PreflightInterface
{
    public function __construct(
        private readonly StorageInterface $storage = new ApcuStorage(),
        private readonly ?Closure $classLoaderProvider = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'overdrive';
    }

    /**
     * @inheritDoc
     */
    public function getRunCallback(): Closure
    {
        return fn () => Overdrive::install($this->storage, $this->classLoaderProvider);
    }

    /**
     * Fetches the configured storage.
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @inheritDoc
     */
    public function getVendor(): string
    {
        return 'nytris';
    }
}
