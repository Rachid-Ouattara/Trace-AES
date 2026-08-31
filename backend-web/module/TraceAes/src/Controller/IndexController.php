<?php

namespace TraceAes\Controller;

use TraceAes\Model\AlerteTable;
use TraceAes\Model\TrajetTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private $trajetTable;
    private $alerteTable;

    public function __construct(TrajetTable $trajetTable, AlerteTable $alerteTable)
    {
        $this->trajetTable = $trajetTable;
        $this->alerteTable = $alerteTable;
    }

    public function indexAction()
    {
        return new ViewModel([
            'trajetsEnCours' => $this->trajetTable->compterEnCours(),
            'alertesNouvelles' => count($this->alerteTable->fetchByStatut('nouvelle')),
        ]);
    }
}
