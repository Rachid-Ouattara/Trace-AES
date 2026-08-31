<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class PointControleTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchByType($type)
    {
        return $this->tableGateway->select(['type' => $type]);
    }
}
