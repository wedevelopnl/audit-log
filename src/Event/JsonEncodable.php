<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

use InvalidArgumentException;
use JsonException;

use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * Everything an audit record freezes is stored as JSON at flush time, inside the
 * audited transaction. A value json_encode() rejects (NAN, INF, invalid UTF-8)
 * would abort that flush and roll back the audited change, so it is rejected
 * where it enters instead.
 *
 * @internal
 */
final class JsonEncodable
{
    public static function assert(mixed $value, string $what): void
    {
        try {
            json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(sprintf('%s must be JSON-encodable: %s.', $what, $e->getMessage()), previous: $e);
        }
    }
}
