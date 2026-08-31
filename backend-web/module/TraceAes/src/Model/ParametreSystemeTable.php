<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class ParametreSystemeTable
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

    public function fetchToutesLesValeurs()
    {
        $valeurs = [];
        foreach ($this->tableGateway->select() as $ligne) {
            $valeurs[$ligne['cle']] = $ligne['valeur'];
        }
        return $valeurs;
    }

    public function mettreAJour($cle, $valeur)
    {
        $this->tableGateway->update(['valeur' => $valeur], ['cle' => $cle]);
    }
}
