<?php

namespace TraceAes\Service;

use TraceAes\Model\AgentTable;
use Zend\Authentication\AuthenticationService;

class AuthService
{
    private $auth;
    private $agentTable;

    public function __construct(AuthenticationService $auth, AgentTable $agentTable)
    {
        $this->auth = $auth;
        $this->agentTable = $agentTable;
    }

    public function connecter($nomUtilisateur, $motDePasse)
    {
        $agent = $this->agentTable->findByUsername($nomUtilisateur);

        if (! $agent || ! $agent['mot_de_passe_hash'] || ! password_verify($motDePasse, $agent['mot_de_passe_hash'])) {
            return false;
        }

        $this->auth->getStorage()->write([
            'id' => (int) $agent['id'],
            'nom' => $agent['nom'],
            'prenom' => $agent['prenom'],
            'role' => $agent['role'],
        ]);

        return true;
    }

    public function deconnecter()
    {
        $this->auth->clearIdentity();
    }

    public function estConnecte()
    {
        return $this->auth->hasIdentity();
    }

    public function identite()
    {
        return $this->auth->hasIdentity() ? $this->auth->getIdentity() : null;
    }
}
