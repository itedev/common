<?php

namespace ITE\Common\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface ExtensionInterface
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
interface ExtensionInterface
{
    /**
     * Return config tree for extend bundle config.
     *
     * @param ContainerBuilder $container
     * @return null|NodeDefinition
     */
    public function getConfiguration(ContainerBuilder $container);

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function loadConfiguration(array $config, ContainerBuilder $container);
}
