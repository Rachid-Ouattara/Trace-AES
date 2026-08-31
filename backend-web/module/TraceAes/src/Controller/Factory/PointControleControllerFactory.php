<?php

namespace TraceAes\Controller\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Controller\PointControleController;
use TraceAes\Model\PointControleTable;
use TraceAes\Service\PointControleService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PointControleControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PointControleController(
            $container->get(PointControleTable::class),
            $container->get(PointControleService::class)
        );
    }
}
