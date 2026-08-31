<?php

namespace TraceAes\Service;

use Exception;
use TraceAes\Model\DepotTable;
use TraceAes\Model\PointControleTable;
use Zend\Db\Adapter\AdapterInterface;

class PointControleService
{
    private $adapter;
    private $pointControleTable;
    private $depotTable;

    public function __construct(AdapterInterface $adapter, PointControleTable $pointControleTable, DepotTable $depotTable)
    {
        $this->adapter = $adapter;
        $this->pointControleTable = $pointControleTable;
        $this->depotTable = $depotTable;
    }

    public function enregistrerPointControle(array $data)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $pointControleId = $this->pointControleTable->insert($data);

            if ($data['type'] === 'depot') {
                $this->depotTable->insert($pointControleId, $data['nom']);
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $pointControleId;
    }
}
