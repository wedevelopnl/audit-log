<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

interface RecordStore
{
    public function add(NewAuditRecord $record): void;
}
