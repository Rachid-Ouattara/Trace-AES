<?php

namespace TraceAes\Service\Factory;

use Interop\Container\ContainerInterface;
use TraceAes\Model\CiterneTable;
use TraceAes\Model\SocieteTransportTable;
use TraceAes\Service\CiterneService;
use Zend\ServiceManager\Factory\FactoryInterface;

class CiterneServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CiterneService(
            $container->get('Zend\Db\Adapter\Adapter'),
            $container->get(CiterneTable::class),
            $container->get(SocieteTransportTable::class)
        );
    }
}
