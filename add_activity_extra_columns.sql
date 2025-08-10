-- Tambah kolom Priority, Customer, dan Project pada tabel activities
-- Jalankan file ini sekali pada database MySQL/MariaDB Anda.

ALTER TABLE activities
    ADD COLUMN IF NOT EXISTS priority ENUM('Urgent','Normal','Low') NOT NULL DEFAULT 'Normal',
    ADD COLUMN IF NOT EXISTS customer VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS project VARCHAR(255) NULL;


