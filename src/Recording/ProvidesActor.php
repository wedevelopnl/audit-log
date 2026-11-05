<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Opt-in: the act carries its actor, captured where the act was decided (e.g. a
 * request that dispatched the work to a Messenger worker, where no principal is
 * logged in). Takes precedence over the ActorResolver port, which is then not
 * called; null records the act without an actor.
 *
 * Capture the actor from the authenticated principal, never from request input:
 * the recorder stores whatever the event returns.
 */
interface ProvidesActor
{
    public function actor(): ?Actor;
}
