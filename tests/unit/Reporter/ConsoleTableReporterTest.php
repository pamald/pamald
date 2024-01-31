<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit\Reporter;

use Codeception\Attribute\DataProvider;
use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Reporter\ConsoleTableReporter;
use Sweetchuck\Utils\Filter\CustomFilter;

/**
 * @covers \Pamald\Pamald\Reporter\TableReporterBase
 * @covers \Pamald\Pamald\Reporter\ConsoleTableReporter
 */
class ConsoleTableReporterTest extends ReporterTestBase
{

    /**
     * @return array<string, mixed[]>
     */
    public static function casesGenerate(): array
    {
        $optionSets = [
            'all-in-one' => [
                'default' => [],
                'group-by-right-direct' => [
                    'groups' => [
                        'direct-prod' => [
                            'enabled' => true,
                            'id' => 'direct-prod',
                            'title' => 'Direct prod',
                            'weight' => 0,
                            'showEmpty' => false,
                            'emptyContent' => '-- empty --',
                            'filter' => (new CustomFilter())
                                ->setOperator(function (LockDiffEntry $entry): bool {
                                    return $entry->right?->isDirectDependency()
                                        && $entry->right->typeOfRelationship() === 'prod';
                                }),
                            'comparer' => null,
                        ],
                        'direct-dev' => [
                            'enabled' => true,
                            'id' => 'direct-dev',
                            'title' => 'Direct dev',
                            'weight' => 1,
                            'showEmpty' => false,
                            'emptyContent' => '-- empty --',
                            'filter' => (new CustomFilter())
                                ->setOperator(function (LockDiffEntry $entry): bool {
                                    return $entry->right?->isDirectDependency()
                                        && $entry->right->typeOfRelationship() === 'dev';
                                }),
                            'comparer' => null,
                        ],
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
                    ],
                ],
            ],
            'basic' => [
                'default' => [],
            ],
        ];

        return static::collectGenerateCases(
            ConsoleTableReporter::class,
            'ansi',
            $optionSets,
        );
    }

    /**
     * @param null|array<string, \Pamald\Pamald\PackageInterface> $leftPackages
     * @param null|array<string, \Pamald\Pamald\PackageInterface> $rightPackages
     * @param array<string, mixed> $options
     */
    #[DataProvider('casesGenerate')]
    public function testGenerate(
        string $expected,
        ?array $leftPackages = null,
        ?array $rightPackages = null,
        array $options = [],
    ): void {
        if (!isset($options['stream'])) {
            $options['stream'] = static::createStream();
        }

        $differ = new LockDiffer();
        $entries = $differ->diff($leftPackages, $rightPackages);
        (new ConsoleTableReporter())
            ->setOptions($options)
            ->generate($entries);
        rewind($options['stream']);
        $this->tester->assertSame(
            $expected,
            stream_get_contents($options['stream']),
        );
        fclose($options['stream']);
    }
}
