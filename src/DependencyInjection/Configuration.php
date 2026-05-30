<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('audit_log');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('translation_domain')
                    ->info('Translation domain used to render audit messages.')
                    ->defaultValue('audit')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
