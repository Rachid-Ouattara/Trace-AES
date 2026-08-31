<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
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

    public function insert(array $data)
    {
        $this->tableGateway->insert([
            'chargement_id' => (int) $data['chargement_id'],
            'chauffeur_agent_id' => (int) $data['chauffeur_agent_id'],
            'itineraire_declare' => new Expression('ST_GeogFromText(?)', [$data['itineraire_wkt']]),
            'heure_depart_prevue' => $data['heure_depart_prevue'],
            'heure_arrivee_prevue' => $data['heure_arrivee_prevue'],
        ]);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
