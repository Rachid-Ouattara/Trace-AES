<?php

namespace TraceAes\Model;

use Zend\Db\TableGateway\TableGatewayInterface;

class SocieteTransportTable
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

    /**
     * Reutilise la societe existante si le nom correspond exactement,
     * sinon en cree une nouvelle. Evite d'avoir besoin d'un ecran
     * d'administration separe pour ce referentiel tres simple.
     */
    public function trouverOuCreerParNom($nom, $pays)
    {
        $existante = $this->tableGateway->select(['nom' => $nom])->current();
        if ($existante) {
            return (int) $existante['id'];
        }

        $this->tableGateway->insert(['nom' => $nom, 'pays' => $pays]);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }
}
