<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

/**
 * @todo Support for {"require": {"ext-FOO": "*"}}.
 */
class LockDiffer
{

    /**
     * @param array<string, \Pamald\Pamald\PackageInterface> $leftPackages
     * @param array<string, \Pamald\Pamald\PackageInterface> $rightPackages
     *
     * @return array<string, LockDiffEntry>
     */
    public function diff(
        array $leftPackages = [],
        array $rightPackages = [],
    ): array {
        // @todo Maybe both $leftPackages and $rightPackages are also optional.
        assert($leftPackages || $rightPackages, 'One of the $leftPackages or $rightPackages is required.');

        $packageNames = array_unique(array_merge(
            array_keys($leftPackages),
            array_keys($rightPackages),
        ));
        sort($packageNames);

        $entries = [];
        foreach ($packageNames as $name) {
            $left = $leftPackages[$name] ?? null;
            $right = $rightPackages[$name] ?? null;
            if (!$this->isChanged($left, $right)) {
                continue;
            }

            $entries[$name] = new LockDiffEntry($left, $right);
        }

        return $entries;
    }

    public function isChanged(?PackageInterface $left, ?PackageInterface $right): bool
    {
        assert($left || $right, 'One of the $left or $right is required.');

        if (!$left || !$right) {
            return true;
        }

        // @todo The repository which the package is downloaded from also can be
        // changed based on the composer.json#/repositories.
        // @todo Applied patches can be changed.
        return $left->versionString() !== $right->versionString()
            || $left->typeOfRelationship() !== $right->typeOfRelationship()
            || $left->isDirectDependency() !== $right->isDirectDependency();
    }
}
