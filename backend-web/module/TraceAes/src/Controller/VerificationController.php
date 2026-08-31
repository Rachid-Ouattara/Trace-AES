<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\AgentTable;
use TraceAes\Model\PointControleTable;
use TraceAes\Model\TrajetTable;
use TraceAes\Service\VerificationArriveeService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class VerificationController extends AbstractActionController
{
    private $verificationService;
    private $trajetTable;
    private $pointControleTable;
    private $agentTable;

    public function __construct(
        VerificationArriveeService $verificationService,
        TrajetTable $trajetTable,
        PointControleTable $pointControleTable,
        AgentTable $agentTable
    ) {
        $this->verificationService = $verificationService;
        $this->trajetTable = $trajetTable;
        $this->pointControleTable = $pointControleTable;
        $this->agentTable = $agentTable;
    }

    public function addAction()
    {
        $viewData = [
            'trajets' => $this->trajetTable->fetchSansVerification(),
            'pointsControle' => $this->pointControleTable->fetchByType('livraison'),
            'agentsBrigade' => $this->agentTable->fetchByRole('agent_brigade'),
            'erreurs' => [],
            'donnees' => [],
            'resultat' => null,
        ];

        if (! $this->getRequest()->isPost()) {
            return new ViewModel($viewData);
        }

        $donnees = [
            'trajet_id' => $this->params()->fromPost('trajet_id'),
            'point_controle_id' => $this->params()->fromPost('point_controle_id'),
            'agent_id' => $this->params()->fromPost('agent_id'),
            'volume_mesure_litres' => $this->params()->fromPost('volume_mesure_litres'),
            'etat_scelle_constate' => $this->params()->fromPost('etat_scelle_constate'),
            'date_verification' => date('Y-m-d H:i:s'),
        ];
        $viewData['donnees'] = $donnees;

        $erreurs = $this->validerVerification($donnees);
        if ($erreurs) {
            $viewData['erreurs'] = $erreurs;
            return new ViewModel($viewData);
        }

        try {
            $resultat = $this->verificationService->enregistrerVerification($donnees);
        } catch (Exception $e) {
            $viewData['erreurs'] = ["Erreur lors de l'enregistrement : " . $e->getMessage()];
            return new ViewModel($viewData);
        }

        $viewData['resultat'] = $resultat;
        $viewData['trajets'] = $this->trajetTable->fetchSansVerification();
        $viewData['donnees'] = [];

        return new ViewModel($viewData);
    }

    private function validerVerification(array $donnees)
    {
        $erreurs = [];

        foreach (['trajet_id', 'point_controle_id', 'agent_id', 'volume_mesure_litres', 'etat_scelle_constate'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if ($erreurs) {
            return $erreurs;
        }

        if (! is_numeric($donnees['volume_mesure_litres']) || (float) $donnees['volume_mesure_litres'] < 0) {
            $erreurs[] = 'Le volume mesuré doit être un nombre positif.';
        }

        if (! in_array($donnees['etat_scelle_constate'], ['intact', 'rompu'], true)) {
            $erreurs[] = 'État du scellé invalide.';
        }

        return $erreurs;
    }
}
