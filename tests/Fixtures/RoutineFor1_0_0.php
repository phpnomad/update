<?php

namespace PHPNomad\Update\Tests\Fixtures;

use PHPNomad\Update\Interfaces\UpgradeRoutine;

class RoutineFor1_0_0 implements UpgradeRoutine
{
    public static function getTargetVersion(): string
    {
        return '1.0.0';
    }

    public function up(): void
    {
    }
}
