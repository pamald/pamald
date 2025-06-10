<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

enum DependencyEnvironment: string
{

    case Production = 'production';

    case Development = 'development';
}
