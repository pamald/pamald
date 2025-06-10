<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

/**
 * @todo Support for {"require": {"ext-FOO": "*"}}.
 */
class LockDiffer
{

    /**
     * @param array<string, \Pamald\Pamald\DependencyInterface> $leftDependencies
     * @param array<string, \Pamald\Pamald\DependencyInterface> $rightDependencies
     *
     * @return array<string, LockDiffEntry>
     */
    public function diff(
        array $leftDependencies = [],
        array $rightDependencies = [],
    ): array {
        // @todo Maybe both $leftDependencies and $rightDependencies are also optional.
        assert(
            $leftDependencies || $rightDependencies,
            'One of the $leftPackages or $rightPackages is required.',
        );

        $dependencyNames = array_unique(array_merge(
            array_keys($leftDependencies),
            array_keys($rightDependencies),
        ));
        sort($dependencyNames);

        $entries = [];
        foreach ($dependencyNames as $name) {
            $left = $leftDependencies[$name] ?? null;
            $right = $rightDependencies[$name] ?? null;
            if (!$this->isChanged($left, $right)) {
                continue;
            }

            $entries[$name] = new LockDiffEntry($left, $right);
        }

        return $entries;
    }

    public function isChanged(?DependencyInterface $left, ?DependencyInterface $right): bool
    {
        assert(
            $left || $right,
            'One of the $left or $right is required.',
        );

        if (!$left || !$right) {
            return true;
        }

        // @todo The repository which the package is downloaded from also can be
        // changed based on the composer.json#/repositories.
        // @todo Applied patches can be changed.
        return $left->versionString() !== $right->versionString()
            || $left->type() !== $right->type()
            || $left->link() !== $right->link()
            || $left->environment() !== $right->environment()
            || $left->isDirectDependency() !== $right->isDirectDependency();
    }
}
