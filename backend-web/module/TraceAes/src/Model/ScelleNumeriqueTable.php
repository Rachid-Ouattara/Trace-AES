<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class ScelleNumeriqueTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function findByChargement($chargementId)
    {
        return $this->tableGateway->select(['chargement_id' => (int) $chargementId])->current();
    }

    public function insertPourChargement($chargementId, $type = 'qr')
    {
        $code = strtoupper(bin2hex(random_bytes(8)));

        $this->tableGateway->insert([
            'chargement_id' => (int) $chargementId,
            'code' => $code,
            'type' => $type,
            'etat' => 'intact',
        ]);

        return $code;
    }
}
