<?php

namespace TraceAes\Service;

use Exception;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\ScelleNumeriqueTable;
use Zend\Db\Adapter\AdapterInterface;

/**
 * Orchestre la creation d'un chargement et du scelle numerique qui lui est
 * systematiquement associe (chaine de garde : un chargement scelle = une
 * seule operation atomique).
 */
class ChargementService
{
    private $adapter;
    private $chargementTable;
    private $scelleNumeriqueTable;

    public function __construct(
        AdapterInterface $adapter,
        ChargementTable $chargementTable,
        ScelleNumeriqueTable $scelleNumeriqueTable
    ) {
        $this->adapter = $adapter;
        $this->chargementTable = $chargementTable;
        $this->scelleNumeriqueTable = $scelleNumeriqueTable;
    }

    public function enregistrerChargement(array $data)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $chargementId = $this->chargementTable->insert($data);
            $codeScelle = $this->scelleNumeriqueTable->insertPourChargement($chargementId);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return [
            'chargement_id' => $chargementId,
            'code_scelle' => $codeScelle,
        ];
    }
}
