<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony\Http;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Recording\Origin;
use WeDevelop\AuditLog\Recording\OriginResolver;

/**
 * Default origin resolution, read live at call time. A current request means a
 * web channel; its absence means console/worker. Consumers override this port
 * for finer channel logic (e.g. distinguishing API from UI by firewall/route).
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
