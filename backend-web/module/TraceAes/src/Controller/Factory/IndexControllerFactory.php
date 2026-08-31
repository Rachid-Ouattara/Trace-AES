<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\IndexController;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\TrajetTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IndexController(
            $container->get(TrajetTable::class),
            $container->get(AlerteTable::class)
        );
    }
}
