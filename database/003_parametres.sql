-- TRACE-AES — migration : parametres systeme configurables
-- Remplace les seuils fixes du moteur d'alertes (MoteurAlertesService) par
-- des valeurs modifiables depuis l'interface d'administration.

CREATE TABLE parametre_systeme (
    cle VARCHAR(100) PRIMARY KEY,
    valeur VARCHAR(255) NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    description TEXT
);

INSERT INTO parametre_systeme (cle, valeur, libelle, description) VALUES
    ('seuil_ecart_volume_pourcent', '5', 'Seuil d''écart de volume (%)', 'Écart entre volume déclaré et volume mesuré à partir duquel une alerte "écart_volume" est générée.'),
    ('seuil_retard_pourcent', '30', 'Seuil de retard anormal (%)', 'Dépassement de la durée de trajet estimée à partir duquel une alerte "retard_anormal" est générée.'),
    ('duree_min_deviation_minutes', '10', 'Durée minimale de déviation soutenue (minutes)', 'Temps continu hors corridor déclaré nécessaire avant de générer une alerte "deviation_trajet".');
