# Nytris Overdrive

[![Build Status](https://github.com/nytris/overdrive/workflows/CI/badge.svg)](https://github.com/nytris/overdrive/actions?query=workflow%3ACI)

Optimises PHP autoloading performance.

## Usage
Install this package with Composer:

```shell
$ composer require nytris/overdrive
```

### Configure for Nytris Ignition

See [Nytris Ignition documentation][] for details on setting up Ignition, into which this preflight will be installed:

`nytris.ignition.php`
```php
<?php

declare(strict_types=1);

use Nytris\Ignition\IgnitionConfig;
use Nytris\Overdrive\OverdrivePreflight;

$ignitionConfig = new IgnitionConfig();

$ignitionConfig->installPreflight(new OverdrivePreflight());

return $ignitionConfig;
```

## See also

- [Nytris Antilag][Nytris Antilag]
- [Nytris Boost][Nytris Boost]
- [Nytris Ignition][Nytris Ignition]

[Nytris Antilag]: https://github.com/nytris/antilag
[Nytris Boost]: https://github.com/nytris/boost
[Nytris Ignition]: https://github.com/nytris/ignition
[Nytris Ignition documentation]: https://github.com/nytris/ignition/blob/main/README.md
