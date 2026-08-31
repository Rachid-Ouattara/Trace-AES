<?php

namespace TraceAes\Service;

use TraceAes\Model\AlerteTable;
use TraceAes\Model\VerificationArriveeTable;
use Zend\Db\Adapter\AdapterInterface;

/**
 * Prepare les donnees geographiques (points de controle, trajets en cours,
 * alertes) pour le tableau de bord cartographique. Les colonnes GEOGRAPHY
 * ne peuvent pas etre lues telles quelles cote PHP : chaque requete les
 * convertit explicitement (ST_X/ST_Y, ST_AsGeoJSON) plutot que de passer
 * par TableGateway::select().
 */
class CarteDataService
{
    private $adapter;
    private $alerteTable;
    private $verificationArriveeTable;

    public function __construct(
        AdapterInterface $adapter,
        AlerteTable $alerteTable,
        VerificationArriveeTable $verificationArriveeTable
    ) {
        $this->adapter = $adapter;
        $this->alerteTable = $alerteTable;
        $this->verificationArriveeTable = $verificationArriveeTable;
    }

    public function recupererDonnees()
    {
        $pointsControle = $this->fetchPointsControle();
        $trajets = $this->fetchTrajetsEnCours();

        $trajetIds = array_column($trajets, 'id');
        $dernieresPositions = $this->fetchDernieresPositions($trajetIds);

        foreach ($trajets as &$trajet) {
            $trajet['derniere_position'] = $dernieresPositions[$trajet['id']] ?? null;
        }
        unset($trajet);

        $pointsControleParId = [];
        foreach ($pointsControle as $point) {
            $pointsControleParId[$point['id']] = $point;
        }

        $alertes = $this->fetchAlertesAvecPosition($dernieresPositions, $pointsControleParId);

        return [
            'pointsControle' => $pointsControle,
            'trajets' => $trajets,
            'alertes' => $alertes,
        ];
    }

    private function fetchPointsControle()
    {
        $sql = "SELECT id, nom, type, ville,
                       ST_Y(localisation::geometry) AS latitude,
                       ST_X(localisation::geometry) AS longitude
                FROM point_controle";

        $result = $this->adapter->query($sql, $this->adapter::QUERY_MODE_EXECUTE);

        return array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'nom' => $row['nom'],
                'type' => $row['type'],
                'ville' => $row['ville'],
                'latitude' => (float) $row['latitude'],
                'longitude' => (float) $row['longitude'],
            ];
        }, iterator_to_array($result));
    }

    private function fetchTrajetsEnCours()
    {
        $sql = "SELECT id, chargement_id, ST_AsGeoJSON(itineraire_declare::geometry) AS itineraire_geojson
                FROM trajet WHERE statut = 'en_cours'";

        $result = $this->adapter->query($sql, $this->adapter::QUERY_MODE_EXECUTE);

        return array_map(function ($row) {
            $geojson = json_decode($row['itineraire_geojson'], true);
            $itineraire = array_map(function ($paire) {
                // GeoJSON = [longitude, latitude] ; Leaflet attend [latitude, longitude]
                return [$paire[1], $paire[0]];
            }, $geojson['coordinates'] ?? []);

            return [
                'id' => (int) $row['id'],
                'chargement_id' => (int) $row['chargement_id'],
                'itineraire' => $itineraire,
            ];
        }, iterator_to_array($result));
    }

    private function fetchDernieresPositions(array $trajetIds)
    {
        if (! $trajetIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($trajetIds), '?'));
        $sql = "SELECT DISTINCT ON (trajet_id) trajet_id,
                       ST_Y(position::geometry) AS latitude,
                       ST_X(position::geometry) AS longitude,
                       horodatage
                FROM position_gps
                WHERE trajet_id IN ($placeholders)
                ORDER BY trajet_id, horodatage DESC";

        $statement = $this->adapter->createStatement($sql, $trajetIds);
        $result = $statement->execute();

        $positions = [];
        foreach ($result as $row) {
            $positions[(int) $row['trajet_id']] = [
                'latitude' => (float) $row['latitude'],
                'longitude' => (float) $row['longitude'],
                'horodatage' => $row['horodatage'],
            ];
        }

        return $positions;
    }

    private function fetchAlertesAvecPosition(array $dernieresPositions, array $pointsControleParId)
    {
        $alertes = [];

        foreach ($this->alerteTable->fetchByStatut('nouvelle') as $alerte) {
            $position = null;

            if ($alerte['type_alerte'] === 'deviation_trajet') {
                $position = $dernieresPositions[(int) $alerte['trajet_id']] ?? null;
            } else {
                $verification = $this->verificationArriveeTable->findByTrajet($alerte['trajet_id']);
                if ($verification) {
                    $point = $pointsControleParId[(int) $verification['point_controle_id']] ?? null;
                    if ($point) {
                        $position = ['latitude' => $point['latitude'], 'longitude' => $point['longitude']];
                    }
                }
            }

            if (! $position) {
                continue;
            }

            $alertes[] = [
                'id' => (int) $alerte['id'],
                'trajet_id' => (int) $alerte['trajet_id'],
                'type_alerte' => $alerte['type_alerte'],
                'description' => $alerte['description'],
                'latitude' => $position['latitude'],
                'longitude' => $position['longitude'],
            ];
        }

        return $alertes;
    }
}
