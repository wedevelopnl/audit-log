<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\Subject;

/**
 * Snapshots a display label for the subject at record time, so the trail
 * survives the subject's deletion. Not consulted for events implementing
 * ProvidesSubjectLabel: their own label wins.
 */
interface SubjectLabeller
{
    public function label(?Subject $subject): ?string;
}
