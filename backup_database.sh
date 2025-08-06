#!/bin/bash
# Backup database PostgreSQL ultimate_website ke file ultimate_website_backup.sql
# Pastikan pg_dump sudah ada di PATH dan environment variable PGUSER/PGPASSWORD sudah di-set, atau edit baris di bawah ini

DB_NAME="ultimate_website"
BACKUP_FILE="ultimate_website_backup_$(date +%Y%m%d_%H%M%S).sql"

# Jika perlu, set user dan password di sini:
# export PGUSER=postgres
# export PGPASSWORD=password

pg_dump -U postgres -h localhost -F p "$DB_NAME" > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "Backup berhasil: $BACKUP_FILE"
else
    echo "Backup gagal."
fi
