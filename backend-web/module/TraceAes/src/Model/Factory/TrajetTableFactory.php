<?php

namespace TraceAes\Model\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\TrajetTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

class TrajetTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get('Zend\Db\Adapter\Adapter');
        return new TrajetTable(new TableGateway('trajet', $adapter));
    }
}
