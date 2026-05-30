<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony\Translation;

use Override;
use Symfony\Contracts\Translation\TranslatorInterface;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Reading\AuditRenderer;

final readonly class TranslatorAuditRenderer implements AuditRenderer
{
    public function __construct(
        private TranslatorInterface $translator,
        private string $domain,
    ) {
    }

    #[Override]
    public function renderMessage(RenderPayload $payload, ?string $locale = null): string
    {
        return $this->line($payload->message, $locale);
    }

    /** @return list<string> */
    #[Override]
    public function renderInfo(RenderPayload $payload, ?string $locale = null): array
    {
        return array_map(fn (RenderLine $line): string => $this->line($line, $locale), $payload->info);
    }

    private function line(RenderLine $line, ?string $locale): string
    {
        return $this->translator->trans($line->translationKey, $line->parameters, $this->domain, $locale);
    }
}
