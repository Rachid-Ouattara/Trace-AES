<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\AgentTable;
use TraceAes\Service\AuthService;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthService(
            $container->get(AuthenticationService::class),
            $container->get(AgentTable::class)
        );
    }
}
