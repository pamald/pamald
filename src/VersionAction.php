<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

enum VersionAction: string
{
    case None = 'none';

    case Upgrade = 'upgrade';

    case Downgrade = 'downgrade';

    public static function fromStrings(?string $a, ?string $b): self
    {
        if ($a === $b) {
            return self::None;
        }

        if (!$a && $b) {
            return self::Upgrade;
        }

        if ($a && !$b) {
            return self::Downgrade;
        }

        return match (version_compare($a, $b)) {
            -1 => self::Upgrade,
            0 => self::None,
            default => self::Downgrade,
        };
    }
}
