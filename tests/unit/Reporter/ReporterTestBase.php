<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Tests\Unit\Reporter;

use Pamald\Pamald\Tests\Helper\DummyPackage;
use Pamald\Pamald\Tests\Unit\TestBase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @todo Test case: Call the same reporter instance twice with different inputs.
 */
class ReporterTestBase extends TestBase
{

    /**
     * @return resource
     */
    protected static function createStream()
    {
        $filePath = 'php://memory';
        $resource = fopen($filePath, 'rw');
        if ($resource === false) {
            throw new \RuntimeException("file $filePath could not be opened");
        }

        return $resource;
    }

    /**
     * @phpstan-param array<string, array<string, mixed>> $optionSets
     *
     * @phpstan-return array<string, mixed[]>
     */
    protected static function collectGenerateCases(
        string $classFqn,
        string $extension,
        array $optionSets = [],
    ): array {
        $classParts = explode('\\', $classFqn);
        $class = end($classParts);
        $dataDir = codecept_data_dir('Reporter');
        $inputs = Yaml::parseFile("$dataDir/cases.yml");
        $expectedFiles = (new Finder())
            ->in("$dataDir/$class")
            ->name("*.$extension");
        $cases = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $expectedFile */
        foreach ($expectedFiles as $expectedFile) {
            $caseName = $expectedFile->getFilenameWithoutExtension();
            [$dataSetName, $optionSetName] = explode('.', $caseName, 2);
            $cases[$caseName] = [
                'expected' => file_get_contents($expectedFile->getPathname()),
                'leftPackages' => [],
                'rightPackages' => [],
            ];

            foreach ($inputs[$dataSetName]['leftPackages'] ?? [] as $packageName => $packageRaw) {
                $cases[$caseName]['leftPackages'][$packageName] = new DummyPackage($packageRaw);
            }

            foreach ($inputs[$dataSetName]['rightPackages'] ?? [] as $packageName => $packageRaw) {
                $cases[$caseName]['rightPackages'][$packageName] = new DummyPackage($packageRaw);
            }

            $cases[$caseName] += $inputs[$dataSetName];
            $cases[$caseName]['options'] = $optionSets[$dataSetName][$optionSetName] ?? [];
        }

        return $cases;
    }
}
