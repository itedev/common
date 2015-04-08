<?php

namespace ITE\Common\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface ExtensionInterface
 *
 * @package ITE\Common\DependencyInjection
 */
interface ExtensionInterface
{
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

    /**
     * @param ArrayNodeDefinition $pluginsNode
     * @param ContainerBuilder $container
     */
    public function addConfiguration(ArrayNodeDefinition $pluginsNode, ContainerBuilder $container);


}