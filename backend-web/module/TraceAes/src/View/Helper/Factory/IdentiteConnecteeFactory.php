<?php

namespace TraceAes\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Service\AuthService;
use TraceAes\View\Helper\IdentiteConnectee;
use Zend\ServiceManager\Factory\FactoryInterface;

class IdentiteConnecteeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IdentiteConnectee($container->get(AuthService::class));
    }
}
