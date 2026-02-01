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

namespace Nytris\Overdrive\Tests\Functional\Direct;

use Mockery\MockInterface;
use Nytris\Overdrive\Overdrive;
use Nytris\Overdrive\Storage\StorageInterface;
use Nytris\Overdrive\Tests\Functional\AbstractFunctionalTestCase;

/**
 * Class OverdriveTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class OverdriveTest extends AbstractFunctionalTestCase
{
    private MockInterface&StorageInterface $storage;

    public function setUp(): void
    {
        $this->storage = mock(StorageInterface::class, [
            'fetchMapCache' => [
                'My\First\MyClass' => '/my/first/MyClass.php',
            ],
            'isSupported' => true,
            'saveMapCache' => null,
        ]);
    }

    public function tearDown(): void
    {
        Overdrive::uninstall();
    }

    public function testInstallLoadsClassMapCacheWhenSupported(): void
    {
        Overdrive::install($this->storage);

        static::assertEquals(
            [
                'My\First\MyClass' => '/my/first/MyClass.php',
            ],
            Overdrive::getClassMap()
        );
    }

    public function testInstallDoesNotLoadClassMapCacheWhenNotSupported(): void
    {
        $this->storage->allows()
            ->isSupported()
            ->andReturnFalse();

        $this->storage->expects()
            ->fetchMapCache()
            ->never();

        Overdrive::install($this->storage);

        static::assertEquals([], Overdrive::getClassMap());
    }
}
