<?php

namespace TraceAes\Controller;

use TraceAes\Model\CiterneTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CiterneController extends AbstractActionController
{
    private $table;

    public function __construct(CiterneTable $table)
    {
        $this->table = $table;
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
}
