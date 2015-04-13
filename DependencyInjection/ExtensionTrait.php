<?php

namespace ITE\Common\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Trait ExtensionTrait
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
trait ExtensionTrait
{
    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(array $config, ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(ContainerBuilder $container)
    {
        return null;
    }
}