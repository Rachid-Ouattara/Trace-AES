<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class AlerteTable
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

    public function fetchByStatut($statut)
    {
        return $this->tableGateway->select(['statut' => $statut]);
    }

    public function find($id)
    {
        $row = $this->tableGateway->select(['id' => (int) $id])->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Alerte id %d introuvable', (int) $id));
        }
        return $row;
    }
}
