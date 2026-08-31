<?php

namespace TraceAes\Model\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\ChargementTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

class ChargementTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get('Zend\Db\Adapter\Adapter');
        return new ChargementTable(new TableGateway('chargement', $adapter));
    }
}
