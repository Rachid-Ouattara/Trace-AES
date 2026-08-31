<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGatewayInterface;

class ChargementTable
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
            throw new RuntimeException(sprintf('Chargement id %d introuvable', (int) $id));
        }
        return $row;
    }

    public function fetchSansTrajet()
    {
        return $this->tableGateway->select(function (Select $select) {
            $select->join('trajet', 'trajet.chargement_id = chargement.id', [], Select::JOIN_LEFT)
                ->where(['trajet.id' => null]);
        });
    }

    public function insert(array $data)
    {
        $this->tableGateway->insert([
            'citerne_id' => (int) $data['citerne_id'],
            'depot_id' => (int) $data['depot_id'],
            'agent_depot_id' => (int) $data['agent_depot_id'],
            'volume_declare_litres' => $data['volume_declare_litres'],
            'destination_id' => (int) $data['destination_id'],
        ]);

        // PDO_PGSQL::lastInsertId() ne fonctionne pas sans nom de sequence
        // explicite (contrairement a MySQL). lastval() renvoie la valeur
        // generee par le DEFAULT nextval(...) de l'insert qui precede, dans
        // la meme session/connexion.
        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
