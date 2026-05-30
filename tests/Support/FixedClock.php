<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;

final readonly class FixedClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
