<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Service\MoteurAlertesService;
use Zend\ServiceManager\Factory\FactoryInterface;

class MoteurAlertesServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MoteurAlertesService();
    }
}
