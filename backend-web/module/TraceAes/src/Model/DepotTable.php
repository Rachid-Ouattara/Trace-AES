<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class DepotTable
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

    public function insert($pointControleId, $nom)
    {
        $this->tableGateway->insert([
            'point_controle_id' => (int) $pointControleId,
            'nom' => $nom,
        ]);
    }
}
