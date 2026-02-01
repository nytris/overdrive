<?php

/*
 * Nytris Overdrive
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/nytris/overdrive/
 *
 * Released under the MIT license.
 * https://github.com/nytris/overdrive/raw/main/MIT-LICENSE.txt
 */

namespace Composer\Autoload;

use Nytris\Overdrive\Overdrive;
use function spl_autoload_register as native_spl_autoload_register;

// Hook installation of Composer's autoloader.
function spl_autoload_register(?callable $callback, bool $throw = true, bool $prepend = false): bool {
    $result = native_spl_autoload_register($callback, $throw, $prepend);

    Overdrive::hookAutoloaders();

    return $result;
}
