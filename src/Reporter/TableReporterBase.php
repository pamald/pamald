<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Reporter;

use Pamald\Pamald\DependencyEnvironment;
use Pamald\Pamald\DependencyType;
use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\DependencyInterface;
use Pamald\Pamald\ReporterInterface;
use Pamald\Pamald\StreamAwareInterface;
use Pamald\Pamald\StreamAwareTrait;
use Sweetchuck\Utils\Comparer\ArrayValueComparer;
use Sweetchuck\Utils\Filter\EnabledFilter;

/**
 * @phpstan-import-type PamaldConsoleTableReporterColumnDef from \Pamald\Pamald\Phpstan
 * @phpstan-import-type PamaldConsoleTableReporterGroupDef  from \Pamald\Pamald\Phpstan
 */
abstract class TableReporterBase implements ReporterInterface, StreamAwareInterface
{
    use StreamAwareTrait;

    protected bool $quiet = true;

    public function getQuiet(): bool
    {
        return $this->quiet;
    }

    public function setQuiet(bool $quiet): static
    {
        $this->quiet = $quiet;

        return $this;
    }

    /**
     * @phpstan-var array<string, \Pamald\Pamald\LockDiffEntry>
     */
    public array $entries;

    /**
     * @phpstan-var array<string, PamaldConsoleTableReporterColumnDef>
     */
    protected array $columns = [];

    /**
     * @phpstan-return array<string, PamaldConsoleTableReporterColumnDef>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @phpstan-param array<string, PamaldConsoleTableReporterColumnDef> $columns
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @phpstan-return array<string, PamaldConsoleTableReporterColumnDef>
     */
    public function getDefaultColumns(): array
    {
        $weight = -1;

        return [
            'name' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'Name',
            ],
            'leftVersionString' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'L Version',
            ],
            'rightVersionString' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'R Version',
                'config' => [
                    'showDirection' => true,
                ],
            ],
            'leftType' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'L Type',
                'config' => [],
            ],
            'rightType' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'R Type',
                'config' => [],
            ],
            'leftLink' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'L Link',
                'config' => [],
            ],
            'rightLink' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'R Link',
                'config' => [],
            ],
            'leftEnvironment' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'L Env',
                'config' => [],
            ],
            'rightEnvironment' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'R Env',
                'config' => [],
            ],
            'leftDirectDependency' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'L Depth',
                'config' => [],
            ],
            'rightDirectDependency' => [
                'enabled' => true,
                'weight' => ++$weight,
                'align' => 'left',
                'title' => 'R Depth',
                'config' => [],
            ],
        ];
    }

    protected function normalizeColumns(): static
    {
        $columns = array_filter(
            $this->getColumns() ?: $this->getDefaultColumns(),
            new EnabledFilter(),
        );

        foreach ($columns as $id => &$column) {
            $column['id'] = $id;
            $column += [
                'title' => $column['id'],
                'align' => 'left',
            ];
        }

        uasort(
            $columns,
            (new ArrayValueComparer())
                ->setKeys([
                    'weight' => [
                        'default' => 0,
                    ],
                    'id' => [
                        'default' => '',
                    ],
                ]),
        );

        $this->setColumns($columns);

        return $this;
    }

    /**
     * @phpstan-var array<string, PamaldConsoleTableReporterGroupDef>
     */
    protected array $groups = [];

    /**
     * @phpstan-return array<string, PamaldConsoleTableReporterGroupDef>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @phpstan-param array<string, PamaldConsoleTableReporterGroupDef> $groups
     */
    public function setGroups(array $groups): static
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @phpstan-return array<string, PamaldConsoleTableReporterGroupDef>
     */
    public function getDefaultGroups(): array
    {
        $filterIsPlatform = function (LockDiffEntry $entry): bool {
            return $entry->left?->type() === DependencyType::Platform
                || $entry->right?->type() === DependencyType::Platform;
        };

        $filterIsProductionDirect = function (LockDiffEntry $entry): bool {
            $isProduction = $entry->left?->environment() === DependencyEnvironment::Production
                || $entry->right?->environment() === DependencyEnvironment::Production;
            $isDirect = $entry->left?->isDirectDependency() === true
                || $entry->right?->isDirectDependency() === true;

            return $isProduction && $isDirect;
        };

        $filterIsProductionIndirect = function (LockDiffEntry $entry): bool {
            $isProduction = $entry->left?->environment() === DependencyEnvironment::Production
                || $entry->right?->environment() === DependencyEnvironment::Production;
            $isIndirect = $entry->left?->isDirectDependency() !== true
                || $entry->right?->isDirectDependency() !== true;

            return $isProduction && $isIndirect;
        };

        $filterIsDevelopmentDirect = function (LockDiffEntry $entry): bool {
            $isDevelopment = $entry->left?->environment() === DependencyEnvironment::Development
                || $entry->right?->environment() === DependencyEnvironment::Development;
            $isDirect = $entry->left?->isDirectDependency() === true
                || $entry->right?->isDirectDependency() === true;

            return $isDevelopment && $isDirect;
        };

        $filterIsDevelopmentIndirect = function (LockDiffEntry $entry): bool {
            $isDevelopment = $entry->left?->environment() === DependencyEnvironment::Development
                || $entry->right?->environment() === DependencyEnvironment::Development;
            $isIndirect = $entry->left?->isDirectDependency() !== true
                || $entry->right?->isDirectDependency() !== true;

            return $isDevelopment && $isIndirect;
        };

        $weight = -1;
        $showHeaderForNonEmpty = true;

        return [
            'platform' => [
                'enabled' => true,
                'id' => 'platform',
                'title' => 'Platform',
                'weight' => ++$weight,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => $filterIsPlatform,
                'comparer' => null,
            ],
            'production-direct' => [
                'enabled' => true,
                'id' => 'production-direct',
                'title' => 'Production - Direct',
                'weight' => ++$weight,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => $filterIsProductionDirect,
                'comparer' => null,
            ],
            'production-indirect' => [
                'enabled' => true,
                'id' => 'production-indirect',
                'title' => 'Production - Indirect',
                'weight' => ++$weight,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => $filterIsProductionIndirect,
                'comparer' => null,
            ],
            'development-direct' => [
                'enabled' => true,
                'id' => 'development-direct',
                'title' => 'Development - Direct',
                'weight' => ++$weight,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => $filterIsDevelopmentDirect,
                'comparer' => null,
            ],
            'development-indirect' => [
                'enabled' => true,
                'id' => 'development-indirect',
                'title' => 'Development - Indirect',
                'weight' => ++$weight,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => $filterIsDevelopmentIndirect,
                'comparer' => null,
            ],
            'other' => [
                'enabled' => true,
                'id' => 'other',
                'title' => 'Other',
                'weight' => 999,
                'showEmpty' => false,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => $showHeaderForNonEmpty,
                'filter' => null,
                'comparer' => null,
            ],
        ];
    }

    protected function normalizeGroups(): static
    {
        $groups = $this->getGroups() ?: $this->getDefaultGroups();
        foreach ($groups as $id => &$group) {
            $group['id'] = $id;
            $group += [
                'enabled' => true,
                'title' => $group['id'],
                'showEmpty' => true,
                'emptyContent' => '-- empty --',
                'showHeaderForNonEmpty' => false,
                'filter' => null,
                'comparer' => null,
            ];
        }
        $groups = array_filter($groups, new EnabledFilter());

        uasort(
            $groups,
            (new ArrayValueComparer())
                ->setKeys([
                    'weight' => [
                        'default' => 0,
                    ],
                ]),
        );

        $this->setGroups($groups);

        return $this;
    }

    /**
     * @phpstan-return array<string, \Pamald\Pamald\LockDiffEntry[]>
     *   Key: group id.
     *   Value: array of entries.
     */
    protected function groupEntries(): array
    {
        $groups = [];
        $entries = $this->entries;
        foreach ($this->getGroups() as $id => $group) {
            if (is_bool($group['filter'])) {
                $result = $group['filter'];
                $filter = function () use ($result): bool {
                    return $result;
                };
            } else {
                $filter = $group['filter'];
            }

            $groups[$id] = !empty($filter) ?
                array_filter($entries, $filter)
                : $entries;

            if (!empty($group['comparer'])) {
                uasort($groups[$id], $group['comparer']);
            }

            $entries = array_diff_key($entries, $groups[$id]);
        }

        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): static
    {
        if (array_key_exists('quiet', $options)) {
            $this->setQuiet($options['quiet']);
        }

        if (array_key_exists('columns', $options)) {
            $this->setColumns($options['columns']);
        }

        if (array_key_exists('groups', $options)) {
            $this->setGroups($options['groups']);
        }

        if (array_key_exists('stream', $options)) {
            $this->setStream($options['stream']);
        }

        return $this;
    }

    /**
     * @phpstan-param array<string, PamaldConsoleTableReporterColumnDef> $columns
     *
     * @phpstan-return array<string, string>
     */
    protected function buildTableRow(array $columns, LockDiffEntry $entry): array
    {
        $row = [];
        foreach ($columns as $colId => $column) {
            switch ($colId) {
                case 'name':
                    $row[$colId] = $entry->name;
                    break;

                case 'leftVersionString':
                    $row[$colId] = $this->buildTableCellVersionString($colId, $entry->left);
                    break;

                case 'rightVersionString':
                    $row[$colId] = $this->buildTableCellVersionString($colId, $entry->right);
                    break;

                case 'leftType':
                    $row[$colId] = $this->buildTableCellType($colId, $entry->left);
                    break;

                case 'rightType':
                    $row[$colId] = $this->buildTableCellType($colId, $entry->right);
                    break;

                case 'leftLink':
                    $row[$colId] = $this->buildTableCellLink($colId, $entry->left);
                    break;

                case 'rightLink':
                    $row[$colId] = $this->buildTableCellLink($colId, $entry->right);
                    break;

                case 'leftEnvironment':
                    $row[$colId] = $this->buildTableCellEnvironment($colId, $entry->left);
                    break;

                case 'rightEnvironment':
                    $row[$colId] = $this->buildTableCellEnvironment($colId, $entry->right);
                    break;

                case 'leftDirectDependency':
                    $row[$colId] = $this->buildTableCellDirectDependency($colId, $entry->left);
                    break;

                case 'rightDirectDependency':
                    $row[$colId] = $this->buildTableCellDirectDependency($colId, $entry->right);
                    break;
            }
        }

        return $row;
    }

    protected function buildTableCellVersionString(string $colId, ?DependencyInterface $package): string
    {
        // @todo String 0.
        return $package ?
            $package->versionString() ?: '?'
            : '';
    }

    protected function buildTableCellType(string $colId, ?DependencyInterface $package): string
    {
        return (string) $package?->type()?->value;
    }

    protected function buildTableCellLink(string $colId, ?DependencyInterface $package): string
    {
        return (string) $package?->link()?->value;
    }

    protected function buildTableCellEnvironment(string $colId, ?DependencyInterface $package): string
    {
        return (string) $package?->environment()?->value;
    }

    protected function buildTableCellDirectDependency(string $colId, ?DependencyInterface $package): string
    {
        if (!$package) {
            return '';
        }

        $isDirect = $package->isDirectDependency();

        return $isDirect === null ?
            '?'
            : ($isDirect ? 'direct' : 'child');
    }
}
