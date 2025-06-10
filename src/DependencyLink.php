<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

enum DependencyLink: string
{

    case Required = 'required';

    case Optional = 'optional';
}
