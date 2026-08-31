<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class CiterneTable
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
            throw new RuntimeException(sprintf('Citerne id %d introuvable', (int) $id));
        }
        return $row;
    }

    public function insert(array $data)
    {
        $this->tableGateway->insert([
            'immatriculation' => $data['immatriculation'],
            'capacite_litres' => $data['capacite_litres'],
            'societe_transport_id' => (int) $data['societe_transport_id'],
        ]);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
