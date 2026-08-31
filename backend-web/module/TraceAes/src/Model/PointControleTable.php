<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGatewayInterface;

class PointControleTable
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

    public function fetchByType($type)
    {
        return $this->tableGateway->select(['type' => $type]);
    }

    public function find($id)
    {
        $row = $this->tableGateway->select(['id' => (int) $id])->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Point de controle id %d introuvable', (int) $id));
        }
        return $row;
    }

    public function insert(array $data)
    {
        $wkt = sprintf('POINT(%.6f %.6f)', $data['longitude'], $data['latitude']);

        $this->tableGateway->insert([
            'nom' => $data['nom'],
            'type' => $data['type'],
            'localisation' => new Expression('ST_GeogFromText(?)', [$wkt]),
            'ville' => $data['ville'],
            'pays' => $data['pays'],
        ]);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
