# Installation

```bash
composer require wedevelopnl/audit-log
```

There is no Flex recipe; register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    WeDevelop\AuditLog\AuditLogBundle::class => ['all' => true],
];
```

The bundle auto-registers its Doctrine DBAL types and the `AuditRecordEntity`
mapping, so the `audit_record` table appears in your schema diff like any entity of
your own. The package does not ship migrations; generate one with your usual tool,
for example with DoctrineMigrationsBundle:

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

Without migrations, `bin/console doctrine:schema:update --dump-sql` prints the DDL.
Then (recommended) grant the application role `INSERT`/`SELECT` only —
`REVOKE UPDATE, DELETE` — to enforce append-only at the database layer.

## Requirements

The default port implementations autowire framework services, so the consuming
application must have these subsystems enabled (the standard Symfony full-stack
setup already does):

- the **security** component — `SecurityActorResolver` injects `TokenStorageInterface`;
- the **translator** (`framework.translator`) — `TranslatorAuditRenderer` injects `TranslatorInterface`.

If either is disabled, container compilation fails with an autowiring error. Re-alias
the affected port (`ActorResolver` / `AuditRenderer`) to your own implementation to
drop the dependency.

## What you provide

- A `SubjectLabeller` implementation (the default returns no label). Alias the
  port to your service:
  `WeDevelop\AuditLog\Recording\SubjectLabeller: '@App\Audit\MySubjectLabeller'`.
  An event that implements `ProvidesSubjectLabel` supplies its own label (for a
  deleted subject, say) and the labeller is not called for it.
- Optionally override `ActorResolver` for richer actor labels.
- Optionally override `OriginResolver` for the channel. The default records every
  HTTP request as `ui` (API calls and webhooks included) and everything else as
  `console` (Messenger workers included). Records are immutable, so decide this
  before you go live:

  ```php
  final readonly class AppOriginResolver implements OriginResolver
  {
      public function __construct(private RequestStack $requests) {}

      public function resolve(): Origin
      {
          $request = $this->requests->getCurrentRequest();
          if (null === $request) {
              return new Origin(AuditChannel::Console, null);
          }

          $channel = str_starts_with($request->getPathInfo(), '/api/') ? AuditChannel::Api : AuditChannel::Ui;

          return new Origin($channel, $request->getClientIp());
      }
  }
  ```

  Alias it like the labeller:
  `WeDevelop\AuditLog\Recording\OriginResolver: '@App\Audit\AppOriginResolver'`.
- One `AuditEvent` (extend `AbstractAuditEvent`) per auditable action, and a
  listener that calls `Recorder::record()`.

## Flushing

`Recorder::record()` persists the record without flushing; it commits with the
application's next `EntityManager::flush()`, atomically with the audited change.
Call `record()` before that flush. Places where no flush follows lose the record:

- `kernel.terminate` / `console.terminate` listeners;
- Doctrine `postFlush` listeners;
- anything after an `EntityManager::clear()` or reset.

The bundle logs an error (`{count} audit record(s) were recorded but never flushed
and are lost.`, with the action codes in the context) on `kernel.terminate` and
`console.terminate` when records are still waiting to be flushed. In a Messenger
handler, record before the handler's flush; worker message boundaries are not
watched. See [ADR-0010](adr/0010-records-commit-with-the-callers-flush.md).

## Recording away from the request

The default resolvers read the security token and the current request when
`record()` is called. Where neither exists — a console command, a Messenger
handler, an anonymous request — the record gets no actor, and outside a request
the `console` channel. For a console command or an anonymous visitor that is the
truth. For work a request dispatched to a worker it is not: the act was decided by
whoever made that request.

Capture the actor and origin while the request is still current, carry them on the
message, and let the event provide them. An event implementing `ProvidesActor` or
`ProvidesOrigin` replaces the matching resolver
([ADR-0011](adr/0011-events-carry-the-actor-and-origin-of-a-deferred-act.md)):

```php
// In the controller or webhook endpoint
$bus->dispatch(new ImportFeed($runId, $actorResolver->resolve(), $originResolver->resolve()));

// The event
final readonly class FeedImported extends AbstractAuditEvent implements ProvidesActor, ProvidesOrigin
{
    public function __construct(
        private string $runId,
        private ?Actor $actor,
        private Origin $origin,
    ) {
    }

    public function code(): string
    {
        return 'feed.imported';
    }

    public function actor(): ?Actor
    {
        return $this->actor;
    }

    public function origin(): Origin
    {
        return $this->origin;
    }
}

// In the message handler
$recorder->record(new FeedImported($message->runId, $message->actor, $message->origin));
$entityManager->flush();
```

Take the actor from the authenticated principal, never from the request body: the
recorder stores whatever the event returns. To record the dispatching request
under another channel, build the origin yourself, e.g.
`new Origin(AuditChannel::Webhook, $origin->ipAddress)`.

A machine caller authenticated through a Symfony authenticator resolves like a
user: `SecurityActorResolver` uses its user identifier for both id and label. The
trail stores no actor kind, so give such principals an identifier that reads as a
machine, such as `machine:billing-sync`.

## Configuration

```yaml
audit_log:
    translation_domain: audit   # default
```

## Rendering

`AuditRenderer` translates each record's frozen `RenderPayload` in the configured
domain. A message key defaults to the event's action code (override
`AbstractAuditEvent::messageKey()` to change it), so every code needs an entry in
that domain, or the raw key renders:

```yaml
# translations/audit+intl-icu.en.yaml — ICU placeholders match the parameter
# keys returned by parameters(), e.g. ['from' => ..., 'to' => ...]
user.role_changed: 'Role changed from {from} to {to}'
```

The rendered strings are plain text that interpolates recorded values such as actor
and subject labels, which may come from user input. Escape them in the view: Twig's
autoescaping does this, so never pass them through `|raw`.
