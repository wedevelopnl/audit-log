<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use WeDevelop\AuditLog\Infrastructure\Doctrine\AuditRecordEntity;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;

final class EntityManagerFactory
{
    public static function createWithSchema(): EntityManagerInterface
    {
        if (!Type::hasType(ChangesetType::NAME)) {
            Type::addType(ChangesetType::NAME, ChangesetType::class);
        }
        if (!Type::hasType(RenderPayloadType::NAME)) {
            Type::addType(RenderPayloadType::NAME, RenderPayloadType::class);
        }

        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__.'/../../../src/Infrastructure/Doctrine'],
            isDevMode: true,
        );
        // PHP 8.5 ships native lazy objects; ORM 3 needs them enabled explicitly
        // (it is the only supported proxy mechanism in the upcoming ORM 4).
        $config->enableNativeLazyObjects(true);
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
        $entityManager = new EntityManager($connection, $config);

        new SchemaTool($entityManager)->createSchema([
            $entityManager->getClassMetadata(AuditRecordEntity::class),
        ]);

        return $entityManager;
    }
}
