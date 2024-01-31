<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

/**
 * @todo Implement \JsonSerialize.
 */
class LockDiffEntry
{

    public readonly string $name;

    public readonly RelationshipAction $relationshipAction;

    public readonly bool $isDirectDependencyChanged;

    public readonly VersionAction $versionAction;

    public readonly bool $isVersionChanged;

    public readonly ?string $versionPartChanged;

    public readonly bool $isVersionMajorChanged;

    public readonly bool $isVersionMinorChanged;

    public readonly bool $isVersionPatchChanged;

    public readonly bool $isVersionPreReleaseChanged;

    public readonly bool $isVersionMetadataChanged;

    public readonly ?PackageInterface $left;

    public readonly ?PackageInterface $right;

    public function __construct(
        ?PackageInterface $left = null,
        ?PackageInterface $right = null,
    ) {
        assert($left || $right, 'At least one package has to be provided');

        $this->left = $left;
        $this->right = $right;

        $this->name = $this->left?->name() ?: $this->right?->name();

        $this
            ->initRelationship()
            ->initVersionAction();

        if (!$left) {
            $this->initAdded();
        } elseif (!$right) {
            $this->initRemoved();
        } else {
            $this->initChanged();
        }
    }

    public function initRelationship(): static
    {
        if (!$this->left) {
            $this->relationshipAction = RelationshipAction::Add;
            $this->isDirectDependencyChanged = true;

            return $this;
        }

        if (!$this->right) {
            $this->relationshipAction = RelationshipAction::Remove;
            $this->isDirectDependencyChanged = true;

            return $this;
        }

        $this->relationshipAction = $this->left->typeOfRelationship() === $this->right->typeOfRelationship() ?
            RelationshipAction::None
            : RelationshipAction::Change;

        $this->isDirectDependencyChanged = $this->left->isDirectDependency() !== $this->right->isDirectDependency();

        return $this;
    }

    public function initVersionAction(): static
    {
        if (!$this->left) {
            $this->versionAction = VersionAction::Upgrade;

            return $this;
        }

        if (!$this->right) {
            $this->versionAction = VersionAction::Downgrade;

            return $this;
        }

        if ($this->left->version() && $this->right->version()) {
            $pattern = '/^\d+(\.\d+)?\.x-dev$/';
            $isLeftBranch = preg_match($pattern, $this->left->versionString()) === 1;
            $isRightBranch = preg_match($pattern, $this->right->versionString()) === 1;
            if (($isLeftBranch && $isRightBranch)
                || (!$isLeftBranch && !$isRightBranch)
            ) {
                $this->versionAction = VersionAction::fromStrings(
                    $this->left->versionString(),
                    $this->right->versionString(),
                );

                return $this;
            }

            $diff = $this->left->version()->diff($this->right->version());
            $diffMajor = $diff['major'] ?? null;
            $diffMinor = $diff['minor'] ?? null;
            $diffPatch = $diff['patch'] ?? null;
            if (!$diffMajor && !$diffMinor && !$diffPatch) {
                $this->versionAction = $isLeftBranch ?
                    VersionAction::Downgrade
                    : VersionAction::Upgrade;

                return $this;
            }
        }

        $this->versionAction = VersionAction::fromStrings(
            $this->left->versionString(),
            $this->right->versionString(),
        );

        return $this;
    }

    protected function initAdded(): static
    {
        $this->isVersionChanged = true;
        $this->versionPartChanged = 'major';
        $this->initVersionChangedFragments(0);

        return $this;
    }

    protected function initRemoved(): static
    {
        $this->isVersionChanged = true;
        $this->versionPartChanged = 'major';
        $this->initVersionChangedFragments(0);

        return $this;
    }

    protected function initChanged(): static
    {
        $this->initVersionChanged();

        return $this;
    }

    protected function initVersionChanged(): static
    {
        if ($this->left?->versionString() === $this->right?->versionString()) {
            $this->versionPartChanged = null;
            $this->isVersionChanged = false;
            $this->initVersionChangedFragments(99);

            return $this;
        }

        $this->isVersionChanged = true;
        if (!$this->left?->version() || !$this->right?->version()) {
            // Version strings can not be compared.
            $this->versionPartChanged = null;
            $this->initVersionChangedFragments(99);

            return $this;
        }

        $leftVersion = $this->left->version();
        $rightVersion = $this->right->version();

        $trueFrom = 99;
        if ($leftVersion->major !== $this->right->version()->major) {
            $this->versionPartChanged = 'major';
            $trueFrom = 0;
        } elseif ($leftVersion->minor !== $rightVersion->minor) {
            $this->versionPartChanged = 'minor';
            $trueFrom = 1;
        } elseif ($leftVersion->patch !== $rightVersion->patch) {
            $this->versionPartChanged = 'patch';
            $trueFrom = 2;
        } elseif ($leftVersion->preRelease !== $rightVersion->preRelease) {
            $this->versionPartChanged = 'preRelease';
            $trueFrom = 3;
        } elseif ($leftVersion->metadata !== $rightVersion->metadata) {
            $this->versionPartChanged = 'metadata';
            $trueFrom = 4;
        }

        $this->initVersionChangedFragments($trueFrom);

        return $this;
    }

    protected function initVersionChangedFragments(int $trueFrom): static
    {
        $this->isVersionMajorChanged = $trueFrom <= 0;
        $this->isVersionMinorChanged = $trueFrom <= 1;
        $this->isVersionPatchChanged = $trueFrom <= 2;
        $this->isVersionPreReleaseChanged = $trueFrom <= 3;
        $this->isVersionMetadataChanged = $trueFrom <= 4;

        return $this;
    }
}
