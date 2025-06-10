<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

enum DependencyType: string
{
    case Platform = 'platform';

    case API = 'api';

    case Package = 'package';

    case Peer = 'peer';
}
