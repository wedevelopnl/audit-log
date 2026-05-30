<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony\Security;

use Override;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use WeDevelop\AuditLog\Recording\Actor;
use WeDevelop\AuditLog\Recording\ActorResolver;

/**
 * Default actor resolution from the security token, read live at call time
 * (worker-mode safe). Uses the user identifier for both id and label; consumers
 * override this port to provide a richer label (e.g. name + email).
 */
final readonly class SecurityActorResolver implements ActorResolver
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    #[Override]
    public function resolve(): ?Actor
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        $identifier = $user->getUserIdentifier();

        return new Actor($identifier, $identifier);
    }
}
