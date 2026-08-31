<?php

namespace TraceAes\Controller;

use TraceAes\Model\AlerteTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AlerteController extends AbstractActionController
{
    private $table;

    public function __construct(AlerteTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        $statut = $this->params()->fromQuery('statut');
        $alertes = $statut ? $this->table->fetchByStatut($statut) : $this->table->fetchAll();
        return new ViewModel(['alertes' => $alertes, 'statut' => $statut]);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        return new ViewModel(['alerte' => $this->table->find($id)]);
    }

    public function traiterAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (! $this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('trace-aes', ['controller' => 'alerte', 'action' => 'view', 'id' => $id]);
        }

        $statut = $this->params()->fromPost('statut');
        $this->table->marquerTraitee($id, $statut);

        return $this->redirect()->toRoute('trace-aes', ['controller' => 'alerte', 'action' => 'view', 'id' => $id]);
    }
}
