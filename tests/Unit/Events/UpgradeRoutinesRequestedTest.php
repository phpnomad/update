<?php

namespace PHPNomad\Update\Tests\Unit\Events;

use PHPNomad\Update\Events\UpgradeRoutinesRequested;
use PHPNomad\Update\Tests\Fixtures\RoutineFor1_0_0;
use PHPNomad\Update\Tests\Fixtures\RoutineFor1_1_0;
use PHPNomad\Update\Tests\Fixtures\RoutineFor1_2_0;
use PHPNomad\Update\Tests\Fixtures\RoutineFor2_0_0;
use PHPNomad\Update\Tests\TestCase;

class UpgradeRoutinesRequestedTest extends TestCase
{
    /**
     * The first routine is at the current version, so it already ran. The
     * routines after it must each be judged on their own version, and only
     * the one inside the range may be registered.
     */
    public function testRegistersOnlyTheQualifyingRoutineWhenTheFirstDoesNotQualify(): void
    {
        $event = new UpgradeRoutinesRequested('1.0.0', '1.1.0');

        $event->maybeRegisterRoutines(RoutineFor1_0_0::class, RoutineFor1_1_0::class, RoutineFor2_0_0::class);

        $this->assertSame([RoutineFor1_1_0::class], $event->getRoutines());
    }

    /**
     * Several routines in one list qualify. Each is registered once, and the
     * routine before the range is left out.
     */
    public function testRegistersEachQualifyingRoutineOnceWhenSeveralQualify(): void
    {
        $event = new UpgradeRoutinesRequested('1.0.0', '2.0.0');

        $event->maybeRegisterRoutines(
            RoutineFor1_0_0::class,
            RoutineFor1_1_0::class,
            RoutineFor1_2_0::class,
            RoutineFor2_0_0::class
        );

        $this->assertSame(
            [RoutineFor1_1_0::class, RoutineFor1_2_0::class, RoutineFor2_0_0::class],
            $event->getRoutines()
        );
    }

    /**
     * No routine in the list falls inside the range, so nothing is registered.
     */
    public function testRegistersNothingWhenNoRoutineQualifies(): void
    {
        $event = new UpgradeRoutinesRequested('1.2.0', '1.5.0');

        $event->maybeRegisterRoutines(
            RoutineFor1_0_0::class,
            RoutineFor1_1_0::class,
            RoutineFor1_2_0::class,
            RoutineFor2_0_0::class
        );

        $this->assertSame([], $event->getRoutines());
    }

    /**
     * A single routine is judged like any other.
     */
    public function testRegistersASingleQualifyingRoutine(): void
    {
        $event = new UpgradeRoutinesRequested('1.1.0', '1.2.0');

        $event->maybeRegisterRoutines(RoutineFor1_2_0::class);

        $this->assertSame([RoutineFor1_2_0::class], $event->getRoutines());
    }

    /**
     * Two listeners may name the same routine, or one listener may run twice.
     * Each routine is still registered only once, in the order first seen.
     */
    public function testRegistersARoutineOnceAcrossSeveralCalls(): void
    {
        $event = new UpgradeRoutinesRequested('1.0.0', '2.0.0');

        $event->maybeRegisterRoutines(RoutineFor1_1_0::class, RoutineFor1_2_0::class);
        $event->maybeRegisterRoutines(RoutineFor1_2_0::class, RoutineFor2_0_0::class);
        $event->maybeRegisterRoutines(RoutineFor1_1_0::class, RoutineFor1_2_0::class);

        $this->assertSame(
            [RoutineFor1_1_0::class, RoutineFor1_2_0::class, RoutineFor2_0_0::class],
            $event->getRoutines()
        );
    }

    /**
     * A list that names the same routine twice registers it once.
     */
    public function testRegistersARoutineOnceWhenOneListRepeatsIt(): void
    {
        $event = new UpgradeRoutinesRequested('1.0.0', '2.0.0');

        $event->maybeRegisterRoutines(RoutineFor1_1_0::class, RoutineFor1_1_0::class);

        $this->assertSame([RoutineFor1_1_0::class], $event->getRoutines());
    }

    /**
     * A routine at the current version already ran, and a routine past the
     * target version belongs to a later upgrade.
     */
    public function testExcludesTheCurrentVersionAndIncludesTheTargetVersion(): void
    {
        $event = new UpgradeRoutinesRequested('1.1.0', '1.2.0');

        $event->maybeRegisterRoutines(RoutineFor1_1_0::class, RoutineFor1_2_0::class, RoutineFor2_0_0::class);

        $this->assertSame([RoutineFor1_2_0::class], $event->getRoutines());
    }
}
