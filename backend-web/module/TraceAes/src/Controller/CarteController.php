<?php

namespace TraceAes\Controller;

use TraceAes\Service\CarteDataService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CarteController extends AbstractActionController
{
    private $carteDataService;

    public function __construct(CarteDataService $carteDataService)
    {
        $this->carteDataService = $carteDataService;
    }

    public function indexAction()
    {
        return new ViewModel([
            'donnees' => $this->carteDataService->recupererDonnees(),
        ]);
    }
}
