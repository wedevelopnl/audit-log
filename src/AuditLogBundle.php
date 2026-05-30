<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog;

use Override;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WeDevelop\AuditLog\DependencyInjection\AuditLogExtension;

final class AuditLogBundle extends Bundle
{
    #[Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new AuditLogExtension();
    }
}
