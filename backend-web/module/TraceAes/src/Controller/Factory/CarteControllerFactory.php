<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\CarteController;
use TraceAes\Service\CarteDataService;
use Zend\ServiceManager\Factory\FactoryInterface;

class CarteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CarteController($container->get(CarteDataService::class));
    }
}
