<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpKernel\KernelInterface;
use WeDevelop\AuditLog\Infrastructure\Doctrine\AuditRecordEntity;
use WeDevelop\AuditLog\Reading\AuditQuery;
use WeDevelop\AuditLog\Reading\RecordReader;
use WeDevelop\AuditLog\Recording\Recorder;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserDeletedEvent;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserRoleChangedEvent;

use function is_array;

#[CoversNothing]
final class AuditLogBundleTest extends KernelTestCase
{
    #[Override]
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * Boot without debug. In debug mode FrameworkBundle re-pushes a handler on top
     * of Symfony's ErrorHandler, burying it below PHPUnit's own handler and making
     * it impossible to pop cleanly. Non-debug leaves Symfony's handler on top of the
     * stack so the test can restore the global state it disturbed. The wiring this
     * test exercises does not depend on the debug toolchain.
     *
     * @param array<string, mixed> $options
     */
    #[Override]
    protected static function createKernel(array $options = []): KernelInterface
    {
        $options['debug'] ??= false;

        return parent::createKernel($options);
    }

    public function testRecordsAndReadsBackThroughTheWiredContainer(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('test.em');
        new SchemaTool($em)->createSchema([$em->getClassMetadata(AuditRecordEntity::class)]);

        /** @var Recorder $recorder */
        $recorder = $container->get('test.recorder');
        // Tombstone event (carries its own subject label); no token => null actor.
        $recorder->record(new UserDeletedEvent('user-1', 'jan@example.com'));
        // Non-tombstone event => the NullSubjectLabeller default is exercised.
        $recorder->record(new UserRoleChangedEvent('user-2', 'member', 'admin'));
        $em->flush();

        /** @var RecordReader $reader */
        $reader = $container->get('test.reader');
        $page = $reader->page(new AuditQuery());

        self::assertSame(2, $page->total);

        // Index into entries only after asserting the count, so a zero-row regression
        // fails as a legible assertion rather than an opaque undefined-key warning
        // (the suite runs with failOnWarning/failOnNotice).
        $deletedEntries = $reader->page(new AuditQuery(code: 'user.deleted'))->entries;
        self::assertCount(1, $deletedEntries);
        $deleted = $deletedEntries[0];
        self::assertSame('jan@example.com', $deleted->subjectLabel);
        self::assertNull($deleted->actorId);

        $roleChangedEntries = $reader->page(new AuditQuery(code: 'user.role_changed'))->entries;
        self::assertCount(1, $roleChangedEntries);
        $roleChanged = $roleChangedEntries[0];
        self::assertNull($roleChanged->subjectLabel); // NullSubjectLabeller returned null
        self::assertSame('role', $roleChanged->changes?->fields[0]->field);
    }

    /**
     * FrameworkBundle::boot() installs Symfony's global error and exception handlers
     * (ErrorHandler::register) outside SymfonyRuntime and never removes them on kernel
     * shutdown. parent::tearDown() shuts the kernel down; popping the leaked handlers
     * afterwards keeps PHPUnit's global-state check from flagging the test as risky.
     */
    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::popSymfonyErrorHandlers();
    }

    /**
     * Pop every handler whose callable is Symfony's ErrorHandler off the top of the
     * global error and exception handler stacks, restoring them to the state PHPUnit
     * snapshotted before the kernel booted.
     */
    private static function popSymfonyErrorHandlers(): void
    {
        while (self::topHandlerIsSymfony(self::peekHandler('error'))) {
            restore_error_handler();
        }

        while (self::topHandlerIsSymfony(self::peekHandler('exception'))) {
            restore_exception_handler();
        }
    }

    /**
     * Inspect the top of the requested handler stack without mutating it. Probing
     * with set_*_handler(null) pushes a throwaway entry whose return value is the
     * real top handler; restoring immediately pops the throwaway again.
     */
    private static function peekHandler(string $kind): mixed
    {
        if ('error' === $kind) {
            $handler = set_error_handler(null);
            restore_error_handler();

            return $handler;
        }

        $handler = set_exception_handler(null);
        restore_exception_handler();

        return $handler;
    }

    private static function topHandlerIsSymfony(mixed $handler): bool
    {
        return is_array($handler) && ($handler[0] ?? null) instanceof ErrorHandler;
    }
}
