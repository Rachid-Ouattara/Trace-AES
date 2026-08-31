<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\VerificationArriveeTable;
use TraceAes\Service\CarteDataService;
use Zend\ServiceManager\Factory\FactoryInterface;

class CarteDataServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CarteDataService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(AlerteTable::class),
            $container->get(VerificationArriveeTable::class)
        );
    }
}
