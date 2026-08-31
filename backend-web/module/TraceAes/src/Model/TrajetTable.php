<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class TrajetTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function find($id)
    {
        $row = $this->tableGateway->select(['id' => (int) $id])->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Trajet id %d introuvable', (int) $id));
        }
        return $row;
    }
}
