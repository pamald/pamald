<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

enum RelationshipAction: string
{
    case None = 'none';

    case Add = 'add';

    case Remove = 'remove';

    case Change = 'change';
}
