<?php

namespace PHPNomad\Update\Events;

use PHPNomad\Events\Interfaces\Event;
use PHPNomad\Update\Interfaces\UpgradeRoutine;

class UpgradeRoutinesRequested implements Event
{
    private string $currentVersion;
    private string $targetVersion;
    private array $routines = [];

    /**
     * UpgradeEvent constructor.
     *
     * @param string $currentVersion The current version of the system.
     * @param string $targetVersion The target version for the upgrade.
     */
    public function __construct(string $currentVersion, string $targetVersion) {
        $this->currentVersion = $currentVersion;
        $this->targetVersion = $targetVersion;
    }

    /**
     * Get the current version of the system.
     *
     * @return string The current version.
     */
    public function getCurrentVersion(): string {
        return $this->currentVersion;
    }

    /**
     * Get the target version for the upgrade.
     *
     * @return string The target version.
     */
    public function getTargetVersion(): string {
        return $this->targetVersion;
    }

    /**
     * Registers each of the given routines whose target version falls inside
     * this upgrade, after the current version and up to the target version.
     * A routine outside that range is skipped, and a routine that is already
     * registered is not added again.
     *
     * @param class-string<UpgradeRoutine> $routine The first routine to consider.
     * @param class-string<UpgradeRoutine> ...$routines Any further routines to consider.
     *
     * @return $this
     */
    public function maybeRegisterRoutines(string $routine, string ...$routines) {
        array_unshift($routines, $routine);

        foreach ($routines as $item) {
            if (in_array($item, $this->routines, true) || !$this->isInRange($item)) {
                continue;
            }

            $this->routines[] = $item;
        }

        return $this;
    }

    /**
     * Whether the routine's target version is after the current version and
     * no later than the target version.
     *
     * @param class-string<UpgradeRoutine> $routine
     *
     * @return bool
     */
    private function isInRange(string $routine): bool
    {
        $version = $routine::getTargetVersion();

        return version_compare($version, $this->currentVersion, '>')
            && version_compare($version, $this->targetVersion, '<=');
    }

    /**
     * Get the registered upgrade routines.
     *
     * @return UpgradeRoutine[] An array of registered upgrade routines.
     */
    public function getRoutines(): array {
        return $this->routines;
    }

    /**
     * @inheitDoc
     */
    public static function getId(): string
    {
        return 'update_routines_requested';
    }
}
