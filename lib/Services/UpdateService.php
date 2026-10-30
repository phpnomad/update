<?php

namespace PHPNomad\Update\Services;

use PHPNomad\Di\Interfaces\InstanceProvider;
use PHPNomad\Events\Interfaces\EventStrategy;
use PHPNomad\Update\Events\UpgradeRoutinesRequested;
use PHPNomad\Update\Interfaces\UpgradeRoutine;
use PHPNomad\Utils\Helpers\Arr;

class UpdateService
{
    protected EventStrategy $events;

    protected InstanceProvider $provider;

    public function __construct(EventStrategy $events, InstanceProvider $provider)
    {
        $this->provider = $provider;
        $this->events = $events;
    }

    /**
     * Gets the list of routines, sorted in the order they should run.
     *
     * @param string $currentVersion
     * @param string $targetVersion
     * @return UpgradeRoutine[]
     */
    protected function getRoutines(string $currentVersion, string $targetVersion)
    {
        $event = new UpgradeRoutinesRequested($currentVersion, $targetVersion);
        $this->events->broadcast($event);
        $routines = $event->getRoutines();

        Arr::sort($routines, fn($a, $b) => version_compare($a::getTargetVersion(), $b::getTargetVersion()));

        $instances = [];

        /** @var class-string<UpgradeRoutine>[] $routines */
        foreach ($routines as $routine) {
            $instances[] = $this->provider->get($routine);
        }

        return $instances;
    }

    /**
     * Runs the update based on the provided set of versions.
     *
     * @param string $currentVersion
     * @param string $targetVersion
     * @return void
     */
    public function update(string $currentVersion, string $targetVersion)
    {
        $routines = $this->getRoutines($currentVersion, $targetVersion);

        Arr::each($routines, fn(UpgradeRoutine $routine) => $routine->up());
    }
}