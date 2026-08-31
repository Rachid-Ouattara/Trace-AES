<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\DepotTable;
use TraceAes\Model\PointControleTable;
use TraceAes\Service\PointControleService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PointControleServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PointControleService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(PointControleTable::class),
            $container->get(DepotTable::class)
        );
    }
}
