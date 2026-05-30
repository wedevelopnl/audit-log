<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use Override;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Recording\RecordStore;

final class InMemoryRecordStore implements RecordStore
{
    /** @var list<NewAuditRecord> */
    public array $records = [];

    #[Override]
    public function add(NewAuditRecord $record): void
    {
        $this->records[] = $record;
    }
}
