# 11. Events may carry the actor and origin of a deferred act

- Status: Accepted
- Date: 2026-11-05

## Context

The resolvers read the moment from ambient state at call time ([ADR-0007](0007-worker-mode-ambient-resolution.md)):
the security token for the actor, the current request for the origin. An act
decided in one place and carried out in another — a request or webhook that
dispatches work to a Messenger worker, a replay of a lost record — is recorded
where neither exists. It gets no actor and the `Console` channel, so "an import a
user started in the UI" and "a console command nobody ran" look the same in the
trail. The `Job` and `Webhook` channels exist but no resolver can produce them,
because a parameterless, stateless resolver cannot know which message it is
handling.

## Decision

An event MAY implement `Recording\ProvidesActor` and/or `Recording\ProvidesOrigin`
to carry the actor and origin captured where the act was decided. Like
`ProvidesSubjectLabel`, each takes precedence over its port, which is then not
called; an event that provides a null actor is recorded without one.

The interfaces live in `Recording`, not `Event`, because `Actor` and `Origin` are
`Recording` types and `Event` depends on nothing.

The consumer MUST capture the values from the authenticated principal and the
current request — typically by calling the `ActorResolver` and `OriginResolver`
ports at the dispatch site — and carry them on the message. They MUST NOT come
from request input: the recorder stores whatever the event returns.

## Consequences

The resolvers stay stateless and ADR-0007 is unchanged; deferred acts become
attributable with the actor and channel of the moment they were decided (P3, P4).
The values travel by hand: each message that leads to an audited act carries them,
and a message that does not falls back to no actor and `Console` without any
warning. Faithful attribution is the consumer's responsibility at the dispatch
site, as the subject label already is for `ProvidesSubjectLabel`.

## Alternatives considered

- **Messenger middleware** that stamps each dispatched envelope with the resolved
  actor and origin and exposes them to the resolvers while the worker handles it.
  Automatic, but it needs per-message mutable state that ADR-0007 forbids, couples
  the bundle to Messenger, and does nothing for messages a non-PHP producer
  enqueues. Rejected for now; it can be added on top of this decision if consumers
  end up threading the values through many messages.
- **An actor and origin parameter on `Recorder::record()`.** Breaks every
  `Recorder` implementation (decorators included) and splits what is recorded
  between the call and the event. Rejected.
- **A default "system" actor instead of null.** Claims an attribution the trail
  cannot back and hides that the actor is unknown. Rejected.
