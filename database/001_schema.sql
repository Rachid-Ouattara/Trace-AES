-- TRACE-AES — schéma PostgreSQL/PostGIS
-- Dérivé du MCD Merise (dossier technique, section 2) : chaîne de garde
-- chargement -> trajet -> vérification à l'arrivée -> alertes.

CREATE EXTENSION IF NOT EXISTS postgis;

-- ------------------------------------------------------------------
-- Référentiels
-- ------------------------------------------------------------------

CREATE TABLE societe_transport (
    id              BIGSERIAL PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    pays            VARCHAR(50) NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE citerne (
    id                      BIGSERIAL PRIMARY KEY,
    immatriculation         VARCHAR(30) NOT NULL UNIQUE,
    capacite_litres         NUMERIC(10,2) NOT NULL CHECK (capacite_litres > 0),
    societe_transport_id    BIGINT NOT NULL REFERENCES societe_transport(id),
    created_at              TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE point_controle (
    id              BIGSERIAL PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    type            VARCHAR(20) NOT NULL CHECK (type IN ('depot', 'livraison', 'checkpoint')),
    localisation    GEOGRAPHY(POINT, 4326) NOT NULL,
    ville           VARCHAR(100),
    pays            VARCHAR(50) NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE depot (
    id              BIGSERIAL PRIMARY KEY,
    point_controle_id BIGINT NOT NULL UNIQUE REFERENCES point_controle(id),
    nom             VARCHAR(150) NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE agent (
    id              BIGSERIAL PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    role            VARCHAR(20) NOT NULL CHECK (role IN ('agent_depot', 'chauffeur', 'agent_brigade')),
    telephone       VARCHAR(30),
    societe_transport_id BIGINT REFERENCES societe_transport(id),
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------------
-- Chaîne de garde : chargement -> scellé -> trajet -> vérification
-- ------------------------------------------------------------------

CREATE TABLE chargement (
    id                      BIGSERIAL PRIMARY KEY,
    citerne_id              BIGINT NOT NULL REFERENCES citerne(id),
    depot_id                BIGINT NOT NULL REFERENCES depot(id),
    agent_depot_id          BIGINT NOT NULL REFERENCES agent(id),
    volume_declare_litres   NUMERIC(10,2) NOT NULL CHECK (volume_declare_litres > 0),
    destination_id          BIGINT NOT NULL REFERENCES point_controle(id),
    date_chargement         TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE scelle_numerique (
    id                  BIGSERIAL PRIMARY KEY,
    chargement_id       BIGINT NOT NULL UNIQUE REFERENCES chargement(id),
    code                VARCHAR(64) NOT NULL UNIQUE, -- valeur encodée dans le QR/NFC
    type                VARCHAR(10) NOT NULL CHECK (type IN ('qr', 'nfc')),
    etat                VARCHAR(15) NOT NULL DEFAULT 'intact' CHECK (etat IN ('intact', 'rompu')),
    date_pose            TIMESTAMPTZ NOT NULL DEFAULT now(),
    date_dernier_scan    TIMESTAMPTZ
);

CREATE TABLE trajet (
    id                          BIGSERIAL PRIMARY KEY,
    chargement_id               BIGINT NOT NULL UNIQUE REFERENCES chargement(id),
    chauffeur_agent_id          BIGINT NOT NULL REFERENCES agent(id),
    itineraire_declare          GEOGRAPHY(LINESTRING, 4326) NOT NULL,
    corridor_tolerance_metres   NUMERIC(6,1) NOT NULL DEFAULT 2000.0,
    heure_depart_prevue         TIMESTAMPTZ NOT NULL,
    heure_arrivee_prevue        TIMESTAMPTZ NOT NULL,
    statut                      VARCHAR(20) NOT NULL DEFAULT 'en_cours'
                                    CHECK (statut IN ('en_cours', 'termine', 'interrompu'))
);

CREATE TABLE position_gps (
    id              BIGSERIAL PRIMARY KEY,
    trajet_id       BIGINT NOT NULL REFERENCES trajet(id),
    position        GEOGRAPHY(POINT, 4326) NOT NULL,
    horodatage      TIMESTAMPTZ NOT NULL
);

CREATE TABLE verification_arrivee (
    id                      BIGSERIAL PRIMARY KEY,
    trajet_id               BIGINT NOT NULL UNIQUE REFERENCES trajet(id),
    point_controle_id       BIGINT NOT NULL REFERENCES point_controle(id),
    agent_id                BIGINT NOT NULL REFERENCES agent(id),
    volume_mesure_litres    NUMERIC(10,2) NOT NULL CHECK (volume_mesure_litres >= 0),
    etat_scelle_constate    VARCHAR(15) NOT NULL CHECK (etat_scelle_constate IN ('intact', 'rompu')),
    date_verification       TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------------
-- Moteur d'alertes (cf. dossier technique, section 3)
-- ------------------------------------------------------------------

CREATE TABLE alerte (
    id                  BIGSERIAL PRIMARY KEY,
    trajet_id           BIGINT NOT NULL REFERENCES trajet(id),
    type_alerte         VARCHAR(30) NOT NULL
                            CHECK (type_alerte IN ('ecart_volume', 'deviation_trajet', 'rupture_scelle', 'retard_anormal')),
    description         TEXT NOT NULL,
    valeur_mesuree       NUMERIC(12,2),
    seuil               NUMERIC(12,2),
    date_detection       TIMESTAMPTZ NOT NULL DEFAULT now(),
    statut              VARCHAR(20) NOT NULL DEFAULT 'nouvelle'
                            CHECK (statut IN ('nouvelle', 'traitee', 'fausse_alerte')),
    agent_traitement_id  BIGINT REFERENCES agent(id),
    date_traitement      TIMESTAMPTZ
);

-- ------------------------------------------------------------------
-- Index
-- ------------------------------------------------------------------

CREATE INDEX idx_position_gps_trajet ON position_gps(trajet_id);
CREATE INDEX idx_position_gps_geom ON position_gps USING GIST(position);
CREATE INDEX idx_trajet_itineraire ON trajet USING GIST(itineraire_declare);
CREATE INDEX idx_point_controle_geom ON point_controle USING GIST(localisation);
CREATE INDEX idx_alerte_trajet ON alerte(trajet_id);
CREATE INDEX idx_alerte_statut ON alerte(statut);
CREATE INDEX idx_chargement_citerne ON chargement(citerne_id);
