-- TRACE-AES — migration : authentification des agents
-- Ajoute les identifiants de connexion sur la table agent et le role 'admin'.

ALTER TABLE agent ADD COLUMN nom_utilisateur VARCHAR(50) UNIQUE;
ALTER TABLE agent ADD COLUMN mot_de_passe_hash VARCHAR(255);

ALTER TABLE agent DROP CONSTRAINT agent_role_check;
ALTER TABLE agent ADD CONSTRAINT agent_role_check
    CHECK (role IN ('agent_depot', 'chauffeur', 'agent_brigade', 'admin'));
