<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Model\SocieteTransportTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SocieteController extends AbstractActionController
{
    private $table;

    public function __construct(SocieteTransportTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel(['societes' => $this->table->fetchAll()]);
    }

    public function addAction()
    {
        $viewModel = new ViewModel(['erreurs' => [], 'donnees' => []]);

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $nom = trim((string) $this->params()->fromPost('nom'));
        $pays = trim((string) $this->params()->fromPost('pays'));
        $viewModel->setVariable('donnees', ['nom' => $nom, 'pays' => $pays]);

        $erreurs = [];
        if ($nom === '') {
            $erreurs[] = 'Le nom est obligatoire.';
        }
        if ($pays === '') {
            $erreurs[] = 'Le pays est obligatoire.';
        }
        if ($erreurs) {
            $viewModel->setVariable('erreurs', $erreurs);
            return $viewModel;
        }

        try {
            $this->table->trouverOuCreerParNom($nom, $pays);
        } catch (Exception $e) {
            $viewModel->setVariable('erreurs', ["Erreur lors de l'enregistrement : " . $e->getMessage()]);
            return $viewModel;
        }

        return $this->redirect()->toRoute('trace-aes', ['controller' => 'societe']);
    }
}
