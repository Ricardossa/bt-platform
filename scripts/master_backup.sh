#!/bin/bash

# ============================================================
# BT PLATFORM - MASTER BACKUP ENGINE (DYNAMIC EDITION)
# ============================================================

# 1. Busca as configurações REAIS do banco de dados da Plataforma via PHP
# Isso garante que o script use o que você digitou no painel!
CONFIG_JSON=$(php -r "require_once '/opt/bt-platform/bt-platform/bootstrap/app.php'; \$p = BT\Core\Database\Database::fetch('SELECT * FROM platform LIMIT 1'); echo json_encode(\$p);")

BKP_HOST=$(echo \$CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_host;")
BKP_SHARE=$(echo \$CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_path;")
BKP_USER=$(echo \$CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_user;")
BKP_PASS=$(echo \$CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_pass;")
BKP_RETENTION=$(echo \$CONFIG_JSON | php -r "echo json_decode(file_get_contents('php://stdin'))->backup_retention;")

# Credenciais MariaDB (Estáticas no Core)
DB_NAME="bt_platform"
DB_USER="bt_platform"
DB_PASS="BTPlatform2026!"
DATE=$(date +%Y%m%d_%H%M%S)

# Pastas Internas da VM
INTERNAL_BKP_DIR="/opt/bt-platform/bt-platform/storage/backups/internal"
ENTERPRISE_BKP_DIR="/opt/bt-platform/bt-platform/storage/backups"

# Ponto de Montagem Local
MOUNT_POINT="/mnt/truenas/backups/BKP-MASTER"

echo "🚀 Iniciando Ciclo de Backup Mestre Dinâmico..."

# 2. Tenta Montar o TrueNAS automaticamente se os dados existirem
if [ ! -z "$BKP_SHARE" ] && [ ! -z "$BKP_USER" ]; then
    echo "📂 Tentando conectar ao TrueNAS ($BKP_HOST)..."
    mkdir -p "$MOUNT_POINT"
    # Tenta montar via CIFS (Samba)
    mount -t cifs -o username="$BKP_USER",password="$BKP_PASS",iocharset=utf8 "$BKP_SHARE" "$MOUNT_POINT" 2>/dev/null
fi

# 3. Gera o Dump do Banco MariaDB
DB_FILENAME="master_db_$DATE.sql.gz"
mysqldump -h 127.0.0.1 -u$DB_USER -p$DB_PASS $DB_NAME | gzip > "$INTERNAL_BKP_DIR/$DB_FILENAME"

if [ $? -eq 0 ]; then
    echo "✅ Dump MariaDB gerado com sucesso."

    # 4. Sincroniza para o TrueNAS (Se montado)
    if mountpoint -q "$MOUNT_POINT"; then
        echo "🚀 Enviando arquivos para o Storage Externo..."
        cp "$INTERNAL_BKP_DIR/$DB_FILENAME" "$MOUNT_POINT/"
        mkdir -p "$MOUNT_POINT/unidades"
        cp -r "$ENTERPRISE_BKP_DIR"/* "$MOUNT_POINT/unidades/" 2>/dev/null
        echo "✅ Tudo sincronizado fisicamente no TrueNAS."
    else
        echo "⚠️ TrueNAS não está montado. Backup mantido apenas na VM."
    fi

    # 5. Rotação (Configurável via Painel)
    find "$INTERNAL_BKP_DIR" -name "master_db_*.sql.gz" -mtime +$BKP_RETENTION -exec rm {} \;
    echo "✅ Rotação concluída (Retenção: $BKP_RETENTION dias)."
else
    echo "❌ Erro ao gerar backup. Verifique as credenciais do banco."
fi
