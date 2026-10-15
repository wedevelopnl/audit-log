<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
