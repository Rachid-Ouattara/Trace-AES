<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\CiterneTable;
use TraceAes\Service\CiterneService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CiterneController extends AbstractActionController
{
    private $table;
    private $citerneService;

    public function __construct(CiterneTable $table, CiterneService $citerneService)
    {
        $this->table = $table;
        $this->citerneService = $citerneService;
    }

    public function indexAction()
    {
        return new ViewModel(['citernes' => $this->table->fetchAll()]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel(['citerne' => $this->table->find($id)]);
    }

    public function addAction()
    {
        $viewData = ['erreurs' => [], 'donnees' => []];

        if (! $this->getRequest()->isPost()) {
            return new ViewModel($viewData);
        }

        $donnees = [
            'immatriculation' => trim((string) $this->params()->fromPost('immatriculation')),
            'capacite_litres' => $this->params()->fromPost('capacite_litres'),
            'societe_nom' => trim((string) $this->params()->fromPost('societe_nom')),
            'societe_pays' => trim((string) $this->params()->fromPost('societe_pays')),
        ];
        $viewData['donnees'] = $donnees;

        $erreurs = $this->validerCiterne($donnees);
        if ($erreurs) {
            $viewData['erreurs'] = $erreurs;
            return new ViewModel($viewData);
        }

        try {
            $citerneId = $this->citerneService->enregistrerCiterne($donnees);
        } catch (Exception $e) {
            $viewData['erreurs'] = ["Erreur lors de l'enregistrement : " . $e->getMessage()];
            return new ViewModel($viewData);
        }

        return $this->redirect()->toRoute('trace-aes', [
            'controller' => 'citerne',
            'action' => 'view',
            'id' => $citerneId,
        ]);
    }

    private function validerCiterne(array $donnees)
    {
        $erreurs = [];

        foreach (['immatriculation', 'capacite_litres', 'societe_nom', 'societe_pays'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if (! $erreurs && (! is_numeric($donnees['capacite_litres']) || (float) $donnees['capacite_litres'] <= 0)) {
            $erreurs[] = 'La capacité doit être un nombre supérieur à zéro.';
        }

        return $erreurs;
    }
}
