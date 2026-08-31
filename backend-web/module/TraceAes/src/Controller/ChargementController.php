<?php

namespace TraceAes\Controller;

use TraceAes\Model\ChargementTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ChargementController extends AbstractActionController
{
    private $table;

    public function __construct(ChargementTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel(['chargements' => $this->table->fetchAll()]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel(['chargement' => $this->table->find($id)]);
    }
}
