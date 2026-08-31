<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\PointControleTable;
use TraceAes\Service\PointControleService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PointControleController extends AbstractActionController
{
    private $table;
    private $service;

    public function __construct(PointControleTable $table, PointControleService $service)
    {
        $this->table = $table;
        $this->service = $service;
    }

    public function indexAction()
    {
        return new ViewModel(['pointsControle' => $this->table->fetchAll()]);
    }

    public function addAction()
    {
        $viewModel = new ViewModel(['erreurs' => [], 'donnees' => []]);

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $donnees = [
            'nom' => trim((string) $this->params()->fromPost('nom')),
            'type' => $this->params()->fromPost('type'),
            'latitude' => $this->params()->fromPost('latitude'),
            'longitude' => $this->params()->fromPost('longitude'),
            'ville' => trim((string) $this->params()->fromPost('ville')),
            'pays' => trim((string) $this->params()->fromPost('pays')),
        ];
        $viewModel->setVariable('donnees', $donnees);

        $erreurs = $this->validerPointControle($donnees);
        if ($erreurs) {
            $viewModel->setVariable('erreurs', $erreurs);
            return $viewModel;
        }

        try {
            $this->service->enregistrerPointControle($donnees);
        } catch (Exception $e) {
            $viewModel->setVariable('erreurs', ["Erreur lors de l'enregistrement : " . $e->getMessage()]);
            return $viewModel;
        }

        return $this->redirect()->toRoute('trace-aes', ['controller' => 'point-controle']);
    }

    private function validerPointControle(array $donnees)
    {
        $erreurs = [];

        foreach (['nom', 'type', 'latitude', 'longitude', 'ville', 'pays'] as $champ) {
            if ($donnees[$champ] === null || $donnees[$champ] === '') {
                $erreurs[] = sprintf('Le champ "%s" est obligatoire.', $champ);
            }
        }

        if (! in_array($donnees['type'], ['depot', 'livraison', 'checkpoint'], true)) {
            $erreurs[] = 'Type de point de contrôle invalide.';
        }

        if ($erreurs) {
            return $erreurs;
        }

        if (! is_numeric($donnees['latitude']) || $donnees['latitude'] < -90 || $donnees['latitude'] > 90) {
            $erreurs[] = 'Latitude invalide (doit être comprise entre -90 et 90).';
        }
        if (! is_numeric($donnees['longitude']) || $donnees['longitude'] < -180 || $donnees['longitude'] > 180) {
            $erreurs[] = 'Longitude invalide (doit être comprise entre -180 et 180).';
        }

        return $erreurs;
    }
}
