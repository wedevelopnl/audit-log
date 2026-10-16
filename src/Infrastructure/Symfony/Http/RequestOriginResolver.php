<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony\Http;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Recording\Origin;
use WeDevelop\AuditLog\Recording\OriginResolver;

/**
 * Default origin resolution, read live at call time. Any current request is
 * recorded as Ui, API calls and webhooks included; no request means Console,
 * Messenger workers included. Api, Job and Webhook are never produced here, and
 * records cannot be corrected later, so applications that need them override
 * this port (e.g. by firewall or route) before recording.
 */
final readonly class RequestOriginResolver implements OriginResolver
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Override]
    public function resolve(): Origin
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return new Origin(AuditChannel::Console, null);
        }

        return new Origin(AuditChannel::Ui, $request->getClientIp());
    }
}
