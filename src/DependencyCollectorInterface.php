<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

interface DependencyCollectorInterface
{
    /**
     * @param null|array<mixed> $lock
     * @param null|array<mixed> $json
     *
     * @return \Pamald\Pamald\DependencyInterface[]
     */
    public function collect(?array $lock, ?array $json): array;
}
