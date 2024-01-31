<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Reporter;

use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\PackageInterface;
use Pamald\Pamald\ReporterInterface;
use Sweetchuck\Utils\Comparer\ArrayValueComparer;
use Sweetchuck\Utils\Filter\EnabledFilter;

abstract class TableReporterBase implements ReporterInterface
{
    use StreamOutputTrait;

    /**
     * @phpstan-var array<string, \Pamald\Pamald\LockDiffEntry>
     */
    public array $entries;

    /**
     * @phpstan-var array<string, pamald-console-table-reporter-column-def>
     */
    protected array $columns = [
        'name' => [
            'enabled' => true,
            'weight' => 0,
            'align' => 'left',
            'title' => 'Name',
        ],
        'leftVersionString' => [
            'enabled' => true,
            'weight' => 1,
            'align' => 'left',
            'title' => 'L Version',
        ],
        'rightVersionString' => [
            'enabled' => true,
            'weight' => 2,
            'align' => 'left',
            'title' => 'R Version',
            'config' => [
                'showDirection' => true,
            ],
        ],
        'leftTypeOfRelationship' => [
            'enabled' => true,
            'weight' => 3,
            'align' => 'left',
            'title' => 'L Relationship',
            'config' => [
                'showDirection' => true,
            ],
        ],
        'rightTypeOfRelationship' => [
            'enabled' => true,
            'weight' => 4,
            'align' => 'left',
            'title' => 'R Relationship',
            'config' => [
                'showDirection' => true,
            ],
        ],
        'leftDirectDependency' => [
            'enabled' => true,
            'weight' => 5,
            'align' => 'left',
            'title' => 'L Depth',
        ],
        'rightDirectDependency' => [
            'enabled' => true,
            'weight' => 6,
            'align' => 'left',
            'title' => 'R Depth',
        ],
    ];

    /**
     * @phpstan-return array<string, pamald-console-table-reporter-column-def>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @phpstan-param array<string, pamald-console-table-reporter-column-def> $columns
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    protected function normalizeColumns(): static
    {
        $columns = array_filter(
            $this->getColumns(),
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
     * @phpstan-var array<string, pamald-console-table-reporter-group-def>
     */
    protected array $groups = [
        'other' => [
            'enabled' => true,
            'id' => 'other',
            'title' => 'Other',
            'weight' => 999,
            'showEmpty' => false,
            'emptyContent' => '-- empty --',
            'filter' => null,
            'comparer' => null,
        ],
    ];

    /**
     * @phpstan-return array<string, pamald-console-table-reporter-group-def>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @phpstan-param array<string, pamald-console-table-reporter-group-def> $groups
     */
    public function setGroups(array $groups): static
    {
        $this->groups = $groups;

        return $this;
    }

    protected function normalizeGroups(): static
    {
        $groups = array_filter(
            $this->getGroups(),
            new EnabledFilter(),
        );

        uasort(
            $groups,
            (new ArrayValueComparer())
                ->setKeys([
                    'weight' => [
                        'default' => 0,
                    ],
                ]),
        );

        foreach ($groups as $id => &$group) {
            $group['id'] = $id;
        }

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
     * @phpstan-param array<string, pamald-console-table-reporter-column-def> $columns
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

                case 'leftTypeOfRelationship':
                    $row[$colId] = $this->buildTableCellTypeOfRelationship($colId, $entry->left);
                    break;

                case 'rightTypeOfRelationship':
                    $row[$colId] = $this->buildTableCellTypeOfRelationship($colId, $entry->right);
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

    protected function buildTableCellVersionString(string $colId, ?PackageInterface $package): string
    {
        // @todo String 0.
        return $package ?
            $package->versionString() ?: '?'
            : '';
    }

    protected function buildTableCellTypeOfRelationship(string $colId, ?PackageInterface $package): string
    {
        return $package ?
            $package->typeOfRelationship() ?: '?'
            : '';
    }

    protected function buildTableCellDirectDependency(string $colId, ?PackageInterface $package): string
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
