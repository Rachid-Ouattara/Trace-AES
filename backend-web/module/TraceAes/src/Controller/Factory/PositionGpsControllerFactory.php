<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\PositionGpsController;
use TraceAes\Service\PositionGpsService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PositionGpsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PositionGpsController($container->get(PositionGpsService::class));
    }
}
