<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\ParametreController;
use TraceAes\Model\ParametreSystemeTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class ParametreControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ParametreController($container->get(ParametreSystemeTable::class));
    }
}
