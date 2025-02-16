<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit;

use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Tests\Helper\DummyPackage;
use Pamald\Pamald\VersionAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(LockDiffer::class)]
#[CoversClass(VersionAction::class)]
class LockDifferTest extends TestBase
{

    /**
     * @return array<mixed>
     */
    public static function casesDiff(): array
    {
        $dataDir = static::getFixturesDir() . '/LockDiffer';
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
    #[Test]
    #[DataProvider('casesDiff')]
    public function testDiff(
        array $expected,
        array $leftPackages,
        array $rightPackages,
    ): void {
        $lockDiffer = new LockDiffer();
        $actual = $lockDiffer->diff($leftPackages, $rightPackages);
        static::assertSame(
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

            static::assertSame(
                $expectedEntry,
                $actualEntry,
                "entries with key '$key' are the same",
            );
        }
    }
}
