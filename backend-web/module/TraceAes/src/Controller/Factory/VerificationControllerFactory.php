<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\VerificationController;
use TraceAes\Model\AgentTable;
use TraceAes\Model\PointControleTable;
use TraceAes\Model\TrajetTable;
use TraceAes\Service\VerificationArriveeService;
use Zend\ServiceManager\Factory\FactoryInterface;

class VerificationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VerificationController(
            $container->get(VerificationArriveeService::class),
            $container->get(TrajetTable::class),
            $container->get(PointControleTable::class),
            $container->get(AgentTable::class)
        );
    }
}
