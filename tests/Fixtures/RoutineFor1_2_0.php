<?php

namespace PHPNomad\Update\Tests\Fixtures;

use PHPNomad\Update\Interfaces\UpgradeRoutine;

class RoutineFor1_2_0 implements UpgradeRoutine
{
    public static function getTargetVersion(): string
    {
        return '1.2.0';
    }

    public function up(): void
    {
    }
}
