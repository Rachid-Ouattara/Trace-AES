<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\TrajetController;
use TraceAes\Model\AgentTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\TrajetTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class TrajetControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TrajetController(
            $container->get(TrajetTable::class),
            $container->get(ChargementTable::class),
            $container->get(AgentTable::class)
        );
    }
}
