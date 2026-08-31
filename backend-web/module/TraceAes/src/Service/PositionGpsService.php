<?php

namespace TraceAes\Service;

use Exception;
use RuntimeException;
use TraceAes\Model\AlerteTable;
use TraceAes\Model\PositionGpsTable;
use TraceAes\Model\TrajetTable;
use Zend\Db\Adapter\AdapterInterface;

class PositionGpsService
{
    const FENETRE_ANALYSE_MINUTES = 30;

    private $adapter;
    private $positionGpsTable;
    private $trajetTable;
    private $alerteTable;
    private $moteurAlertes;

    public function __construct(
        AdapterInterface $adapter,
        PositionGpsTable $positionGpsTable,
        TrajetTable $trajetTable,
        AlerteTable $alerteTable,
        MoteurAlertesService $moteurAlertes
    ) {
        $this->adapter = $adapter;
        $this->positionGpsTable = $positionGpsTable;
        $this->trajetTable = $trajetTable;
        $this->alerteTable = $alerteTable;
        $this->moteurAlertes = $moteurAlertes;
    }

    public function enregistrerPosition($trajetId, $latitude, $longitude, $horodatage)
    {
        $trajet = $this->trajetTable->find($trajetId);
        if ($trajet['statut'] !== 'en_cours') {
            throw new RuntimeException(sprintf(
                'Le trajet %d n\'est pas en cours (statut actuel : %s), position refusee.',
                (int) $trajetId,
                $trajet['statut']
            ));
        }

        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        $deviationDetectee = false;

        try {
            $this->positionGpsTable->insert($trajetId, $latitude, $longitude, $horodatage);

            $depuis = date('Y-m-d H:i:s', strtotime($horodatage) - self::FENETRE_ANALYSE_MINUTES * 60);
            $positionsRecentes = $this->positionGpsTable->fetchRecentesAvecDistance($trajetId, $depuis);

            $deviation = $this->moteurAlertes->evaluerDeviationTrajet(
                (float) $trajet['corridor_tolerance_metres'],
                $positionsRecentes
            );

            if ($deviation && ! $this->alerteTable->existeAlerteNonTraitee($trajetId, 'deviation_trajet')) {
                $this->alerteTable->insert($deviation + ['trajet_id' => (int) $trajetId]);
                $deviationDetectee = true;
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return ['deviation_detectee' => $deviationDetectee];
    }
}
