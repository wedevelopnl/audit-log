<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;

final class AuditLogExtension extends Extension implements PrependExtensionInterface
{
    /** @param array<array-key, mixed> $configs */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{translation_domain: string} $config */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('audit_log.translation_domain', $config['translation_domain']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
    }

    // The bundle's payoff: register our DBAL types and the entity mapping with
    // DoctrineBundle, so the shipped entity works with zero consumer config.
    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
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
                        'dir' => __DIR__.'/../Infrastructure/Doctrine',
                        'prefix' => 'WeDevelop\\AuditLog\\Infrastructure\\Doctrine',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }
}
