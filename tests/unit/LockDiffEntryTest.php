<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\PackageInterface;
use Pamald\Pamald\RelationshipAction;
use Pamald\Pamald\Tests\Helper\DummyPackage;
use Pamald\Pamald\VersionAction;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Pamald\Pamald\LockDiffEntry
 * @covers \Pamald\Pamald\VersionAction
 */
class LockDiffEntryTest extends TestBase
{

    /**
     * @return array<mixed>
     */
    public static function casesConstructor(): array
    {
        $dataDir = codecept_data_dir('LockDiffEntry');
        $cases = [];
        foreach (Yaml::parseFile("$dataDir/casesConstruct.yml") as $id => $data) {
            if (isset($data['expected']['relationshipAction'])) {
                $data['expected']['relationshipAction'] = RelationshipAction::from(
                    $data['expected']['relationshipAction'],
                );
            }

            if (isset($data['expected']['versionAction'])) {
                $data['expected']['versionAction'] = VersionAction::from(
                    $data['expected']['versionAction'],
                );
            }

            $cases[$id] = [
                $data['expected'],
                isset($data['left']) ? new DummyPackage($data['left']) : null,
                isset($data['right']) ? new DummyPackage($data['right']) : null,
            ];
        }
        return $cases;
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     */
    #[DataProvider('casesConstructor')]
    public function testConstructor(
        array $expected,
        ?PackageInterface $left = null,
        ?PackageInterface $right = null,
    ): void {
        $actual = new LockDiffEntry($left, $right);

        static::assertTrue(true);

        foreach ($expected as $property => $expectedValue) {
            static::assertSame(
                $expectedValue,
                $actual->{$property},
                "\$actual->$property is good",
            );
        }
    }
}
