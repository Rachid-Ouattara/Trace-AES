<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\ChargementController;
use TraceAes\Model\AgentTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\CiterneTable;
use TraceAes\Model\DepotTable;
use TraceAes\Model\PointControleTable;
use TraceAes\Model\ScelleNumeriqueTable;
use TraceAes\Service\ChargementService;
use Zend\ServiceManager\Factory\FactoryInterface;

class ChargementControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ChargementController(
            $container->get(ChargementTable::class),
            $container->get(ChargementService::class),
            $container->get(CiterneTable::class),
            $container->get(DepotTable::class),
            $container->get(PointControleTable::class),
            $container->get(AgentTable::class),
            $container->get(ScelleNumeriqueTable::class)
        );
    }
}
