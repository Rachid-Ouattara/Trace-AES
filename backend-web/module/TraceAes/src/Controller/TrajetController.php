<?php

namespace TraceAes\Controller;

use TraceAes\Model\TrajetTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TrajetController extends AbstractActionController
{
    private $table;

    public function __construct(TrajetTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel(['trajets' => $this->table->fetchAll()]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel(['trajet' => $this->table->find($id)]);
    }
}
