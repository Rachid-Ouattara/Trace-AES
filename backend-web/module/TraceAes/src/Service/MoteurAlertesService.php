<?php

namespace TraceAes\Service;

/**
 * Moteur de regles (dossier technique, section 3) : compare les donnees
 * declarees (chargement, trajet) aux donnees observees, et produit les
 * alertes correspondantes.
 */
class MoteurAlertesService
{
    const SEUIL_ECART_VOLUME_POURCENT = 5.0;
    const SEUIL_RETARD_POURCENT = 30.0;
    const DUREE_MIN_DEVIATION_SECONDES = 600; // 10 minutes, cf. dossier technique

    /**
     * $trajet et $chargement proviennent de TableGateway::find() (ArrayObject),
     * pas de tableaux PHP natifs — pas de type hint 'array' possible ici.
     */
    public function evaluerVerificationArrivee($trajet, $chargement, array $verification)
    {
        $alertes = [];

        $ecartVolume = $this->evaluerEcartVolume($chargement, $verification);
        if ($ecartVolume) {
            $alertes[] = $ecartVolume;
        }

        $ruptureScelle = $this->evaluerRuptureScelle($verification);
        if ($ruptureScelle) {
            $alertes[] = $ruptureScelle;
        }

        $retard = $this->evaluerRetard($trajet, $verification);
        if ($retard) {
            $alertes[] = $retard;
        }

        return $alertes;
    }

    /**
     * $positionsRecentes : lignes ['horodatage' => ..., 'distance_metres' => ...],
     * la plus recente en premier (cf. PositionGpsTable::fetchRecentesAvecDistance).
     * Une deviation n'est retenue que si le vehicule est reste hors corridor
     * de facon continue depuis au moins DUREE_MIN_DEVIATION_SECONDES.
     */
    public function evaluerDeviationTrajet($toleranceMetres, array $positionsRecentes)
    {
        if (! $positionsRecentes) {
            return null;
        }

        $hodsCorridor = [];
        foreach ($positionsRecentes as $position) {
            if ((float) $position['distance_metres'] <= $toleranceMetres) {
                break;
            }
            $hodsCorridor[] = $position;
        }

        if (! $hodsCorridor) {
            return null;
        }

        $plusRecent = strtotime($hodsCorridor[0]['horodatage']);
        $plusAncien = strtotime(end($hodsCorridor)['horodatage']);
        $dureeSecondes = $plusRecent - $plusAncien;

        if ($dureeSecondes < self::DUREE_MIN_DEVIATION_SECONDES) {
            return null;
        }

        $distanceMax = max(array_map(function ($p) {
            return (float) $p['distance_metres'];
        }, $hodsCorridor));

        return [
            'type_alerte' => 'deviation_trajet',
            'description' => sprintf(
                'Position hors corridor declare depuis %d min (distance max constatee : %.0f m, tolerance : %.0f m).',
                round($dureeSecondes / 60),
                $distanceMax,
                $toleranceMetres
            ),
            'valeur_mesuree' => round($distanceMax, 1),
            'seuil' => $toleranceMetres,
        ];
    }

    private function evaluerEcartVolume($chargement, array $verification)
    {
        $volumeDeclare = (float) $chargement['volume_declare_litres'];
        $volumeMesure = (float) $verification['volume_mesure_litres'];

        if ($volumeDeclare <= 0) {
            return null;
        }

        $ecartPourcent = abs($volumeDeclare - $volumeMesure) / $volumeDeclare * 100;
        if ($ecartPourcent <= self::SEUIL_ECART_VOLUME_POURCENT) {
            return null;
        }

        return [
            'type_alerte' => 'ecart_volume',
            'description' => sprintf(
                'Volume mesure (%.2f L) different du volume declare (%.2f L), soit %.1f%% d\'ecart.',
                $volumeMesure,
                $volumeDeclare,
                $ecartPourcent
            ),
            'valeur_mesuree' => round($ecartPourcent, 2),
            'seuil' => self::SEUIL_ECART_VOLUME_POURCENT,
        ];
    }

    private function evaluerRuptureScelle(array $verification)
    {
        if ($verification['etat_scelle_constate'] === 'intact') {
            return null;
        }

        return [
            'type_alerte' => 'rupture_scelle',
            'description' => 'Scelle constate rompu a la verification d\'arrivee.',
            'valeur_mesuree' => null,
            'seuil' => null,
        ];
    }

    private function evaluerRetard($trajet, array $verification)
    {
        $depart = strtotime($trajet['heure_depart_prevue']);
        $arriveePrevue = strtotime($trajet['heure_arrivee_prevue']);
        $arriveeReelle = strtotime($verification['date_verification']);

        $dureeEstimee = $arriveePrevue - $depart;
        if ($dureeEstimee <= 0) {
            return null;
        }

        $depassementPourcent = ($arriveeReelle - $arriveePrevue) / $dureeEstimee * 100;
        if ($depassementPourcent <= self::SEUIL_RETARD_POURCENT) {
            return null;
        }

        return [
            'type_alerte' => 'retard_anormal',
            'description' => sprintf(
                'Arrivee constatee avec %.1f%% de depassement par rapport a la duree de trajet estimee.',
                $depassementPourcent
            ),
            'valeur_mesuree' => round($depassementPourcent, 2),
            'seuil' => self::SEUIL_RETARD_POURCENT,
        ];
    }
}
