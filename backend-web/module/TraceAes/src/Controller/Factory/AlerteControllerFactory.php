<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\AlerteController;
use TraceAes\Model\AlerteTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class AlerteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AlerteController($container->get(AlerteTable::class));
    }
}
