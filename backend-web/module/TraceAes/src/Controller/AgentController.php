<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\AgentTable;
use TraceAes\Model\SocieteTransportTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AgentController extends AbstractActionController
{
    private $table;
    private $societeTransportTable;

    public function __construct(AgentTable $table, SocieteTransportTable $societeTransportTable)
    {
        $this->table = $table;
        $this->societeTransportTable = $societeTransportTable;
    }

    public function indexAction()
    {
        return new ViewModel(['agents' => $this->table->fetchAll()]);
    }

    public function addAction()
    {
        $viewModel = new ViewModel([
            'societes' => $this->societeTransportTable->fetchAll(),
            'erreurs' => [],
            'donnees' => [],
            'modeEdition' => false,
        ]);

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $donnees = $this->collecterDonnees();
        $viewModel->setVariable('donnees', $donnees);

        $erreurs = $this->validerAgent($donnees, true);
        if ($erreurs) {
            $viewModel->setVariable('erreurs', $erreurs);
            return $viewModel;
        }

        try {
            $this->table->insert($donnees);
        } catch (Exception $e) {
            $viewModel->setVariable('erreurs', ["Erreur lors de l'enregistrement : " . $e->getMessage()]);
            return $viewModel;
        }

        return $this->redirect()->toRoute('trace-aes', ['controller' => 'agent']);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $agent = $this->table->find($id);

        $viewModel = new ViewModel([
            'societes' => $this->societeTransportTable->fetchAll(),
            'erreurs' => [],
            'donnees' => $agent,
            'modeEdition' => true,
            'agentId' => $id,
        ]);
        $viewModel->setTemplate('trace-aes/agent/add');

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $donnees = $this->collecterDonnees();
        $viewModel->setVariable('donnees', $donnees);

        $erreurs = $this->validerAgent($donnees, false);
        if ($erreurs) {
            $viewModel->setVariable('erreurs', $erreurs);
            return $viewModel;
        }

        try {
            $this->table->update($id, $donnees);
        } catch (Exception $e) {
            $viewModel->setVariable('erreurs', ["Erreur lors de l'enregistrement : " . $e->getMessage()]);
            return $viewModel;
        }

        return $this->redirect()->toRoute('trace-aes', ['controller' => 'agent']);
    }

    private function collecterDonnees()
    {
        return [
            'nom' => trim((string) $this->params()->fromPost('nom')),
            'prenom' => trim((string) $this->params()->fromPost('prenom')),
            'role' => $this->params()->fromPost('role'),
            'telephone' => trim((string) $this->params()->fromPost('telephone')),
            'societe_transport_id' => $this->params()->fromPost('societe_transport_id'),
            'nom_utilisateur' => trim((string) $this->params()->fromPost('nom_utilisateur')),
            'mot_de_passe' => (string) $this->params()->fromPost('mot_de_passe'),
        ];
    }

    private function validerAgent(array $donnees, $motDePasseObligatoireSiIdentifiant)
    {
        $erreurs = [];

        foreach (['nom', 'prenom', 'role'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if (! in_array($donnees['role'], ['agent_depot', 'chauffeur', 'agent_brigade', 'admin'], true)) {
            $erreurs[] = 'Rôle invalide.';
        }

        if ($donnees['nom_utilisateur'] !== '' && $motDePasseObligatoireSiIdentifiant && $donnees['mot_de_passe'] === '') {
            $erreurs[] = 'Un mot de passe est requis pour créer un compte de connexion.';
        }

        if ($donnees['nom_utilisateur'] !== '' && $donnees['mot_de_passe'] !== '' && strlen($donnees['mot_de_passe']) < 8) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        return $erreurs;
    }
}
