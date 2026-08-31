<?php

namespace TraceAes\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class AgentTable
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

    public function fetchByRole($role)
    {
        return $this->tableGateway->select(['role' => $role]);
    }

    public function find($id)
    {
        $row = $this->tableGateway->select(['id' => (int) $id])->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Agent id %d introuvable', (int) $id));
        }
        return $row;
    }

    public function findByUsername($nomUtilisateur)
    {
        $row = $this->tableGateway->select(['nom_utilisateur' => $nomUtilisateur])->current();
        return $row ?: null;
    }

    public function insert(array $data)
    {
        $valeurs = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'role' => $data['role'],
            'telephone' => $data['telephone'] ?: null,
            'societe_transport_id' => $data['societe_transport_id'] ? (int) $data['societe_transport_id'] : null,
            'nom_utilisateur' => $data['nom_utilisateur'] ?: null,
        ];

        if (! empty($data['mot_de_passe'])) {
            $valeurs['mot_de_passe_hash'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $this->tableGateway->insert($valeurs);

        $adapter = $this->tableGateway->adapter;
        $result = $adapter->query('SELECT lastval() AS id', $adapter::QUERY_MODE_EXECUTE);
        return (int) $result->current()['id'];
    }

    public function update($id, array $data)
    {
        $valeurs = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'role' => $data['role'],
            'telephone' => $data['telephone'] ?: null,
            'societe_transport_id' => $data['societe_transport_id'] ? (int) $data['societe_transport_id'] : null,
            'nom_utilisateur' => $data['nom_utilisateur'] ?: null,
        ];

        if (! empty($data['mot_de_passe'])) {
            $valeurs['mot_de_passe_hash'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $this->tableGateway->update($valeurs, ['id' => (int) $id]);
    }
}
