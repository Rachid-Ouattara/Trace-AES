<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\CiterneController;
use TraceAes\Model\CiterneTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class CiterneControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CiterneController($container->get(CiterneTable::class));
    }
}
