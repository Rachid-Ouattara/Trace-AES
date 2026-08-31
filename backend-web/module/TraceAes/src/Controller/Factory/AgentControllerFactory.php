<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\AgentController;
use TraceAes\Model\AgentTable;
use TraceAes\Model\SocieteTransportTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class AgentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AgentController(
            $container->get(AgentTable::class),
            $container->get(SocieteTransportTable::class)
        );
    }
}
