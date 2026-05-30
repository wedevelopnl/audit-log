<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\Subject;

/**
 * Snapshots a display label for the subject at record time, so the trail
 * survives the subject's deletion.
 */
interface SubjectLabeller
{
    public function label(?Subject $subject): ?string;
}
