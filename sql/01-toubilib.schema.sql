DROP TABLE IF EXISTS "rdv";
DROP TABLE IF EXISTS "praticien";
DROP TABLE IF EXISTS "patient";
DROP TABLE IF EXISTS "specialite";
DROP SEQUENCE IF EXISTS specialite_id_seq;

CREATE SEQUENCE specialite_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."specialite" (
    "id" integer DEFAULT nextval('specialite_id_seq') NOT NULL,
    "libelle" character varying(48) NOT NULL,
    "description" text,
    CONSTRAINT "specialite_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

ALTER SEQUENCE specialite_id_seq OWNED BY "public"."specialite"."id";

CREATE TABLE "public"."patient" (
    "id" character varying(64) NOT NULL,
    "nom" character varying(64) NOT NULL,
    "prenom" character varying(64) NOT NULL,
    "date_naissance" date,
    "adresse" text,
    "code_postal" character varying(8),
    "ville" character varying(64),
    "email" character varying(128),
    "telephone" character varying(24) NOT NULL,
    CONSTRAINT "patient_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."praticien" (
    "id" character varying(64) NOT NULL,
    "nom" character varying(48) NOT NULL,
    "prenom" character varying(48) NOT NULL,
    "ville" character varying(48) NOT NULL,
    "email" character varying(128) NOT NULL,
    "telephone" character varying(24) NOT NULL,
    "specialite_id" integer NOT NULL,
    "organisation" boolean DEFAULT false NOT NULL,
    "nouveau_patient" boolean DEFAULT true NOT NULL,
    "titre" character varying(8) DEFAULT 'Dr.' NOT NULL,
    CONSTRAINT "praticien_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "praticien_specialite_fkey" FOREIGN KEY ("specialite_id")
        REFERENCES "specialite" ("id")
) WITH (oids = false);

CREATE TABLE "public"."rdv" (
    "id" character varying(64) NOT NULL,
    "praticien_id" character varying(64) NOT NULL,
    "patient_id" character varying(64) NOT NULL,
    "date_heure_debut" timestamp NOT NULL,
    "statut" smallint DEFAULT 0 NOT NULL,
    "duree" smallint DEFAULT 30 NOT NULL,
    "date_heure_fin" timestamp,
    "date_creation" timestamp,
    "motif_visite" character varying(128),
    CONSTRAINT "rdv_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "rdv_praticien_fkey" FOREIGN KEY ("praticien_id")
        REFERENCES "praticien" ("id"),
    CONSTRAINT "rdv_patient_fkey" FOREIGN KEY ("patient_id")
        REFERENCES "patient" ("id")
) WITH (oids = false);
