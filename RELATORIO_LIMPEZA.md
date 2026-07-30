# Relatório de Limpeza Controlada e Organização (Unidade Y)

Este relatório detalha a movimentação de arquivos e diretórios que não pertencem ao core operacional da BT Platform, visando a organização do projeto sem a exclusão de dados.

## 📁 Estrutura de Quarentena Criada
Todo o conteúdo não essencial foi movido para: `/opt/bt-platform/bt-platform/99_QUARENTENA/`

### 🛠️ Pasta: `public_tools/`
Contém scripts de diagnóstico, debug e utilitários que poluem a área pública do servidor.

| Arquivos Movidos | Motivo | Risco |
| :--- | :--- | :--- |
| `check_activities.php`, `check_db_final.php`, `check_schema.php`, etc. | Scripts de diagnóstico e validação. | Baixo |
| `debug_activation.php`, `debug_atividades.php` | Ferramentas de desenvolvimento. | Baixo |
| `migrate_activation.php`, `migrate_updates.php` | Scripts de migração de execução única. | Baixo |
| `*.bak`, `*.bkp` | Cópias de segurança manuais de arquivos PHP. | Baixo |
| `qrcode_*.png` | Imagens temporárias geradas para teste. | Baixo |
| `print_bridge.php` (redundante), `*.vbs` | Resíduos da estrutura Enterprise na Master. | Baixo |

### 📂 Pasta: `root_leftovers/`
Contém diretórios históricos, backups de versões antigas e materiais de setup.

| Itens Movidos | Motivo | Risco |
| :--- | :--- | :--- |
| `01_PLATAFORMA_MASTER/`, `03_DISTRIBUICAO_E_SETUP/` | Estruturas de pastas pré-migração. | Baixo |
| `99_BACKUPS_ANTIGOS/`, `BACKUP_FINANCEIRO_SEGURANCA/` | Diretórios de backup históricos. | Baixo |
| `bt-platform_v4/`, `bt-platform-old/`, `public-old/` | Versões legadas do código. | Baixo |
| `BT_Update_v*.zip` | Pacotes de atualização já publicados. | Baixo |
| `Ligar_Impressora_Local.bat`, `ajustar_servidor.sh` | Scripts utilitários temporários. | Baixo |
| `.idea/`, `.codex/` | Configurações de IDE. | Baixo |

---

## ✅ Itens Preservados (Core da Platform)
Os seguintes diretórios permanecem intocados na raiz da BT Platform:
- `app/`: Lógica de negócio Master.
- `core/`: Bibliotecas base.
- `config/`: Configurações globais.
- `public/`: Interface administrativa limpa.
- `storage/`: Armazenamento de APKs e atualizações.
- `database/`: Esquemas de banco de dados.
- `bootstrap/`: Inicialização do sistema.
- `04_MOBILE_E_APKS/`: Fontes dos aplicativos móveis.

## 🏁 Objetivo Alcançado
A BT Platform agora apresenta uma estrutura profissional e organizada, contendo apenas o necessário para sua execução. Todos os ativos históricos estão preservados e acessíveis via `99_QUARENTENA` para auditoria futura.

> [!NOTE]
> Nenhum código-fonte original foi alterado. Nenhum arquivo foi deletado.
