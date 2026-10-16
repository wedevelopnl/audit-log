<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\AuditRecordEntity;
use WeDevelop\AuditLog\Infrastructure\Doctrine\UnflushedRecordWarner;
use WeDevelop\AuditLog\Reading\AuditQuery;
use WeDevelop\AuditLog\Reading\AuditRenderer;
use WeDevelop\AuditLog\Reading\RecordReader;
use WeDevelop\AuditLog\Recording\Recorder;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserDeletedEvent;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserRoleChangedEvent;

use function is_array;

/**
 * Each test boots a debug kernel in its own process: FrameworkBundle::boot()
 * registers global error and exception handlers that kernel shutdown never
 * removes, and only a fresh process leaves PHPUnit's handler state untouched.
 */
#[CoversNothing]
#[RunTestsInSeparateProcesses]
final class AuditLogBundleTest extends KernelTestCase
{
    #[Override]
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
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

    public function testRendersThroughTheTranslatorInTheDefaultAuditDomain(): void
    {
        self::bootKernel();

        /** @var AuditRenderer $renderer */
        $renderer = self::getContainer()->get('test.renderer');

        self::assertSame(
            'Deleted jan@example.com',
            $renderer->renderMessage(new RenderPayload(new RenderLine('user.deleted', ['%email%' => 'jan@example.com'])), 'en'),
        );
    }

    public function testListensForUnflushedRecordsOnBothTerminateEvents(): void
    {
        self::bootKernel();

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = self::getContainer()->get('event_dispatcher');

        foreach (['kernel.terminate', 'console.terminate'] as $event) {
            $warners = array_filter(
                $dispatcher->getListeners($event),
                static fn (mixed $listener): bool => is_array($listener) && $listener[0] instanceof UnflushedRecordWarner,
            );
            self::assertCount(1, $warners, $event);
        }
    }
}
