<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit\Reporter;

use Codeception\Attribute\DataProvider;
use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Reporter\JsonReporter;

/**
 * @covers \Pamald\Pamald\Reporter\JsonReporter
 * @covers \Pamald\Pamald\LockDiffEntry
 * @covers \Pamald\Pamald\PackageJsonSerializerTrait
 */
class JsonReporterTest extends ReporterTestBase
{

    /**
     * @return array<string, mixed[]>
     */
    public static function casesGenerate(): array
    {
        return static::collectGenerateCases(
            JsonReporter::class,
            'json',
            [
                'basic' => [
                    'default' => [],
                    'flags' => [
                        'jsonEncodeFlags' => \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
                    ],
                ],
            ],
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
        (new JsonReporter())
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
