<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordReader;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordStore;
use WeDevelop\AuditLog\Infrastructure\Symfony\Http\RequestOriginResolver;
use WeDevelop\AuditLog\Infrastructure\Symfony\NullSubjectLabeller;
use WeDevelop\AuditLog\Infrastructure\Symfony\Security\SecurityActorResolver;
use WeDevelop\AuditLog\Infrastructure\Symfony\Translation\TranslatorAuditRenderer;
use WeDevelop\AuditLog\Infrastructure\Symfony\Uid\UuidIdentityGenerator;
use WeDevelop\AuditLog\Reading\AuditRenderer;
use WeDevelop\AuditLog\Reading\RecordReader;
use WeDevelop\AuditLog\Recording\ActorResolver;
use WeDevelop\AuditLog\Recording\DefaultRecorder;
use WeDevelop\AuditLog\Recording\IdentityGenerator;
use WeDevelop\AuditLog\Recording\OriginResolver;
use WeDevelop\AuditLog\Recording\Recorder;
use WeDevelop\AuditLog\Recording\RecordStore;
use WeDevelop\AuditLog\Recording\SubjectLabeller;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    $services->set(DefaultRecorder::class);
    $services->alias(Recorder::class, DefaultRecorder::class);

    $services->set(UuidIdentityGenerator::class);
    $services->alias(IdentityGenerator::class, UuidIdentityGenerator::class);

    $services->set(SecurityActorResolver::class);
    $services->alias(ActorResolver::class, SecurityActorResolver::class);

    $services->set(RequestOriginResolver::class);
    $services->alias(OriginResolver::class, RequestOriginResolver::class);

    $services->set(NullSubjectLabeller::class);
    $services->alias(SubjectLabeller::class, NullSubjectLabeller::class);

    $services->set(DoctrineRecordStore::class);
    $services->alias(RecordStore::class, DoctrineRecordStore::class);

    $services->set(DoctrineRecordReader::class);
    $services->alias(RecordReader::class, DoctrineRecordReader::class);

    $services->set(TranslatorAuditRenderer::class)
        ->arg('$domain', '%audit_log.translation_domain%');
    $services->alias(AuditRenderer::class, TranslatorAuditRenderer::class);
};
