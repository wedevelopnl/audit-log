<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Appends a record to the trail. Append-only: there is no update or delete.
 */
interface RecordStore
{
    public function add(NewAuditRecord $record): void;
}
