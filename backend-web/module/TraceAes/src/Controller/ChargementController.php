<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\AgentTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\CiterneTable;
use TraceAes\Model\DepotTable;
use TraceAes\Model\PointControleTable;
use TraceAes\Model\ScelleNumeriqueTable;
use TraceAes\Service\ChargementService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ChargementController extends AbstractActionController
{
    private $table;
    private $chargementService;
    private $citerneTable;
    private $depotTable;
    private $pointControleTable;
    private $agentTable;
    private $scelleNumeriqueTable;

    public function __construct(
        ChargementTable $table,
        ChargementService $chargementService,
        CiterneTable $citerneTable,
        DepotTable $depotTable,
        PointControleTable $pointControleTable,
        AgentTable $agentTable,
        ScelleNumeriqueTable $scelleNumeriqueTable
    ) {
        $this->table = $table;
        $this->chargementService = $chargementService;
        $this->citerneTable = $citerneTable;
        $this->depotTable = $depotTable;
        $this->pointControleTable = $pointControleTable;
        $this->agentTable = $agentTable;
        $this->scelleNumeriqueTable = $scelleNumeriqueTable;
    }

    public function indexAction()
    {
        return new ViewModel(['chargements' => $this->table->fetchAll()]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel([
            'chargement' => $this->table->find($id),
            'scelle' => $this->scelleNumeriqueTable->findByChargement($id),
        ]);
    }

    public function addAction()
    {
        $viewData = [
            'citernes' => $this->citerneTable->fetchAll(),
            'depots' => $this->depotTable->fetchAll(),
            'destinations' => $this->pointControleTable->fetchByType('livraison'),
            'agentsDepot' => $this->agentTable->fetchByRole('agent_depot'),
            'erreurs' => [],
            'donnees' => [],
        ];

        if (! $this->getRequest()->isPost()) {
            return new ViewModel($viewData);
        }

        $donnees = [
            'citerne_id' => $this->params()->fromPost('citerne_id'),
            'depot_id' => $this->params()->fromPost('depot_id'),
            'agent_depot_id' => $this->params()->fromPost('agent_depot_id'),
            'volume_declare_litres' => $this->params()->fromPost('volume_declare_litres'),
            'destination_id' => $this->params()->fromPost('destination_id'),
        ];
        $viewData['donnees'] = $donnees;

        $erreurs = $this->validerChargement($donnees);
        if ($erreurs) {
            $viewData['erreurs'] = $erreurs;
            return new ViewModel($viewData);
        }

        try {
            $resultat = $this->chargementService->enregistrerChargement($donnees);
        } catch (Exception $e) {
            $viewData['erreurs'] = ["Erreur lors de l'enregistrement : " . $e->getMessage()];
            return new ViewModel($viewData);
        }

        return $this->redirect()->toRoute('trace-aes', [
            'controller' => 'chargement',
            'action' => 'view',
            'id' => $resultat['chargement_id'],
        ]);
    }

    private function validerChargement(array $donnees)
    {
        $erreurs = [];

        foreach (['citerne_id', 'depot_id', 'agent_depot_id', 'volume_declare_litres', 'destination_id'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if (! $erreurs && ! is_numeric($donnees['volume_declare_litres'])) {
            $erreurs[] = 'Le volume déclaré doit être un nombre.';
        } elseif (! $erreurs && (float) $donnees['volume_declare_litres'] <= 0) {
            $erreurs[] = 'Le volume déclaré doit être supérieur à zéro.';
        }

        return $erreurs;
    }
}
