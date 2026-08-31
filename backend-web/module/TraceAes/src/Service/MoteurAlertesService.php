<?php

namespace TraceAes\Service;

/**
 * Moteur de regles (dossier technique, section 3) : compare les donnees
 * declarees (chargement, trajet) aux donnees observees a la verification
 * d'arrivee, et produit les alertes correspondantes.
 *
 * Ne couvre ici que les trois familles calculables a la verification
 * d'arrivee (ecart de volume, rupture de scelle, retard anormal). La
 * deviation de trajet necessite un flux de positions GPS pendant le
 * trajet, non encore implemente.
 */
class MoteurAlertesService
{
    const SEUIL_ECART_VOLUME_POURCENT = 5.0;
    const SEUIL_RETARD_POURCENT = 30.0;

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
