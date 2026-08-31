<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\ChargementController;
use TraceAes\Model\ChargementTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class ChargementControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ChargementController($container->get(ChargementTable::class));
    }
}
