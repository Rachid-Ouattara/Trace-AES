<?php

namespace TraceAes\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGatewayInterface;

class PositionGpsTable
{
    private $tableGateway;
    private $adapter;

    public function __construct(TableGatewayInterface $tableGateway, AdapterInterface $adapter)
    {
        $this->tableGateway = $tableGateway;
        $this->adapter = $adapter;
    }

    public function insert($trajetId, $latitude, $longitude, $horodatage)
    {
        $wkt = sprintf('POINT(%.6f %.6f)', $longitude, $latitude);

        $this->tableGateway->insert([
            'trajet_id' => (int) $trajetId,
            'position' => new Expression('ST_GeogFromText(?)', [$wkt]),
            'horodatage' => $horodatage,
        ]);
    }

    /**
     * Positions du trajet depuis $depuis, avec la distance (en metres) au
     * corridor declare, la plus recente en premier. Alimente le controle
     * de deviation soutenue du moteur d'alertes.
     */
    public function fetchRecentesAvecDistance($trajetId, $depuis)
    {
        $sql = 'SELECT p.horodatage, ST_Distance(t.itineraire_declare, p.position) AS distance_metres
                FROM position_gps p
                JOIN trajet t ON t.id = p.trajet_id
                WHERE p.trajet_id = ? AND p.horodatage >= ?
                ORDER BY p.horodatage DESC';

        $statement = $this->adapter->createStatement($sql, [(int) $trajetId, $depuis]);
        $result = $statement->execute();

        return iterator_to_array($result);
    }
}
