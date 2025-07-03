#!/bin/bash

# Script untuk menjalankan migrasi database MySQL
# Pastikan MySQL sudah berjalan dan Anda memiliki akses ke database

DB_USER="root"
DB_PASS=""
DB_NAME="sistempengumpulantugas"
DB_HOST="localhost"
SQL_FILE="database.sql"

echo "Menjalankan migrasi database ke $DB_NAME..."

mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < $SQL_FILE

if [ $? -eq 0 ]; then
  echo "Migrasi database berhasil."
else
  echo "Migrasi database gagal."
fi
