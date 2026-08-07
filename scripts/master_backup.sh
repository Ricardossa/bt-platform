#!/bin/bash

# ============================================================
# BT PLATFORM - MASTER BACKUP ENGINE (GOLD v4 - ORGANIZED)
# ============================================================

echo "🚀 Iniciando Ciclo de Backup Mestre Dinâmico..."

# 1. Busca as configurações REAIS do banco via PHP
CONFIG_JSON=$(php -r "define('BT_CLI', true); require_once '/opt/bt-platform/bt-platform/bootstrap/app.php'; \$p = BT\Core\Database\Database::fetch('SELECT * FROM platform LIMIT 1'); echo json_encode(\$p);")

BKP_SHARE_RAW=$(echo $CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_path ?? '';")
BKP_USER=$(echo $CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_user ?? '';")
BKP_PASS=$(echo $CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_pass ?? '';")
BKP_RETENTION=$(echo $CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_retention ?? 7;")

# 2. Normalização de Caminho para o Linux
BKP_SHARE=$(echo "$BKP_SHARE_RAW" | tr '\\' '/')
MOUNT_ROOT="/mnt/truenas/backups"
TARGET_DIR="BKP-MASTER"

# 3. Tenta Montar o Share Raiz
if [ ! -z "$BKP_SHARE" ] && [ ! -z "$BKP_USER" ]; then
    echo "📂 Verificando conexão física com $BKP_SHARE..."
    mkdir -p "$MOUNT_ROOT"

    if ! mountpoint -q "$MOUNT_ROOT"; then
        mount -t cifs -o username="$BKP_USER",password="$BKP_PASS",iocharset=utf8,vers=3.0,file_mode=0777,dir_mode=0777 "$BKP_SHARE" "$MOUNT_ROOT"
    fi
fi

# 4. Gera o Dump do Banco MariaDB (Apenas da Master)
DB_NAME="bt_platform"
DB_USER="bt_platform"
DB_PASS="BTPlatform2026!"
DATE=$(date +%Y%m%d_%H%M%S)
DB_FILENAME="master_db_$DATE.sql.gz"

# Caminhos de Origem na VM
STORAGE_DIR="/opt/bt-platform/bt-platform/storage/backups"
INTERNAL_BKP_DIR="$STORAGE_DIR/internal"

mkdir -p "$INTERNAL_BKP_DIR"
mysqldump -h 127.0.0.1 -u$DB_USER -p$DB_PASS $DB_NAME | gzip > "$INTERNAL_BKP_DIR/$DB_FILENAME"

if [ $? -eq 0 ]; then
    echo "✅ Dump MariaDB gerado: $DB_FILENAME"

    # 5. Sincroniza para o TrueNAS
    if mountpoint -q "$MOUNT_ROOT"; then
        FINAL_PATH="$MOUNT_ROOT/$TARGET_DIR"
        echo "🚀 Despachando arquivos para o TrueNAS..."

        # Garante a estrutura de pastas no TrueNAS
        mkdir -p "$FINAL_PATH/unidades"

        # Copia o Banco da Master (O dump que acabamos de fazer)
        cp "$INTERNAL_BKP_DIR/$DB_FILENAME" "$FINAL_PATH/"

        # Copia os Backups das Unidades (Tudo na storage/backups EXCETO a pasta internal)
        # Usamos o find para pegar apenas as pastas de clientes
        find "$STORAGE_DIR" -maxdepth 1 -mindepth 1 -type d ! -name "internal" -exec cp -r {} "$FINAL_PATH/unidades/" \;

        echo "✅ Tudo sincronizado com sucesso no TrueNAS!"
    else
        echo "⚠️ TrueNAS Offline. Backup mantido apenas na VM."
    fi

    # 6. Rotação (VM)
    find "$INTERNAL_BKP_DIR" -name "master_db_*.sql.gz" -mtime +$BKP_RETENTION -exec rm {} \;
    echo "✅ Limpeza concluída ($BKP_RETENTION dias)."
else
    echo "❌ Erro crítico ao gerar backup."
fi
