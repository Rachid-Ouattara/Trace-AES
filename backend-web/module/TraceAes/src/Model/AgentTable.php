<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class AgentTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchByRole($role)
    {
        return $this->tableGateway->select(['role' => $role]);
    }

    public function findByUsername($nomUtilisateur)
    {
        $row = $this->tableGateway->select(['nom_utilisateur' => $nomUtilisateur])->current();
        return $row ?: null;
    }
}
