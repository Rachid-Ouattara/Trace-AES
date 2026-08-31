<?php

namespace TraceAes\Model\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\ParametreSystemeTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

class ParametreSystemeTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get('Zend\Db\Adapter\Adapter');
        return new ParametreSystemeTable(new TableGateway('parametre_systeme', $adapter));
    }
}
