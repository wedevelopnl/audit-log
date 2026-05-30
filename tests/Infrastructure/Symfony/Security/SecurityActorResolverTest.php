<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Symfony\Security;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;
use WeDevelop\AuditLog\Infrastructure\Symfony\Security\SecurityActorResolver;
use WeDevelop\AuditLog\Recording\Actor;

#[CoversClass(SecurityActorResolver::class)]
final class SecurityActorResolverTest extends TestCase
{
    public function testResolvesNullWhenNoTokenIsSet(): void
    {
        $storage = new TokenStorage();
        $resolver = new SecurityActorResolver($storage);

        // No token (e.g. CLI/worker boot) — resolved live, so null.
        self::assertNull($resolver->resolve());
    }

    public function testReadsTheCurrentTokenAtCallTimeNotConstructionTime(): void
    {
        $storage = new TokenStorage();
        $resolver = new SecurityActorResolver($storage);

        $storage->setToken($this->tokenFor('jan@example.com'));
        $jan = $resolver->resolve();
        self::assertInstanceOf(Actor::class, $jan);
        self::assertSame('jan@example.com', $jan->id);

        // Change the ambient token; the resolver must reflect it (no captured state).
        $storage->setToken($this->tokenFor('ana@example.com'));
        $ana = $resolver->resolve();
        self::assertInstanceOf(Actor::class, $ana);
        self::assertSame('ana@example.com', $ana->label);
    }

    private function tokenFor(string $identifier): TokenInterface
    {
        return new UsernamePasswordToken(new InMemoryUser($identifier, null), 'main');
    }
}
