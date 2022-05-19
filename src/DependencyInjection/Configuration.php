<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusAgreementPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bitbag_sylius_agreement_plugin');
        $rootNode = $treeBuilder->getRootNode();
        /** @phpstan-ignore-next-line  */
        $rootNode
            ->children()
            ->arrayNode('modes')
            ->scalarPrototype()
            ->end()
            ->end()
            ->arrayNode('contexts')
            ->ignoreExtraKeys(false)
            ->beforeNormalization()
            ->always(static function ($arg): array {
                return $arg;
            })
            ->end()
            ->end();

        return $treeBuilder;
    }
}
