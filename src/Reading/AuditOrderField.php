<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * The stored columns a trail can be ordered by. The rendered message is not
 * one: it is produced at read time, so the database cannot sort on it.
 *
 * Rows without an actor or subject label sort where the database puts NULLs
 * (Postgres: last ascending, first descending).
 */
enum AuditOrderField: string
{
    case OccurredAt = 'occurredAt';
    case Code = 'code';
    case Channel = 'channel';
    case Actor = 'actor';
    case Subject = 'subject';
}
