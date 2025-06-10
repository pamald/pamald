<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit\Reporter;

use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Reporter\JiraTableReporter;
use Pamald\Pamald\Reporter\TableReporterBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sweetchuck\Utils\Filter\CustomFilter;

#[CoversClass(TableReporterBase::class)]
#[CoversClass(JiraTableReporter::class)]
class JiraTableReporterTest extends ReporterTestBase
{

    /**
     * @return array<string, mixed[]>
     */
    public static function casesGenerate(): array
    {
        $optionSets = [
            'no-diff' => [
                'default' => [],
                'noisy' => [
                    'quiet' => false,
                ],
            ],
            'all-in-one' => [
                'default' => [],
            ],
            'basic' => [
                'default' => [],
            ],
        ];

        return static::collectGenerateCases(
            JiraTableReporter::class,
            'txt',
            $optionSets,
        );
    }

    /**
     * @param null|array<string, \Pamald\Pamald\DependencyInterface> $leftPackages
     * @param null|array<string, \Pamald\Pamald\DependencyInterface> $rightPackages
     * @param array<string, mixed> $options
     */
    #[Test]
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
        (new JiraTableReporter())
            ->setOptions($options)
            ->generate($entries);
        rewind($options['stream']);
        static::assertSame(
            $expected,
            stream_get_contents($options['stream']),
        );
        fclose($options['stream']);
    }
}
