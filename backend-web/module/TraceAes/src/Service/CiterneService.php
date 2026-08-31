<?php

namespace TraceAes\Service;

use Exception;
use TraceAes\Model\CiterneTable;
use TraceAes\Model\SocieteTransportTable;
use Zend\Db\Adapter\AdapterInterface;

class CiterneService
{
    private $adapter;
    private $citerneTable;
    private $societeTransportTable;

    public function __construct(
        AdapterInterface $adapter,
        CiterneTable $citerneTable,
        SocieteTransportTable $societeTransportTable
    ) {
        $this->adapter = $adapter;
        $this->citerneTable = $citerneTable;
        $this->societeTransportTable = $societeTransportTable;
    }

    public function enregistrerCiterne(array $data)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $societeId = $this->societeTransportTable->trouverOuCreerParNom(
                $data['societe_nom'],
                $data['societe_pays']
            );

            $citerneId = $this->citerneTable->insert([
                'immatriculation' => $data['immatriculation'],
                'capacite_litres' => $data['capacite_litres'],
                'societe_transport_id' => $societeId,
            ]);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $citerneId;
    }
}
