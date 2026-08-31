<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\PositionGpsTable;
use TraceAes\Model\TrajetTable;
use TraceAes\Service\MoteurAlertesService;
use TraceAes\Service\PositionGpsService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PositionGpsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PositionGpsService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(PositionGpsTable::class),
            $container->get(TrajetTable::class),
            $container->get(AlerteTable::class),
            $container->get(MoteurAlertesService::class)
        );
    }
}
