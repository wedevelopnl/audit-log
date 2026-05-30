<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * Opt-in: the act itself carries the subject label (e.g. a deletion, where the
 * subject is already gone at the moment of recording).
 */
interface ProvidesSubjectLabel
{
    public function subjectLabel(): ?string;
}
