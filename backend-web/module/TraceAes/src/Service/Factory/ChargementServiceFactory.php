<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\ScelleNumeriqueTable;
use TraceAes\Service\ChargementService;
use Zend\ServiceManager\Factory\FactoryInterface;

class ChargementServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ChargementService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(ChargementTable::class),
            $container->get(ScelleNumeriqueTable::class)
        );
    }
}
