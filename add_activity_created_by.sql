-- Tambah kolom created_by pada tabel activities (MySQL/MariaDB)

ALTER TABLE activities
    ADD COLUMN IF NOT EXISTS created_by INT NULL,
    ADD CONSTRAINT fk_activities_created_by_users
        FOREIGN KEY (created_by) REFERENCES users(id);


