<?php

namespace TraceAes\Service;

use Exception;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\ChargementTable;
use TraceAes\Model\TrajetTable;
use TraceAes\Model\VerificationArriveeTable;
use Zend\Db\Adapter\AdapterInterface;

class VerificationArriveeService
{
    private $adapter;
    private $verificationTable;
    private $trajetTable;
    private $chargementTable;
    private $alerteTable;
    private $moteurAlertes;

    public function __construct(
        AdapterInterface $adapter,
        VerificationArriveeTable $verificationTable,
        TrajetTable $trajetTable,
        ChargementTable $chargementTable,
        AlerteTable $alerteTable,
        MoteurAlertesService $moteurAlertes
    ) {
        $this->adapter = $adapter;
        $this->verificationTable = $verificationTable;
        $this->trajetTable = $trajetTable;
        $this->chargementTable = $chargementTable;
        $this->alerteTable = $alerteTable;
        $this->moteurAlertes = $moteurAlertes;
    }

    public function enregistrerVerification(array $data)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $trajet = $this->trajetTable->find($data['trajet_id']);
            $chargement = $this->chargementTable->find($trajet['chargement_id']);

            $verificationId = $this->verificationTable->insert($data);

            $alertesDeclenchees = $this->moteurAlertes->evaluerVerificationArrivee($trajet, $chargement, $data);
            foreach ($alertesDeclenchees as $alerte) {
                $this->alerteTable->insert($alerte + ['trajet_id' => $data['trajet_id']]);
            }

            $this->trajetTable->updateStatut($data['trajet_id'], 'termine');

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return [
            'verification_id' => $verificationId,
            'alertes_generees' => count($alertesDeclenchees),
        ];
    }
}
