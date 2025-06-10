<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Reporter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * @todo Configurable TableStyle.
 *
 * @todo
 *   Configurable column content.
 *   - text vs UTF-8 chars or for boolean values.
 *   - Upgrade/Downgrade indicator arrows.
 *
 * @phpstan-import-type PamaldConsoleTableReporterOptions from \Pamald\Pamald\Phpstan
 */
class ConsoleTableReporter extends TableReporterBase
{

    use StreamOutputTrait;

    protected ?Table $table = null;

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function getDefaultTable(): Table
    {
        return new Table(new StreamOutput($this->getStream()));
    }

    /**
     * @phpstan-param PamaldConsoleTableReporterOptions $options
     */
    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('table', $options)) {
            $this->setTable($options['table']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $entries): static
    {
        $this->entries = $entries;
        if (!$this->table) {
            $this->table = $this->getDefaultTable();
        }
        $this->table->setRows([]);

        $stats = $this
            ->normalizeColumns()
            ->normalizeGroups()
            ->setTableHeaders()
            ->setTableRows();

        if ($stats['isEmpty'] && $this->getQuiet()) {
            return $this;
        }

        $this
            ->table
            ->render();

        return $this;
    }

    protected function setTableHeaders(): static
    {
        $this->table->setHeaders(array_column(
            $this->getColumns(),
            'title',
            'id',
        ));

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function setTableRows(): array
    {
        $stats = [
            'isEmpty' => true,
        ];
        $columns = $this->getColumns();
        $groupDefs = $this->getGroups();
        $groups = $this->groupEntries();

        foreach ($groups as $groupId => $entries) {
            $groupDef = $groupDefs[$groupId];
            if (!$entries && $groupDef['showEmpty']) {
                $stats['isEmpty'] = false;
                $this->table->addRow([
                    new TableCell(
                        $groupDef['title'],
                        [
                            'colspan' => count($columns),
                            // @todo Use different color.
                        ],
                    ),
                ]);
                $this->table->addRow([
                    new TableCell(
                        $groupDef['emptyContent'],
                        [
                            'colspan' => count($columns),
                            // @todo Use different color.
                        ],
                    ),
                ]);

                continue;
            }

            if ($entries && $groupDef['showHeaderForNonEmpty']) {
                $stats['isEmpty'] =  false;
                $this->table->addRow([
                    new TableCell(
                        $groupDef['title'] ?: $groupDef['id'],
                        [
                            'colspan' => count($columns),
                            // @todo Use different color.
                        ],
                    ),
                ]);
            }

            foreach ($entries as $entry) {
                $stats['isEmpty'] = false;
                $this->table->addRow($this->buildTableRow($columns, $entry));
            }
        }

        return $stats;
    }
}
