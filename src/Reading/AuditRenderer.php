<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use WeDevelop\AuditLog\Event\RenderPayload;

/**
 * Renders a frozen RenderPayload into localized strings. Framework-free contract;
 * the default implementation lives in Infrastructure\Symfony.
 *
 * The output is plain text that interpolates recorded values (labels, which may
 * originate from user input): escape it wherever it is rendered as markup.
 */
interface AuditRenderer
{
    public function renderMessage(RenderPayload $payload, ?string $locale = null): string;

    /** @return list<string> */
    public function renderInfo(RenderPayload $payload, ?string $locale = null): array;
}
