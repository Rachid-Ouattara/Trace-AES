<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class AgentTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchByRole($role)
    {
        return $this->tableGateway->select(['role' => $role]);
    }
}
