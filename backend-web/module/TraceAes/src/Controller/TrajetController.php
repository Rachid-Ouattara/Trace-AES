<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\AgentTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\TrajetTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TrajetController extends AbstractActionController
{
    private $table;
    private $chargementTable;
    private $agentTable;

    public function __construct(TrajetTable $table, ChargementTable $chargementTable, AgentTable $agentTable)
    {
        $this->table = $table;
        $this->chargementTable = $chargementTable;
        $this->agentTable = $agentTable;
    }

    public function indexAction()
    {
        return new ViewModel(['trajets' => $this->table->fetchAll()]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel(['trajet' => $this->table->find($id)]);
    }

    public function addAction()
    {
        $viewData = [
            'chargements' => $this->chargementTable->fetchSansTrajet(),
            'chauffeurs' => $this->agentTable->fetchByRole('chauffeur'),
            'erreurs' => [],
            'donnees' => [],
        ];

        if (! $this->getRequest()->isPost()) {
            return new ViewModel($viewData);
        }

        $donnees = [
            'chargement_id' => $this->params()->fromPost('chargement_id'),
            'chauffeur_agent_id' => $this->params()->fromPost('chauffeur_agent_id'),
            'itineraire_wkt' => trim((string) $this->params()->fromPost('itineraire_wkt')),
            'heure_depart_prevue' => $this->params()->fromPost('heure_depart_prevue'),
            'heure_arrivee_prevue' => $this->params()->fromPost('heure_arrivee_prevue'),
        ];
        $viewData['donnees'] = $donnees;

        $erreurs = $this->validerTrajet($donnees);
        if ($erreurs) {
            $viewData['erreurs'] = $erreurs;
            return new ViewModel($viewData);
        }

        try {
            $trajetId = $this->table->insert($donnees);
        } catch (Exception $e) {
            $viewData['erreurs'] = ["Erreur lors de l'enregistrement : " . $e->getMessage()];
            return new ViewModel($viewData);
        }

        return $this->redirect()->toRoute('trace-aes', [
            'controller' => 'trajet',
            'action' => 'view',
            'id' => $trajetId,
        ]);
    }

    private function validerTrajet(array $donnees)
    {
        $erreurs = [];

        foreach (['chargement_id', 'chauffeur_agent_id', 'itineraire_wkt', 'heure_depart_prevue', 'heure_arrivee_prevue'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if ($erreurs) {
            return $erreurs;
        }

        if (stripos($donnees['itineraire_wkt'], 'LINESTRING') !== 0) {
            $erreurs[] = 'L\'itinéraire doit être au format WKT LINESTRING, ex. : LINESTRING(-1.53 12.37, -1.60 12.40)';
        }

        $depart = strtotime($donnees['heure_depart_prevue']);
        $arrivee = strtotime($donnees['heure_arrivee_prevue']);
        if ($depart && $arrivee && $arrivee <= $depart) {
            $erreurs[] = 'L\'heure d\'arrivée prévue doit être postérieure à l\'heure de départ.';
        }

        return $erreurs;
    }
}
