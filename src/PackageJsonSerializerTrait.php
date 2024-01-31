<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

use Sweetchuck\Utils\VersionNumber;

trait PackageJsonSerializerTrait
{

    // region PackageInterface
    abstract public function name(): string;

    abstract public function type(): ?string;

    abstract public function versionString(): ?string;

    abstract public function version(): ?VersionNumber;

    abstract public function typeOfRelationship(): ?string;

    abstract public function isDirectDependency(): ?bool;

    abstract public function homepage(): ?string;

    /**
     * @return null|array{type: string, url: string}
     */
    abstract public function vcsInfo(): ?array;

    /**
     * @return null|array{type: string, url: string}
     */
    abstract public function issueTracker(): ?array;
    // endregion

    /**
     * @return array<string, mixed>
     *
     * @todo PHPStan type.
     *
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return array_filter(
            [
                'name' => $this->name(),
                'type' => $this->type(),
                'versionString' => $this->versionString(),
                'typeOfRelationship' => $this->typeOfRelationship(),
                'isDirectDependency' => $this->isDirectDependency(),
                'homepage' => $this->homepage(),
                'vcsInfo' => $this->vcsInfo(),
                'issueTracker' => $this->issueTracker(),
            ],
            fn (mixed $value): bool => $value !== null,
        );
    }
}
