<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\SocieteController;
use TraceAes\Model\SocieteTransportTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class SocieteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SocieteController($container->get(SocieteTransportTable::class));
    }
}
