<?php

namespace TraceAes\Controller;

use TraceAes\Model\ParametreSystemeTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ParametreController extends AbstractActionController
{
    private $table;

    public function __construct(ParametreSystemeTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        $viewModel = new ViewModel([
            'parametres' => $this->table->fetchAll(),
            'succes' => false,
            'erreurs' => [],
        ]);

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $erreurs = [];
        $valeurs = (array) $this->params()->fromPost('valeur', []);

        foreach ($valeurs as $cle => $valeur) {
            if (! is_numeric($valeur) || (float) $valeur < 0) {
                $erreurs[] = sprintf('La valeur pour "%s" doit être un nombre positif.', $cle);
            }
        }

        if ($erreurs) {
            $viewModel->setVariable('erreurs', $erreurs);
            return $viewModel;
        }

        foreach ($valeurs as $cle => $valeur) {
            $this->table->mettreAJour($cle, $valeur);
        }

        $viewModel->setVariable('parametres', $this->table->fetchAll());
        $viewModel->setVariable('succes', true);
        return $viewModel;
    }
}
