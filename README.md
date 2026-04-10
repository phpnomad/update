# phpnomad/update

[![Latest Version](https://img.shields.io/packagist/v/phpnomad/update.svg)](https://packagist.org/packages/phpnomad/update)
[![Total Downloads](https://img.shields.io/packagist/dt/phpnomad/update.svg)](https://packagist.org/packages/phpnomad/update)
[![PHP Version](https://img.shields.io/packagist/php-v/phpnomad/update.svg)](https://packagist.org/packages/phpnomad/update)
[![License](https://img.shields.io/packagist/l/phpnomad/update.svg)](https://packagist.org/packages/phpnomad/update)

`phpnomad/update` runs upgrade routines when an application moves from one version to another. You define routines as small classes that declare a target version and do their work in an `up()` method, and the package handles filtering them to the right range, sorting them by version, and running them in order. Coordination happens through `phpnomad/event`, so routines can be contributed from any initializer without manual wiring.

## Installation

Install via Composer:

```bash
composer require phpnomad/update
```

## Overview

- `UpgradeRoutine` is the interface each routine implements. It declares a target version via a static method and does its work in `up()`.
- `HasUpdates` is what your loader initializers implement to contribute routines. When `phpnomad/loader` boots an initializer that implements it, the loader attaches the routines to the collection event for you.
- `UpdateService::update($current, $target)` broadcasts the collection event, filters the returned routines to the ones whose target version falls above the current version and at or below the target, sorts them, and runs each one.
- `PlatformUpdateRequested` is the event a host broadcasts when it detects that the stored application version differs from the running version. A listener in the host calls `UpdateService::update()` in response.
- `StoredVersionDatastore` is the interface a host implements to read and write the currently installed version (options table, config file, environment variable, whatever fits the runtime).

## Routine example

A routine is a class that knows its target version and what to do:

```php
<?php

namespace MyApp\Upgrades;

use PHPNomad\Update\Interfaces\UpgradeRoutine;

class InstallWidgetsTable implements UpgradeRoutine
{
    public static function getTargetVersion(): string
    {
        return '1.2.0';
    }

    public function up(): void
    {
        // Install or migrate your 1.2.0 schema here.
    }
}
```

Register it by implementing `HasUpdates` on a loader initializer and returning the class name from `getRoutines()`. The loader wires the rest. See the bootstrapping guide at [phpnomad.com](https://phpnomad.com) for the full initializer pattern.

## Documentation

Full framework documentation lives at [phpnomad.com](https://phpnomad.com), including the loader and event packages this one builds on.

## License

Released under the MIT License. See [LICENSE.txt](LICENSE.txt) for details.
