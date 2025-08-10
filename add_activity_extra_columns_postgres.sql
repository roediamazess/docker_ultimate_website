-- Tambah kolom Priority, Customer, dan Project pada tabel activities (PostgreSQL)

DO $$
BEGIN
    CREATE TYPE activity_priority AS ENUM ('Urgent','Normal','Low');
EXCEPTION WHEN duplicate_object THEN
    NULL;
END $$;

ALTER TABLE activities
    ADD COLUMN IF NOT EXISTS priority activity_priority DEFAULT 'Normal' NOT NULL,
    ADD COLUMN IF NOT EXISTS customer VARCHAR(255),
    ADD COLUMN IF NOT EXISTS project VARCHAR(255);


