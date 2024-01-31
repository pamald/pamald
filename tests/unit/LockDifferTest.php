<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Tests\Helper\DummyPackage;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Pamald\Pamald\LockDiffer
 * @covers \Pamald\Pamald\VersionAction
 */
class LockDifferTest extends TestBase
{

    /**
     * @return array<mixed>
     */
    public static function casesDiff(): array
    {
        $dataDir = codecept_data_dir('LockDiffer');
        $cases = [];
        foreach (Yaml::parseFile("$dataDir/casesDiff.yml") as $id => $raw) {
            $cases[$id] = [
                $raw['expected'],
                [],
                [],
            ];

            foreach ($raw['leftPackages'] ?? [] as $packageName => $packageValues) {
                $cases[$id][1][$packageName] = new DummyPackage($packageValues);
            }

            foreach ($raw['rightPackages'] ?? [] as $packageName => $packageValues) {
                $cases[$id][2][$packageName] = new DummyPackage($packageValues);
            }
        }

        return $cases;
    }

    /**
     * @param array<string, array<string, mixed>> $expected
     * @param array<string, \Pamald\Pamald\PackageInterface> $leftPackages
     * @param array<string, \Pamald\Pamald\PackageInterface> $rightPackages
     */
    #[DataProvider('casesDiff')]
    public function testDiff(
        array $expected,
        array $leftPackages,
        array $rightPackages,
    ): void {
        $lockDiffer = new LockDiffer();
        $actual = $lockDiffer->diff($leftPackages, $rightPackages);
        $this->tester->assertSame(
            array_keys($expected),
            array_keys($actual),
            'entries have same keys',
        );
        foreach ($expected as $key => $expectedEntry) {
            $actualEntry = json_decode(json_encode($actual[$key]) ?: '{}', true);

            unset(
                $actualEntry['left'],
                $actualEntry['right'],
            );

            $this->tester->assertSame(
                $expectedEntry,
                $actualEntry,
                "entries with key '$key' are the same",
            );
        }
    }
}
