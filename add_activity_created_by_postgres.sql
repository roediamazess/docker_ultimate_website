-- Tambah kolom created_by pada tabel activities (PostgreSQL)

ALTER TABLE activities
    ADD COLUMN IF NOT EXISTS created_by INTEGER REFERENCES users(id);


