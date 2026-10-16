<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WeDevelop\AuditLog\AuditLogBundle;
use WeDevelop\AuditLog\DependencyInjection\AuditLogExtension;
use WeDevelop\AuditLog\DependencyInjection\Configuration;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;
use WeDevelop\AuditLog\Infrastructure\Symfony\Translation\TranslatorAuditRenderer;

use function dirname;

#[CoversClass(AuditLogBundle::class)]
#[CoversClass(AuditLogExtension::class)]
#[CoversClass(Configuration::class)]
final class AuditLogExtensionTest extends TestCase
{
    public function testTheBundleProvidesTheAuditLogExtension(): void
    {
        $extension = new AuditLogBundle()->getContainerExtension();

        self::assertInstanceOf(AuditLogExtension::class, $extension);
        self::assertSame('audit_log', $extension->getAlias());
    }

    public function testRendersInTheAuditDomainByDefault(): void
    {
        self::assertSame('audit', $this->load([])->getParameter('audit_log.translation_domain'));
    }

    public function testRendersInTheConfiguredDomain(): void
    {
        $container = $this->load([['translation_domain' => 'admin_audit']]);

        self::assertSame('admin_audit', $container->getParameter('audit_log.translation_domain'));
        self::assertSame(
            '%audit_log.translation_domain%',
            $container->getDefinition(TranslatorAuditRenderer::class)->getArgument('$domain'),
        );
    }

    public function testRejectsAnEmptyTranslationDomain(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load([['translation_domain' => '']]);
    }

    public function testPrependsTheDoctrineTypesAndEntityMapping(): void
    {
        $container = new ContainerBuilder();
        new AuditLogExtension()->prepend($container);

        self::assertSame([[
            'dbal' => [
                'types' => [
                    ChangesetType::NAME => ChangesetType::class,
                    RenderPayloadType::NAME => RenderPayloadType::class,
                ],
            ],
            'orm' => [
                'mappings' => [
                    'AuditLog' => [
                        'type' => 'attribute',
                        'dir' => dirname(__DIR__, 2).'/src/DependencyInjection/../Infrastructure/Doctrine',
                        'prefix' => 'WeDevelop\\AuditLog\\Infrastructure\\Doctrine',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]], $container->getExtensionConfig('doctrine'));
    }

    /** @param list<array<string, mixed>> $configs */
    private function load(array $configs): ContainerBuilder
    {
        $container = new ContainerBuilder();
        new AuditLogExtension()->load($configs, $container);

        return $container;
    }
}
