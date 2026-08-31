<?php

namespace TraceAes\Controller;

use TraceAes\Service\AuthService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function loginAction()
    {
        if ($this->authService->estConnecte()) {
            return $this->redirect()->toRoute('trace-aes');
        }

        $viewModel = new ViewModel(['erreur' => null]);
        $viewModel->setTerminal(true);

        if (! $this->getRequest()->isPost()) {
            return $viewModel;
        }

        $nomUtilisateur = trim((string) $this->params()->fromPost('nom_utilisateur'));
        $motDePasse = (string) $this->params()->fromPost('mot_de_passe');

        if ($this->authService->connecter($nomUtilisateur, $motDePasse)) {
            return $this->redirect()->toRoute('trace-aes');
        }

        $viewModel->setVariable('erreur', 'Identifiants incorrects.');
        return $viewModel;
    }

    public function logoutAction()
    {
        $this->authService->deconnecter();
        return $this->redirect()->toRoute('trace-aes', ['controller' => 'auth', 'action' => 'login']);
    }
}
