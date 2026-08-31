<?php

namespace TraceAes\Service;

use TraceAes\Model\ParametreSystemeTable;

/**
 * Moteur de regles (dossier technique, section 3) : compare les donnees
 * declarees (chargement, trajet) aux donnees observees, et produit les
 * alertes correspondantes. Les seuils sont configurables depuis
 * /trace-aes/parametre (table parametre_systeme) ; valeurs ci-dessous
 * utilisees seulement si une cle est absente de la base.
 */
class MoteurAlertesService
{
    const DEFAUT_SEUIL_ECART_VOLUME_POURCENT = 5.0;
    const DEFAUT_SEUIL_RETARD_POURCENT = 30.0;
    const DEFAUT_DUREE_MIN_DEVIATION_SECONDES = 600;

    private $parametreSystemeTable;
    private $parametres;

    public function __construct(ParametreSystemeTable $parametreSystemeTable)
    {
        $this->parametreSystemeTable = $parametreSystemeTable;
    }

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
     * de facon continue depuis au moins la duree minimale configuree.
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
        $dureeMinSecondes = $this->parametre('duree_min_deviation_minutes', self::DEFAUT_DUREE_MIN_DEVIATION_SECONDES / 60) * 60;

        if ($dureeSecondes < $dureeMinSecondes) {
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

        $seuil = $this->parametre('seuil_ecart_volume_pourcent', self::DEFAUT_SEUIL_ECART_VOLUME_POURCENT);
        $ecartPourcent = abs($volumeDeclare - $volumeMesure) / $volumeDeclare * 100;
        if ($ecartPourcent <= $seuil) {
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
            'seuil' => $seuil,
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

        $seuil = $this->parametre('seuil_retard_pourcent', self::DEFAUT_SEUIL_RETARD_POURCENT);
        $depassementPourcent = ($arriveeReelle - $arriveePrevue) / $dureeEstimee * 100;
        if ($depassementPourcent <= $seuil) {
            return null;
        }

        return [
            'type_alerte' => 'retard_anormal',
            'description' => sprintf(
                'Arrivee constatee avec %.1f%% de depassement par rapport a la duree de trajet estimee.',
                $depassementPourcent
            ),
            'valeur_mesuree' => round($depassementPourcent, 2),
            'seuil' => $seuil,
        ];
    }

    private function parametre($cle, $defaut)
    {
        if ($this->parametres === null) {
            $this->parametres = $this->parametreSystemeTable->fetchToutesLesValeurs();
        }

        return isset($this->parametres[$cle]) ? (float) $this->parametres[$cle] : $defaut;
    }
}
