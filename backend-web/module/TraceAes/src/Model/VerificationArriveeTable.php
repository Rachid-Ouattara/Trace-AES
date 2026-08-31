<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class VerificationArriveeTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function insert(array $data)
    {
        $this->tableGateway->insert([
            'trajet_id' => (int) $data['trajet_id'],
            'point_controle_id' => (int) $data['point_controle_id'],
            'agent_id' => (int) $data['agent_id'],
            'volume_mesure_litres' => $data['volume_mesure_litres'],
            'etat_scelle_constate' => $data['etat_scelle_constate'],
            'date_verification' => $data['date_verification'],
        ]);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
