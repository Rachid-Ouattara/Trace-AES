<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\TrajetTable;
use TraceAes\Model\VerificationArriveeTable;
use TraceAes\Service\MoteurAlertesService;
use TraceAes\Service\VerificationArriveeService;
use Zend\ServiceManager\Factory\FactoryInterface;

class VerificationArriveeServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VerificationArriveeService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(VerificationArriveeTable::class),
            $container->get(TrajetTable::class),
            $container->get(ChargementTable::class),
            $container->get(AlerteTable::class),
            $container->get(MoteurAlertesService::class)
        );
    }
}
