<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Symfony\Translation;

use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Symfony\Translation\TranslatorAuditRenderer;

use function sprintf;

#[CoversClass(TranslatorAuditRenderer::class)]
final class TranslatorAuditRendererTest extends TestCase
{
    public function testRendersMessageAndInfoUsingTheConfiguredDomain(): void
    {
        $renderer = new TranslatorAuditRenderer($this->echoTranslator(), 'audit');
        $payload = new RenderPayload(
            new RenderLine('user.deleted', ['email' => 'jan@example.com']),
            [new RenderLine('user.deleted.note')],
        );

        self::assertSame('user.deleted|audit|null|email=jan@example.com', $renderer->renderMessage($payload));
        self::assertSame(['user.deleted.note|audit|null|'], $renderer->renderInfo($payload));
    }

    public function testThreadsTheRequestedLocaleThroughBothMethods(): void
    {
        $renderer = new TranslatorAuditRenderer($this->echoTranslator(), 'audit');
        $payload = new RenderPayload(
            new RenderLine('user.deleted', ['email' => 'jan@example.com']),
            [new RenderLine('user.deleted.note')],
        );

        self::assertSame('user.deleted|audit|nl|email=jan@example.com', $renderer->renderMessage($payload, 'nl'));
        self::assertSame(['user.deleted.note|audit|nl|'], $renderer->renderInfo($payload, 'nl'));
    }

    private function echoTranslator(): TranslatorInterface
    {
        return new class implements TranslatorInterface {
            /** @param array<string, scalar> $parameters */
            #[Override]
            public function trans(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                $pairs = [];
                foreach ($parameters as $key => $value) {
                    $pairs[] = $key.'='.(string) $value;
                }

                return sprintf('%s|%s|%s|%s', (string) $id, $domain ?? 'null', $locale ?? 'null', implode(',', $pairs));
            }

            #[Override]
            public function getLocale(): string
            {
                return 'en';
            }
        };
    }
}
