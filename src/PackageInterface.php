<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

use Sweetchuck\Utils\VersionNumber;

/**
 * @todo Add "downloaded from URL" or something like that.
 * @todo Implement \JsonSerialize.
 */
interface PackageInterface
{

    public function name(): string;

    public function type(): ?string;

    /**
     * Semantic version number, branch name or version constraint.
     *
     * Examples: "1.2.3", "1.x-dev", "*", ">= 1.2".
     *
     * @return null|string
     */
    public function versionString(): ?string;

    public function version(): ?VersionNumber;

    /**
     * @return null|string
     *   - prod_required
     *   - prod_optional
     *   - dev_required
     *   - peer
     *
     * @todo Maybe the "indirect" should be valid value as well.
     * @todo Enum vs string?
     */
    public function typeOfRelationship(): ?string;

    public function isDirectDependency(): ?bool;

    /**
     * @todo Maybe extra interface.
     */
    public function homepage(): ?string;

    /**
     * @return null|array{type: string, url: string}
     *   type: github|gitlab|azure
     *   Other keys are depend on the value of the "type".
     *
     * @todo Maybe extra interface.
     */
    public function vcsInfo(): ?array;

    /**
     * @return null|array{type: string, url: string}
     *   type: github|gitlab|jira|drupal.org|redmine
     *   Other keys are depend on the value of the "type".
     *
     * @todo Maybe extra interface.
     */
    public function issueTracker(): ?array;
}
