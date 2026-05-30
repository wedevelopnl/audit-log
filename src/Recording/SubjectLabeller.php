<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\Subject;

/**
 * Produces a display label for the live subject, snapshotted at the moment so
 * the trail stays readable after the subject is deleted. Returns null when no
 * label can be resolved.
 */
interface SubjectLabeller
{
    public function label(?Subject $subject): ?string;
}
