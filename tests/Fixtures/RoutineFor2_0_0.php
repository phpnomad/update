<?php

namespace PHPNomad\Update\Tests\Fixtures;

use PHPNomad\Update\Interfaces\UpgradeRoutine;

class RoutineFor2_0_0 implements UpgradeRoutine
{
    public static function getTargetVersion(): string
    {
        return '2.0.0';
    }

    public function up(): void
    {
    }
}
