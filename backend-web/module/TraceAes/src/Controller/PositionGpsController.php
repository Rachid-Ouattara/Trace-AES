<?php

namespace TraceAes\Controller;

use Exception;
use TraceAes\Service\PositionGpsService;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Endpoint destine a l'application mobile (agents de terrain / chauffeurs) :
 * envoi periodique de la position GPS pendant un trajet en cours.
 */
class PositionGpsController extends AbstractActionController
{
    private $positionGpsService;

    public function __construct(PositionGpsService $positionGpsService)
    {
        $this->positionGpsService = $positionGpsService;
    }

    public function enregistrerAction()
    {
        if (! $this->getRequest()->isPost()) {
            return $this->reponseJson(['erreur' => 'Methode non autorisee, POST attendu.'], 405);
        }

        $trajetId = $this->params()->fromPost('trajet_id');
        $latitude = $this->params()->fromPost('latitude');
        $longitude = $this->params()->fromPost('longitude');
        $horodatage = $this->params()->fromPost('horodatage') ?: date('Y-m-d H:i:s');

        $erreurs = $this->validerPosition($trajetId, $latitude, $longitude);
        if ($erreurs) {
            return $this->reponseJson(['erreurs' => $erreurs], 422);
        }

        try {
            $resultat = $this->positionGpsService->enregistrerPosition($trajetId, $latitude, $longitude, $horodatage);
        } catch (Exception $e) {
            return $this->reponseJson(['erreur' => $e->getMessage()], 422);
        }

        return $this->reponseJson([
            'succes' => true,
            'deviation_detectee' => $resultat['deviation_detectee'],
        ]);
    }

    private function validerPosition($trajetId, $latitude, $longitude)
    {
        $erreurs = [];

        if ($trajetId === null || $trajetId === '') {
            $erreurs[] = 'trajet_id est obligatoire.';
        }
        if (! is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
            $erreurs[] = 'latitude invalide (doit etre comprise entre -90 et 90).';
        }
        if (! is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
            $erreurs[] = 'longitude invalide (doit etre comprise entre -180 et 180).';
        }

        return $erreurs;
    }

    private function reponseJson(array $data, $statusCode = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
}
